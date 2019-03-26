<?php

class WP_Auth0_Admin_Generic {

	protected $options;

	protected $_option_name;

	protected $_description;

	protected $_textarea_rows = 4;

	protected $actions_middlewares = array();

	/**
	 * WP_Auth0_Admin_Generic constructor.
	 *
	 * @param WP_Auth0_Options_Generic $options
	 */
	public function __construct( WP_Auth0_Options_Generic $options ) {
		$this->options      = $options;
		$this->_option_name = $options->get_options_name();
	}

	/**
	 * Add settings section and fields for each of the settings screen
	 *
	 * @param string $section_name - name used for the settings section (usually empty)
	 * @param string $id - settings screen id
	 * @param array  $options - array of settings fields
	 */
	protected function init_option_section( $section_name, $id, $options ) {
		$options_name = $this->_option_name . '_' . strtolower( $id );
		$section_id   = "wp_auth0_{$id}_settings_section";

		add_settings_section(
			$section_id,
			$section_name,
			array( $this, 'render_description' ),
			$options_name
		);

		$options = apply_filters( 'auth0_settings_fields', $options, $id );

		foreach ( $options as $setting ) {
			$callback = function_exists( $setting['function'] )
				? $setting['function']
				: array( $this, $setting['function'] );

			add_settings_field(
				$setting['id'],
				$setting['name'],
				$callback,
				$options_name,
				$section_id,
				array(
					'label_for' => $setting['id'],
					'opt_name'  => isset( $setting['opt'] ) ? $setting['opt'] : null,
				)
			);
		}
	}

	/**
	 * Render description at the top of the settings block
	 */
	public function render_description() {
		if ( ! empty( $this->_description ) ) {
			printf( '<p class="a0-step-text">%s</p>', $this->_description );
		}
	}

	public function input_validator( $input, $old_options = null ) {
		if ( empty( $old_options ) ) {
			$old_options = $this->options->get_options();
		}

		foreach ( $this->actions_middlewares as $action ) {
			$input = $this->$action( $old_options, $input );
		}

		return $input;
	}

	/**
	 * Wrapper for add_settings_error to output error message on settings change failure.
	 *
	 * @param string $error - Translated error message.
	 * @param string $type - Notice type, "error" by default or "updated".
	 */
	protected function add_validation_error( $error, $type = 'error' ) {
		add_settings_error(
			$this->_option_name,
			$this->_option_name,
			$error,
			$type
		);
	}

	/**
	 * TODO: Deprecate
	 *
	 * @codeCoverageIgnore - To be deprecated
	 */
	protected function rule_validation( $old_options, $input, $key, $rule_name, $rule_script ) {
		$input[ $key ] = ( isset( $input[ $key ] ) ? $input[ $key ] : null );

		if ( ( $input[ $key ] !== null && $old_options[ $key ] === null ) || ( $input[ $key ] === null && $old_options[ $key ] !== null ) ) {

			try {

				$operations    = new WP_Auth0_Api_Operations( $this->options );
				$input[ $key ] = $operations->toggle_rule(
					$this->options->get( 'auth0_app_token' ),
					( is_null( $input[ $key ] ) ? $old_options[ $key ] : null ),
					$rule_name,
					$rule_script
				);

			} catch ( Exception $e ) {
				$this->add_validation_error( $e->getMessage() );
				$input[ $key ] = null;
			}
		}

		return $input;
	}

	/**
	 * Output a stylized switch on the options page
	 *
	 * @param string $id - input id attribute
	 * @param string $input_name - input name attribute
	 * @param string $expand_id - id of a field that should be hidden until this switch is active
	 */
	protected function render_switch( $id, $input_name, $expand_id = '' ) {
		$value = $this->options->get( $input_name );
		if ( $field_is_const = $this->options->has_constant_val( $input_name ) ) {
			$this->render_const_notice( $input_name );
		}
		printf(
			'<div class="a0-switch"><input type="checkbox" name="%s[%s]" id="%s" data-expand="%s" value="1" %s %s>
			<label for="%s"></label></div>',
			esc_attr( $this->_option_name ),
			esc_attr( $input_name ),
			esc_attr( $id ),
			! empty( $expand_id ) ? esc_attr( $expand_id ) : '',
			checked( empty( $value ), false, false ),
			$field_is_const ? 'disabled' : '',
			esc_attr( $id )
		);
	}

