<?php

class WP_Auth0_InitialSetup {

	protected $a0_options;
	protected $connection_profile;
	protected $enterprise_connection_step;
	protected $adminuser_step;
	protected $connections_step;
	protected $end_step;

	public function __construct( WP_Auth0_Options $a0_options ) {
		$this->a0_options = $a0_options;

		$this->connection_profile         = new WP_Auth0_InitialSetup_ConnectionProfile( $this->a0_options );
		$this->enterprise_connection_step = new WP_Auth0_InitialSetup_EnterpriseConnection( $this->a0_options );
		$this->adminuser_step             = new WP_Auth0_InitialSetup_AdminUser( $this->a0_options );
		$this->connections_step           = new WP_Auth0_InitialSetup_Connections( $this->a0_options );
		$this->end_step                   = new WP_Auth0_InitialSetup_End( $this->a0_options );
	}

	/**
	 * @deprecated - 3.10.0, will move add_action calls out of this class in the next major.
	 *
	 * @codeCoverageIgnore - Deprecated.
	 */
	public function init() {

		add_action( 'admin_action_wpauth0_callback_step1', [ $this->connection_profile, 'callback' ] );
		add_action( 'admin_action_wpauth0_callback_step3_social', [ $this->adminuser_step, 'callback' ] );

		if ( isset( $_REQUEST['page'] ) && 'wpa0-setup' === $_REQUEST['page'] ) {
			if ( isset( $_REQUEST['error'] ) ) {
				add_action( 'admin_notices', [ $this, 'notify_error' ] );
			}
		}

		if ( isset( $_REQUEST['error'] ) && 'cant_create_client' == $_REQUEST['error'] ) {
			add_action( 'admin_notices', [ $this, 'cant_create_client_message' ] );
		}

		if ( isset( $_REQUEST['error'] ) && 'cant_create_client_grant' == $_REQUEST['error'] ) {
			add_action( 'admin_notices', [ $this, 'cant_create_client_grant_message' ] );
		}

		if ( isset( $_REQUEST['error'] ) && 'cant_exchange_token' == $_REQUEST['error'] ) {
			add_action( 'admin_notices', [ $this, 'cant_exchange_token_message' ] );
		}

		if ( isset( $_REQUEST['error'] ) && 'rejected' == $_REQUEST['error'] ) {
			add_action( 'admin_notices', [ $this, 'rejected_message' ] );
		}

		if ( isset( $_REQUEST['error'] ) && 'access_denied' == $_REQUEST['error'] ) {
			add_action( 'admin_notices', [ $this, 'access_denied' ] );
		}
	}

	public function notify_error() {
		printf( '<div class="notice notice-error">%s</div>', strip_tags( $_REQUEST['error'] ) );
	}

	public function render_setup_page() {

		$step    = ( isset( $_REQUEST['step'] ) ? $_REQUEST['step'] : 1 );
		$profile = ( isset( $_REQUEST['profile'] ) ? $_REQUEST['profile'] : null );

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
					if ( $profile == 'social' ) {
						$this->connections_step->render( $step );
					} elseif ( $profile == 'enterprise' ) {
						$this->enterprise_connection_step->render( $step );
					}
					break;

				case 3:
					if ( $profile == 'social' ) {
						$this->adminuser_step->render( $step );
					} elseif ( $profile == 'enterprise' ) {
						// $this->connections_step->render($step);
					}
					break;

				case 4:
					$this->end_step->render( $step );
					break;
			}
		}
	}

	public function cant_create_client_message() {
		?>
		  <div id="message" class="error">
			  <p>
				  <strong>
					<?php echo __( 'There was an error creating the Auth0 App. Check the ', 'wp-auth0' ); ?>
					  <a target="_blank" href="<?php echo admin_url( 'admin.php?page=wpa0-errors' ); ?>"><?php echo __( 'Error log', 'wp-auth0' ); ?></a>
					<?php echo __( ' for more information. If the problem persists, please create it manually in the ', 'wp-auth0' ); ?>
					  <a target="_blank" href="https://manage.auth0.com/#/applications"><?php echo __( 'Auth0 Dashboard', 'wp-auth0' ); ?></a>
					<?php echo __( ' and copy the Client ID and Client Secret.', 'wp-auth0' ); ?>
				  </strong>
			  </p>
		  </div>
		<?php
	}

	public function cant_create_client_grant_message() {
		?>
		<div id="message" class="error">
			<p>
				<strong>
					<?php echo __( 'There was an error creating the necessary client grants. ', 'wp-auth0' ); ?>
					<?php
					echo __(
						'Go to your Auth0 dashboard > APIs > Auth0 Management API > Machine to Machine Applications tab and authorize this Application. ',
						'wp-auth0'
					);
					?>
					<?php echo __( 'Make sure to add the following scopes: ', 'wp-auth0' ); ?>
					<code><?php echo implode( '</code>, <code>', WP_Auth0_Api_Client::get_required_scopes() ); ?></code>
					<?php echo __( 'You can also check the ', 'wp-auth0' ); ?>
					<a target="_blank" href="<?php echo admin_url( 'admin.php?page=wpa0-errors' ); ?>"><?php echo __( 'Error log', 'wp-auth0' ); ?></a>
					<?php echo __( ' for more information.', 'wp-auth0' ); ?>
				</strong>
			</p>
		</div>
		<?php
	}

	public function cant_exchange_token_message() {
		?>
		  <div id="message" class="error">
			  <p>
				  <strong>
					<?php echo __( 'There was an error retrieving your Auth0 credentials. Check the ', 'wp-auth0' ); ?>
					<a target="_blank" href="<?php echo admin_url( 'admin.php?page=wpa0-errors' ); ?>"><?php echo __( 'Error log', 'wp-auth0' ); ?></a>
					<?php echo __( ' for more information.', 'wp-auth0' ); ?>
					<?php echo __( 'Please check that your server has internet access and can reach ', 'wp-auth0' ); ?>
					<code>https://<?php echo $this->a0_options->get( 'domain' ); ?></code>
				  </strong>
			  </p>
		  </div>
		<?php
	}

	public function rejected_message() {
		?>
	  <div id="message" class="error">
		<p>
		  <strong>
			<?php echo __( 'The required scoped were rejected.', 'wp-auth0' ); ?>
		  </strong>
		</p>
	  </div>
		<?php
	}

	public function access_denied() {
		?>
		  <div class="notice notice-error">
			  <p>
				  <strong>
					<?php echo __( 'Please create your Auth0 account first at ', 'wp-auth0' ); ?>
			<a href="https://manage.auth0.com">https://manage.auth0.com</a>
				  </strong>
			  </p>
		  </div>
		<?php
	}
}
