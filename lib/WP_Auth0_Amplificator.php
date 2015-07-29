<?php

class WP_Auth0_Amplificator {

	public static function init() {
		add_action( 'wp_ajax_auth0_amplificator', array( __CLASS__, 'share' ) );
	}

	public static function share() {
		if ( ! isset( $_POST['provider'] ) ) {
			wp_die();
		}

		$provider = $_POST['provider'];
		$page_url = $_POST['page_url'];

		switch ( $provider ) {
			case 'facebook': self::_share_facebook($page_url); break;
			case 'twitter': self::_share_twitter($page_url); break;
		}

		wp_die();
	}

	protected static function _share_facebook($page_url) {
		$user_profiles = WP_Auth0_DBManager::get_current_user_profiles();

		foreach ($user_profiles as $user_profile) {
			foreach ($user_profile->identities as $identity) {
				if ($identity->provider == 'facebook') {

					$options = WP_Auth0_Options::Instance();
					$message = $options->get('social_facebook_message');

					$message = str_replace('%page_url%', $page_url, $message);
					$message = str_replace('%site_url%', site_url('/'), $message);

					$message = urlencode($message);

					$url = "https://graph.facebook.com/{$identity->user_id}/feed?message={$message}&access_token={$identity->access_token}";
					$response = wp_remote_post( $url );

					$message = '';
					$success = ($response['response']['code'] === 200);
					if ( ! $success ) {
						$body = json_decode($response['body']);
						if ($body->error->code == 506) {
							$message = 'Facebook does not allow to share the same content twice.';
						} else {
							$message = $body->error->error_user_msg;
						}
					}


					echo json_encode(array(
						'success' => $success,
						'message' => $message
					));

					return;
				}
			}
		}

	}

	protected static function _share_twitter($page_url) {

		require_once WPA0_PLUGIN_DIR . 'lib/twitter-api-php/TwitterAPIExchange.php';
		$user_profiles = WP_Auth0_DBManager::get_current_user_profiles();

		foreach ($user_profiles as $user_profile) {
			foreach ($user_profile->identities as $identity) {
				if ($identity->provider == 'twitter') {

					$options = WP_Auth0_Options::Instance();
					$message = $options->get('social_twitter_message');

					$message = str_replace('%page_url%', $page_url, $message);

					$settings = array(
					    'consumer_key' => $options->get('social_twitter_key'),
					    'consumer_secret' => $options->get('social_twitter_secret'),
					    'oauth_access_token' => $identity->access_token,
					    'oauth_access_token_secret' => $identity->access_token_secret
					);

					$twitter = new TwitterAPIExchange($settings);
					$response = json_decode($twitter->buildOauth('https://api.twitter.com/1.1/statuses/update.json', 'POST')
					    ->setPostfields(array('status' => $message))
					    ->performRequest());

					$message = '';
					$success = ( ! isset($response->errors) );
					if ( ! $success ) {
						if ($response->errors[0]->code == 187) {
							$message = 'Twitter does not allow to share the same content twice.';
						} else {
							$message = $response->errors[0]->message;
						}
					}


					echo json_encode(array(
						'success' => $success,
						'message' => $message
					));

					return;
				}
			}
		}

	}

}
