<div class="wrap">
	<?php screen_icon(); ?>
	<h2><?php _e('Auth0 - Initial setup', WPA0_LANG); ?></h2>

	<p>The plugin needs to get access to your Auth0 account in order to help you configure it and sincronize the profiles and his wizard will guide you through the initial setup of the Auth0 client.</p>

    <p>The first step is to create an account in <a href="http://auth0.com" target="_blank">Auth0</a> if you don't have one.</p>
    <p>Then you need to authorize the plugin to access some scopes and will provide you a token you will need to copy. The scopes required by the plugin:</p>

	<ul class="auth0">
        <li><b>Read and write user and app metadata</b> this will be used to sync up your user database and profiles</li>
        <li><b>Read and write clients (your apps)</b> this will be used to help you with the initial setup</li>
        <li><b>Read and write connections</b> this will be used to help you enable social login and retrieve the apps token to be used by the amplificator.</li>
        <li><b>Read and write rules</b> this will be used to help you enable features as MFA and geo information recolection to be used in the dashboard.</li>
    </ul>

    <a href="#" class="auth0-btn">Click here</a>

</div>
