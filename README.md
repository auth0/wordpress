![](https://raw.githubusercontent.com/auth0/wp-auth0/master/banner-1544x500.png)

Login by Auth0
====

Single Sign On for Enterprises + Social Login + User/Passwords. For all your WordPress instances. Powered by Auth0.

Download and install <https://wordpress.org/plugins/auth0/>
Documentation: <https://auth0.com/docs/cms/wordpress>

## Important note on 3.5

This is a major update that requires changes to your Auth0 Dashboard to be completed. You can save a new [API token](https://auth0.com/docs/api/management/v2/tokens#get-a-token-manually) in your Basic settings in wp-admin before upgrading and the changes will be made automatically during the update. Otherwise, please review your [Client Advanced Settings](https://auth0.com/docs/cms/wordpress/configuration#client-setup), specifically your Grant Types, and [authorize your Client for the Management API](https://auth0.com/docs/cms/wordpress/configuration#authorize-the-client-for-the-management-api). 

## Installation

[Please see the Installation page on auth0.com/docs](https://auth0.com/docs/cms/wordpress/installation)

## Extending the plugin

[Please see the Extending page on auth0.com/docs](https://auth0.com/docs/cms/wordpress/extending)

We're happy to review and approve new filters and actions that help you integrate even further in this plugin. Please
 see the Contributing section at the bottom of this page.

## API authentication

This plugin provides the ability integrate with **wp-jwt-auth** plugin to authenticate api calls via a HTTP Authorization Header.

This plugin will detect if you have wp-jwt-auth installed and active and will offer to configure it. Accepting this, will set up the client id, secret and the custom user repository.

After the user logs in via Auth0 in your Api client (ie: using Lock in a mobile app) you will get a JWT (JSON Web Token). Then you need to send this token in a HTTP header in the following way:

```
Authorization: Bearer ##jwt##
```

For example:

```
Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJjb250ZW50IjoiVGhpcyBpcyB5b3VyIHVzZXIgSldUIHByb3ZpZGVkIGJ5IHRoZSBBdXRoMCBzZXJ2ZXIifQ.b47GoWoY_5n4jIyG0hPTLFEQtSegnVydcvl6gpWNeUE
```

This JWT should match with a registered user in your WP installation.

You can use this feature with API's provided by plugins like **WP REST API (WP API)**.

## Technical Notes

**IMPORTANT**: By using this plugin you are delegating the site authentication to Auth0. That means that you won't be using the  **WordPress** database to authenticate users anymore and the default WP login box won't show anymore. However, we can still associate your existing users by merging them by email. This section explains how.

When you install this plugin you have at least one existing user in the database (the admin user). If the site is already being used, you probably have more than just the admin. We want you to keep those users, of course.

### Migrating Existing Users

Auth0 allows multiple authentication providers. You can have social providers like Facebook, Twitter, Google+, etc., you can have a database of users/passwords (just like  **WordPress** but hosted in **Auth0**) or you can use an Enterprise directory like Active Directory, LDAP, Office365, SAML and others. All those authentication providers might give you an email and a flag indicating whether the email was verified or not. We use that email (only if its verified) to associate a previous existing user with the one coming from Auth0.

If the email was not verified (`email_verified` property of the user is `false`) and there is an account with that email in WordPress, the user will be presented with a page saying that the email was not verified and a link to "Re-send the verification email".

For both scenarios you may configure in the WP admin whether it is mandatory that the user has a verified email or not.

### Accessing Profile Information

You can access the rich user profile information coming from the Identity Providers.  **WordPress** defines a function called `wp_get_current_user` to populate the global variable `current_user` with the logged in `WP_User`. Similarly we define `get_currentauth0userinfo` that populates `current_user` and `currentauth0_user` with the information of the Normalized profile.

### Enabling dual (Auth0 and Wordpress) login

You can enable the standard Wordpress login by turning on the "WordPress login enabled" setting (enabled by default). This will make visible a link on the login page to swap between both.

### Using the plugin widget

You can enable the Auth0 as a Wordpress widget in order to show it in the sidebar. The widget inherits the plugin settings and it can be overridden with its own settings.

Also, a new layout setting is enabled in order to be shown as a modal. Enabling the "Show as modal" setting, a button which trigger the modal is generated.

### Using the login widget as a shortcode

Also, you can use the Auth0 widget as a shortcode in your posts.

The way to use it is just adding the following:

```
    [auth0]
```

And can be customized by adding the following parameters:

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

```
    [auth0 show_as_modal="true" social_big_buttons="true" modal_trigger_name="Login button: This text is configurable!"]
```

All the details about the parameters on the lock wiki (https://github.com/auth0/lock/wiki/Auth0Lock-customization)

## FAQs

### Is this plugin compatible with WooCommerce?

Yes, this plugin will override the default WooCommerce login forms with the Lock widget.

### What should I do if I end up with two accounts for the same user?

Under some situations, you may end up with a user with two accounts.  **WordPress** allows you to do merge users. You just delete one of the accounts and then attribute its contents to the user you want to merge with. Go to Users, select the account you want to delete, and in the confirmation dialog select another user to transfer the content.

### Can I customize the login widget?

You can style the login form by adding your css on the "Customize the Login Widget CSS" Auth0 setting and the widget settings

```
    form a.a0-btn-small { background-color: red !important; }
```

The login widget, called Lock, is open source. For more information about it: https://github.com/auth0/lock

### Can I access the user profile information?

The Auth0 plugin transparently handles login information for your  **WordPress** site and the plugins you use, so that it looks like any other login.

### When I install this plugin, will existing users still be able to login?

Yes. Read more about the requirements for that to happen in the Technical Notes.

### What authentication providers do you support?

For a complete list look at <https://auth0.com/docs/identityproviders>

### "This account does not have an email associated..."

If you get this error, make sure you are requesting the Email attribute from each provider in the Auth0 Dashboard under Connections -> Social (expand each provider). Take into account that not all providers return Email addresses for users (e.g. Twitter). If this happens, you can always add an Email address to any logged in user through the Auth0 Dashboard (pr API). See Users -> Edit.

### The form_title setting is ignored when I set up the dict setting

Internally, the plugin uses the dict setting to change the Auth0 widget title. When you set up the dict field it overrides the form_title one.

To change the form_title in this case, you need to add the following attribute to the dict json:

```
      {
        signin:{
            title: "The desired form title"
        }
      }
```

### How can I set up the settings that are not provided in the settings page?

We added a new field called "Extra settings" that allows you to add a json object with all the settings you want to configure.

Have in mind that all the "Extra settings" that we allow to set up in the plugin settings page will be overrided (For more information about it: https://github.com/auth0/widget).

## Contributing

Thank you in advance!

All PR should be done towards the `dev` branch.

### How to build the initial setup assets?

You need to install the stylus tool and run this command (inside /assets/css):

```
$ stylus -c -o initial-setup.css initial-setup/main.styl
```

To watch and auto-compile it while working:

```
$ stylus -w -o initial-setup.css initial-setup/main.styl
```

## Screenshots

![](https://raw.githubusercontent.com/auth0/wp-auth0/master/screenshot-1.png)

![](https://raw.githubusercontent.com/auth0/wp-auth0/master/screenshot-2.png)

![](https://raw.githubusercontent.com/auth0/wp-auth0/master/screenshot-3.png)

![](https://raw.githubusercontent.com/auth0/wp-auth0/master/screenshot-4.png)

![](https://raw.githubusercontent.com/auth0/wp-auth0/master/screenshot-5.png)

![](https://raw.githubusercontent.com/auth0/wp-auth0/master/screenshot-6.png)

## Issue Reporting

If you have found a bug or if you have a feature request, please report them at this repository issues section. Please do not report security vulnerabilities on the public GitHub issue tracker. The [Responsible Disclosure Program](https://auth0.com/whitehat) details the procedure for disclosing security issues.

## Author

* [Auth0](auth0.com)

## License

This project is licensed under the MIT license. See the [LICENSE](LICENSE) file for more info.
