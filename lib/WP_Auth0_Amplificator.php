<?php

class WP_Auth0_Amplificator {

	public static function init() {
		add_action( 'wp_ajax_auth0_amplificator', array(__CLASS__, 'share') );
	}

	public static function share(){
		if (!isset($_POST["provider"])) wp_die();

		$provider = $_POST["provider"];

		switch ($provider) {
			case 'facebook': self::shareFacebook(); break;
			case 'twitter': self::shareTwitter(); break;
		}

		wp_die();
	}

	protected static function shareFacebook() {
		// needs publish actions
	}

	protected static function shareTwitter() {
		//you need to have read/write permission when you create the app
	}

}