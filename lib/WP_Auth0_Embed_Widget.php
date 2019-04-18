<?php

class WP_Auth0_Embed_Widget extends WP_Widget {

	function __construct() {
		parent::__construct(
			$this->getWidgetId(),
			$this->getWidgetName(),
			array( 'description' => $this->getWidgetDescription() )
		);
	}

	protected function getWidgetId() {
		return 'wp_auth0_widget';
	}

	protected function getWidgetName() {
		return __( 'Auth0 Login', 'wp-auth0' );
	}

	protected function getWidgetDescription() {
		return __( 'Shows Auth0 login form in your sidebar', 'wp-auth0' );
	}

	protected function showAsModal() {
		return false;
	}

	public function form( $instance ) {
		wp_enqueue_media();
		wp_enqueue_script( 'wpa0_admin' );
		wp_enqueue_style( 'media' );
		require WPA0_PLUGIN_DIR . 'templates/a0-widget-setup-form.php';
	}

	public function widget( $args, $instance ) {

		if ( WP_Auth0::ready() ) {

			$instance['show_as_modal']      = $this->showAsModal();
			$instance['modal_trigger_name'] = isset( $instance['modal_trigger_name'] )
				? $instance['modal_trigger_name']
				: __( 'Login', 'wp-auth0' );

			if ( ! isset( $instance['redirect_to'] ) || empty( $instance['redirect_to'] ) ) {
				$instance['redirect_to'] = home_url( $_SERVER['REQUEST_URI'] );
			}

			echo $args['before_widget'];
			require_once WPA0_PLUGIN_DIR . 'templates/login-form.php';
			renderAuth0Form( false, $instance );
			echo $args['after_widget'];

		} else {
			_e( 'Please check your Auth0 configuration', 'wp-auth0' );
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
