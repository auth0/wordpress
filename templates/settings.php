<div class="wrap">
	<?php screen_icon(); ?>
	<h2><?php _e('Auth0 Settings', WPA0_LANG); ?></h2>
    <?php if( count(get_settings_errors()) == 0 && isset($_GET['settings-updated']) ) { ?>
        <div id="message" class="updated">
            <p><strong><?php _e('Settings saved.') ?></strong></p>
        </div>
    <?php } ?>
    <?php settings_errors(); ?>
	<form action="options.php" method="post" onsubmit="return presubmit();">
		<?php settings_fields( WP_Auth0_Options::Instance()->get_options_name() ); ?>
		<?php do_settings_sections( WP_Auth0_Options::Instance()->get_options_name() ); ?>
		<?php submit_button(); ?>
	</form>
</div>

<script type="text/javascript">
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
