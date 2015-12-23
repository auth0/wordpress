<?php

class WP_Auth0_InitialSetup_AdminUser {

      protected $a0_options;

      public function __construct(WP_Auth0_Options $a0_options) {
          $this->a0_options = $a0_options;
      }

      public function render($step) {
        wp_enqueue_script( 'wpa0_lock', WP_Auth0_Options::Instance()->get('cdn_url'), 'jquery' );
        $client_id = $this->a0_options->get( 'client_id' );
        $domain = $this->a0_options->get( 'domain' );
        $current_user = wp_get_current_user();
        include WPA0_PLUGIN_DIR . 'templates/initial-setup/admin-creation.php';
      }

      public function callback() {

        if (!isset($_REQUEST['page']) || $_REQUEST['page'] != 'wpa0-setup') {
          return;
        }

        if (!isset($_REQUEST['auth0'])) {
          return;
        }

        $current_user = wp_get_current_user();

        $domain = $this->a0_options->get('domain');
        $jwt = $this->a0_options->get( 'auth0_app_token' );

        $data = array(
          'email' => $current_user->user_email,
          'password' => $_POST['admin-password'],
          'connection' => '',
          'email_verified' => true
        );

        WP_Auth0_Api_Client::create_user($domain, $jwt, $data);
      }

    }
