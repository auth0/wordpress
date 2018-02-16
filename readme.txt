=== PLUGIN_NAME ===
Tags: PLUGIN_TAGS
Tested up to: 4.9.4
Requires at least: 3.8
Requires PHP: 5.3
License: MIT
License URI: https://github.com/auth0/wp-auth0/blob/master/LICENSE.md
Stable tag: trunk
Contributors: auth0, glena, rrauch, auth0josh

PLUGIN_DESCRIPTION

== Description ==

This plugin gives WordPress a new Login Widget (powered by [Auth0](https://auth0.com)) that enables:

- Universal authentication
    + +30 Social Providers
    + Enterprise connections (ADFS, Active directory / LDAP, SAML, Office 365, Google Apps and more)
    + Connect your own database
    + Passwordless connections (using SMS, Magic links and Email codes)
- Ultra secure
    + Multifactor authentication
    + Password policies
    + Email validation
    + Mitigate brute force attacks
- Easy access to your users data
    + User stats
    + Profile data
    + Login history and locations

== Installation ==

Before you start, **make sure the admin user has a valid email that you own**, read the Technical Notes for more information.

1. Install from the WordPress Store or upload the entire `wp-auth0` folder to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Create an account in Auth0 (https://auth0.com) and add a new PHP Application. Copy the Client ID, Client Secret and Domain from the Settings of the Application.
1. On the Settings of the Auth0 application change the Callback URL to be: `http://your-domain/index.php?auth0=1`. Using **TLS/SSL** is **recommended for production**.
1. Go back to WordPress `Settings` - `Auth0 Settings` edit the *Domain*, *Client ID* and *Client Secret* with the ones you copied from Auth0 Dashboard.

== Screenshots ==

1. The new login page on WordPress
2. The admin to configure the plugin
3. The new plugin quick setup
4. Get info about the supported enterprise connections
5. Set up the Auth0 widgets
6. Your home page with the login widget enabled

== Technical Notes ==

**IMPORTANT**: By using this plugin you are delegating the site authentication to Auth0. That means that you won't be using the WordPress database to authenticate users anymore and the default WP login box won't show anymore. However, we can still associate your existing users by merging them by email. This section explains how.

When you install this plugin you have at least one existing user in the database (the admin user). If the site is already being used, you probably have more than just the admin. We want you to keep those users, of course.

= Migrating Existing Users =

Auth0 allows multiple authentication providers. You can have social providers like Facebook, Twitter, Google+, etc., you can have a database of users/passwords (just like WordPress but hosted in Auth0) or you can use an Enterprise directory like Active Directory, LDAP, Office365, SAML and others. All those authentication providers might give you an email and a flag indicating whether the email was verified or not. We use that email (only if its verified) to associate a previous **existing** user with the one coming from Auth0.

If the email was not verified and there is an account with that email in WordPress, the user will be presented with a page saying that the email was not verified and a link to "Re-send the verification email".

For both scenarios you may configure in the WP admin whether is mandatory that the user has a verified email or not.

= Accessing Profile Information =

WordPress defines a function called `wp_get_current_user` to populate the global variable `current_user` with the logged in WP_User. Similarly we define `get_currentauth0userinfo` that populates `current_user` and `currentauth0_user` with the information of the [Normalized profile](https://docs.auth0.com/user-profile)

= Enabling dual (Auth0 and WordPress) login =

You can enable the standard WordPress login by turning on the "WordPress login enabled" setting (enabled by default). This will make visible a link on the login page to swap between both.

= Using the plugin widget =

You can enable the Auth0 as a WordPress widget in order to show it in the sidebar. The widget inherits the plugin settings and it can be overridden with its own settings.

Also, a new layout setting is enabled in order to be shown as a modal. Enabling the "Show as modal" setting, a button which trigger the modal is generated.

= Using the login widget as a shortcode =

Also, you can use the Auth0 widget as a shortcode in your editor. Just add the following:

    [auth0]

It can be customized by adding the following attributes:

* form_title: string
* dict: string, should be a the language or a valid json with the translation (see https://github.com/auth0/lock/wiki/Auth0Lock-customization#dict-stringobject)
* social_big_buttons: boolean
* gravatar: boolean
* username_style: string, "email" or "username"
* icon_url: string (valid url)
* extra_conf: string, valid json
* show_as_modal: boolean
* modal_trigger_name: string, button text

Example:

    [auth0 show_as_modal="true" social_big_buttons="true" modal_trigger_name="Login button: This text is configurable!"]

All the details about the parameters on the lock wiki (https://github.com/auth0/lock/wiki/Auth0Lock-customization)

== Frequently Asked Questions ==

= Is this plugin compatible with WooCommerce? =

Yes, this plugin will override the default WooCommerce login forms with the Lock widget.

= What should I do if I end up with two accounts for the same user? =

Under some situations, you may end up with a user with two accounts. WordPress allows you to do merge users. You just delete one of the accounts and then attribute its contents to the user you want to merge with. Go to Users, select the account you want to delete, and in the confirmation dialog select another user to transfer the content.

= Can I customize the Login Widget? =

You can style the login form by adding your css on the "Customize the Login Widget CSS" Auth0 setting and the widget settings

    form a.a0-btn-small { background-color: red !important; }

The Login Widget is Open Source. For more information about it: https://github.com/auth0/lock

= Can I access the user profile information? =

The Auth0 plugin transparently handles login information for your WordPress site and the plugins you use, so that it looks like any other login.

= When I install this plugin, will existing users still be able to login? =

Yes. Read more about the requirements for that to happen in the Technical Notes.

= What authentication providers do you support? =

For a complete list look at https://docs.auth0.com/identityproviders

= "This account does not have an email associated..." =

If you get this error, make sure you are requesting the Email attribute from each provider in the Auth0 Dashboard under Connections -> Social (expand each provider). Take into account that not all providers return Email addresses for users (e.g. Twitter). If this happens, you can always add an Email address to any logged in user through the Auth0 Dashboard (or API). See Users -> Edit.

= The form_title setting is ignored when I set up the dict setting =

Internally, the plugin uses the dict setting to change the Auth0 widget title. When you set up the dict field it overrides the form_title one.

To change the form_title in this case, you need to add the following attribute to the dict json:

      {
        signin:{
            title: "The desired form title"
        }
      }

= How can I set up the settings that are not provided in the settings page? =

We added a new field called "Extra settings" that allows you to add a json object with all the settings you want to configure.

Have in mind that all the "Extra settings" that we allow to set up in the plugin settings page will be overridden.

== Changelog ==

[Complete CHANGELOG.md maintained on Github](https://github.com/auth0/wp-auth0/blob/master/CHANGELOG.md)

= 3.5.2 =

[Details](https://github.com/auth0/wp-auth0/blob/master/CHANGELOG.md#352-2018-01-26)

= 3.5.1 =

[Details](https://github.com/auth0/wp-auth0/blob/master/CHANGELOG.md#351-2018-01-26)

= 3.5.0 =

[Details](https://github.com/auth0/wp-auth0/blob/master/CHANGELOG.md#350-2018-01-25)

= 3.4.0 =

[Details](https://github.com/auth0/wp-auth0/blob/master/CHANGELOG.md#340-2018-01-08)

= 3.3.2 =

[Details](https://github.com/auth0/wp-auth0/blob/master/CHANGELOG.md#332-2017-10-05)

= 3.2.24 =

[Details](https://github.com/auth0/wp-auth0/blob/master/CHANGELOG.md#3224-2017-08-14)

= 3.2.23 =

[Details](https://github.com/auth0/wp-auth0/blob/master/CHANGELOG.md#3223-2017-07-18)

= 3.2.22 =

[Details](https://github.com/auth0/wp-auth0/blob/master/CHANGELOG.md#3222-2017-06-26)

= 3.2.21 =

[Details](https://github.com/auth0/wp-auth0/blob/master/CHANGELOG.md#3221-2017-06-14)

= 3.2.5 =

[Details](https://github.com/auth0/wp-auth0/blob/master/CHANGELOG.md#325-2016-09-07)

= 3.2.0 =

[Details](https://github.com/auth0/wp-auth0/blob/master/CHANGELOG.md#320-2016-08-16)

= 3.1.4 =

[Details](https://github.com/auth0/wp-auth0/blob/master/CHANGELOG.md#314-2016-07-01)

= 3.1.3 =

[Details](https://github.com/auth0/wp-auth0/blob/master/CHANGELOG.md#313-2016-06-15)

= 3.1.2 =

[Details](https://github.com/auth0/wp-auth0/blob/master/CHANGELOG.md#312-2016-06-13)

= 3.1.1 =

[Details](https://github.com/auth0/wp-auth0/blob/master/CHANGELOG.md#311-2016-06-06)