<?php
// phpcs:ignoreFile
/**
 * @deprecated - 3.8.0, not used and no replacement provided.
 *
 * @codeCoverageIgnore - Deprecated
 */
class WP_Auth0_InitialSetup_Signup {

	protected $a0_options;

	/**
	 * @deprecated - 3.8.0, not used and no replacement provided.
	 */
	public function __construct( WP_Auth0_Options $a0_options ) {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );

		$this->a0_options = $a0_options;
	}

	public function render() {
		include WPA0_PLUGIN_DIR . 'templates/initial-setup/signup.php';
	}

	public function callback() {
	}

}
