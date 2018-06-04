=== PLUGIN_NAME ===
Tags: PLUGIN_TAGS
Tested up to: 4.9.6
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

Please see the Auth0 Docs site for [complete installation and configuration instructions](https://auth0.com/docs/cms/wordpress/installation).

== Screenshots ==

1. The new login page on WordPress
2. Twenty Seventeen theme with login widget
3. The admin to configure the plugin
4. Set up Enterprise connections
5. Set up the Auth0 widget

== Technical Notes ==

**IMPORTANT**: By using this plugin you are delegating the site authentication to Auth0. That means that you won't be using the WordPress database to authenticate users anymore and the default WP login box won't show anymore. However, we can still associate your existing users by merging them by email. This section explains how.

When you install this plugin you have at least one existing user in the database (the admin user). If the site is already being used, you probably have more than just the admin. We want you to keep those users, of course.

= Migrating Existing Users =

Auth0 allows multiple authentication providers. You can have social providers like Facebook, Twitter, Google+, etc., you can have a database of users/passwords (just like WordPress but hosted in Auth0) or you can use an Enterprise directory like Active Directory, LDAP, Office365, SAML and others. All those authentication providers might give you an email and a flag indicating whether the email was verified or not. We use that email (only if its verified) to associate a previous **existing** user with the one coming from Auth0.

If the email was not verified and there is an account with that email in WordPress, the user will be presented with a page saying that the email was not verified and a link to "Re-send the verification email".

For both scenarios you may configure in the WP admin whether is mandatory that the user has a verified email or not.

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

= Can I customize the Auth0 login form? =

You can style the login form by adding your css on the "Customize the Login Widget CSS" Auth0 setting and the widget settings

    form a.a0-btn-small { background-color: red !important; }

The Auth0 login form is called Lock and it's [open source on GitHub](https://github.com/auth0/lock).

= Can I access the user profile information? =

The Auth0 plugin transparently handles login information for your WordPress site and the plugins you use, so that it looks like any other login.

= When I install this plugin, will existing users still be able to login? =

Yes, either allowing the WordPress login form to be displayed or by migrating existing users. See the **Technical Notes** section above.

= What authentication providers do you support? =

See our [complete list of supported social and enterprise authentication providers](https://auth0.com/docs/identityproviders).

= How can I set up the settings that are not provided in the settings page? =

We added a new field called "Extra settings" that allows you to add a JSON object with all the settings you want to configure. For more information on what else can be configured, see the [Lock customization section in GitHub](https://github.com/auth0/lock#customization).

= Is this plugin compatible with WooCommerce? =

Yes, this plugin will override the default WooCommerce login forms with the Auth0 login form.

= My question is not covered here; what do I do? =

All is not lost!

* If you're setting up the plugin for the first time or having issues with users logging in, please review the [configuration](https://auth0.com/docs/cms/wordpress/configuration) and [troubleshooting](https://auth0.com/docs/cms/wordpress/troubleshoot) pages at [auth0.com/docs](https://auth0.com/docs/cms/wordpress/).
* If you found a bug in the plugin code [submit an issue](https://github.com/auth0/wp-auth0/issues) or [create a pull request](https://github.com/auth0/wp-auth0/pulls) on GitHub.
* If you have questions about how to use Auth0 or the plugin, please [post on our community site](https://community.auth0.com/) or create a [support forum request here](https://wordpress.org/support/plugin/auth0).
* You can also see additional documentation and answers on our [support site](https://support.auth0.com/). Customers on a paid Auth0 plan can [submit a trouble ticket](https://support.auth0.com/tickets) for a fast response.

= My question is not covered here; what do I do? =

All is not lost!

* If you're setting up the plugin for the first time or having issues after an upgrade, please review the [configuration page at auth0.com/docs](https://auth0.com/docs/cms/wordpress/configuration)
* If you found a bug in the plugin code [submit an issue](https://github.com/auth0/wp-auth0/issues) or [create a pull request](https://github.com/auth0/wp-auth0/pulls) on [GitHub](https://github.com/auth0/wp-auth0/).
* If you have questions about how to use Auth0 or the plugin, please [post on our community site](https://community.auth0.com/) or create a [support forum request here](https://wordpress.org/support/plugin/auth0).
* You can also see additional documentation and answers on our [support site](https://support.auth0.com/). Customers on a paid Auth0 plan can submit trouble tickets for a fast response.

== Changelog ==

[Complete CHANGELOG.md maintained on Github](https://github.com/auth0/wp-auth0/blob/master/CHANGELOG.md)

= 3.6.0 =

[Details](https://github.com/auth0/wp-auth0/blob/master/CHANGELOG.md#360-2018-06-05)

= 3.5.2 =

[Details](https://github.com/auth0/wp-auth0/blob/master/CHANGELOG.md#352-2018-02-22)

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