<?php

class WP_Auth0_Import_Settings {

	protected $a0_options;

	public function __construct( WP_Auth0_Options $a0_options ) {
		$this->a0_options = $a0_options;
	}

	public function init() {
		add_action( 'admin_action_wpauth0_export_settings', array( $this, 'export_settings' ) );
		add_action( 'admin_action_wpauth0_import_settings', array( $this, 'import_settings' ) );

		if ( isset( $_REQUEST['error'] ) && isset( $_REQUEST['page'] ) && $_REQUEST['page'] === 'wpa0-import-settings' ) {
			add_action( 'admin_notices', array( $this, 'show_error' ) );
		}
	}

	public function show_error() {
		printf(
			'<div class="notice notice-error"><p><strong>%s</strong></p></div>',
			sanitize_text_field( $_REQUEST['error'] )
		);
	}

	public function render_import_settings_page() {

		include WPA0_PLUGIN_DIR . 'templates/import_settings.php';

	}

	public function import_settings() {

		if ( ! function_exists( 'wp_handle_upload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}


		if ( isset( $_FILES['settings-file'] ) && $_FILES['settings-file']['error'] !== 4 ) {

			if ( $_FILES['settings-file']['error'] === 0 ) {
				$uploadedfile = $_FILES['settings-file'];
				$upload_overrides = array( 'test_form' => false, 'mimes' => array( 'json' => 'application/json' ) );

				$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );

				if ( $movefile && !isset( $movefile['error'] ) ) {

					$settings_json = file_get_contents( $movefile['file'] );
					unlink( $movefile['file'] );

					if ( empty( $settings_json ) ) {
						exit( wp_redirect( admin_url( 'admin.php?page=wpa0-import-settings&error=' . urlencode( 'The settings file is empty.' ) ) ) );
					}

					$settings = json_decode( $settings_json, true );

					if ( empty( $settings ) ) {
						exit( wp_redirect( admin_url( 'admin.php?page=wpa0-import-settings&error=' . urlencode( 'The settings file is not valid.' ) ) ) );
					}

				} else {
					exit( wp_redirect( admin_url( 'admin.php?page=wpa0-import-settings&error=' . urlencode( $movefile['error'] ) ) ) );
				}
			} else {
				switch ( $_FILES['settings-file']['error'] ) {
				case 1:
				case 2:
					exit( wp_redirect( admin_url( 'admin.php?page=wpa0-import-settings&error=' . urlencode( 'The file you are uploading is too big.' ) ) ) );
					break;
				case 3:
					exit( wp_redirect( admin_url( 'admin.php?page=wpa0-import-settings&error=' . urlencode( 'There was an error uploading the file.' ) ) ) );
					break;
				case 6:
				case 7:
				case 8:
					exit( wp_redirect( admin_url( 'admin.php?page=wpa0-import-settings&error=' . urlencode( 'There was an error importing your settings, please try again.' ) ) ) );
					break;
				}
			}
		} else {
			$settings_json = trim( stripslashes( $_POST['settings-json'] ) );

			if ( empty( $settings_json ) ) {
				exit( wp_redirect( admin_url( 'admin.php?page=wpa0-import-settings&error=' . urlencode( 'Please upload the Auth0 for Wordpress setting file or copy the content.' ) ) ) );
			}

			$settings = json_decode( $settings_json, true );

			if ( empty( $settings ) ) {
				exit( wp_redirect( admin_url( 'admin.php?page=wpa0-import-settings&error=' . urlencode( 'The settings json is not valid.' ) ) ) );
			}
		}

		foreach ( $settings as $key => $value ) {
			$this->a0_options->set( $key, $value, false );
		}

		$this->a0_options->update_all();

		exit( wp_redirect( admin_url( 'admin.php?page=wpa0' ) ) );
	}

	public function export_settings() {
		header( 'Content-Type: application/json' );
		$name = urlencode( get_auth0_curatedBlogName() );
		header( "Content-Disposition: attachment; filename=auth0_for_wordpress_settings-$name.json" );
		header( 'Pragma: no-cache' );


		$settings = $this->a0_options->get_options();
		echo json_encode( $settings );
		exit;
	}

}
