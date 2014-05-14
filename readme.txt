=== Wordpress Auth0 Integration ===
Tags: Login, oauth, authentication, facebook, google
Tested up to: 3.9
Requires at least: 3.8
License: MIT
License URI: https://github.com/auth0/wp-auth0/blob/master/LICENSE.md
Stable tag: trunk
Contributors: hrajchert, launchpeople

Provides Single Sing On to your wordpress site. You can use different auth providers as facebook, google, twitter, active directory, etc

== Description ==
This plugins allows you to extend the default user implementation and use the service provided by www.auth0.com

You can make your users to login with facebook, google, linkedin, etc by a click of a button


== Installation ==

1. Upload the entire `wp-auth0` folder to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. In `settings` - `Auth0 Settings` edit the *Domain*, *Client ID* and *Client Secret* from your auth0 dashboard
1. Go to your auth0 dashboard, edit your application and add this to the available callbacks http://<your-domain>/index.php?auth0=1
