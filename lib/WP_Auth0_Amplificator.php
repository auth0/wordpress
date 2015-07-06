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
		// needs publish actions
	}

	protected static function _share_twitter() {
		//you need to have read/write permission when you create the app
	}

}
