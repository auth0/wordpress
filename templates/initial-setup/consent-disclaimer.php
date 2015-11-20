<div class="wrap">

	<img src="http://cdn.auth0.com/styleguide/latest/img/badge.png" />

	<h2><?php _e('Get started with Auth0 for WordPress', WPA0_LANG); ?></h2>


		<p><?php _e('This wizard gets you started with the Auth0 for WordPress plug-in. You\'ll be transferred to Auth0 and can login or sign-up. Then you\'ll authorize the plug-in and configure identity providers, whether social or enterprise connections. Finally, you\'ll migrate your own WordPress administrator account to Auth0, ready to configure the plug-in through the WordPress dashboard.', WPA0_LANG); ?></p>


		<a href="<?php echo $consent_url; ?>" class="a0-button primary"><?php echo _e('Start'); ?></a>
		<a href=""  class="a0-button secondary"><?php echo _e('Skip the wizard'); ?></a>

		<p class="a0-message a0-notice">
			<b><?php echo _e('IMPORTANT'); ?>:</b>
			<?php echo _e('This plug-in replaces the standard WordPress login screen. The experience is improved of course, but different.  By default, there is a link to the regular WordPress login screen should you need it.'); ?>
		</p>


</div>

<?php if($need_event_track) { ?>
<script type="text/javascript">
	document.addEventListener("DOMContentLoaded", function() {
		if (typeof(a0metricsLib) !== 'undefined') {
			a0metricsLib.track('submit:settings', {
				site_title:'<?php echo $site_title; ?>',
				site_url:'<?php echo $site_url; ?>',
				lock_version:'<?php echo $lock_version; ?>',
				wordpress_url:'<?php echo $wordpress_url; ?>',
				plugin_version:'<?php echo $plugin_version; ?>'
			});
		}
	});
</script>
<?php } ?>
