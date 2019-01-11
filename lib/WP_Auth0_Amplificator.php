<?php

/**
 * Class WP_Auth0_Amplificator
 *
 * @deprecated - 3.9.0, functionality removed
 *
 * @codeCoverageIgnore
 */
class WP_Auth0_Amplificator {

	protected $a0_options;
	protected $db_manager;

	/**
	 * WP_Auth0_Amplificator constructor.
	 *
	 * @param WP_Auth0_DBManager $db_manager
	 * @param WP_Auth0_Options   $a0_options
	 *
	 * @deprecated - 3.9.0, functionality removed
	 */
	public function __construct( WP_Auth0_DBManager $db_manager, WP_Auth0_Options $a0_options ) {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );
		$this->db_manager = $db_manager;
		$this->a0_options = $a0_options;
	}

	public function init() {
		add_action( 'wp_ajax_auth0_amplificator', array( $this, 'share' ) );
	}

	public function share() {
		if ( ! isset( $_POST['provider'] ) ) {
			exit;
		}

		$provider = $_POST['provider'];
		$page_url = $_POST['page_url'];

		switch ( $provider ) {
			case 'facebook':
				$this->_share_facebook( $page_url );
				break;
			case 'twitter':
				$this->_share_twitter( $page_url );
				break;
		}

		exit;
	}

	public function get_share_text( $provider, $page_url ) {
		$message = $this->a0_options->get( "social_{$provider}_message" );

		$message = str_replace( '%page_url%', $page_url, $message );
		$message = str_replace( '%site_url%', home_url(), $message );

		return $message;
	}

	protected function _share_facebook( $page_url ) {
		$current_user = get_currentauth0user();
		$user_profile = $current_user->auth0_obj;

		foreach ( $user_profile->identities as $identity ) {
			if ( $identity->provider == 'facebook' ) {

				$share_text = urlencode( $this->get_share_text( 'facebook', $page_url ) );

				$url      = "https://graph.facebook.com/{$identity->user_id}/feed?message={$share_text}&access_token={$identity->access_token}";
				$response = wp_remote_post( $url );

				$message = '';
				$success = ( $response['response']['code'] === 200 );
				if ( ! $success ) {
					$body = json_decode( $response['body'] );
					if ( $body->error->code == 506 ) {
						$message = __( 'Facebook does not allow to share the same content twice.', 'wp-auth0' );
					} else {
						if ( isset( $body->error->error_user_msg ) ) {
							$message = $body->error->error_user_msg;
						} elseif ( isset( $body->error->message ) ) {
							$message = $body->error->message;
						} else {
							$message = __( 'An error has occurred.', 'wp-auth0' );
						}
					}
				}

				echo json_encode(
					array(
						'success' => $success,
						'message' => $message,
					)
				);

				return;
			}
		}

	}

	protected function _share_twitter( $page_url ) {
		$current_user = get_currentauth0user();
		$user_profile = $current_user->auth0_obj;

		foreach ( $user_profile->identities as $identity ) {
			if ( $identity->provider == 'twitter' ) {

				$share_text = $this->get_share_text( 'twitter', $page_url );

				$settings = array(
					'consumer_key'              => $this->a0_options->get( 'social_twitter_key' ),
					'consumer_secret'           => $this->a0_options->get( 'social_twitter_secret' ),
					'oauth_access_token'        => $identity->access_token,
					'oauth_access_token_secret' => $identity->access_token_secret,
				);

				$twitter  = new TwitterAPIExchange( $settings );
				$response = json_decode(
					$twitter->buildOauth( 'https://api.twitter.com/1.1/statuses/update.json', 'POST' )
					->setPostfields( array( 'status' => $share_text ) )
					->performRequest()
				);

				$message = '';
				$success = ( ! isset( $response->errors ) );
				if ( ! $success ) {
					if ( $response->errors[0]->code == 187 ) {
						$message = __( 'Twitter does not allow to share the same content twice.', 'wp-auth0' );
					} else {
						$message = $response->errors[0]->message;
					}
				}

				echo json_encode(
					array(
						'success' => $success,
						'message' => $message,
					)
				);

				return;
			}
		}
	}
}
