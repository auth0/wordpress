<?php

class WP_Auth0_Embed_Widget extends WP_Widget {

	function __construct() {
		parent::__construct(
			$this->getWidgetId(),
			__( $this->getWidgetName(), 'wp_auth0_widget_domain' ),
			array( 'description' => __( $this->getWidgetDescription(), 'wpb_widget_domain' ) )
		);
	}

	protected function getWidgetId() {
		return 'wp_auth0_widget';
	}

	protected function getWidgetName() {
		return 'Auth0 Lock Embed';
	}

	protected function getWidgetDescription() {
		return 'Shows Auth0 Lock Embed in your sidebar';
	}

	protected function showAsModal() {
		return false;
	}

	public function form( $instance ) {

		wp_enqueue_media();
		wp_enqueue_script( 'wpa0_admin', WPA0_PLUGIN_URL . 'assets/js/admin.js', array( 'jquery' ) );
		wp_enqueue_style( 'media' );
		wp_localize_script( 'wpa0_admin', 'wpa0', array(
				'media_title' => __( 'Choose your icon', 'wp-auth0' ),
				'media_button' => __( 'Choose icon', 'wp-auth0' ),
			) );
		require WPA0_PLUGIN_DIR . 'templates/a0-widget-setup-form.php';
	}

	public function widget( $args, $instance ) {

		$options = WP_Auth0_Options::Instance();
		$client_id = $options->get( 'client_id' );

		if ( trim( $client_id ) !== '' ) {

			if ( WP_Auth0_Options::Instance()->get( 'passwordless_enabled' ) ) {
				wp_enqueue_script( 'wpa0_lock', WP_Auth0_Options::Instance()->get( 'passwordless_cdn_url' ), 'jquery' );
			} else {
				wp_enqueue_script( 'wpa0_lock', WP_Auth0_Options::Instance()->get( 'cdn_url' ), 'jquery' );
			}

			echo $args['before_widget'];

			$instance['show_as_modal'] = $this->showAsModal();
			$instance['modal_trigger_name'] = isset( $instance['modal_trigger_name'] ) ? $instance['modal_trigger_name'] : 'Login';

			if ( !isset( $instance['redirect_to'] ) || empty($instance['redirect_to']) ) {
				$instance['redirect_to'] = home_url( $_SERVER["REQUEST_URI"] );
			}
			
			require_once WPA0_PLUGIN_DIR . 'templates/login-form.php';
			renderAuth0Form( false, $instance );

			echo $args['after_widget'];
		}
	}

	public function update( $new_instance, $old_instance ) {
		if ( trim( $new_instance['dict'] ) !== '' ) {
			if ( strpos( $new_instance['dict'], '{' ) !== false && json_decode( $new_instance['dict'] ) === null ) {
				$new_instance['dict'] = $old_instance['dict'];
			}
		}
		if ( trim( $new_instance['extra_conf'] ) !== '' ) {
			if ( json_decode( $new_instance['extra_conf'] ) === null ) {
				$new_instance['extra_conf'] = $old_instance['extra_conf'];
			}
		}
		return $new_instance;
	}
}
