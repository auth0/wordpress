<?php

class WP_Auth0_Routes {

  protected $a0_options;

  public function __construct(WP_Auth0_Options $a0_options) {
    $this->a0_options = $a0_options;
  }

  public function init() {
      add_action('parse_request', array($this, 'custom_requests'));
  }

  public function setup_rewrites($force_ws =false) {
		add_rewrite_tag( '%auth0%', '([^&]+)' );
		add_rewrite_tag( '%code%', '([^&]+)' );
		add_rewrite_tag( '%state%', '([^&]+)' );
		add_rewrite_tag( '%auth0_error%', '([^&]+)' );

		add_rewrite_rule( '^auth0', 'index.php?auth0=1', 'top' );
		add_rewrite_rule( '^oauth2-config?', 'index.php?a0_action=oauth2-config', 'bottom' );

    if ( $force_ws || $this->a0_options->get('migration_ws') ) {
      add_rewrite_rule( '^migration-ws?', 'index.php?a0_action=migration-ws', 'top' );
    }
	}

  public function custom_requests ( $wp ) {

    $page = null;

    if (isset($wp->query_vars['a0_action'])) {
      $page = $wp->query_vars['a0_action'];
    }

    if (isset($wp->query_vars['pagename'])) {
      $page = $wp->query_vars['pagename'];
    }

    if( ! empty($page) ) {
        switch ($page) {
            case 'oauth2-config': $this->oauth2_config(); exit;
            case 'migration-ws': $this->migration_ws(); exit;
        }
    }
  }

  protected function migration_ws() {
    if ( $this->a0_options->get('migration_ws') == 0 ) return;

    $username = $_POST['username'];
    $password = $_POST['password'];

    $user = wp_authenticate($username, $password);

    if ($user instanceof WP_Error) {
      WP_Auth0_ErrorManager::insert_auth0_error( 'migration_ws',$user );
      $user = null;
    }

    $user = apply_filters( 'auth0_migration_ws_authenticated', $user );

    echo json_encode($user);
    exit;

  }
  protected function oauth2_config() {

      $callback_url = admin_url( 'admin.php?page=wpa0-setup&step=2' );

      echo json_encode(array(
          'redirect_uris' => array(
              $callback_url
          )
      ));
      exit;
  }
}
