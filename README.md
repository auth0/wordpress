![](https://raw.githubusercontent.com/auth0/wp-auth0/master/banner-1544x500.png)

Wordpress Plugin for Auth0
====

Single Sign On for Enterprises + Social Login + User/Passwords. For all your WordPress instances. Powered by Auth0.

Demo: <http://auth0wp.azurewebsites.net>

Documentation: <https://auth0.com/docs/cms>

## Contributions

All PR should be done towards the `dev` branch.

## Installation

Before you start, **make sure the admin user has a valid email that you own**, read the Technical Notes for more information.

1. Install from the  **WordPress** Store or upload the entire wp-auth0 folder to the /wp-content/plugins/ directory.
2. Activate the plugin through the 'Plugins' menu in  **WordPress**.
3. Create an account in Auth0 (https://auth0.com) and add a new Application.
4. Copy the Client ID, Client Secret and Domain from the Settings of the Application.
5. On the Settings of the Auth0 application change the Callback URL to be: http://your-domain/index.php?auth0=1. Using TLS/SSL is recommended for production.
6. Go back to  **WordPress** Settings - Auth0 Settings edit the Domain, Client ID and Client Secret with the ones you copied from Auth0 Dashboard.

## Implicit Flow

There are cases where the server is behind a firewall and does not have access to internet (or at least, can't reach the Auth0 servers). In those cases, you can enable the Auth0 Implicit Flow in the advanced settings of the Auth0 Settings page.

When it is enabled, the token is returned in the login callback and then sent back to the WordPress server so it doesn't need to call the Auth0 webservices.

## Integrating with the plugin

### User login action

The plugin provides an action to get notified each time a user logs in or is created in WordPress. This action is called `auth0_user_login` and receives 5 params:
1. $user_id (int): the id of the user logged in
2. $user_profile (stdClass): the Auth0 profile of the user
3. $is_new (boolean): `true` if the user was created on WordPress, `false` if doesn't. Don't get confused with Auth0 registrations, this flag will tell you if a new user was created on the WordPress database.
4. $id_token (string): the user's JWT.
5. $access_token (string): the user's access token. It is not provided when using the **Implicit flow**.

To hook to this action, you will need to do the following:
```
    add_action( 'auth0_user_login', 'auth0UserLoginAction', 0,5 );

    function auth0UserLoginAction($user_id, $user_profile, $is_new, $id_token, $access_token) {
        ...
    }
```

### Render verify email page

This filter is called when a user with an unverified email logs in, and the `Requires verified email` setting is enabled.

To hook to this filter, you will need to do the following:
```
    add_filter( 'auth0_verify_email_page', 'render_verify_email_page', 1, 3 );
    function render_verify_email_page($html, $userinfo, $id_token) {
        return "You need to verify your email to log in.";
    }   
```

### Customize users matching

This filter is called after the plugin finds the related user to login (based on the auth0 `user_id`). It allows to override the default behaviour with custom matching rules(for example, always match by email).

If the filter returns null, it will lookup by email as stated in the [How does it work?](https://auth0.com/docs/cms/wordpress/how-does-it-work) document.

```
    add_filter( 'auth0_get_wp_user', 'auth0_get_wp_user_handler', 1, 2 );
    function auth0_get_wp_user_handler($user, $userinfo) {
        $user = get_user_by( 'email', $userinfo->email );

        if ($joinUser instanceof WP_User) {
            return $user;
        }

        return null;
    }   
```

### Customize autologin connection

This filter will allow to programatically set which connection the plugin should use when autologin is enabled.

```
    add_filter( 'auth0_get_auto_login_connection', 'auth0_get_auto_login_connection', 1, 1 );

    function auth0_get_auto_login_connection($connection) {

        if ( /* check some condition */ ) {
            return 'twitter';
        }

        return $connection;
    }
```

## API authentication

The last version of the plugin provides the ability integrate with **wp-jwt-auth** plugin to authenticate api calls via a HTTP Authorization Header.

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

## Fedback webtask creation

```
wt create --name wp-auth0-slack \
    --secret SLACK_WEBHOOK_URL=... \
    --secret SLACK_CHANNEL_NAME=... \
    --output url slack-notifier.js 
```

## Ping webtask creation

```
wt create --name wp-auth0-ping \
    --profile wptest-default \
    ping.js
```

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
* remember_last_login: boolean
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

### Can I customize the Login Widget?

You can style the login form by adding your css on the "Customize the Login Widget CSS" Auth0 setting and the widget settings

```
    form a.a0-btn-small { background-color: red !important; }
```

The Login Widget is Open Source. For more information about it: https://github.com/auth0/lock

### Can I access the user profile information?

The Auth0 plugin transparently handles login information for your  **WordPress** site and the plugins you use, so that it looks like any other login.

### When I install this plugin, will existing users still be able to login?

Yes. Read more about the requirements for that to happen in the Technical Notes.

### What authentication providers do you support?

For a complete list look at https://docs.auth0.com/identityproviders

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

![](https://raw.githubusercontent.com/auth0/wp-auth0/master/screenshot-7.png)

## Issue Reporting

If you have found a bug or if you have a feature request, please report them at this repository issues section. Please do not report security vulnerabilities on the public GitHub issue tracker. The [Responsible Disclosure Program](https://auth0.com/whitehat) details the procedure for disclosing security issues.

## Author

* [Auth0](auth0.com)

## License

This project is licensed under the MIT license. See the [LICENSE](LICENSE) file for more info.
