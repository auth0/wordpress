<div class="wrap">

	<?php screen_icon(); ?>
	<h2><?php _e('Auth0 - Initial setup', WPA0_LANG); ?></h2>


		<p>The plugin needs to get access to your Auth0 account in order to help you configure it and sincronize the profiles and his wizard will guide you through the initial setup of the Auth0 client.</p>

	    <p>The first step is to create an account in <a href="http://auth0.com" target="_blank">Auth0</a> if you don't have one.</p>
	    <p>Then you need to authorize the plugin to you account in order to set up the app. The scopes required by the plugin:</p>

		<ul class="auth0">
	        <li><b>Read your connections</b> this will be used to sync your Auth0 connections status.</li>
	        <li><b>Create a client (Auth0 app)</b> this will be used to create the Auth0 app used by the plugin to authenticate users and manage your account (like enable/disable rules and connections from the WordPress admin page).</li>
	    </ul>

		<p><i><b>Note:</b> This plugin will call the Auth0 APIs in order to manage your accout settings. If the server is behing a firewall with restricted internet access, please whitelist the request directed to <b>*.auth0.com</b>. If this is not posible, you can manage your account using the <a href="https://manage.auth0.com">Auth0 dashboard</a></i>.</p>

		<div class="auth0-btn-container">
		    <a href="<?php echo $consent_url; ?>" class="button button-primary">Click here to start</a>
		</div>


</div>
