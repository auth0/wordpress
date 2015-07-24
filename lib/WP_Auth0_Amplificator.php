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

		switch ( $provider ) {
			case 'facebook': self::_share_facebook(); break;
			case 'twitter': self::_share_twitter(); break;
		}
 
		wp_die();
	}

	protected static function _share_facebook() {
		$user_profile = get_currentauth0userinfo();
		$message = 'The message :)';

		foreach ($user_profile->identities as $identity) {
			if ($identity->provider == 'twitter') {

				$url = "https://graph.facebook.com/{$identity->user_id}/feed?message={$message}&access_token={$identity->access_token}";
				wp_remote_post( $url );

				return;
			}
		}


	}

	protected static function _share_twitter() {

		require_once WPA0_PLUGIN_DIR . 'lib/php-jwt/Authentication/JWT.php';
		$options = WP_Auth0_Options::Instance();
		$user_profile = get_currentauth0userinfo();
		$message = 'The message :)';

		foreach ($user_profile->identities as $identity) {
			if ($identity->provider == 'twitter') {

				$settings = array(
				    'oauth_access_token' => $options->get('social_twitter_key'),
				    'oauth_access_token_secret' => $options->get('social_twitter_secret'),
				    'consumer_key' => "YOUR_CONSUMER_KEY",
				    'consumer_secret' => "YOUR_CONSUMER_SECRET"
				);

				$twitter = new TwitterAPIExchange($settings);
				echo $twitter->buildOauth('https://api.twitter.com/1.1/statuses/update.json', 'POST')
				    ->setPostfields(array('status' => $message))
				    ->performRequest();

				return;
			}
		}

	}

}
