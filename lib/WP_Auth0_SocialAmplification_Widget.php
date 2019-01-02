<?php

/**
 * Class WP_Auth0_SocialAmplification_Widget
 *
 * @deprecated - 3.9.0, functionality removed
 *
 * @codeCoverageIgnore
 */
class WP_Auth0_SocialAmplification_Widget extends WP_Widget {

	protected static $db_manager;
	protected static $social_amplificator;
	protected $options;

	public static function set_context( WP_Auth0_DBManager $db_manager, WP_Auth0_Amplificator $social_amplificator ) {
		self::$db_manager          = $db_manager;
		self::$social_amplificator = $social_amplificator;
	}

	/**
	 * WP_Auth0_SocialAmplification_Widget constructor.
	 *
	 * @deprecated - 3.9.0, functionality removed
	 */
	function __construct() {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );
		parent::__construct(
			$this->getWidgetId(),
			__( $this->getWidgetName(), 'wp_auth0_widget_domain' ),
			array( 'description' => __( $this->getWidgetDescription(), 'wpb_widget_domain' ) )
		);
		$this->options = WP_Auth0_Options::Instance();
	}

	protected function getWidgetId() {
		return 'wp_auth0_social_amplification_widget';
	}

	protected function getWidgetName() {
		return __( 'Auth0 Social Amplification', 'wp-auth0' );

	}

	protected function getWidgetDescription() {
		return __( 'Shows Auth0 Social Amplification widget in a sidebar', 'wp-auth0' );
	}

	/**
	 * Build the widget settings form in the Customizer and wp-admin > Widgets page.
	 *
	 * @param array $instance - Current instance of this widget.
	 */
	public function form( $instance ) {
		$fields = array(
			'amplificator_title'     => __( 'Widget title', 'wp-auth0' ),
			'amplificator_subtitle'  => __( 'Widget subtitle', 'wp-auth0' ),
			'social_twitter_message' => __( 'Twitter message', 'wp-auth0' ),
		);

		foreach ( $fields as $field => $title ) {
			$field_value = isset( $instance[ $field ] )
				? $instance[ $field ]
				: $this->options->get( $field );
			printf(
				'<p><label for="%s">%s:</label><textarea class="widefat" id="%s" name="%s">%s</textarea></p>',
				esc_attr( $this->get_field_id( $field ) ),
				sanitize_text_field( $title ),
				esc_attr( $this->get_field_id( $field ) ),
				esc_attr( $this->get_field_name( $field ) ),
				esc_textarea( $field_value )
			);
		}

		printf(
			'<p>%s <a href="%s" target="_blank">%s</a>.</p>
			<p>%s</p><ul><li><code>%s</code> - %s</li><li><code>%s</code> - %s</li></ul>',
			__( 'App keys are required and are set on the Advanced tab of the', 'wp-auth0' ),
			admin_url( 'admin.php?page=wpa0#advanced' ),
			__( 'Auth0 settings page', 'wp-auth0' ),
			__( 'You can use the following tags in the messages above:', 'wp-auth0' ),
			'%page_url%',
			__( 'This will be replaced by the current page URL', 'wp-auth0' ),
			'%site_url%',
			__( 'This will be replaced by the site URL', 'wp-auth0' )
		);
	}

	public function update( $new_instance, $old_instance ) {
		$new_instance['social_twitter_message'] = sanitize_text_field( $new_instance['social_twitter_message'] );
		$new_instance['amplificator_title']     = sanitize_text_field( $new_instance['amplificator_title'] );
		$new_instance['amplificator_subtitle']  = sanitize_text_field( $new_instance['amplificator_subtitle'] );
		return $new_instance;
	}

	public function widget( $args, $instance ) {

		$client_id = WP_Auth0_Options::Instance()->get( 'client_id' );

		$current_user = get_currentauth0user();
		$user_profile = $current_user->auth0_obj;

		if ( trim( $client_id ) != '' && $user_profile ) {
			$supportedProviders = array( 'facebook', 'twitter' );
			$enabledProviders   = array();

			$social_facebook_key = $this->options->get( 'social_facebook_key' );
			if ( ! empty( $social_facebook_key ) ) {
				$enabledProviders[] = 'facebook';
			}

			$social_twitter_key = $this->options->get( 'social_twitter_key' );
			if ( ! empty( $social_twitter_key ) ) {
				$enabledProviders[] = 'twitter';
			}

			$providers = array();
			if ( ! empty( $user_profile->identities ) ) {
				foreach ( $user_profile->identities as $identity ) {
					$providers[] = $identity->provider;
				}
			}

			$providers = array_intersect( array_unique( $providers ), $supportedProviders );

			echo $args['before_widget'];

			if ( ! empty( $instance['amplificator_title'] ) ) {
				$widget_title = strip_tags( $instance['amplificator_title'] );
				echo '<h2 class="widget-title">' . sanitize_text_field( $widget_title ) . '</h2>';
			}

			if ( ! empty( $instance['amplificator_subtitle'] ) ) {
				$widget_subtitle = strip_tags( $instance['amplificator_subtitle'] );
				echo '<p>' . sanitize_text_field( $widget_subtitle ) . '</p>';
			}

			wp_enqueue_style( 'auth0-aplificator-css', WPA0_PLUGIN_CSS_URL . 'amplificator.css' );
			wp_enqueue_script( 'auth0-aplificator-js', WPA0_PLUGIN_JS_URL . 'amplificator.js', array( 'jquery' ), WPA0_VERSION );
			wp_localize_script( 'auth0-aplificator-js', 'auth0_ajax', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );

			$current_page_url = self::current_page_url();

			foreach ( $supportedProviders as $provider ) {

				if ( in_array( $provider, $providers ) && in_array( $provider, $enabledProviders ) ) {
					$js_function = "Auth0Amplify(this,'$provider', '$current_page_url')";
				} else {
					$share_url = '';
					switch ( $provider ) {

						case 'facebook':
							$share_url = 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode( $current_page_url );
							break;

						case 'twitter':
							$message = '';
							if ( ! empty( $instance['social_twitter_message'] ) ) {
								$message = str_replace( '%page_url%', $current_page_url, $instance['social_twitter_message'] );
								$message = str_replace( '%site_url%', home_url(), $message );
							}

							$share_url = sprintf(
								'https://twitter.com/share?url=%s&text=%s',
								rawurlencode( $current_page_url ),
								rawurlencode( $message )
							);
							break;
					}

					$js_function = sprintf(
						"javascript: void window.open('%s', '','height=300, width=600')",
						$share_url
					);
				}

				printf(
					'<div onclick="%s" title="%s" class="a0-social a0-%s" dir="ltr"><span>%s</span></div>',
					esc_attr( $js_function ),
					esc_attr( $provider ),
					esc_attr( $provider ),
					esc_attr( $provider )
				);
			}

			echo $args['after_widget'];
		}

	}

	/**
	 * @deprecated - 3.9.0, functionality removed
	 */
	protected static function current_page_url() {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );
		return home_url( $_SERVER['REQUEST_URI'] );
	}

}
