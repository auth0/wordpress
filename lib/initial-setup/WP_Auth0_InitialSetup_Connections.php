<?php

class WP_Auth0_InitialSetup_Connections {

      protected $a0_options;

      public function __construct(WP_Auth0_Options $a0_options) {
          $this->a0_options = $a0_options;
      }

      public function render($step) {
        include WPA0_PLUGIN_DIR . 'templates/initial-setup/connections.php';
      }

      public function callback() {
        $operations = new WP_Auth0_Api_Operations($this->a0_options);
        $migration_connection_id = $operations->social_validation($app_token, $migration_token);
        $this->a0_options->set( 'migration_connection_id', $migration_connection_id );
      }

    }
