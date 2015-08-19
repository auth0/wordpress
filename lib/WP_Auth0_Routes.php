<?php

class WP_Auth0_Routes {

    public function init() {
        add_action('parse_request', array($this, 'custom_requests'));
    }

    public function setup_rewrites() {
  		add_rewrite_tag( '%auth0%', '([^&]+)' );
  		add_rewrite_tag( '%code%', '([^&]+)' );
  		add_rewrite_tag( '%state%', '([^&]+)' );
  		add_rewrite_tag( '%auth0_error%', '([^&]+)' );

  		add_rewrite_rule( '^auth0', 'index.php?auth0=1', 'top' );
  		add_rewrite_rule( '^oauth2-config?', 'index.php?a0_action=oauth2-config', 'top' );
  	}

    public function custom_requests ( $wp ) {
        if( ! empty($wp->query_vars['a0_action']) ) {
            switch ($wp->query_vars['a0_action']) {
                case 'oauth2-config': $this->oauth2_config(); exit;
            }
        }
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
