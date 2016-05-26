<?php

class WP_Auth0_Export_Users {

	protected $db_manager;

	public function __construct( WP_Auth0_DBManager $db_manager ) {
		$this->db_manager = $db_manager;
	}

	public function init() {
		add_action( 'admin_footer', array( $this, 'a0_add_users_export' ) );
		add_action( 'load-users.php', array( $this, 'a0_export_selected_users' ) );
		add_action( 'admin_action_wpauth0_export_users', array( $this, 'a0_export_users' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue' ) );
	}

	public function admin_enqueue() {
		if ( ! isset( $_REQUEST['page'] ) || 'wpa0-users-export' !== $_REQUEST['page'] ) {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_style( 'wpa0_bootstrap', WPA0_PLUGIN_URL . 'assets/bootstrap/css/bootstrap.min.css' );
		wp_enqueue_script( 'wpa0_bootstrap', WPA0_PLUGIN_URL . 'assets/bootstrap/js/bootstrap.min.js' );
		wp_enqueue_style( 'wpa0_admin_initial_settup', WPA0_PLUGIN_URL . 'assets/css/initial-setup.css' );
		wp_enqueue_style( 'media' );
	}

	public function a0_add_users_export() {
		$screen = get_current_screen();
		if ( $screen->id != "users" )   // Only add to users.php page
			return;
?>
	    <script type="text/javascript">
	        jQuery(document).ready(function($) {
	            $('<option>').val('a0_users_export').text('Export users profile').appendTo("select[name='action']");
              $('#doaction').click(function(){
                if ($("select[name='action']").val() === 'a0_users_export') {
                  metricsTrack('export:users');
                }
              });
	        });
	    </script>
	    <?php
	}

	public function render_export_users() {
		include WPA0_PLUGIN_DIR . 'templates/export-users.php';
	}

	public function a0_export_users( $user_ids = null ) {
		header( 'Content-Type: application/csv' );
		header( 'Content-Disposition: attachment; filename=users_export.csv' );
		header( 'Pragma: no-cache' );

		$users = $this->db_manager->get_auth0_users( $user_ids );

		echo $this->process_str( "email", true );
		echo $this->process_str( "nickname", true );
		echo $this->process_str( "name", true );
		echo $this->process_str( "givenname", true );
		echo $this->process_str( "gender", true );
		echo $this->process_numeric( "age", true );
		echo $this->process_numeric( "latitude", true );
		echo $this->process_numeric( "longitude", true );
		echo $this->process_numeric( "zipcode", true );
		echo $this->process_numeric( "income", true );
		echo $this->process_numeric( "country_code", true );
		echo $this->process_numeric( "country_name", true );
		echo $this->process_str( "idp", true );
		echo $this->process_str( "created_at", true );
		echo $this->process_str( "last_login", true );
		echo $this->process_numeric( "logins_count", false );
		echo "\n";

		foreach ( $users as $user ) {
			$profile = new WP_Auth0_UserProfile(  get_user_meta( $user->ID, 'auth0_obj', true ) );

			echo $this->process_str( $profile->get_email(), true );
			echo $this->process_str( $profile->get_nickname(), true );
			echo $this->process_str( $profile->get_name(), true );
			echo $this->process_str( $profile->get_givenname(), true );
			echo $this->process_str( $profile->get_gender(), true );
			echo $this->process_numeric( $profile->get_age(), true );
			echo $this->process_numeric( $profile->get_latitude(), true );
			echo $this->process_numeric( $profile->get_longitude(), true );
			echo $this->process_numeric( $profile->get_zipcode(), true );
			echo $this->process_numeric( $profile->get_income(), true );
			echo $this->process_numeric( $profile->get_country_code(), true );
			echo $this->process_numeric( $profile->get_country_name(), true );
			echo $this->process_str( implode( '|', $profile->get_idp() ), true );
			echo $this->process_str( $profile->get_created_at(), true );
			echo $this->process_str( $profile->get_last_login(), true );
			echo $this->process_numeric( $profile->get_logins_count(), false );
			echo "\n";
		}

		exit;
	}

	public function a0_export_selected_users() {
		if ( isset( $_GET['action'] ) && $_GET['action'] === 'a0_users_export' && isset( $_GET['users'] ) ) {
			$user_ids = $_GET['users'];

			if ( $user_ids ) {
				$this->a0_export_users( $user_ids );
			}
		}
	}

	protected function process_str( $attr, $coma ) {
		return ( !empty( $attr ) ? '"'.$attr.'"' : '' ). ( $coma ? ',' : '' );
	}

	protected function process_numeric( $attr, $coma ) {
		return ( !empty( $attr ) ? $attr : '' ). ( $coma ? ',' : '' );
	}
}
