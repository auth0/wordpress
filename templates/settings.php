<div class="a0-wrap settings">

	<?php require WPA0_PLUGIN_DIR . 'templates/initial-setup/partials/header.php'; ?>

	<div class="container-fluid">

		<div class="row">
			<h1><?php _e( 'Auth0 WordPress Plugin Settings', 'wp-auth0' ); ?></h1>

			<div class="row a0-message a0-warning manage">
				<?php _e( 'For your Auth0 dashboard with more settings and connection options click', 'wp-auth0' ); ?>
				<a target="_blank" href="https://manage.auth0.com/#/clients/<?php echo WP_Auth0_Options::Instance()->get( 'client_id' ); ?>/connections"><?php _e( 'here', 'wp-auth0' ); ?></a>.
			</div>

			<?php if ( count( get_settings_errors() ) == 0 && isset( $_GET['settings-updated'] ) ) { ?>
				<div id="message" class="updated">
					<p><strong><?php _e( 'Settings saved.' ); ?></strong></p>
				</div>
			<?php } ?>
			<?php settings_errors(); ?>

			<ul class="nav nav-tabs" role="tablist">
				<li role="presentation">
					<a id="tab-basic" href="#basic" aria-controls="basic" role="tab" data-toggle="tab" class="js-a0-settings-tabs">
						<?php _e( 'Basic', 'wp-auth0' ); ?>
					</a>
				</li>
				<li role="presentation">
					<a id="tab-features" href="#features" aria-controls="features" role="tab" data-toggle="tab" class="js-a0-settings-tabs">
						<?php _e( 'Features', 'wp-auth0' ); ?>
					</a>
				</li>
				<li role="presentation">
					<a id="tab-appearance" href="#appearance" aria-controls="appearance" role="tab" data-toggle="tab" class="js-a0-settings-tabs">
						<?php _e( 'Appearance', 'wp-auth0' ); ?>
					</a>
				</li>
				<li role="presentation">
					<a id="tab-advanced" href="#advanced" aria-controls="advanced" role="tab" data-toggle="tab" class="js-a0-settings-tabs">
						<?php _e( 'Advanced', 'wp-auth0' ); ?>
					</a>
				</li>
				<li role="presentation">
					<a id="tab-help" href="#help" aria-controls="help" role="tab" data-toggle="tab" class="js-a0-settings-tabs">
						<?php _e( 'Help', 'wp-auth0' ); ?>
					</a>
				</li>
			</ul>
		</div>
		<form action="options.php" method="post" id="js-a0-settings-form" class="a0-settings-form">
			<?php settings_fields( WP_Auth0_Options::Instance()->get_options_name() . '_basic' ); ?>

			<div class="tab-content">
				<?php foreach ( array( 'basic', 'features', 'appearance', 'advanced' ) as $tab ) : ?>
					<div role="tabpanel" class="tab-pane row" id="<?php echo $tab; ?>">
						<?php do_settings_sections( WP_Auth0_Options::Instance()->get_options_name() . '_' . $tab ); ?>
					</div>
				<?php endforeach; ?>

				<div role="tabpanel" class="tab-pane row" id="help">

					<p>
						<?php
						_e( 'Thank you for installing Login by Auth0! Auth0 is a powerful identity solution that that secures billions of logins every month. In addition to the options here, there are many more features available in the', 'wp-auth0' );
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
							_e( 'If you are setting up the plugin for the first time or having issues after an upgrade, please review the  to make sure your Application is setup correctly.', 'wp-auth0' )
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

					<div class="a0-feedback">
						<div>

							<h2><?php _e( 'How is the Auth0 WP plugin working for you?', 'wp-auth0' ); ?></h2>

							<div>
								<input type="radio" name="feedback_calification" class="feedback_calification" id="feedback_calification_1" value="1" />
								<label for="feedback_calification_1" class="feedback-face calification-1"></label>

								<input type="radio" name="feedback_calification" class="feedback_calification" id="feedback_calification_2" value="2" />
								<label for="feedback_calification_2" class="feedback-face calification-2"></label>

								<input type="radio" name="feedback_calification" class="feedback_calification" id="feedback_calification_3" value="3" />
								<label for="feedback_calification_3" class="feedback-face calification-3"></label>

								<input type="radio" name="feedback_calification" class="feedback_calification" id="feedback_calification_4" value="4" />
								<label for="feedback_calification_4" class="feedback-face calification-4"></label>

								<input type="radio" name="feedback_calification" class="feedback_calification" id="feedback_calification_5" value="5" />
								<label for="feedback_calification_5" class="feedback-face calification-5"></label>
							</div>
						</div>

						<div class="a0-separator"></div>

						<div>
							<h2><?php _e( 'What one thing would you change?', 'wp-auth0' ); ?></h2>
							<textarea id="feedback_text" placeholder="
							<?php
							_e( 'Be as brief or detailed as you like!', 'wp-auth0' )
							?>
			  "></textarea>
						</div>

						<div>
							<div class="a0-buttons">
								<span class="a0-button primary" onclick="send_feedback()"><?php _e( 'Send Feedback', 'wp-auth0' ); ?></span>
							</div>
						</div>
					</div>

				</div>
			</div>

			<div class="row">
				<div class="a0-buttons">
					<input type="submit" name="submit" id="submit" class="a0-button primary" value="<?php _e( 'Save Changes', 'wp-auth0' ); ?>" />
				</div>
			</div>
		</form>
	</div>
</div>

<script type="text/javascript">
	function send_feedback() {
		var url = 'https://sandbox.it.auth0.com/api/run/wptest/wp-auth0-slack?webtask_no_cache=1';
		var data = {
			"score": jQuery('.feedback_calification:checked').val(),
			"account": '<?php echo WP_Auth0::get_tenant(); ?>',
			"feedback": jQuery('#feedback_text').val()
		};
		var successMsg = "<?php _e( 'Done! Thank you for your feedback.', 'wp-auth0' ); ?>";
		jQuery.post(url, data, function(response) {
			jQuery('.a0-feedback').html('<h2 class="message">' + successMsg + '</h2>')
		});
	}
</script>
