<?php

class WP_Auth0_Embed_Widget extends WP_Widget {

	function __construct() {
		parent::__construct(
			$this->getWidgetId(),
			$this->getWidgetName(),
			[ 'description' => $this->getWidgetDescription() ]
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
		return 'form';
	}

	public function widget( $args, $instance ) {

		if ( wp_auth0_is_ready() ) {

			$instance['show_as_modal']      = $this->showAsModal();
			$instance['modal_trigger_name'] = isset( $instance['modal_trigger_name'] )
				? $instance['modal_trigger_name']
				: __( 'Login', 'wp-auth0' );

			if ( ! isset( $instance['redirect_to'] ) || empty( $instance['redirect_to'] ) ) {
				// Null coalescing validates the input variable.
				// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
				$instance['redirect_to'] = home_url( $_SERVER['REQUEST_URI'] ?? '' );
			}

			echo $args['before_widget'];
			\WP_Auth0_Lock::render( false, $instance );
			echo $args['after_widget'];

		} else {
			_e( 'Please check your Auth0 configuration', 'wp-auth0' );
		}
	}

	public function update( $new_instance, $old_instance ) {
		$new_instance['dict'] = trim( $new_instance['dict'] );
		if ( $new_instance['dict'] && json_decode( $new_instance['dict'] ) === null ) {
			$new_instance['dict'] = $old_instance['dict'];
		}

		$new_instance['extra_conf'] = trim( $new_instance['extra_conf'] );
		if ( $new_instance['extra_conf'] && json_decode( $new_instance['extra_conf'] ) === null ) {
			$new_instance['extra_conf'] = $old_instance['extra_conf'];
		}

		if ( ! empty( $new_instance['redirect_to'] ) ) {
			$admin_advanced = new WP_Auth0_Admin_Advanced(
				WP_Auth0_Options::Instance(),
				new WP_Auth0_Routes( WP_Auth0_Options::Instance() )
			);

			$new_instance['redirect_to'] = $admin_advanced->validate_login_redirect(
				$new_instance['redirect_to'],
				$old_instance['redirect_to']
			);
		}

		return $new_instance;
	}
}
