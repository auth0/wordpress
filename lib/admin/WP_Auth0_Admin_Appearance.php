<?php

class WP_Auth0_Admin_Appearance extends WP_Auth0_Admin_Generic {

	// TODO: Deprecate
	const APPEARANCE_DESCRIPTION = 'Settings related to the way the login widget is shown.';

	protected $_description;

	protected $actions_middlewares = array(
		'basic_validation',
	);

	/**
	 * WP_Auth0_Admin_Appearance constructor.
	 *
	 * @param WP_Auth0_Options_Generic $options
	 */
	public function __construct( WP_Auth0_Options_Generic $options ) {
		parent::__construct( $options );
		$this->_description = __( 'Settings related to the way the login widget is shown.', 'wp-auth0' );
	}

	/**
	 * All settings in the Appearance tab
	 *
	 * @see \WP_Auth0_Admin::init_admin
	 * @see \WP_Auth0_Admin_Generic::init_option_section
	 */
	public function init() {
		$options = array(
			array( 'name' => __( 'Icon URL', 'wp-auth0' ), 'opt' => 'icon_url',
				'id' => 'wpa0_icon_url', 'function' => 'render_icon_url' ),
			array( 'name' => __( 'Form Title', 'wp-auth0' ), 'opt' => 'form_title',
				'id' => 'wpa0_form_title', 'function' => 'render_form_title' ),
			array( 'name' => __( 'Big Social Buttons', 'wp-auth0' ), 'opt' => 'social_big_buttons',
				'id' => 'wpa0_social_big_buttons', 'function' => 'render_social_big_buttons' ),
			array( 'name' => __( 'Enable Gravatar Integration', 'wp-auth0' ), 'opt' => 'gravatar',
				'id' => 'wpa0_gravatar', 'function' => 'render_gravatar' ),
			array( 'name' => __( 'Login Form CSS', 'wp-auth0' ), 'opt' => 'custom_css',
				'id' => 'wpa0_custom_css', 'function' => 'render_custom_css' ),
			array( 'name' => __( 'Login Form JS', 'wp-auth0' ), 'opt' => 'custom_js',
				'id' => 'wpa0_custom_js', 'function' => 'render_custom_js' ),
			array( 'name' => __( 'Login Name Style', 'wp-auth0' ), 'opt' => 'username_style',
				'id' => 'wpa0_username_style', 'function' => 'render_username_style' ),
			array( 'name' => __( 'Primary Color', 'wp-auth0' ), 'opt' => 'primary_color',
				'id' => 'wpa0_primary_color', 'function' => 'render_primary_color' ),
			array( 'name' => __( 'Language', 'wp-auth0' ), 'opt' => 'language',
				'id' => 'wpa0_language', 'function' => 'render_language' ),
			array( 'name' => __( 'Language Dictionary', 'wp-auth0' ), 'opt' => 'language_dictionary',
				'id' => 'wpa0_language_dictionary', 'function' => 'render_language_dictionary' ),
		);
		$this->init_option_section( '', 'appearance', $options );
	}

	public function render_form_title() {
		$v = $this->options->get( 'form_title' );
?>
      <input type="text" name="<?php echo $this->options->get_options_name(); ?>[form_title]" id="wpa0_form_title" value="<?php echo esc_attr( $v ); ?>"/>
      <div class="subelement">
        <span class="description"><?php echo __( 'This is the title for the login widget', 'wp-auth0' ); ?></span>
      </div>
    <?php
	}

  public function render_language() {
    $v = $this->options->get( 'language' );
?>
      <input type="text" name="<?php echo $this->options->get_options_name(); ?>[language]" id="wpa0_language" value="<?php echo esc_attr( $v ); ?>" />
      <div class="subelement">
        <span class="description"><?php echo __( 'This is the widget\'s language param.', 'wp-auth0' ); ?><a target="_blank" href="https://github.com/auth0/lock#ui-options"><?php echo __( 'More info', 'wp-auth0' ); ?></a></span>
      </div>
    <?php
  }

	public function render_primary_color() {
    $v = $this->options->get( 'primary_color' );
?>
      <input type="text" name="<?php echo $this->options->get_options_name(); ?>[primary_color]" id="wpa0_primary_color" value="<?php echo esc_attr( $v ); ?>" />
      <div class="subelement">
        <span class="description"><?php echo __( 'The primary color for Lock', 'wp-auth0' ); ?></span>
      </div>
    <?php
  }

  public function render_language_dictionary() {
    $v = $this->options->get( 'language_dictionary' );
?>
      <textarea name="<?php echo $this->options->get_options_name(); ?>[language_dictionary]" id="wpa0_language_dictionary"><?php echo esc_attr( $v ); ?></textarea>
      <div class="subelement">
        <span class="description"><?php echo __( 'This is the widget\'s languageDictionary param.', 'wp-auth0' ); ?><a target="_blank" href="https://github.com/auth0/lock#ui-options"><?php echo __( 'More info', 'wp-auth0' ); ?></a></span>
      </div>
    <?php
  }

	public function render_custom_css() {
		$v = $this->options->get( 'custom_css' );
?>
      <textarea name="<?php echo $this->options->get_options_name(); ?>[custom_css]" id="wpa0_custom_css"><?php echo esc_attr( $v ); ?></textarea>
      <div class="subelement">
        <span class="description"><?php echo __( 'This should be a valid CSS to customize the Auth0 login widget. ', 'wp-auth0' ); ?><a target="_blank" href="https://github.com/auth0/wp-auth0#can-i-customize-the-login-widget"><?php echo __( 'More info', 'wp-auth0' ); ?></a></span>
      </div>
    <?php
	}

