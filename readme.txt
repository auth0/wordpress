=== Auth0 - Single Sign On with Social, Enterprise and User/Passwords ===
Tags: login, oauth, authentication, single sign on, ldap, active directory, saml, windows azure ad, google apps, two factor, two-factor, facebook, google, twitter, baidu, renren, linkedin, github, paypal, yahoo, amazon, vkontakte, salesforce, box, dwolla, yammer
Tested up to: 3.9
Requires at least: 3.8
License: MIT
License URI: https://github.com/auth0/wp-auth0/blob/master/LICENSE.md
Stable tag: trunk
Contributors: hrajchert, rrauch

Single Sign On for Enterprises + Social Login + User/Passwords. For all your WorpdPress instances. Powered by Auth0.

== Description ==

This plugin gives WordPress a new Login Widget (powered by [Auth0](https://auth0.com)) that enables:

* Single Sign On with **Enterprise Directories** (LDAP, AD, Google Apps, Office365 and SAML Provider)
* Shared **User/Password between multiple Wordpresses** for Single Sign On
* Single Sign On with **+30 Social Providers** (https://docs.auth0.com/identityproviders)
* **User Management** Dashboard
* Optional **Two Factor Authentication**
* Single Sign On between Wordpress and other Applications
* **Reporting and Analytics**

... and **we use multi hash iterations algorithm to store users passwords (bcrypt)**, meaning that you won't have issues with hackers trying to get into your web site.

== Installation ==

Before you start, **make sure the admin user has a valid email that you own**, read the Technical Notes for more information.

1. Install from the WordPress Store or upload the entire `wp-auth0` folder to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Create an account in Auth0 (https://auth0.com) and add a new PHP Application. Copy the Client ID, Client Secret and Domain from the Settings of the Application.
1. On the Settings of the Auth0 application change the Callback URL to be: `http://your-domain/index.php?auth0=1`. Using **TLS/SSL** is **recommended for production**.
1. Go back to Wordpress `Settings` - `Auth0 Settings` edit the *Domain*, *Client ID* and *Client Secret* with the ones you copied from Auth0 Dashboard.

== Screenshots ==

1. The new login page on Wordpress
2. The admin to configure the plugin
3. Auth0 dashboard to create a new Application
4. Enable or disable social plugins from the Auth0 dashboard
5. This is what happens if you are in the admin and your session expires
6. Configure enterprise Connections

== Technical Notes ==

**IMPORTANT**: By using this plugin you are delegating the site authentication to Auth0. That means that you won't be using the WordPress database to authenticate users anymore and the default WP login box won't show anymore. However, we can still associate your existing users by merging them by email. This section explains how.

When you install this plugin you have at least one existing user in the database (the admin user). If the site is already being used, you probably have more than just the admin. We want you to keep those users, of course.

= Migrating Existing Users =

Auth0 allows multiple authentication providers. You can have social providers like Facebook, Twitter, Google+, etc., you can have a database of users/passwords (just like WordPress but hosted in Auth0) or you can use an Enterprise directory like Active Directory, LDAP, Office365, SAML and others. All those authentication providers might give you an email and a flag indicating whether the email was verified or not. We use that email (only if its verified) to associate a previous **existing** user with the one coming from Auth0.

If the email was not verified and there is an account with that email in WordPress, the user will be presented with a page saying that the email was not verified and a link to "Re-send the verification email".

For both scenarios you may configure in the WP admin whether is mandatory that the user has a verified email or not.

= Accesing Profile Information =

Wordpress defines a function called `get_currentuserinfo` to populate the global variable `current_user` with the logged in WP_User. Similary we define `get_currentauth0userinfo` that populates `current_user` and `currentauth0_user` with the information of the [Normalized profile](https://docs.auth0.com/user-profile)

= Enabling dual (Auth0 and Wordpress) login =

You can enable the standard Wordpress login by turning on the "WordPress login enabled" setting (enabled by default). This will make visible a link on the login page to swap between both.

= Using the plugin widget =

You can enable the Auth0 as a Wordpress widget in order to show it in the sidebar. The widget inherits the plugin settings and it can be overrided with its own settings.

Also, a new layout setting is enabled in order to be shown as a modal. Enabling the "Show as modal" setting, a button which trigger the modal is generated.

= Using the login widget as a shortcode =

Also, you can use the Auth0 widget as a shortcode in your posts.

The way to use it is just adding the following:

    [auth0]

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

    [auth0 show_as_modal="true" social_big_buttons="true" modal_trigger_name="Login button: This text is configurable!"]

All the details about the parameters on the lock wiki (https://github.com/auth0/lock/wiki/Auth0Lock-customization)

== Frequently Asked Questions ==

= What should I do if I end up with two accounts for the same user? =

Under some situations, you may end up with a user with two accounts. Wordpress allows you to do merge users. You just delete one of the accounts and then attribute its contents to the user you want to merge with. Go to Users, select the account you want to delete, and in the confirmation dialog select another user to transfer the content.

= Can I customize the Login Widget? =

You can style the login form by adding a filter like this

    add_filter( 'auth0_login_css', function() {
        return "form a.a0-btn-small { background-color: red }";
    } );

The Login Widget is Open Source. For more information about it: https://github.com/auth0/widget

= Can I access the user profile information? =

The Auth0 plugin transparently handles login information for your Wordpress site and the plugins you use, so that it looks like any other login.

= When I install this plugin, will existing users still be able to login? =

Yes. Read more about the requirements for that to happen in the Technical Notes.

= What authentication providers do you support? =

For a complete list look at https://docs.auth0.com/identityproviders

= "This account does not have an email associated..." = 

If you get this error, make sure you are requesting the Email attribute from each provider in the Auth0 Dashboard under Connections -> Social (expand each provider). Take into account that not all providers return Email addresses for users (e.g. Twitter). If this happens, you can always add an Email address to any logged in user through the Auth0 Dashbaord (pr API). See Users -> Edit. 

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

Have in mind that all the "Extra settings" that we allow to set up in the plugin settings page will be overrided.
