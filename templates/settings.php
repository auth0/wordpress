<div class="a0-wrap settings">

	<?php require(WPA0_PLUGIN_DIR . 'templates/initial-setup/partials/header.php'); ?>

	<div class="container-fluid">

		<h1><?php _e('Auth0 Settings', WPA0_LANG); ?></h1>
	    <?php if( count(get_settings_errors()) == 0 && isset($_GET['settings-updated']) ) { ?>
	        <div id="message" class="updated">
	            <p><strong><?php _e('Settings saved.') ?></strong></p>
	        </div>
	    <?php } ?>
	    <?php settings_errors(); ?>
		<form action="options.php" method="post" onsubmit="return presubmit();">

			<ul class="nav nav-tabs" role="tablist">
		    <li role="presentation"><a href="#basic" aria-controls="basic" role="tab" data-toggle="tab">Basic</a></li>
		    <li role="presentation" class="active"><a href="#features" aria-controls="features" role="tab" data-toggle="tab">Features</a></li>
		    <li role="presentation"><a href="#appearance" aria-controls="appearance" role="tab" data-toggle="tab">Appearance</a></li>
		    <li role="presentation"><a href="#advanced" aria-controls="advanced" role="tab" data-toggle="tab">Advanced</a></li>
		  </ul>

		  <div class="tab-content">
		    <div role="tabpanel" class="tab-pane" id="basic">
		    	<?php settings_fields( WP_Auth0_Options::Instance()->get_options_name() . '_basic' ); ?>
					<?php do_settings_sections( WP_Auth0_Options::Instance()->get_options_name() . '_basic' ); ?>
		    </div>
		    <div role="tabpanel" class="tab-pane active" id="features">
		    	<?php settings_fields( WP_Auth0_Options::Instance()->get_options_name() . '_features' ); ?>
					<?php do_settings_sections( WP_Auth0_Options::Instance()->get_options_name() . '_features' ); ?>
		    </div>
		    <div role="tabpanel" class="tab-pane" id="appearance">
		    	<?php settings_fields( WP_Auth0_Options::Instance()->get_options_name() . '_appearance' ); ?>
					<?php do_settings_sections( WP_Auth0_Options::Instance()->get_options_name() . '_appearance' ); ?>
		    </div>
		    <div role="tabpanel" class="tab-pane" id="advanced">
		    	<?php settings_fields( WP_Auth0_Options::Instance()->get_options_name() . '_advanced' ); ?>
					<?php do_settings_sections( WP_Auth0_Options::Instance()->get_options_name() . '_advanced' ); ?>
		    </div>
		  </div>

			<div class="a0-buttons">			    
				<input type="submit" name="submit" id="submit" class="a0-button primary" value="Save Changes" />
			</div>
		</form>
	</div>
</div>

<script type="text/javascript">

	jQuery('.nav-tabs a').click(function (e) {
	  e.preventDefault()
	  jQuery(this).tab('show')
	})

	function presubmit() {
		if (typeof(a0metricsLib) !== 'undefined') {
			a0metricsLib.track('submit:settings', {
				wp_login_enabled: jQuery('#wpa0_wp_login_enabled').val(),
				sso: jQuery('#wpa0_sso').val(),
				singlelogout: jQuery('#wpa0_singlelogout').val(),
				form_title: jQuery('#wpa0_form_title').val(),
				social_big_buttons: jQuery('#wpa0_social_big_buttons').val(),
				gravatar: jQuery('#wpa0_gravatar').val(),
				custom_css: (jQuery('#wpa0_custom_css').val() !== ""),
				custom_js: (jQuery('#wpa0_custom_js').val() !== ""),
				username_style: jQuery('input[name=wp_auth0_settings\\[username_style\\]]:checked').val(),
				remember_last_login: jQuery('#wpa0_remember_last_login').val(),
				link_auth0_users: jQuery('#wpa0_link_auth0_users').val(),
				migration_ws: jQuery('#wpa0_auth0_migration_ws').val(),
				dict_setted: (jQuery('#wpa0_dict').val() !== false),
				implicit_workflow: jQuery('#wpa0_auth0_implicit_workflow').val(),
				auto_login: jQuery('#wpa0_auto_login').val(),
				auto_login_method: jQuery('#wpa0_auto_login_method').val(),
				ip_range_check: jQuery('#wpa0_ip_range_check').val(),
				has_extra_settings: jQuery('#wpa0_extra_conf').val(),
				cdn_url: jQuery('#wpa0_cdn_url').val()
			});
		}
		return true;
	}
</script>
