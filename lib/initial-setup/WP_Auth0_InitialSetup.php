<?php

class WP_Auth0_InitialSetup {

	protected $a0_options;
	protected $connection_profile;
	protected $adminuser_step;
	protected $connections_step;
	protected $end_step;

	public function __construct( WP_Auth0_Options $a0_options ) {
		$this->a0_options = $a0_options;

		$this->connection_profile = new WP_Auth0_InitialSetup_ConnectionProfile( $this->a0_options );
		$this->adminuser_step     = new WP_Auth0_InitialSetup_AdminUser( $this->a0_options );
		$this->connections_step   = new WP_Auth0_InitialSetup_Connections( $this->a0_options );
		$this->end_step           = new WP_Auth0_InitialSetup_End( $this->a0_options );
	}

	public function notify_error( $error ) {
		printf( '<div class="notice notice-error"><p><strong>%s</strong></p></div>', sanitize_text_field( $error ) );
	}

	public function render_setup_page() {
		// Not processing form data, only pulling from the URL.
		// phpcs:disable WordPress.Security.NonceVerification.NoNonceVerification

		$step = ( isset( $_REQUEST['step'] ) ? absint( $_GET['step'] ) : 1 );

		if ( is_numeric( $step ) && $step >= 1 && $step <= 6 ) {

			$last_step = $this->a0_options->get( 'last_step' );

			if ( $step > $last_step ) {
				$this->a0_options->set( 'last_step', $step );
			}

			switch ( $step ) {
				case 1:
					$this->connection_profile->render( $step );
					break;

				case 2:
					$this->connections_step->render( $step );
					break;

				case 3:
					$this->adminuser_step->render( $step );
					break;

				case 4:
					$this->end_step->render( $step );
					break;
			}
		}

	  // phpcs:enable WordPress.Security.NonceVerification.NoNonceVerification
	}

	public function cant_create_client_message() {
		?>
		  <div class="notice notice-error">
			  <p>
				  <strong>
					<?php _e( 'There was an error creating the Auth0 App. Check the ', 'wp-auth0' ); ?>
					  <a target="_blank" href="<?php echo admin_url( 'admin.php?page=wpa0-errors' ); ?>"><?php _e( 'error log', 'wp-auth0' ); ?></a>
					<?php _e( ' for more information. If the problem persists, please follow the ', 'wp-auth0' ); ?>
					  <a target="_blank" href="https://auth0.com/docs/cms/wordpress/installation#manual-setup"><?php _e( 'manual setup instructions', 'wp-auth0' ); ?></a>.
				  </strong>
			  </p>
		  </div>
		<?php
	}

	public function cant_create_client_grant_message() {
		?>
		<div class="notice notice-error">
			<p>
				<strong>
					<?php _e( 'There was an error creating the necessary client grants. ', 'wp-auth0' ); ?>
					<?php
					_e(
						'Go to your Auth0 dashboard > APIs > Auth0 Management API > Machine to Machine Applications tab and authorize this Application. ',
						'wp-auth0'
					);
					?>
					<?php _e( 'Make sure to add the following scopes: ', 'wp-auth0' ); ?>
					<code><?php echo implode( '</code>, <code>', WP_Auth0_Api_Client::get_required_scopes() ); ?></code>
					<?php _e( 'You can also check the ', 'wp-auth0' ); ?>
					<a target="_blank" href="<?php echo admin_url( 'admin.php?page=wpa0-errors' ); ?>"><?php _e( 'Error log', 'wp-auth0' ); ?></a>
					<?php _e( ' for more information.', 'wp-auth0' ); ?>
				</strong>
			</p>
		</div>
		<?php
	}

	public function cant_exchange_token_message() {
		?>
		  <div class="notice notice-error">
			  <p>
				  <strong>
					<?php _e( 'There was an error retrieving your Auth0 credentials. Check the ', 'wp-auth0' ); ?>
					<a target="_blank" href="<?php echo admin_url( 'admin.php?page=wpa0-errors' ); ?>"><?php _e( 'Error log', 'wp-auth0' ); ?></a>
					<?php _e( ' for more information.', 'wp-auth0' ); ?>
					<?php _e( 'Please check that your server has internet access and can reach ', 'wp-auth0' ); ?>
					<code><?php echo esc_url( 'https://' . $this->a0_options->get( 'domain' ) ); ?></code>
				  </strong>
			  </p>
		  </div>
		<?php
	}

	public function rejected_message() {
		?>
	  <div class="notice notice-error">
		<p>
		  <strong>
				<?php _e( 'The required scopes were rejected.', 'wp-auth0' ); ?>
		  </strong>
		</p>
	  </div>
		<?php
	}

	public function access_denied_message() {
		?>
		  <div class="notice notice-error">
			  <p>
				  <strong>
					<?php _e( 'Please create your Auth0 account first at ', 'wp-auth0' ); ?>
			<a href="https://manage.auth0.com">https://manage.auth0.com</a>
				  </strong>
			  </p>
		  </div>
		<?php
	}

	public static function get_setup_client_id() {
		return site_url();
	}

	public static function get_setup_redirect_uri() {
		return admin_url( 'admin.php?page=wpa0-setup&callback=1' );
	}
}