	public function render_custom_js() {
		$v = $this->options->get( 'custom_js' );
?>
      <textarea name="<?php echo $this->options->get_options_name(); ?>[custom_js]" id="wpa0_custom_js"><?php echo esc_attr( $v ); ?></textarea>
      <div class="subelement">
        <span class="description"><?php echo __( 'This should be a valid JS to customize the Auth0 login widget to, for example, add custom buttons. ', 'wp-auth0' ); ?><a target="_blank" href="https://auth0.com/docs/hrd#option-3-adding-custom-buttons-to-lock"><?php echo __( 'More info', 'wp-auth0' ); ?></a></span>
      </div>
    <?php
	}

	public function render_username_style() {
		$v = $this->options->get( 'username_style' );
?>
      <input type="radio" name="<?php echo $this->options->get_options_name(); ?>[username_style]" id="wpa0_username_style_auto" value="" <?php echo esc_attr( $v ) == '' ? 'checked="true"' : ''; ?> />
      <label for="wpa0_username_style_auto"><?php echo __( 'Auto', 'wp-auth0' ); ?></label>

      <input type="radio" name="<?php echo $this->options->get_options_name(); ?>[username_style]" id="wpa0_username_style_email" value="email" <?php echo esc_attr( $v ) == 'email' ? 'checked="true"' : ''; ?> />
      <label for="wpa0_username_style_email"><?php echo __( 'Email', 'wp-auth0' ); ?></label>

      <input type="radio" name="<?php echo $this->options->get_options_name(); ?>[username_style]" id="wpa0_username_style_username" value="username" <?php echo esc_attr( $v ) == 'username' ? 'checked="true"' : ''; ?> />
      <label for="wpa0_username_style_username"><?php echo __( 'Username', 'wp-auth0' ); ?></label>

      <div class="subelement">
        <span class="description">
          <?php echo __( 'If you want to allow the user to use either email or password, set it to Auto.', 'wp-auth0' ); ?>
          <a target="_blank" href="https://auth0.com/docs/libraries/lock/customization#usernamestyle-string-"><?php echo __( 'More info', 'wp-auth0' ); ?></a>
        </span>
      </div>
    <?php
	}

	public function render_social_big_buttons() {
		$v = absint( $this->options->get( 'social_big_buttons' ) );

		echo $this->render_a0_switch( "wpa0_social_big_buttons", "social_big_buttons", 1, 1 == $v );
	}

	public function render_gravatar() {
		$v = absint( $this->options->get( 'gravatar' ) );

		echo $this->render_a0_switch( "wpa0_gravatar", "gravatar", 1, 1 == $v );
?>

      <div class="subelement">
        <span class="description">
          <?php echo __( 'Read more about the gravatar integration ', 'wp-auth0' ); ?>
          <a target="_blank" href="https://auth0.com/docs/libraries/lock/customization#gravatar-boolean-"><?php echo __( 'HERE', 'wp-auth0' ); ?></a></span>
      </div>
    <?php
	}

	public function render_icon_url() {
		$v = $this->options->get( 'icon_url' );
?>
      <input type="text" name="<?php echo $this->options->get_options_name(); ?>[icon_url]" id="wpa0_icon_url" value="<?php echo esc_attr( $v ); ?>"/>
      <a target="_blank" href="javascript:void(0);" id="wpa0_choose_icon" class="button-secondary"><?php echo __( 'Choose Icon', 'wp-auth0' ); ?></a>
      <div class="subelement">
        <span class="description"><?php echo __( 'The icon should be 32x32 pixels!', 'wp-auth0' ); ?></span>
      </div>
    <?php
	}
	// TODO: Deprecate
	public function render_appearance_description() {
?>

    <p class=\"a0-step-text\"><?php echo self::APPEARANCE_DESCRIPTION; ?></p>

    <?php
	}

	public function basic_validation( $old_options, $input ) {
    $input['form_title'] = sanitize_text_field( $input['form_title'] );
		$input['icon_url'] = esc_url( $input['icon_url'], array( 'http', 'https' ) );
		$input['social_big_buttons'] = ( isset( $input['social_big_buttons'] ) ? $input['social_big_buttons'] : 0 );
		$input['gravatar'] = ( isset( $input['gravatar'] ) ? $input['gravatar'] : 0 );
    $input['language'] = sanitize_text_field( $input['language'] );
    $input['primary_color'] = sanitize_text_field( $input['primary_color'] );

    if ( trim( $input['language_dictionary'] ) !== '' ) {
      if ( json_decode( $input['language_dictionary'] ) === null ) {
        $error = __( 'The language dictionary parameter should be a valid json object.', 'wp-auth0' );
        $this->add_validation_error( $error );
        $input['language'] = $old_options['language'];
      }
    }

		// if ( trim( $input['extra_conf'] ) !== '' ) {
		//  if ( json_decode( $input['extra_conf'] ) === null ) {
		//    $error = __( 'The Extra settings parameter should be a valid json object.', 'wp-auth0' );
		//    $this->add_validation_error( $error );
		//  }
		// }

		return $input;
	}


}