	/**
	 * Output a stylized text field on the options page
	 *
	 * @param string $id - input id attribute
	 * @param string $input_name - input name attribute
	 * @param string $type - input type attribute
	 * @param string $placeholder - input placeholder
	 * @param string $style - inline CSS
	 */
	protected function render_text_field( $id, $input_name, $type = 'text', $placeholder = '', $style = '' ) {
		$value = $this->options->get( $input_name );

		// Secure fields are not output by default; validation keeps last value if a new one is not entered
		if ( 'password' === $type ) {
			$placeholder = ! empty( $value ) ? 'Not visible' : '';
			$value       = '';
		}
		if ( $field_is_const = $this->options->has_constant_val( $input_name ) ) {
			$this->render_const_notice( $input_name );
		}
		printf(
			'<input type="%s" name="%s[%s]" id="%s" value="%s" placeholder="%s" style="%s" %s>',
			esc_attr( $type ),
			esc_attr( $this->_option_name ),
			esc_attr( $input_name ),
			esc_attr( $id ),
			esc_attr( $value ),
			$placeholder ? esc_attr( $placeholder ) : '',
			$style ? esc_attr( $style ) : '',
			$field_is_const ? 'disabled' : ''
		);
	}

	/**
	 * Output a stylized textarea field on the options page
	 *
	 * @param string $id - input id attribute
	 * @param string $input_name - input name attribute
	 */
	protected function render_textarea_field( $id, $input_name ) {
		$value = $this->options->get( $input_name );
		if ( $field_is_const = $this->options->has_constant_val( $input_name ) ) {
			$this->render_const_notice( $input_name );
		}
		printf(
			'<textarea name="%s[%s]" id="%s" rows="%d" class="code" %s>%s</textarea>',
			esc_attr( $this->_option_name ),
			esc_attr( $input_name ),
			esc_attr( $id ),
			$this->_textarea_rows,
			$field_is_const ? 'disabled' : '',
			esc_textarea( $value )
		);
	}

	/**
	 * Output one or many radio buttons associated to the same option key.
	 *
	 * @param array            $buttons - Array of buttons to output; items can be strings or arrays with "label" and "value" keys.
	 * @param string           $id - Input ID attribute.
	 * @param string           $input_name - Option name saved to the options array.
	 * @param int|float|string $curr_value - Current option value.
	 */
	protected function render_radio_buttons( array $buttons, $id, $input_name, $curr_value ) {
		if ( $field_is_const = $this->options->has_constant_val( $input_name ) ) {
			$this->render_const_notice( $input_name );
		}
		foreach ( $buttons as $index => $button ) {
			$id_attr = $id . '_' . $index;
			$label   = is_array( $button ) ? $button['label'] : ucfirst( $button );
			$value   = is_array( $button ) ? $button['value'] : $button;
			printf(
				'<label for="%s"><input type="radio" name="%s[%s]" id="%s" value="%s" %s %s>&nbsp;%s</label>',
				esc_attr( $id_attr ),
				esc_attr( $this->_option_name ),
				esc_attr( $input_name ),
				esc_attr( $id_attr ),
				esc_attr( $value ),
				checked( $value === $curr_value, true, false ),
				$field_is_const ? 'disabled' : '',
				sanitize_text_field( $label )
			);
		}
	}

	/**
	 * Output a field description
	 *
	 * @param string $text - description text to display
	 */
	protected function render_field_description( $text ) {
		$period = ! in_array( $text[ strlen( $text ) - 1 ], array( '.', ':' ) ) ? '.' : '';
		printf( '<div class="subelement"><span class="description">%s%s</span></div>', $text, $period );
	}

	/**
	 * Check if the setting is provided by a constant and indicate.
	 *
	 * @param string $input_name - Input name for the field, used as option key.
	 */
	protected function render_const_notice( $input_name ) {
		printf(
			'<p class="const-setting-notice"><span class="description">%s <code>%s</code></span></p>',
			__( 'Value is set in the constant ', 'wp-auth0' ),
			$this->options->get_constant_name( $input_name )
		);
	}

	/**
	 * Output translated dashboard HTML link
	 *
	 * @param string $path - dashboard sub-section, if any
	 *
	 * @return string
	 */
	protected function get_dashboard_link( $path = '' ) {
		return sprintf(
			'<a href="https://manage.auth0.com/#/%s" target="_blank">%s</a>',
			$path,
			__( 'Auth0 dashboard', 'wp-auth0' )
		);
	}

	/**
	 * Output a docs HTML link
	 *
	 * @param string $path - docs sub-page, if any
	 * @param string $text - link text, should be translated before passing
	 *
	 * @return string
	 */
	protected function get_docs_link( $path, $text = '' ) {
		$path = '/' === $path[0] ? substr( $path, 1 ) : $path;
		$text = empty( $text ) ? __( 'here', 'wp-auth0' ) : sanitize_text_field( $text );
		return sprintf( '<a href="https://auth0.com/docs/%s" target="_blank">%s</a>', $path, $text );
	}

	/**
	 * @deprecated - 3.6.0, use WP_Auth0_Admin_Generic::render_switch() instead
	 *
	 * @codeCoverageIgnore - Deprecated
	 */
	protected function render_a0_switch( $id, $name, $value, $checked ) {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );
		$this->render_switch( $id, $name );
	}
}
