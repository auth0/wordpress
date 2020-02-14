<div class="a0-wrap settings wrap">

  <div class="container-fluid">

	  <h1><?php _e( 'Login by Auth0 Setup Wizard', 'wp-auth0' ); ?></h1>
	  <p class="a0-step-text"><?php _e( "Users can log in within their own credentials - social like Google or Facebook, or username and password -  or use their employee credentials through an enterprise connection. Use either or both and you'll increase your WordPress site's security and consolidate identity data.", 'wp-auth0' ); ?></p>
		<p><?php _e( 'Once configured, this plugin replaces the standard WordPress login screen (see the "WordPress Login Enabled" setting under the Basic tab to keep the WordPress login form enabled). Auth0 adds many features to make login easier and better for your users but the old system will still be there too.', 'wp-auth0' ); ?></p>
		<p>
		<?php
		  _e( 'For more information on installation and configuration, including manual steps, please see the', 'wp-auth0' );
		printf(
			' <strong><a href="https://auth0.com/docs/cms/wordpress/installation" target="_blank">%s</a></strong>',
			__( 'documentation pages here', 'wp-auth0' )
		);
		?>
		  .</p>

		<?php if ( wp_auth0_is_ready() ) : ?>

		  <div class="a0-step-text a0-message a0-warning">
			  <p>
				  <?php _e( 'Login by Auth0 is set up and ready for use.', 'wp-auth0' ); ?>
				  <?php _e( 'To start over and re-run the Setup Wizard:', 'wp-auth0' ); ?>
			  </p>
			  <ol>
				<li>
					<a href="<?php echo admin_url( 'admin.php?page=wpa0#basic' ); ?>">
						<?php _e( 'Go to Auth0 > Settings > Basic.', 'wp-auth0' ); ?>
					</a>
				</li>
				<li><?php _e( 'Delete the Domain, Client ID, and Client Secret and save changes.', 'wp-auth0' ); ?></li>
				<li><?php _e( 'Delete the created Application ', 'wp-auth0' ); ?>
					<a target="_blank"
					   href="https://manage.auth0.com/#/applications/<?php echo esc_attr( wp_auth0_get_option( 'client_id' ) ); ?>/settings" >
						<?php _e( 'here', 'wp-auth0' ); ?>
					</a>
				</li>
				<li>
					<?php _e( 'Delete the created Database Connection ', 'wp-auth0' ); ?>
					<a href="https://manage.auth0.com/#/connections/database" target="_blank">
						<?php _e( 'here', 'wp-auth0' ); ?>
					</a>.
					<?php _e( 'Please note that this will delete all Auth0 users for this connection.', 'wp-auth0' ); ?>
				</li>
			  </ol>
		  </div>

		<?php else : ?>

	  <div class="a0-step-text a0-message a0-warning">

		<b>Important:</b>
			<?php _e( 'To continue you need an Auth0 account.', 'wp-auth0' ); ?>

		<a class="a0-button default pull-right" target="_blank" href="https://auth0.com/signup" >
			<?php _e( 'Sign up for free', 'wp-auth0' ); ?>
		</a>

	  </div>

		<form action="options.php" method="POST">
		<?php wp_nonce_field( WP_Auth0_InitialSetup_ConnectionProfile::SETUP_NONCE_ACTION ); ?>
			<input type="hidden" name="action" value="wpauth0_callback_step1" />
			<?php wp_nonce_field( WP_Auth0_InitialSetup_ConnectionProfile::SETUP_NONCE_ACTION ); ?>
			<h3><?php _e( 'Standard Setup', 'wp-auth0' ); ?></h3>
			<p>
				<?php _e( 'This will create and configure an Application and a database connection for this site.', 'wp-auth0' ); ?>
				<a href="https://auth0.com/docs/cms/wordpress/installation#option-1-standard-setup" target="_blank">
					<?php _e( 'Detailed instructions for this option are here.', 'wp-auth0' ); ?>
				</a>
			</p>
			<p>
				<?php _e( 'Enter your tenant Domain', 'wp-auth0' ); ?>
				(<a href="https://auth0.com/docs/getting-started/the-basics#domains" target="_blank"><?php _e( 'more information', 'wp-auth0' ); ?></a>):
			</p>
			<input type="text" name="domain" class="js-a0-setup-input" placeholder="tenant-name.auth0.com" required>
			<p>
				<a href="https://auth0.com/docs/api/management/v2/get-access-tokens-for-test#get-access-tokens-manually"
					target="_blank">
					<?php _e( 'Create a Management API token using these steps', 'wp-auth0' ); ?>
				</a>
				<?php _e( ' and paste it below:', 'wp-auth0' ); ?>
			</p>

			<input type="text" name="apitoken" class="js-a0-setup-input" autocomplete="off" required>
			<p>
				<?php _e( 'Scopes required', 'wp-auth0' ); ?>:
				<code><?php echo implode( '</code> <code>', WP_Auth0_Api_Client::ConsentRequiredScopes() ); ?></code>
			</p>
			<p><input type="submit" class="a0-button primary" value="<?php _e( 'Start Standard Setup', 'wp-auth0' ); ?>"/></p>
		</form>

		<form action="options.php" method="POST">
			<?php wp_nonce_field( WP_Auth0_InitialSetup_ConnectionProfile::SETUP_NONCE_ACTION ); ?>
			<input type="hidden" name="action" value="wpauth0_callback_step1"/>
			<h3><?php _e( 'User Migration Setup', 'wp-auth0' ); ?></h3>
			<p>
			<?php _e( 'This includes everything above plus data migration from your WordPress database.', 'wp-auth0' ); ?>
			<?php _e( 'This requires an inbound connection from Auth0 servers and cannot be changed later without losing data.', 'wp-auth0' ); ?>
				<a href="https://auth0.com/docs/cms/wordpress/how-does-it-work#scenario-data-migration" target="_blank">
			<?php _e( 'More information here.', 'wp-auth0' ); ?>
				</a>
			</p>
			<p><input type="submit" class="a0-button primary" value="<?php _e( 'Start User Migration Setup', 'wp-auth0' ); ?>"/></p>
		</form>

		<h3><?php _e( 'Manual Setup', 'wp-auth0' ); ?></h3>
		<p><?php _e( 'If you already have an Application or want to use an existing database connection, please follow the steps below.', 'wp-auth0' ); ?></p>
		<p><a class="a0-button primary" href="https://auth0.com/docs/cms/wordpress/installation#manual-setup"
			  target="_blank"><?php _e( 'Manual Setup Instructions', 'wp-auth0' ); ?></a></p>

		<h3><?php _e( 'Import Setup', 'wp-auth0' ); ?></h3>
		<p>
			<?php _e( 'Already set up another WordPress instance with Auth0?', 'wp-auth0' ); ?>
			<?php _e( 'Save time and import existing Auth0 settings.', 'wp-auth0' ); ?>
		</p>

		<p><a class="a0-button primary" href="<?php echo admin_url( 'admin.php?page=wpa0-import-settings' ); ?>">
						<?php _e( 'Import Settings', 'wp-auth0' ); ?></a></p>

		<?php endif; ?>
  </div>
</div>
