<?php

class WP_Auth0_InitialSetup_Migration {

    protected $a0_options;

    public function __construct(WP_Auth0_Options $a0_options) {
        $this->a0_options = $a0_options;
    }

    public function render($step) {
      $migration_ws = $this->a0_options->get('migration_ws');
      $token = $this->a0_options->get('migration_token');
      $token_id = $this->a0_options->get('migration_token_id');

      if (empty($token) || empty($token_id)) {
        $secret = $this->a0_options->get( 'client_secret' );
        $token_id = uniqid();
        $token = JWT::encode(array('scope' => 'migration_ws', 'jti' => $token_id), JWT::urlsafeB64Decode( $secret ));
      }

      include WPA0_PLUGIN_DIR . 'templates/initial-setup/data-migration.php';
    }

    public function callback() {
      $migration_ws = (isset($_REQUEST['migration_ws']) ? $_REQUEST['migration_ws'] : false);
      $migration_token = (isset($_REQUEST['migration_token']) ? $_REQUEST['migration_token'] : null);
      $migration_token_id = (isset($_REQUEST['migration_token_id']) ? $_REQUEST['migration_token_id'] : null);

      $app_token = $this->a0_options->get( 'auth0_app_token' );

      $this->a0_options->set('migration_ws', $migration_ws);
      $this->a0_options->set('migration_token', $migration_token);
      $this->a0_options->set('migration_token_id', $migration_token_id);

      if ($migration_ws) {
        $operations = new WP_Auth0_Api_Operations($this->a0_options);
        $migration_connection_id = $operations->enable_users_migration($app_token, $migration_token);
        $this->a0_options->set( 'migration_connection_id', $migration_connection_id );
      }

      wp_redirect( admin_url( 'admin.php?page=wpa0-setup&step=3' ) );
      exit;
    }

  }
