<div class="a0-wrap settings">

	<?php require(WPA0_PLUGIN_DIR . 'templates/initial-setup/partials/header.php'); ?>

	<div class="container-fluid">

		<div class="row">
			<h1><?php _e('Auth0 WordPress Plugin Settings', WPA0_LANG); ?></h1>

			<p class="row a0-message a0-warning manage">
				For your Auth0 dashboard with more settings click <a href="https://manage.auth0.com">here</a>.
			</p>
			
	    <?php if( count(get_settings_errors()) == 0 && isset($_GET['settings-updated']) ) { ?>
	        <div id="message" class="updated">
	            <p><strong><?php _e('Settings saved.') ?></strong></p>
	        </div>
	    <?php } ?>
	    <?php settings_errors(); ?>

	    <ul class="nav nav-tabs" role="tablist">
		    <li role="presentation"><a href="#basic" aria-controls="basic" role="tab" data-toggle="tab">Basic</a></li>
		    <li role="presentation" class="active"><a href="#features" aria-controls="features" role="tab" data-toggle="tab">Features</a></li>
		    <li role="presentation"><a href="#connections" aria-controls="connections" role="tab" data-toggle="tab">Connections</a></li>
		    <li role="presentation"><a href="#appearance" aria-controls="appearance" role="tab" data-toggle="tab">Appearance</a></li>
		    <li role="presentation"><a href="#advanced" aria-controls="advanced" role="tab" data-toggle="tab">Advanced</a></li>
		    <li role="presentation"><a href="#dashboard" aria-controls="dashboard" role="tab" data-toggle="tab">Dashboard</a></li>
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

		    	<p class="a0-message a0-tip row">
      			<b>Pro Tip:</b>
      			To set your own app keys and settings for the social connections, access the <a target="_blank" href="https://manage.auth0.com/#/connections/social">Auth0 Dashboard</a>. 
       		</p>

		    	<div class="connections row">
					  <?php foreach($social_connections as $social_connection) { ?>
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
					<?php do_settings_sections( WP_Auth0_Options::Instance()->get_options_name() . '_dashboard'); ?>
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

		jQuery('.nav-tabs a').click(function (e) {
		  e.preventDefault()
		  jQuery(this).tab('show')
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
				jQuery('#wpa0_passwordless_cdn_url').show();
				jQuery('#wpa0_passwordless_method_social').parent().parent().show();
			} else {
				jQuery('#wpa0_passwordless_method_social').parent().parent().hide();
				jQuery('#wpa0_passwordless_cdn_url').hide();
				jQuery('#wpa0_cdn_url').show();
			}
		});

		if (jQuery('#wpa0_passwordless_enabled:checked').length === 0) {
			jQuery('#wpa0_passwordless_method_social').parent().parent().hide();
		}

	});

	function confirmExit() {
    return "There are some pending actions. if you leave the page now, some connection will not be updated.";
	}
</script>

