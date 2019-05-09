<?php
/**
 * Global WP-Auth0 functions.
 *
 * @since 3.10.0
 */

/**
 * Return a stored option value.
 *
 * @since 3.10.0
 *
 * @param string $key - Settings key to get.
 * @param mixed  $default - Default value to return if not found.
 *
 * @return mixed
 */
function wp_auth0_get_option( $key, $default = null ) {
	return WP_Auth0_Options::Instance()->get( $key, $default );
}

/**
 * @param array $action
 *
 * @return bool
 */
function wp_auth0_is_current_login_action( array $action ) {

	// Not on wp-login.php.
	if ( ! isset( $GLOBALS['pagenow'] ) || 'wp-login.php' !== $GLOBALS['pagenow'] ) {
		return false;
	}

	$current_action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : null;
	return in_array( $current_action, $action );
}

function wp_auth0_login_override_url( $login_url = null ) {
	$wle = WP_Auth0_Options::Instance()->get( 'wordpress_login_enabled' );
	if ( 'no' === $wle ) {
		return '';
	}

	$wle_code = '';
	if ( 'code' === $wle ) {
		$wle_code = WP_Auth0_Options::Instance()->get( 'wle_code' );
	}

	$login_url = $login_url ?: wp_login_url();
	return add_query_arg( 'wle', $wle_code, $login_url );
}

if ( ! function_exists( 'get_auth0userinfo' ) ) {
	function get_auth0userinfo( $user_id ) {
		$profile = WP_Auth0_UsersRepo::get_meta( $user_id, 'auth0_obj' );
		return $profile ? WP_Auth0_Serializer::unserialize( $profile ) : false;
	}
}

if ( ! function_exists( 'get_currentauth0user' ) ) {
	function get_currentauth0user() {
		return (object) array(
			'auth0_obj'   => get_auth0userinfo( get_current_user_id() ),
			'last_update' => WP_Auth0_UsersRepo::get_meta( get_current_user_id(), 'last_update' ),
			'auth0_id'    => WP_Auth0_UsersRepo::get_meta( get_current_user_id(), 'auth0_id' ),
		);
	}
}

if ( ! function_exists( 'get_auth0_curatedBlogName' ) ) {
	function get_auth0_curatedBlogName() {

		$name = get_bloginfo( 'name' );

		// WordPress can have a blank site title, which will cause initial client creation to fail
		if ( empty( $name ) ) {
			$name = wp_parse_url( home_url(), PHP_URL_HOST );

			if ( $port = wp_parse_url( home_url(), PHP_URL_PORT ) ) {
				$name .= ':' . $port;
			}
		}

		$name = preg_replace( '/[^A-Za-z0-9 ]/', '', $name );
		$name = preg_replace( '/\s+/', ' ', $name );
		$name = str_replace( ' ', '-', $name );

		return $name;
	}
}

if ( ! function_exists( 'get_currentauth0userinfo' ) ) {
	function get_currentauth0userinfo() {
		global $currentauth0_user;
		$currentauth0_user = get_auth0userinfo( get_current_user_id() );
		return $currentauth0_user;
	}
}
