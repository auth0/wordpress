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
		add_rewrite_tag( '%a0_action%', '([^&]+)' );

		add_rewrite_rule( '^auth0', 'index.php?auth0=1', 'top' );
		add_rewrite_rule( '^\.well-known/oauth2-client-configuration', 'index.php?a0_action=oauth2-config', 'top' );

    if ( $force_ws || $this->a0_options->get('migration_ws') ) {
      add_rewrite_rule( '^migration-ws-login', 'index.php?a0_action=migration-ws-login', 'top' );
      add_rewrite_rule( '^migration-ws-get-user', 'index.php?a0_action=migration-ws-get-user', 'top' );
    }
	}

  public function custom_requests ( $wp ) {
    $page = null;

    if (isset($wp->query_vars['a0_action'])) {
      $page = $wp->query_vars['a0_action'];
    }

    if ($page === null && isset($wp->query_vars['pagename'])) {
      $page = $wp->query_vars['pagename'];
    }

    if( ! empty($page) ) {
        switch ($page) {
            case 'oauth2-config': $this->oauth2_config(); exit;
            case 'migration-ws-login': $this->migration_ws_login(); exit;
            case 'migration-ws-get-user': $this->migration_ws_get_user(); exit;
        }
    }
  }

  protected function getAuthorizationHeader() {
      $authorization = false;
      if (function_exists('getallheaders'))
      {
          $headers = getallheaders();
          if (isset($headers['Authorization'])) {
              $authorization = $headers['Authorization'];
          }
      }
      elseif (isset($_SERVER["Authorization"])){
          $authorization = $_SERVER["Authorization"];
      }
      return $authorization;
  }

  protected function migration_ws_login() {

    if ( $this->a0_options->get('migration_ws') == 0 ) return;

    $authorization = $this->getAuthorizationHeader();
    $authorization = trim(str_replace('Bearer ', '', $authorization));

    $secret = $this->a0_options->get( 'client_secret' );
    $token_id = $this->a0_options->get( 'migration_token_id' );

    $user = null;

    try {
        if (empty($authorization)) {
            throw new Exception('Unauthorized');
        }

        $token = JWT::decode($authorization, JWT::urlsafeB64Decode( $secret ), array('HS256'));

        if ($token->jti != $token_id) {
            throw new Exception('Invalid token id');
        }

        if (!isset($_POST['username'])) {
          throw new Exception('username is required');
        }

        if (!isset($_POST['password'])) {
          throw new Exception('password is required');
        }

        $username = $_POST['username'];
        $password = $_POST['password'];

        $user = wp_authenticate($username, $password);

        if ($user instanceof WP_Error) {
          WP_Auth0_ErrorManager::insert_auth0_error( 'migration_ws_login',$user );
          $user = array('error' => 'invalid credentials');
        } else {
          if ($user instanceof WP_User) {
            unset($user->data->user_pass);
          }

          $user = apply_filters( 'auth0_migration_ws_authenticated', $user );
        }
    }
    catch(Exception $e) {
        WP_Auth0_ErrorManager::insert_auth0_error( 'migration_ws_login', $e );
        $user = array('error' => $e->getMessage());
    }

    echo json_encode($user);
    exit;

  }
  protected function migration_ws_get_user() {

    if ( $this->a0_options->get('migration_ws') == 0 ) return;

    $authorization = $this->getAuthorizationHeader();
    $authorization = trim(str_replace('Bearer ', '', $authorization));

    $secret = $this->a0_options->get( 'client_secret' );
    $token_id = $this->a0_options->get( 'migration_token_id' );

    $user = null;

    try {
        if (empty($authorization)) {
            throw new Exception('Unauthorized');
        }

        $token = JWT::decode($authorization, JWT::urlsafeB64Decode( $secret ), array('HS256'));

        if ($token->jti != $token_id) {
            throw new Exception('Invalid token id');
        }

        if (!isset($_POST['username'])) {
          throw new Exception('username is required');
        }

        $username = $_POST['username'];

        $user = get_user_by('email', $username);

        if (!$user) {
          $user = get_user_by('slug', $username);
        }

        if ($user instanceof WP_Error) {
          WP_Auth0_ErrorManager::insert_auth0_error( 'migration_ws_get_user',$user );
          $user = array('error' => 'invalid credentials');
        } else {

          if (! $user instanceof WP_User) {
            $user = array('error' => 'invalid credentials');
          } else {
            unset($user->data->user_pass);
            $user = apply_filters( 'auth0_migration_ws_authenticated', $user );
          }
        }
    }
    catch(Exception $e) {
        WP_Auth0_ErrorManager::insert_auth0_error( 'migration_ws_get_user', $e );
        $user = array('error' => $e->getMessage());
    }

    echo json_encode($user);
    exit;

  }
  protected function oauth2_config() {

      $callback_url = admin_url( 'admin.php?page=wpa0-setup&callback=1' );

      echo json_encode(array(
        'client_name' => get_bloginfo('name'),
        'redirect_uris' => array(
            $callback_url
        )
      ));
      exit;
  }
}
