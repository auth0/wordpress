<div class="a0-wrap settings">

	<?php require WPA0_PLUGIN_DIR . 'templates/initial-setup/partials/header.php'; ?>

	<div class="container-fluid">

		<div class="row">
			<h1><?php _e( 'Auth0 WordPress Plugin Settings', 'wp-auth0' ); ?></h1>

			<div class="row a0-message a0-warning manage">
				For your Auth0 dashboard with more settings and connection options click <a target="_blank" href="https://manage.auth0.com/#/clients/<?php echo WP_Auth0_Options::Instance()->get('client_id'); ?>/connections">here</a>.
			</div>

	    <?php if ( count( get_settings_errors() ) == 0 && isset( $_GET['settings-updated'] ) ) { ?>
	        <div id="message" class="updated">
	            <p><strong><?php _e( 'Settings saved.' ) ?></strong></p>
	        </div>
	    <?php } ?>
	    <?php settings_errors(); ?>

	    <ul class="nav nav-tabs" role="tablist">
		    <?php foreach ( array( 'basic', 'features', 'appearance', 'advanced', 'help' ) as $tab ) : ?>
		      <li role="presentation"><a id="tab-<?php echo $tab ?>" href="#<?php echo $tab ?>" aria-controls="<?php
			      echo $tab ?>" role="tab" data-toggle="tab"><?php echo ucfirst( $tab ) ?></a></li>
		    <?php endforeach; ?>
		  </ul>
		</div>
		<form action="options.php" method="post" id="js-a0-settings-form" class="a0-settings-form">
			<?php settings_fields( WP_Auth0_Options::Instance()->get_options_name() . '_basic' ); ?>

		  <div class="tab-content">
			  <?php foreach ( array( 'basic', 'features', 'appearance', 'advanced' ) as $tab ) : ?>
				  <div role="tabpanel" class="tab-pane row" id="<?php echo $tab ?>">
					  <?php do_settings_sections( WP_Auth0_Options::Instance()->get_options_name() . '_' . $tab ); ?>
				  </div>
			  <?php endforeach; ?>

		    <div role="tabpanel" class="tab-pane row" id="help">

					<p>Thank you for installing <a href="https://auth0.com/wordpress" target="_blank">Login by Auth0</a>! Auth0 is a powerful identity solution that that secures billions of logins every month. In addition to the options here, there are many more features available in the <a href="https://manage.auth0.com" target="_blank">Auth0 dashboard</a>, including:</p>

			    <ul class="list">
				    <li>Many social and enterprise <a href="https://auth0.com/docs/identityproviders" target="_blank">login connections</a></li>
				    <li><a href="https://auth0.com/docs/connections/passwordless" target="_blank">Passwordless login connections</a></li>
				    <li><a href="https://auth0.com/docs/anomaly-detection" target="_blank">Anomaly detection</a></li>
				    <li>Profile enrichment, app integrations, and other custom user management tasks using <a href="https://auth0.com/docs/rules/current" target="_blank">Rules</a></li>
			    </ul>

			    <p>If you have any issues or questions, we provide a variety of channels to assist:<p>

					<ul class="list">
				    <li>If you're setting up the plugin for the first time or having issues after an upgrade, please review the <a href="https://auth0.com/docs/cms/wordpress/configuration" target="_blank">plugin configuration page</a> to make sure your Application is setup correctly.</li>
				    <li>If you have questions about how to use Auth0 or the plugin, please <a href="https://community.auth0.com/tags/wordpress" target="_blank">search our community site</a> and create a post (tagged "WordPress") if you don't find what you're looking for.</li>
				    <li>If you find a bug in the plugin code, <a href="https://github.com/auth0/wp-auth0/issues" target="_blank">submit an issue</a> or <a href="https://github.com/auth0/wp-auth0/pulls" target="_blank">create a pull request</a> on Github.</li>
				    <li>You can see additional documentation and answers on our <a href="https://support.auth0.com/" target="_blank">support site</a>. Customers on a paid Auth0 plan can submit trouble tickets for a quick response.</li>
					</ul>

					<div class="a0-feedback">
						<div>

							<h2>How is the Auth0 WP plugin working for you?</h2>

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
							<h2>What one thing would you change?</h2>
							<textarea id="feedback_text" placeholder="Be as brief or detailed as you like!"></textarea>
						</div>

						<div>
							<div class="a0-buttons">
								<span class="a0-button primary" onclick="send_feedback()">Send!</span>
							</div>
						</div>
					</div>

		    </div>
		  </div>

			<div class="row">
				<div class="a0-buttons">
					<input type="submit" name="submit" id="submit" class="a0-button primary" value="Save Changes" />
				</div>
			</div>
		</form>
	</div>
</div>

<script type="text/javascript">

	document.addEventListener("DOMContentLoaded", function() {

		var q = async.queue(function (task, callback) {

			var data = {
				action: 'a0_initial_setup_set_connection',
				connection: task.connection,
				enabled: task.enabled
			};

			onToggleConnection(task.connection, task.enabled);

			jQuery.post(ajaxurl, data, function(response) {
				callback();
			});

		}, 1);

		q.drain = function() {
			window.onbeforeunload = null;
			jQuery('#connections .loading').fadeOut();
		}

		jQuery('.connection .a0-switch input').click(function(e) {

			var data = {
				connection: e.target.value,
				enabled: e.target.checked
			};

			q.push(data);
			jQuery('#connections .loading').fadeIn();
			window.onbeforeunload = confirmExit;

		});

		jQuery('#wpa0_fullcontact').click(function() {
			if (this.checked) {
				jQuery('.subelement.fullcontact').removeClass('hidden');
			} else {
				jQuery('.subelement.fullcontact').addClass('hidden');
			}
		});
	});

	function confirmExit() {
    return "There are some pending actions. if you leave the page now, some connection will not be updated.";
	}

	function send_feedback() {
		var url = 'https://sandbox.it.auth0.com/api/run/wptest/wp-auth0-slack?webtask_no_cache=1';
		var data = {
			"score": jQuery('.feedback_calification:checked').val(),
			"account": '<?php echo $tenant; ?>',
			"feedback": jQuery('#feedback_text').val()
		};
		jQuery.post(url, data, function(response) {
			jQuery('.a0-feedback').html('<h2 class="message">Done! Thank you for your feedback.</h2>')
		});
	}
</script>
