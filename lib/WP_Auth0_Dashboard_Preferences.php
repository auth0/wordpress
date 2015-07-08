<?php

class WP_Auth0_Dashboard_Preferences {

    public static function init() {
        add_action( 'admin_init', array( __CLASS__, 'init_admin' ) );
    }

    protected static function init_option_section($sectionName, $settings) {
		$lowerName = strtolower( str_replace(' ', '_', $sectionName) );
		add_settings_section(
			"wp_auth0_{$lowerName}_settings_section",
			__( $sectionName, WPA0_LANG ),
			array( __CLASS__, "render_{$lowerName}_description" ),
			WP_Auth0_Dashboard_Options::Instance()->get_options_name()
		);

		foreach ( $settings as $setting ) {
			add_settings_field(
				$setting['id'],
				__( $setting['name'], WPA0_LANG ),
				array( __CLASS__, $setting['function'] ),
				WP_Auth0_Dashboard_Options::Instance()->get_options_name(),
				"wp_auth0_{$lowerName}_settings_section",
				array( 'label_for' => $setting['id'] )
			);
		}
	}

	public static function init_admin() {

        if ( ! isset( $_REQUEST['page'] ) || 'wpa0-dashboard' !== $_REQUEST['page'] ) {
			return;
		}

        self::init_option_section( 'Age chart', array(

			array( 'id' => 'wpa0_chart_age_type', 'name' => 'Chart type', 'function' => 'render_age_chart_type' ),

		) );

        self::init_option_section( 'Identity providers chart', array(

			array( 'id' => 'wpa0_chart_idp_type', 'name' => 'Chart type', 'function' => 'render_idp_chart_type' ),

		) );

        self::init_option_section( 'Gender chart', array(

			array( 'id' => 'wpa0_chart_gender_type', 'name' => 'Chart type', 'function' => 'render_gender_chart_type' ),

		) );

        $options_name = WP_Auth0_Dashboard_Options::Instance()->get_options_name();
        register_setting( $options_name, $options_name, array( __CLASS__, 'input_validator' ) );
    }

    public static function render_age_chart_description() {

    }

    public static function render_age_chart_type() {
    	$v = WP_Auth0_Dashboard_Options::Instance()->get( 'chart_age_type' );
    	echo '<input type="radio" name="' . WP_Auth0_Dashboard_Options::Instance()->get_options_name() . '[age_chart_type]" id="wpa0_auth0_age_chart_type_pie" value="pie" ' . checked( $v, 'pie', false ) . '/>';
    	echo '<label for="wpa0_auth0_age_chart_type_pie">' . __( 'Pie', WPA0_LANG ) . '</label>';
        echo ' ';
        echo '<input type="radio" name="' . WP_Auth0_Dashboard_Options::Instance()->get_options_name() . '[age_chart_type]" id="wpa0_auth0_age_chart_type_bars" value="bars" ' . checked( $v, 'bars', false ) . '/>';
        echo '<label for="wpa0_auth0_age_chart_type_bars">' . __( 'Bars', WPA0_LANG ) . '</label>';
    }

    public static function render_identity_providers_chart_description() {

    }

    public static function render_idp_chart_type() {
    	$v = WP_Auth0_Dashboard_Options::Instance()->get( 'chart_idp_type' );
    	echo '<input type="radio" name="' . WP_Auth0_Dashboard_Options::Instance()->get_options_name() . '[idp_chart_type]" id="wpa0_auth0_idp_chart_type_pie" value="pie" ' . checked( $v, 'pie', false ) . '/>';
    	echo '<label for="wpa0_auth0_idp_chart_type_pie">' . __( 'Pie', WPA0_LANG ) . '</label>';
        echo ' ';
        echo '<input type="radio" name="' . WP_Auth0_Dashboard_Options::Instance()->get_options_name() . '[idp_chart_type]" id="wpa0_auth0_idp_chart_type_bars" value="bars" ' . checked( $v, 'bars', false ) . '/>';
        echo '<label for="wpa0_auth0_idp_chart_type_bars">' . __( 'Bars', WPA0_LANG ) . '</label>';
    }

    public static function render_gender_chart_description() {

    }

    public static function render_gender_chart_type() {
    	$v = WP_Auth0_Dashboard_Options::Instance()->get( 'chart_gender_type' );
    	echo '<input type="radio" name="' . WP_Auth0_Dashboard_Options::Instance()->get_options_name() . '[gender_chart_type]" id="wpa0_auth0_gender_chart_type_pie" value="pie" ' . checked( $v, 'pie', false ) . '/>';
    	echo '<label for="wpa0_auth0_gender_chart_type_pie">' . __( 'Pie', WPA0_LANG ) . '</label>';
        echo ' ';
        echo '<input type="radio" name="' . WP_Auth0_Dashboard_Options::Instance()->get_options_name() . '[gender_chart_type]" id="wpa0_auth0_gender_chart_type_bars" value="bars" ' . checked( $v, 'bars', false ) . '/>';
        echo '<label for="wpa0_auth0_gender_chart_type_bars">' . __( 'Bars', WPA0_LANG ) . '</label>';
    }

    public static function render_dashboard_preferences_page() {
        include WPA0_PLUGIN_DIR . 'templates/dashboard_settings.php';
    }

    protected static function validate_chart_type($type) {
        $validChartTypes = array('pie','bars');

        if ( in_array( $type, $validChartTypes ) ) {
            return $type;
        }

        return $validChartTypes[0];
    }

    public static function input_validator( $input ){
        $validChartTypes = array('pie','bars');

		$input['chart_gender_type'] = self::validate_chart_type( $input['chart_gender_type'] );
		$input['chart_idp_type'] = self::validate_chart_type( $input['chart_idp_type'] );
		$input['chart_age_type'] = self::validate_chart_type( $input['chart_age_type'] );

        return $input;
    }
}
