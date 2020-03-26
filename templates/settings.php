<div class="a0-wrap settings wrap">

	<div class="container-fluid">

			<h1><?php _e( 'Login by Auth0 Settings', 'wp-auth0' ); ?></h1>

			<?php settings_errors(); ?>

			<p class="nav nav-tabs" role="tablist">
					<a id="tab-basic" href="#basic" class="js-a0-settings-tabs">
						<?php _e( 'Basic', 'wp-auth0' ); ?>
					</a>
					<a id="tab-features" href="#features" class="js-a0-settings-tabs">
						<?php _e( 'Features', 'wp-auth0' ); ?>
					</a>
					<a id="tab-appearance" href="#appearance" class="js-a0-settings-tabs">
						<?php _e( 'Embedded', 'wp-auth0' ); ?>
					</a>
					<a id="tab-advanced" href="#advanced" class="js-a0-settings-tabs">
						<?php _e( 'Advanced', 'wp-auth0' ); ?>
					</a>
					<a id="tab-help" href="#help" class="js-a0-settings-tabs">
						<?php _e( 'Help', 'wp-auth0' ); ?>
					</a>
			</p>

		<form action="options.php" method="post" id="js-a0-settings-form" class="a0-settings-form">
			<?php settings_fields( WP_Auth0_Options::Instance()->get_options_name() . '_basic' ); ?>

			<div class="tab-content">
				<?php foreach ( WP_Auth0_Admin::OPT_SECTIONS as $tab ) : ?>
					<div class="tab-pane" id="panel-<?php echo $tab; ?>">
						<?php do_settings_sections( WP_Auth0_Options::Instance()->get_options_name() . '_' . $tab ); ?>
					</div>
				<?php endforeach; ?>

				<div class="tab-pane" id="panel-help">

					<p>
						<?php
						_e( 'Thank you for installing Login by Auth0! Auth0 is a powerful identity solution that secures billions of logins every month. In addition to the options here, there are many more features available in the', 'wp-auth0' );
						?>
						<a href="https://manage.auth0.com" target="_blank"><?php _e( 'Auth0 dashboard', 'wp-auth0' ); ?></a>
						<?php _e( 'including:', 'wp-auth0' ); ?>
					</p>

					<ul class="list">
						<li><a href="https://auth0.com/docs/identityproviders" target="_blank">
								<?php
								_e( 'Many social and enterprise login connections', 'wp-auth0' )
								?>
							</a></li>
						<li><a href="https://auth0.com/docs/connections/passwordless" target="_blank">
								<?php
								_e( 'Passwordless login connections', 'wp-auth0' );
								?>
							</a></li>
						<li><a href="https://auth0.com/docs/anomaly-detection" target="_blank">
								<?php
								_e( 'Anomaly detection', 'wp-auth0' );
								?>
							</a></li>
						<li><a href="https://auth0.com/docs/rules/current" target="_blank">
								<?php
								_e( 'Profile enrichment, integrations, and other custom management tasks using Rules', 'wp-auth0' );
								?>
							</a></li>
					</ul>

					<p><?php _e( 'If you have issues or questions, we provide a variety of channels to assist:', 'wp-auth0' ); ?><p>

					<ul class="list">
						<li><a href="https://auth0.com/docs/cms/wordpress/configuration" target="_blank">
								<?php
								_e( 'Configuration documentation', 'wp-auth0' )
								?>
							</a> -
							<?php
							_e( 'If you are setting up the plugin for the first time or having issues after an upgrade, please review the settings to make sure your Application is setup correctly.', 'wp-auth0' )
							?>
						</li>
						<li><a href="https://community.auth0.com/tags/wordpress" target="_blank">
								<?php
								_e( 'Auth0 Community', 'wp-auth0' )
								?>
							</a> -
							<?php
							_e( 'If you have questions about how to use Auth0 or the plugin, please create a post (tagged "WordPress") if you do not find what you are looking for.', 'wp-auth0' )
							?>
						</li>
						<li><a href="https://github.com/auth0/wp-auth0/issues" target="_blank"><?php _e( 'GitHub Issues', 'wp-auth0' ); ?></a> -
							<?php _e( 'If you find a bug in the plugin code, the best place to report that is on GitHub under the Issues tab.', 'wp-auth0' ); ?>
						</li>
						<li><a href="https://support.auth0.com/" target="_blank"><?php _e( 'Support', 'wp-auth0' ); ?></a> -
							<?php _e( 'Customers on a paid Auth0 plan can submit trouble tickets for a quick response.', 'wp-auth0' ); ?>
						</li>
					</ul>
				</div>
			</div>

				<div class="a0-buttons">
					<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e( 'Save Changes', 'wp-auth0' ); ?>" />
				</div>
		</form>
	</div>
</div>
