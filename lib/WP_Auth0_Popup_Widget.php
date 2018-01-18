<?php

class WP_Auth0_Popup_Widget extends WP_Auth0_Embed_Widget {

	protected function getWidgetId() {
		return 'wp_auth0_popup_widget';
	}

	protected function getWidgetName() {
		return "Auth0 Lock Popup";
	}

	protected function getWidgetDescription() {
		return "Shows a button that once clicked will open Auth0 Lock Popup";
	}

	protected function showAsModal() {
		return true;
	}

}
