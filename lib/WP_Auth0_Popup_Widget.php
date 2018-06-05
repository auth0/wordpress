<?php

class WP_Auth0_Popup_Widget extends WP_Auth0_Embed_Widget {

	protected function getWidgetId() {
		return 'wp_auth0_popup_widget';
	}

	protected function getWidgetName() {
		return __( 'Auth0 Popup Login', 'wp-auth0' );
	}

	protected function getWidgetDescription() {
		return __( 'Shows a button to pop up an Auth0 login form in your sidebar', 'wp-auth0' );
	}

	protected function showAsModal() {
		return true;
	}

}
