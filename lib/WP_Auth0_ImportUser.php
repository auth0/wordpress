<?php

class WP_Auth0_ImportUser {

	protected $a0_options;

	public function __construct( WP_Auth0_Options $a0_options ) {
		$this->a0_options = $a0_options;
	}

	public function init() {
		add_action( 'template_redirect', array( $this, 'import' ), 1 );
	}

	public function import() {

		$domain = $this->a0_options->get( 'domain' );
		$jwt = $this->a0_options->get( 'auth0_app_token' );

		$page = 0;

		do {
			$response = WP_Auth0_Api_Client::search_users( $domain, $jwt, "", $page, 100, true );
			foreach ( $response->users as $profile ) {
				self::create( $profile );
			}
			$page++;
		} while ( $response->start + $response->length < $response->total );


		exit;
	}

	public static function create( $profile ) {
		global $wpdb;

		$user = WP_Auth0_Users::find_auth0_user( $profile->user_id );

		if ( $user instanceof WP_Error ) return;

		if ( is_null( $user ) ) {
			$user_id = WP_Auth0_Users::create_user( $profile );

			if ( $user_id instanceof WP_Error ) {
				return;
			}

			update_user_meta( $user_id, 'auth0_id', ( isset( $profile->user_id ) ? $profile->user_id : $profile->sub )); 
			update_user_meta( $user_id, 'auth0_obj', WP_Auth0_Serializer::serialize( $profile )); 
			update_user_meta( $user_id, 'last_update', date( 'c' ) );

			// $wpdb->insert(
			// 	$wpdb->auth0_user,
			// 	array(
			// 		'auth0_id' => $profile->user_id,
			// 		'wp_id' => $user_id,
			// 		'auth0_obj' => WP_Auth0_Serializer::serialize( $profile ),
			// 		'last_update' =>  date( 'c' ),
			// 	),
			// 	array(
			// 		'%s',
			// 		'%d',
			// 		'%s',
			// 		'%s',
			// 		'%s',
			// 		'%s',
			// 	)
			// );
		} else {
			WP_Auth0_Users::update_auth0_object( $user->data->ID,$profile );
		}
	}
}
