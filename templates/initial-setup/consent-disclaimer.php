<div class="wrap">

	<?php screen_icon(); ?>
	<h2><?php _e('Auth0 for WordPress - Quick Start Guide', WPA0_LANG); ?></h2>


		<p>This wizard will guide you through the initial setup of the Auth0 for WordPress plugin.</p>

    <p>Auth0 for WordPress integrates with the Auth0 cloud-based authentication service, giving you and your users:</p>

		<ul class="auth0">
			<li>A modern, easy to use login box with all the features users have come to expect.</li>
			<li>Easy integration with 30+ social identity providers, enterprise SSO, and secure user/password authentication.</li>
			<li>Better security including multifactor authentication, passwordless authentication, password policies, all battle-tested.</li>
			<li>Stats on your users - demographics, locations, history.</li>
			<li>Super easy install - 5 minutes to a better login experience for users and powerful analytics for site owners.</li>
			<li>Customization and easy to use rules that give developers great flexibility.</li>
    </ul>

		<p>For more information about this plugin, please see the <a href="https://auth0.com/docs/wordpress">documentation</a>. For more about the Auth0 service, please visit <a href="https://auth0.com">auth0.com</a>.</p>


		<p><i><b>IMPORTANT:</b>This plugin replaces the standard WordPress login flow. Before you configure it, please be sure that is what what you're expecting! By default, the plugin will show a link to the old login page.</i></p>

		<p>Because Auth0 is a cloud service, you'll need an account at Auth0 where you'll access many of its advanced features and set things up. If you already have an Auth0 account, your WordPress installation will be one of the applications authorized in your account. If you don't have an Auth0 account, this wizard will create one for you and add your site to it all from within WordPress.</p>

		<p>Once set up, most of what you'll need to do can be handled from the plugin's settings page. The plugin calls the Auth0 APIs in order to configure account settings. If your site is behind a firewall that restricts access, you will need to whitelist https requests to *.auth0.com.</p>
		<p>If this isn't possible or desirable, or if you don't want to grant administrator privileges to one of the accounts managed by Auth0, then you can still manage your account manually using the <a href="https://manage.auth0.com/">Auth0 Dashboard</a>.</p>

		<div class="auth0-btn-container">
		    <a href="<?php echo $consent_url; ?>" class="button button-primary">Click here to start</a>
		</div>


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
