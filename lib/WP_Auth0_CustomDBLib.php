<?php
/**
 * Contains class WP_Auth0_CustomDBLib
 *
 * @package WP-Auth0
 *
 * @since 2.0.0
 */

/**
 * Class WP_Auth0_CustomDBLib
 */
class WP_Auth0_CustomDBLib {

	/**
	 * Get JS to use in the custom database script.
	 *
	 * @param string $name  - Database script name.
	 * @param string $token - Migration token.
	 *
	 * @return bool|string
	 */
	public static function get_script( $name, $token ) {
		$script = (string) file_get_contents( WPA0_PLUGIN_DIR . 'lib/scripts-js/db-' . $name . '.js' );
		$script = str_replace( '{THE_WS_TOKEN}', $token, $script );
		$script = str_replace( '{THE_WS_URL}', site_url( 'index.php?a0_action=migration-ws-' . $name ), $script );
		return $script;
	}

	/**
	 * Custom database login script.
	 *
	 * @var string
	 *
	 * @deprecated - 3.9.0, use self::get_script( 'login', $token ) instead.
	 *
	 * @codeCoverageIgnore - Deprecated.
	 */
	public static $login_script = '
function login (email, password, callback) {

  var request = require("request");

  request.post("{THE_WS_URL}", {
    form:{username:email, password:password, access_token:"{THE_WS_TOKEN}"},
  }, function(error, response, body){

    if ( error ) {
      return callback(error);
    }

    var info = JSON.parse(body);

    if (info.error) {
      callback();
    } else {
      var profile = {
        user_id:     info.data.ID,
        username:    info.data.user_login,
        email_verified: true,
        email:       info.data.user_email,
        name:        info.data.display_name
      };

      callback(null, profile);
    }

  });
}
';

	/**
	 * Custom database get user script.
	 *
	 * @var string
	 *
	 * @deprecated - 3.9.0, use self::get_script( 'get-user', $token ) instead.
	 *
	 * @codeCoverageIgnore - Deprecated.
	 */
	public static $get_user_script = '
function getByEmail (email, callback) {

  var request = require("request");

  request.post("{THE_WS_URL}", {
    form:{username:email, access_token:"{THE_WS_TOKEN}"},
  }, function(error, response, body){

    if ( error ) {
      return callback(error);
    }

    var info = JSON.parse(body);

    if (info.error) {
      callback(null);
    } else {
      var profile = {
        user_id:     info.data.ID,
        username:    info.data.user_login,
        email:       info.data.user_email,
        name:        info.data.display_name,
        email_verified: true
      };

      callback(null, profile);
    }

  });
}
';

}
