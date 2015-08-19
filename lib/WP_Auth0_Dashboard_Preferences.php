<?php

class WP_Auth0_Dashboard_Preferences {

  protected $dashboard_options;

  public function __construct(WP_Auth0_Dashboard_Options $dashboard_options) {
    $this->dashboard_options = $dashboard_options;
  }

  public function init() {
    add_action( 'admin_init', array( $this, 'init_admin' ) );
    add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue' ) );
  }

  public function admin_enqueue() {
		if ( ! isset( $_REQUEST['page'] ) || 'wpa0-dashboard' !== $_REQUEST['page'] ) {
			return;
		}

		wp_enqueue_media();;
		wp_enqueue_style( 'wpa0_admin', WPA0_PLUGIN_URL . 'assets/css/settings.css' );
		wp_enqueue_style( 'media' );
	}

  protected function init_option_section($sectionName, $settings) {
		$lowerName = strtolower( str_replace(' ', '_', $sectionName) );
		add_settings_section(
			"wp_auth0_{$lowerName}_settings_section",
			__( $sectionName, WPA0_LANG ),
			array( $this, "render_{$lowerName}_description" ),
			$this->dashboard_options->get_options_name()
		);

		foreach ( $settings as $setting ) {
			add_settings_field(
				$setting['id'],
				__( $setting['name'], WPA0_LANG ),
				array( $this, $setting['function'] ),
				$this->dashboard_options->get_options_name(),
				"wp_auth0_{$lowerName}_settings_section",
				array( 'label_for' => $setting['id'] )
			);
		}
	}

	public function init_admin() {

    $this->init_option_section( 'Chart types', array(

			array( 'id' => 'wpa0_chart_age_type', 'name' => 'Age', 'function' => 'render_age_chart_type' ),
			array( 'id' => 'wpa0_chart_idp_type', 'name' => 'Identity providers', 'function' => 'render_idp_chart_type' ),
			array( 'id' => 'wpa0_chart_gender_type', 'name' => 'Gender', 'function' => 'render_gender_chart_type' ),

		) );

    $options_name = $this->dashboard_options->get_options_name();
    register_setting( $options_name, $options_name, array( $this, 'input_validator' ) );
  }

  public function render_chart_types_description() {

  }

  public function render_age_chart_type() {
  	$v = $this->dashboard_options->get( 'chart_age_type' );

    ?>

    <input type="radio" name="<?php echo $this->dashboard_options->get_options_name() ?>[chart_age_type]" id="wpa0_auth0_age_chart_type_pie" value="pie" <?php echo checked( $v, 'pie', false ); ?>/>
  	<label for="wpa0_auth0_age_chart_type_pie"><?php echo __( 'Pie', WPA0_LANG ); ?></label>
    &nbsp;
    <input type="radio" name="<?php echo $this->dashboard_options->get_options_name() ?>[chart_age_type]" id="wpa0_auth0_age_chart_type_bar" value="bar" <?php echo checked( $v, 'bar', false ); ?>/>
    <label for="wpa0_auth0_age_chart_type_bars"><?php echo __( 'Bars', WPA0_LANG ); ?></label>

    <?php
  }

  public function render_idp_chart_type() {
  	$v = $this->dashboard_options->get( 'chart_idp_type' );

    ?>

  	<input type="radio" name="<?php echo $this->dashboard_options->get_options_name() ?>[chart_idp_type]" id="wpa0_auth0_idp_chart_type_pie" value="pie" <?php echo checked( $v, 'pie', false ); ?>/>
  	<label for="wpa0_auth0_idp_chart_type_pie"><?php echo __( 'Pie', WPA0_LANG ); ?></label>
    &nbsp;
    <input type="radio" name="<?php echo $this->dashboard_options->get_options_name() ?>[chart_idp_type]" id="wpa0_auth0_idp_chart_type_bar" value="bar" <?php echo checked( $v, 'bar', false ); ?>/>
    <label for="wpa0_auth0_idp_chart_type_bars"><?php echo __( 'Bars', WPA0_LANG ); ?></label>

    <?php
  }

  public function render_gender_chart_type() {
  	$v = $this->dashboard_options->get( 'chart_gender_type' );

    ?>

    <input type="radio" name="<?php echo $this->dashboard_options->get_options_name() ?>[chart_gender_type]" id="wpa0_auth0_gender_chart_type_pie" value="pie" <?php echo checked( $v, 'pie', false ); ?>/>
  	<label for="wpa0_auth0_gender_chart_type_pie"><?php echo __( 'Pie', WPA0_LANG ); ?></label>
    &nbsp;
    <input type="radio" name="<?php echo $this->dashboard_options->get_options_name() ?>[chart_gender_type]" id="wpa0_auth0_gender_chart_type_bar" value="bar" <?php echo checked( $v, 'bar', false ); ?>/>
    <label for="wpa0_auth0_gender_chart_type_bars"><?php echo __( 'Bars', WPA0_LANG ); ?></label>

    <?php
  }

  public function render_dashboard_preferences_page() {
      include WPA0_PLUGIN_DIR . 'templates/dashboard_settings.php';
  }

  protected function validate_chart_type($type) {
    $validChartTypes = array('pie','bar');

    if ( in_array( $type, $validChartTypes ) ) {
        return $type;
    }

    return $validChartTypes[0];
  }

  public function input_validator( $input ){

  	$input['chart_gender_type'] = $this->validate_chart_type( $input['chart_gender_type'] );
  	$input['chart_idp_type'] = $this->validate_chart_type( $input['chart_idp_type'] );
  	$input['chart_age_type'] = $this->validate_chart_type( $input['chart_age_type'] );

    return $input;
  }
}
