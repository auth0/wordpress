<?php

class WP_Auth0_InitialSetup_Connections {

      protected $a0_options;
      protected $providers = array(
        array('provider' => 'facebook', 'name' => 'Facebook', 'options' => array(
    			"public_profile" => true,
    			"email" => true,
    			"user_birthday" => true,
    			"publish_actions" => true,
    		)),
        array('provider' => 'twitter', 'name' => 'Twitter', 'options' => array(
    			"profile" => true,
    		)),
        array('provider' => 'google-oauth2', 'name' => 'Google +', 'options' => array(
    			"google_plus" => true,
    			"email" => true,
          "profile" => true,
    		))
      );

      public function __construct(WP_Auth0_Options $a0_options) {
          $this->a0_options = $a0_options;
      }

      public function render($step) {
        $social_connections = array();

        foreach ($this->providers as $provider) {
          $social_connections[] = $this->get_social_connection($provider['provider'], $provider['name']);
        }

        include WPA0_PLUGIN_DIR . 'templates/initial-setup/connections.php';
      }

      protected function get_social_connection($provider, $name) {
        return array(
          'name' => $name,
          'provider' => $provider,
          'status' => $this->a0_options->get( "social_{$provider}" ),
          'key' => $this->a0_options->get( "social_{$provider}_key" ),
      		'secret' => $this->a0_options->get( "social_{$provider}_secret" ),
        );
      }

      public function callback() {
        $input = array();
        $old_input = array();

        $operations = new WP_Auth0_Api_Operations($this->a0_options);

        foreach ($this->providers as $provider) {
          $provider_name = $provider['provider'];

          $old_input["social_{$provider_name}"] = $this->a0_options->get( "social_{$provider_name}" );
          $old_input["social_{$provider_name}_key"] = $this->a0_options->get( "social_{$provider_name}_key" );
          $old_input["social_{$provider_name}_secret"] = $this->a0_options->get( "social_{$provider_name}_secret" );

          if (isset($_REQUEST["social_{$provider_name}"])) {
            $input["social_{$provider_name}"] = $_REQUEST["social_{$provider_name}"];
            $input["social_{$provider_name}_key"] = $_REQUEST["social_{$provider_name}_key"];
            $input["social_{$provider_name}_secret"] = $_REQUEST["social_{$provider_name}_secret"];
          }

          try {
            $input = $operations->social_validation($this->a0_options->get( 'auth0_app_token' ), $old_input, $input, $provider_name, $provider['options'] );
          } catch (Exception $e) {
            die($e->getMessage());
          }
        }

        foreach ($input as $key => $value) {
          $this->a0_options->set( $key, $value );
        }

        wp_redirect( admin_url( 'admin.php?page=wpa0-setup&step=5' ) );
      }

      public function add_validation_error($error) {
        wp_redirect( admin_url( 'admin.php?page=wpa0-setup&step=5&error=' . urlencode('There was an error setting up your connections.') ) );
        exit;
      }
}
