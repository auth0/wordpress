=== Login by Auth0 ===
Tags: login, oauth, authentication, single sign on, ldap, active directory, saml, windows azure ad, google apps, two factor, two-factor, facebook, google, twitter, baidu, renren, linkedin, github, paypal, yahoo, amazon, vkontakte, salesforce, box, dwolla, yammer, passwordless, sms, magiclink, totp, social
Tested up to: 5.1.1
Requires at least: 3.8
Requires PHP: 5.3
License: GPLv2
License URI: https://github.com/auth0/wp-auth0/blob/master/LICENSE
Stable tag: trunk
Contributors: auth0, auth0josh

Login by Auth0 provides improved username/password login, Passwordless login, Social login and Single Sign On for all your sites.

== Description ==

This plugin replaces standard WordPress login forms with one powered by [Auth0](https://auth0.com) that enables:

- **Universal authentication**
    - Over 30 social login providers
    - Enterprise connections (ADFS, Active directory / LDAP, SAML, Office 365, Google Apps and more)
    - Connect your own database
    - Passwordless connections (using email or SMS)
- **Ultra secure**
    - Multifactor authentication
    - Password policies
    - Email validation
    - Mitigate brute force attacks
- **Easy access to your users data**
    - User stats
    - Profile data
    - Login history and locations

== Installation ==

This plugin requires a [free or paid](https://auth0.com/pricing) Auth0 account.

1. [Sign up here](https://auth0.com/signup).
2. Follow the [installation instructions here](https://auth0.com/docs/cms/wordpress/installation).

== Screenshots ==

1. The new login page on WordPress
2. Twenty Seventeen theme with login widget
3. The admin to configure the plugin
4. Set up Enterprise connections
5. Set up the Auth0 widget

== Technical Notes ==

**IMPORTANT**: By using this plugin you are delegating the site authentication and profile handling to Auth0. That means that you won't be using the WordPress database to authenticate users and the default WordPress login forms will be replaced.

Please see our [How It Works page](https://auth0.com/docs/cms/wordpress/how-does-it-work) for more information on how Auth0 authenticates and manages your users.

= Migrating Existing Users =

Auth0 allows multiple authentication providers. You can have social providers like Facebook, Twitter, Google+, and more, a database of users and passwords (just like WordPress but hosted in Auth0), or you can use an Enterprise directory like Active Directory, LDAP, Office365, Google Apps, or SAML. All those authentication providers might give you an email and a flag indicating whether the email was verified or not. We use that email (only if its verified) to associate a previous **existing** user with the one coming from Auth0.

If the email was not verified and there is an account with that email in WordPress, the user will be presented with a page saying that the email was not verified and a link to "Re-send the verification email." For either scenario, you can choose whether it is mandatory that the user has a verified email or not in the plugin settings.

**Please note:** In order for a user to login using Auth0, they will need to sign up via the Auth0 login form (or have an account created for them in Auth0). Once signup is complete, their Auth0 user will be automatically associated with their WordPress user.

= Widget =

You can enable the Auth0 as a WordPress widget in order to show it in a sidebar. The widget inherits the main plugin settings but can be overridden with its own settings in the widget form.

= Shortcode =

Also, you can use the Auth0 widget as a shortcode in your editor. Just add the following to use the global settings:

    [auth0]

Like widgets, shortcode login forms will use the main plugins settings. It can be customized by adding the following attributes:

- `icon_url` - A direct URL to an image used at the top of the login form
- `form_title` - Text to appear at top of the login form
- `gravatar` - Display the user's Gravatar; set to `1` for yes
- `redirect_to` - A direct URL to use after successful login
- `custom_css` - Valid CSS to alter the login form
- `custom_js` - Valid JS to alter the login form
- `dict` - Valid JSON to override form text ([see options here](https://github.com/auth0/lock/blob/master/src/i18n/en.js))
- `extra_conf` - Valid JSON to override Lock configuration ([see options here](https://auth0.com/docs/libraries/lock/v11/configuration))
- `show_as_modal` - Display a button which triggers the login form in a modal; set to `1` for yes
- `modal_trigger_name` - Button text to display when using a modal

Example:

    [auth0 show_as_modal="1" modal_trigger_name="Login button: This text is configurable!"]

== Frequently Asked Questions ==

= Can I customize the Auth0 login form? =

The Auth0 login form is called Lock and it's [open source on GitHub](https://github.com/auth0/lock). You can style the form like any of your site components by enqueuing a stylesheet in your theme. Use the [`login_enqueue_scripts`](https://developer.wordpress.org/reference/hooks/login_enqueue_scripts/) hook to style the form on wp-login.php, [`wp_enqueue_scripts`](https://developer.wordpress.org/reference/hooks/wp_enqueue_scripts/) to style widgets and shortcodes, or both to affect the form in all locations.

= Can I access the user profile information? =

The Auth0 plugin transparently handles login information for your WordPress site and the plugins you use, so that it looks like any other login. User profile data changes in WordPress **are not** currently sent to Auth0 but changes to the Auth0 user account **are** stored in WordPress user meta (under the key `auth0_obj` prefixed with `$wpdb->prefix`).

= When I install this plugin, will existing users still be able to login? =

Yes, either allowing the WordPress login form to be displayed or by migrating existing users. See the **Technical Notes** section above.

= What authentication providers do you support? =

Please see our [complete list of supported social and enterprise authentication providers](https://auth0.com/docs/identityproviders).

= How can I use Lock configuration options that are not provided in the settings page? =

Use the "Extra Settings" field on the plugin settings' **Advanced** tab to add a JSON object with all additional configurations. For more information on what else can be configured, see the [documentation](https://auth0.com/docs/libraries/lock/v11/configuration).

= Is this plugin compatible with WooCommerce? =

Yes, this plugin will override the default WooCommerce login forms with the Auth0 login form.

= My question is not covered here ... what do I do? =

All is not lost!

* If you're setting up the plugin for the first time or having issues with users logging in, please review our [troubleshooting](https://auth0.com/docs/cms/wordpress/troubleshoot) and [configuration](https://auth0.com/docs/cms/wordpress/configuration) documentation.
* If you found a bug in the plugin code, please [submit an issue](https://github.com/auth0/wp-auth0/issues) or [create a pull request](https://github.com/auth0/wp-auth0/pulls) on GitHub.
* If you have questions about how to use Auth0 or the plugin, please [post on our community site](https://community.auth0.com/) or create a [support forum request here](https://wordpress.org/support/plugin/auth0).
* You can see additional documentation and answers on our [support site](https://support.auth0.com/). Customers on a paid Auth0 plan can [submit a trouble ticket](https://support.auth0.com/tickets) for a fast response.

== Changelog ==

**v3.10.0**

- The "Single Logout" functionality has been changed. This setting now logs users out of Auth0 automatically when they log out of WordPress. It no longer logs users out of WordPress automatically if they have already been logged out of Auth0.
- Core WordPress login form display handling has been changed to improve security and maintainability. Please review the "Original Login Form on wp-login.php" option on the **Basic** tab of the plugin settings to make sure this is set properly for your site.
- The following settings have been deprecated and will be removed in the next major release if they are still being used:
    - **Features > "Single Sign-On"**: To use SSO going forward, please activate the "Universal Login Page" setting in the **Features** tab of the plugin settings.
    - **Appearance > "Custom CSS" and "Custom JS"**: If you already have CSS and/or JS stored, the setting will continue to work until the next major release. If not, these fields have been removed.
- The following settings have been removed from the plugin. Please use the Auth Dashboard to manage these going forward. The functionality has not been removed from Auth0, only the ability to manage it from WordPress:
    - **Basic >API Token**
    - **Features > "Password Policy"**
    - **Features > "Multifactor Authentication (MFA)"**
    - **Features > "FullContact"**
    - **Features > "Store Geolocation"**
    - **Features > "Store Zipcode Income"**
    - **Advanced > "Link Users with Same Email"**
- The default Lock version has been updated from 11.5 to 11.14. If you have never changed the Lock URL, this update will be automatic for this and future releases.
- An Auth0 login form (or link to login) will now appear on the WooCommerce Checkout page for sites that allow or require an account to check out.
- The connection with the WP JWT Auth plugin has been deprecated and will be removed in the next major.
- And more!

[Complete list of changes for this and other releases](https://github.com/auth0/wp-auth0/blob/master/CHANGELOG.md#3100-2019-04-16)
