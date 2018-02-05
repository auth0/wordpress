<?php

class WP_Auth0_Admin_Generic {

	protected $options;
	protected $option_name;

	protected $actions_middlewares = array();

	public function __construct( WP_Auth0_Options_Generic $options ) {
		$this->options = $options;
		$this->option_name = $options->get_options_name();
	}

	protected function init_option_section( $sectionName, $id, $settings ) {
		$options_name = $this->option_name . '_' . strtolower( $id );

		add_settings_section(
			"wp_auth0_{$id}_settings_section",
			__( $sectionName, 'wp-auth0' ),
			array( $this, "render_{$id}_description" ),
			$options_name
		);

		foreach ( $settings as $setting ) {
			add_settings_field(
				$setting['id'],
				__( $setting['name'], 'wp-auth0' ),
				array( $this, $setting['function'] ),
				$options_name,
				"wp_auth0_{$id}_settings_section",
				array( 'label_for' => $setting['id'] )
			);
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

	protected function add_validation_error( $error ) {
		add_settings_error(
			$this->option_name,
			$this->option_name,
			$error,
			'error'
		);
	}

	protected function rule_validation( $old_options, $input, $key, $rule_name, $rule_script ) {
		$input[$key] = ( isset( $input[$key] ) ? $input[$key] : null );

		if ( ( $input[$key] !== null && $old_options[$key] === null ) || ( $input[$key] === null && $old_options[$key] !== null ) ) {

			try {

				$operations = new WP_Auth0_Api_Operations( $this->options );
				$input[$key] = $operations->toggle_rule ( $this->options->get( 'auth0_app_token' ), ( is_null( $input[$key] ) ? $old_options[$key] : null ), $rule_name, $rule_script );

			} catch ( Exception $e ) {
				$this->add_validation_error( $e->getMessage() );
				$input[$key] = null;
			}
		}

		return $input;
	}

	/**
	 * Output a stylized switch on the options page
	 *
	 * @param string $id - input id attribute
	 * @param string $input_name - input name attribute
	 * @param string|integer|float $value - input value attribute
	 * @param boolean $checked - is the switch checked or not?
	 */
	protected function render_a0_switch( $id, $input_name, $value, $checked ) {
		?>
    <div class="a0-switch">
      <input <?php echo checked( $checked ); ?> type="checkbox"
             name="<?php echo esc_attr( $this->option_name . '[' . $input_name . ']' ); ?>"
             id="<?php echo esc_attr( $id ); ?>"
             value="<?php echo $value; ?>" />
	    <label for="<?php echo esc_attr( $id ); ?>"></label>
    </div>
    <?php
	}
}