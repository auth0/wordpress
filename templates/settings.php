<div class="a0-wrap settings">

	<?php require WPA0_PLUGIN_DIR . 'templates/initial-setup/partials/header.php'; ?>

	<div class="container-fluid">

		<div class="row">
			<h1><?php _e( 'Auth0 WordPress Plugin Settings', 'wp-auth0' ); ?></h1>

			<div class="row a0-message a0-warning manage">
				For your Auth0 dashboard with more settings click <a target="_blank" href="https://manage.auth0.com">here</a>.
			</div>

	    <?php if ( count( get_settings_errors() ) == 0 && isset( $_GET['settings-updated'] ) ) { ?>
	        <div id="message" class="updated">
	            <p><strong><?php _e( 'Settings saved.' ) ?></strong></p>
	        </div>
	    <?php } ?>
	    <?php settings_errors(); ?>

	    <ul class="nav nav-tabs" role="tablist">
		    <li role="presentation"><a id="tab-basic" href="#basic" aria-controls="basic" role="tab" data-toggle="tab">Basic</a></li>
		    <li role="presentation"><a id="tab-features" href="#features" aria-controls="features" role="tab" data-toggle="tab">Features</a></li>
		    <li role="presentation"><a id="tab-connections" href="#connections" aria-controls="connections" role="tab" data-toggle="tab">Connections</a></li>
		    <li role="presentation"><a id="tab-appearance" href="#appearance" aria-controls="appearance" role="tab" data-toggle="tab">Appearance</a></li>
		    <li role="presentation"><a id="tab-advanced" href="#advanced" aria-controls="advanced" role="tab" data-toggle="tab">Advanced</a></li>
		    <li role="presentation"><a id="tab-dashboard" href="#dashboard" aria-controls="dashboard" role="tab" data-toggle="tab">Dashboard</a></li>
		    <li role="presentation"><a id="tab-help" href="#help" aria-controls="help" role="tab" data-toggle="tab">Help</a></li>
		  </ul>
		</div>
		<form action="options.php" method="post" onsubmit="return presubmit();">
			<?php settings_fields( WP_Auth0_Options::Instance()->get_options_name() . '_basic' ); ?>

		  <div class="tab-content">
		    <div role="tabpanel" class="tab-pane row" id="basic">
					<?php do_settings_sections( WP_Auth0_Options::Instance()->get_options_name() . '_basic' ); ?>
		    </div>
		    <div role="tabpanel" class="tab-pane row active" id="features">
					<?php do_settings_sections( WP_Auth0_Options::Instance()->get_options_name() . '_features' ); ?>
		    </div>
		    <div role="tabpanel" class="tab-pane" id="connections">
		    	<div class="loading" style="display:none;">
		    		<div class="a0-spinner-css"></div>
		    		<span>Updating the connections settings</span>
		    	</div>

		    	<div class="a0-message a0-tip row">
      			<b>Pro Tip:</b>
      			To set your own app keys and settings for the social connections, access the <a target="_blank" href="https://manage.auth0.com/#/connections/social">Auth0 Dashboard</a>.
       		</div>

		    	<div class="connections row">
					  <?php foreach ( $social_connections as $social_connection ) { ?>
					    <div class="connection col-sm-4 col-xs-6">
					      <div class="logo" data-logo="<?php echo $social_connection['icon']; ?>">
					        <span class="logo-child"></span>
					      </div>

					      <div class="a0-switch">
					        <input type="checkbox" name="social_<?php echo $social_connection['provider']; ?>" id="wpa0_social_<?php echo $social_connection['provider']; ?>" value="<?php echo $social_connection['provider']; ?>" <?php echo checked( $social_connection['status'], 1, false ); ?>/>
					        <label for="wpa0_social_<?php echo $social_connection['provider']; ?>"></label>
					      </div>
					    </div>
					  <?php } ?>
					</div>
		    </div>
		    <div role="tabpanel" class="tab-pane row" id="appearance">
					<?php do_settings_sections( WP_Auth0_Options::Instance()->get_options_name() . '_appearance' ); ?>
		    </div>
		    <div role="tabpanel" class="tab-pane row" id="advanced">
					<?php do_settings_sections( WP_Auth0_Options::Instance()->get_options_name() . '_advanced' ); ?>
		    </div>
		    <div role="tabpanel" class="tab-pane row" id="dashboard">
					<?php do_settings_sections( WP_Auth0_Options::Instance()->get_options_name() . '_dashboard' ); ?>
		    </div>
		    <div role="tabpanel" class="tab-pane row" id="help">

					<p>Thank you for installing the <a href="https://auth0.com/wordpress">Auth0 WordPress Plugin</a>.</p>

					<p>This plugin allows you to connect your WP instance to many login solutions. If you have many users commenting or buying products from you, then our social connectors will help. They will let users log in using things like Twitter, Google or Facebook. We also support many enterprise login systems like Active Directory.</p>

					<p>Auth0 is a powerful solution and besides the options you see here on your WordPress instance, there are many more things you can do on your <a href="https://manage.auth0.com">Auth0 dashboard</a>. The dashboard allows you to enable more authentication providers and activate advanced features like running javascript snippets on a login event to do things like record activity or send an email. But don't worry, if you just want to enable social logins for your site, you can safely stay within the options here.</p>

					<p>If you're having any issues - please contact us. We have a variety of channels to help you:<p>

					<ul class="list">
						<li>We have a lot of documentation at <a href="https://auth0.com/docs">our help website</a></li>
						<li>Our <a href="https://ask.auth0.com">forums</a> where you can look the discussion threads or open a new one asking for help.</li>
						<li>Our <a href="https://support.auth0.com">support center</a> to open a support ticket.</li>
						<li>For more information on Auth0, see <a href="https://auth0.com/blog">our blog</a></li>
					</ul>

					<div class="a0-feedback">
						<div>

							<h2>Please give us your feedback, how is the Auth0 WP plugin working for you?</h2>

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
							<textarea id="feedback_text" placeholder="Please feel free to be as brief or detailed as you like"></textarea>
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

	function presubmit() {
		metricsTrack('settings:save');
		return true;
	}

	function onToggleConnection(connection, enabled) {
	  metricsTrack('settings:'+connection+':'+(enabled ? 'on' : 'off'));
	}

	document.addEventListener("DOMContentLoaded", function() {
		var tab = (window.location.hash || 'features').replace('#','');

		checkTab(tab);

		jQuery('#tab-'+tab).tab('show');

		jQuery('.nav-tabs a').click(function (e) {
			checkTab(jQuery(this).attr('aria-controls'));
		})

		jQuery('input[type=checkbox]').change(function(){
			var matches = /\[([a-zA-Z0-9_-].*)\]/.exec(this.name);
			if (matches[1]) {
				metricsTrack('settings:'+matches[1]+':'+(this.checked ? 'on' : 'off'));
			}
		});
		jQuery('input[type=radio]').change(function(){
			var matches = /\[([a-zA-Z0-9_-].*)\]/.exec(this.name);
			if (matches[1]) {
				metricsTrack('settings:'+matches[1], this.value);
			}
		});
		jQuery('#wpa0_passwordless_cdn_url,#wpa0_cdn_url,#wpa0_connections').focusout(function(){
			var matches = /\[([a-zA-Z0-9_-].*)\]/.exec(this.name);
			if (matches[1]) {
				metricsTrack('settings:'+matches[1], this.value);
			}
		});

		jQuery('#wpa0_social_twitter_key,#wpa0_social_twitter_secret,#wpa0_social_facebook_key,#wpa0_social_facebook_secret').focusout(function(){
			var matches = /\[([a-zA-Z0-9_-].*)\]/.exec(this.name);
			if (matches[1]) {
				metricsTrack('settings:'+matches[1]+":"+(this.value === "" ? 'off' : 'on'));
			}
		});

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
		jQuery('#wpa0_passwordless_enabled').click(function() {
			if (this.checked) {
				jQuery('#wpa0_cdn_url').hide();
				jQuery('#wpa0_use_lock_10').parent().parent().parent().hide();
				jQuery('#wpa0_passwordless_cdn_url').show();
				jQuery('#wpa0_passwordless_method_social').parent().parent().show();
			} else {
				jQuery('#wpa0_passwordless_method_social').parent().parent().hide();
				jQuery('#wpa0_passwordless_cdn_url').hide();
				jQuery('#wpa0_cdn_url').show();
				jQuery('#wpa0_use_lock_10').parent().parent().parent().show();
			}
		});
		jQuery('#wpa0_use_lock_10').click(function() {
			if (this.checked) {
				jQuery('#wpa0_cdn_url').val("<?php echo $options->get_default('cdn_url') ?>");
			} else {
				jQuery('#wpa0_cdn_url').val("<?php echo $options->get_default('cdn_url_legacy') ?>");
			}
		});

		if (jQuery('#wpa0_passwordless_enabled:checked').length === 0) {
			jQuery('#wpa0_passwordless_method_social').parent().parent().hide();
		}

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

	function checkTab(tab) {
		if (tab == 'help') {
			jQuery('#submit').hide();
		} else {
			jQuery('#submit').show();
		}
	}
</script>
