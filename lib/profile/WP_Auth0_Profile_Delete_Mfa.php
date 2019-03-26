<?php
/**
 * Contains class WP_Auth0_Profile_Delete_Mfa.
 *
 * @package WP-Auth0
 *
 * @since 3.8.0
 */

/**
 * Class WP_Auth0_Profile_Delete_Mfa.
 * Provides UI and AJAX handlers to delete a user's MFA.
 * TODO: Deprecate
 */
class WP_Auth0_Profile_Delete_Mfa {

	/**
	 * WP_Auth0_Options instance.
	 *
	 * @var WP_Auth0_Options
	 */
	protected $a0_options;

	/**
	 * WP_Auth0_Api_Delete_User_Mfa instance.
	 *
	 * @var WP_Auth0_Api_Delete_User_Mfa
	 */
	protected $api_delete_mfa;

	/**
	 * WP_Auth0_Profile_Delete_Mfa constructor.
	 * TODO: Deprecate
	 *
	 * @param WP_Auth0_Options             $a0_options - WP_Auth0_Options instance.
	 * @param WP_Auth0_Api_Delete_User_Mfa $api_delete_mfa - WP_Auth0_Api_Delete_User_Mfa instance.
	 */
	public function __construct(
		WP_Auth0_Options $a0_options,
		WP_Auth0_Api_Delete_User_Mfa $api_delete_mfa
	) {
		$this->a0_options     = $a0_options;
		$this->api_delete_mfa = $api_delete_mfa;
	}

	/**
	 * Add actions and filters for the profile page.
	 *
	 * @codeCoverageIgnore - Tested in TestProfileDeleteMfa::testInitHooks()
	 */
	public function init() {
		add_action( 'edit_user_profile', array( $this, 'show_delete_mfa' ) );
		add_action( 'show_user_profile', array( $this, 'show_delete_mfa' ) );
		add_action( 'wp_ajax_auth0_delete_mfa', array( $this, 'delete_mfa' ) );
	}

	/**
	 * Show the delete Auth0 MFA data button.
	 * Hooked to: edit_user_profile, show_user_profile
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 */
	public function show_delete_mfa() {

		if ( ! isset( $GLOBALS['user_id'] ) || ! current_user_can( 'edit_users', $GLOBALS['user_id'] ) ) {
			return;
		}

		if ( ! $this->a0_options->get( 'mfa' ) ) {
			return;
		}

		if ( ! get_auth0userinfo( $GLOBALS['user_id'] ) ) {
			return;
		}
		?>
		<table class="form-table">
			<tr>
				<th>
					<label><?php _e( 'Delete MFA Provider', 'wp-auth0' ); ?></label>
				</th>
				<td>
					<input type="button" id="auth0_delete_mfa" class="button button-secondary"
						value="<?php _e( 'Delete MFA Provider', 'wp-auth0' ); ?>" />
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * AJAX function to delete the MFA provider at Auth0.
	 * Hooked to: wp_ajax_auth0_delete_data
	 * IMPORTANT: Internal callback use only, do not call this function directly!
	 */
	public function delete_mfa() {
		check_ajax_referer( 'delete_auth0_mfa' );

		if ( empty( $_POST['user_id'] ) ) {
			wp_send_json_error( array( 'error' => __( 'Empty user_id', 'wp-auth0' ) ) );
		}

		$user_id = $_POST['user_id'];

		if ( ! current_user_can( 'edit_users', $user_id ) ) {
			wp_send_json_error( array( 'error' => __( 'Forbidden', 'wp-auth0' ) ) );
		}

		$profile = get_auth0userinfo( $user_id );

		if ( ! $profile || empty( $profile->sub ) ) {
			wp_send_json_error( array( 'error' => __( 'Auth0 profile data not found', 'wp-auth0' ) ) );
		}

		if ( ! $this->api_delete_mfa->call( $profile->sub ) ) {
			wp_send_json_error( array( 'error' => __( 'API call failed', 'wp-auth0' ) ) );
		}

		wp_send_json_success();
	}
}
