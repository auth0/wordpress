=== Wordpress Auth0 Integration ===
Tags: Login, oauth, authentication, facebook, google
Tested up to: 3.9
Requires at least: 3.8
License: MIT
License URI: https://github.com/auth0/wp-auth0/blob/master/LICENSE.md
Stable tag: trunk
Contributors: 1337 ApS, hrajchert

Provides Single Sing On to your wordpress site. You can use different auth providers as facebook, google, twitter, active directory, etc

== Description ==
This plugins allows you to extend the default user implementation and use the service provided by www.auth0.com

You can make your users to login with facebook, google, linkedin, etc by a click of a button


== Installation ==

Before you start, make sure the admin user has a valid email that you own, read the Technical Notes for more information.

1. Install from the wordpress store or upload the entire `wp-auth0` folder to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. In `settings` - `Auth0 Settings` edit the *Domain*, *Client ID* and *Client Secret* from your auth0 dashboard
1. Go to your auth0 dashboard, edit your application and add this to the available callbacks http://<your-domain>/index.php?auth0=1


== Technical Notes ==

By using this plugin you are delegating the site authentication to Auth0, if a user is valid for Auth0 it will be valid for your site.

When you install this plugin you have at least one existing user in the database, the admin user, and if the site ain't new, you probably have more. We want you to conserve those users! you want to be able to login as admin again, right ;)?

Auth0 allows you to have different ways to authenticate, you can have social providers like facebook, twitter, google+, etc or you can have database users (just like wordpress!). All those providers MAY have an email and that email can be verified or not. We use that email (only if its verified) to join a previous existing user with the one from Auth0.

There are two main scenarios that you need to keep in mind:
    * The user logs in via database
    * The user logs in via a social provider

For now, if you add a database connection, you will start with no users (we plan to add an import feature later). But you still can claim your old user. To do so, you will need to signup using the login widget and then validate your account by clicking on the verification link in the email you'll receive. For database connections, if there was a previous user with that email you will require to verificate the address.

If the user logs in via a social provider, it may have a verified email. If it does, and its the first time the user logs in using that social provider, the plugin will asociate that social account with the previous existing user (that has the same email)

For both scenarios you may configure in the admin to require that the user has a verified email or not.

In any case, you may end up with a situation where a user has two accounts. Remember that wordpress allows you to do something similar to a user merge. To do so, you need to delete an account and attribute its contents to the user you want to merge with. You can go to Users, select the account you want to delete, and in the confirmation dialog you can select another user to attribute content.

Wordpress defines a function called `get_currentuserinfo` to populate the global variable `current_user` with the logged in WP_User. Similary we define `get_currentauth0userinfo` that populates `current_user` and `currentauth0_user` with the information of the [Normalized profile](https://docs.auth0.com/user-profile)
