<div class="wrap">
	<?php screen_icon(); ?>
	<h2><?php _e('Auth0 - Initial setup', WPA0_LANG); ?></h2>

    <p>This wizard will guide you through the initial setup of the Auth0 client.</p>
    <p>The plugin needs to get access to your Auth0 account in order to help you configure it and sincronize the profiles.</p>
    <p>This process will as you to login (or signup if you don't have an Auth0 account), authorize the plugin to access some scopes and will provide you a token you will need to copy.</p>
    <p>Scopes required by the plugin</p>
    <ul>
        <li><b>Read and write user and app metadata</b> this will be used to sync up your user database and profiles</li>
        <li><b>Read and write clients (your apps)</b> this will be used to help you with the initial setup</li>
        <li><b>Read and write connections</b> this will be used to help you enable social login and retrieve the apps token to be used by the amplificator.</li>
        <li><b>Read and write rules</b> this will be used to help you enable features as MFA and geo information recolection to be used in the dashboard.</li>
    </ul>

    <p>Click here</p>

</div>
