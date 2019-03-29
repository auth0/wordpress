<div class="a0-wrap">

  <?php require WPA0_PLUGIN_DIR . 'templates/initial-setup/partials/header.php'; ?>

  <div class="container-fluid">
	<div class="row">
		<div class="col-lg-12">
	  <h1><?php _e( 'Step 1: Choose your account type', 'wp-auth0' ); ?></h1>
	  <p class="a0-step-text"><?php _e( "Users can log in within their own credentials - social like Google or Facebook, or username and password -  or use their employee credentials through an enterprise connection. Use either or both and you'll increase your WordPress site's security and consolidate identity data.", 'wp-auth0' ); ?></p><br>
		<p><?php _e( 'Once configured, this plugin replaces the standard WordPress login screen (see the "WordPress Login Enabled" setting under the Basic tab to keep the WordPress login form enabled). Auth0 adds many features to make login easier and better for your users but the old system will still be there too.', 'wp-auth0' ); ?></p><br>
		<p>
		<?php
		  _e( 'For more information on installation and configuration, including manual steps, please see the', 'wp-auth0' );
		printf(
			' <strong><a href="https://auth0.com/docs/cms/wordpress" target="_blank">%s</a></strong>',
			__( 'documentation pages here' )
		);
		?>
		  .</p>
		</div>
	</div>
		<?php if ( WP_Auth0::ready() ) : ?>
	  <div class="row">
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
				<li><?php _e( 'Delete the Domain and Client ID and save changes.', 'wp-auth0' ); ?></li>
				<li><?php _e( 'Delete the created Application ', 'wp-auth0' ); ?>
					<a target="_blank"
					   href="https://manage.auth0.com/#/applications/<?php echo WP_Auth0_Options::Instance()->get( 'client_id' ); ?>/settings" >
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
	  </div>
		<?php else : ?>
	<div class="row">
	  <div class="a0-step-text a0-message a0-warning">

		<b>Important:</b>
			<?php _e( 'To continue you need an Auth0 account.', 'wp-auth0' ); ?>

		<a class="a0-button default pull-right" target="_blank" href="https://auth0.com/signup" >
			<?php _e( 'Sign up for free', 'wp-auth0' ); ?>
		</a>

	  </div>
		<div class="a0-step-text a0-message a0-tip">
			<b><?php _e( 'Pro Tip' ); ?>:</b>
			<?php _e( 'Already set up another WordPress instance with Auth0? ' ); ?>
			<a href="<?php echo admin_url( 'admin.php?page=wpa0-import-settings' ); ?>"><?php _e( 'Click here' ); ?></a>
			<?php _e( ' to save time and import existing Auth0 settings.' ); ?>
		</div>
	</div>
	<div class="a0-profiles row">
	  <form action="options.php" method="POST" id="profile-form">

		<input type="hidden" name="action" value="wpauth0_callback_step1" />

		<div class="col col-sm-6">
		  <div class="profile">
			<img src="<?php echo WPA0_PLUGIN_URL; ?>/assets/img/initial-setup/simple-end-users-login.svg">

			<h2><?php _e( 'Standard', 'wp-auth0' ); ?></h2>

			<p><?php _e( 'Allow users to login using social, username and password, or passwordless connections.', 'wp-auth0' ); ?></p>

			<div class="a0-buttons">
			  <input type="submit" value="<?php _e( 'Standard', 'wp-auth0' ); ?>" name="type" data-profile-type="social" class="a0-button primary js-a0-select-setup"/>
			</div>
		  </div>
		</div>

		<div class="col col-sm-6">
		  <div class="profile">
			<img src="<?php echo WPA0_PLUGIN_URL; ?>/assets/img/initial-setup/effortless-employee-access.svg">

			<h2><?php _e( 'Enterprise', 'wp-auth0' ); ?></h2>

			<p><?php _e( "Secure this WordPress instance with your organization's login system, like ActiveDirectory", 'wp-auth0' ); ?></p>

			<div class="a0-buttons">
			  <input type="submit" value="<?php _e( 'Enterprise', 'wp-auth0' ); ?>" data-profile-type="enterprise" name="type" class="a0-button primary js-a0-select-setup"/>
			</div>
		  </div>
		</div>

		<input type="hidden" value="" name="profile-type" id="profile-type"/>

		<div class="modal fade" id="connectionSelectedModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
		  <div class="modal-dialog" role="document">
			<div class="modal-content">
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="<?php _e( 'Close', 'wp-auth0' ); ?>">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title" id="connectionSelectedModalLabel"><?php _e( 'Important', 'wp-auth0' ); ?></h4>
			  </div>
			  <div class="modal-body no-padding-bottom">
				  <h4><?php _e( 'Standard Setup', 'wp-auth0' ); ?></h4>
				  <p><?php _e( 'This will create and configure an Application and a database connection for this site.', 'wp-auth0' ); ?></p>
				  <p><a class="a0-button primary" href="#" id="manuallySetToken"><?php _e( 'Start Standard Setup', 'wp-auth0' ); ?></a></p>
				  <br>

				  <h4><?php _e( 'User Migration Setup', 'wp-auth0' ); ?></h4>
				  <p>
						<?php _e( 'This includes everything above plus data migration from your WordPress database.', 'wp-auth0' ); ?>
						<?php _e( 'This requires an inbound connection from Auth0 servers and cannot be changed later without losing data.', 'wp-auth0' ); ?>
					  <a href="https://auth0.com/docs/cms/wordpress/how-does-it-work#scenario-data-migration" target="_blank">
						<?php _e( 'More information here.', 'wp-auth0' ); ?>
					  </a></p>
				  <p><a class="a0-button primary submit" href="#" id="automaticSetup">
					<?php
						  _e( 'Start User Migration Setup', 'wp-auth0' )
					?>
						  </a></p>
				  <br>

				  <h4><?php _e( 'Manual Setup', 'wp-auth0' ); ?></h4>
				  <p><?php _e( 'If you already have an Application or want to use an existing database connection, please follow the steps below.', 'wp-auth0' ); ?></p>
				  <p><a class="a0-button primary" href="https://auth0.com/docs/cms/wordpress/installation#manual-setup"
						target="_blank"><?php _e( 'Manual Setup Instructions', 'wp-auth0' ); ?></a></p>
				  <br>
			  </div>
			</div>
		  </div>
		</div>

		<div class="modal fade" id="enterTokenModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
		  <div class="modal-dialog" role="document">
			<div class="modal-content">
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="<?php _e( 'Close', 'wp-auth0' ); ?>">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title" id="enterTokenModalModalLabel"><?php _e( 'Important', 'wp-auth0' ); ?></h4>
			  </div>
			  <div class="modal-body">
				<p>
					<?php _e( 'Enter your tenant Domain:', 'wp-auth0' ); ?>
				</p>
				<input type="text" name="domain" class="js-a0-setup-input" placeholder="youraccount.auth0.com" required>
				<br><br>
				<p>
					<?php _e( 'Manually create an API token with the', 'wp-auth0' ); ?>
				  <a href="https://auth0.com/docs/api/management/v2/tokens#get-a-token-manually" target="_blank">
						<?php _e( 'token generator', 'wp-auth0' ); ?></a>
					<?php _e( ' and paste it below:', 'wp-auth0' ); ?>
				</p>
				  <p>
					  <small>
						  <?php _e( 'Scopes required', 'wp-auth0' ); ?>:
						  <code><?php echo implode( '</code> <code>', WP_Auth0_Api_Client::ConsentRequiredScopes() ); ?></code>
					  </small>
				  </p>
				<input type="password" name="apitoken" class="js-a0-setup-input" autocomplete="off" required>

			  </div>
			  <div class="modal-footer">
				<input type="submit" class="a0-button primary" value="<?php _e( 'Continue', 'wp-auth0' ); ?>"/>
			  </div>
			</div>
		  </div>
		</div>

	  </form>
	</div>
		<?php endif; ?>
  </div>
</div>
