# Migration Guide: v3 to v4

Version 4.0.0 of this plugin is a major release with a large number of fixes, changes, and removals. Please review this document completely before updating and make sure to test this new version thoroughly on a staging site before deploying to production.

The changes outlined here are potentially breaking and may not affect all sites. For a complete list of all the changes made in this release, please see the [4.0.0 release page](https://github.com/auth0/wp-auth0/releases/tag/4.0.0).

## Table of Contents

- [General Breaking Changes](#general-breaking-changes)
- [Public API Changes](#public-api-changes)
- [Public API Removals](#public-api-removals)

## General Breaking Changes

This section includes changes that have the potential to break all sites, regardless of use case.

- The minimum PHP version being updated from 5.3 to 7.0 and the minimum WP version from 3.8 to 4.9.
- The SSO check on the `wp-login.php` page was removed. If SSO is required, turn on **Features > Universal Login Page** on the Auth0 plugin settings page.
- The **Custom Signup Fields** option has been removed and the data removed from the database. Please see [the pull request](https://github.com/auth0/wp-auth0/pull/765) for how to migrate these fields into an external JS file.
- The **Implicit Login Flow** setting has been removed to simplify the options provided by the plugin and ensure that the correct login flow is used if access tokens are requested.
- The **Client Secret Encoded** option has been removed. If your WordPress site was using an encoded Client Secret, you'll need to regenerate one in the Auth0 dashboard and save it in wp-admin > Auth0 > Settings > Basic > Client Secret.
- Custom JS and CSS will be removed from the main settings and widgets. The field content will be deleted and will no longer be output. Please use the `wp_enqueue_style` and `wp_enqueue_script` hooks to load CSS and JS on the appropriate page(s) to affect Lock behavior and styling.
- If you are using a different language or language dictionary plugin settings, these fields have been removed but their values will be merged into the "Extra Settings" JSON automatically when the plugin updates. Site administrators should backup these values before updating and check that the merge happened correctly after the update. Individual widgets using the language dictionary field have not been changed.
- The user export functionality was removed. There are several capable user export plugins out there that can do a better job than what was built into this plugin.
- The ability to upload a JSON settings file to import settings has been removed. Core WordPress settings did not allow the JSON file type and directly-posted input is easier to validate. Settings JSON can still be pasted into a field and saved. This can should be made before the site is updated to ensure that the fields remain.
- The site will no longer auto-redirect paths with `auth0` in them to the callback URL. Use `/index.php?auth0=1` to use the callback URL directly. Your site may require permalinks to be refreshed manually by going to **wp-admin > Settings > Permalinks** and clicking **Save Changes**.
- API errors will no longer call `error_log()` to appear on the PHP error log. Use the `auth0_insert_error` action to take additional steps when logged errors occur.
- The Enterprise Setup Wizard option was removed. This mainly existed to call attention to possible Enterprise connections, which needed to be maintained. No enterprise-related setup functionality has been removed.
- The `jti` check for JWT-formatted migration tokens has been removed. The token is now checked as-is and new tokens do not follow any format.

## Public API Changes

- Removed second parameter of `WP_Auth0_Users::create_user()`
- Rename style slug from `wpa0_admin_initial_settup` style to `wpa0_admin_initial_setup`
- The `renderAuth0Form()` function was changed and moved to `WP_Auth0_Lock::render()`
- All methods from and instances of `WP_Auth0_Options_Generic` have been changed to `WP_Auth0_Options`
- Renamed the `WP_Auth0_Lock10_Options` class to `WP_Auth0_Lock`
- Removed the `WP_Auth0_Profile_Delete_Data->$users_repo` property and constructor parameter
- Removed the `WP_Auth0_LoginManager->$admin_role` property
- Removed the `WP_Auth0_LoginManager->$ignore_unverified_email` property
- Removed the `WP_Auth0_Admin_Features::FEATURES_DESCRIPTION` class constant
- Removed the `WP_Auth0_Admin_Basic::BASIC_DESCRIPTION` class constant
- Removed the `WP_Auth0_Admin_Appearance::APPEARANCE_DESCRIPTION` class constant
- Removed the `WP_Auth0_Admin_Advanced::ADVANCED_DESCRIPTION` class constant
- `WP_Auth0_LoginManager::__construct()` now requires an instance of `WP_Auth0_Options` as the second parameter
- `WP_Auth0_WooCommerceOverrides::__construct()` now requires an instance of `WP_Auth0_Options` as the second parameter
- Remove the `WP_Auth0_Admin_Generic::$_description` property
- Remove the `WP_Auth0_InitialSetup::$enterprise_connection_step` property
- Remove the `WP_Auth0_Api_Abstract::$api_token` property
- Remove the `WP_Auth0_Api_Abstract::$api_token_decoded` property
- Remove the `WP_Auth0_Api_Client_Credentials::$token_decoded` property

## Public API Removals

### Methods

The following class methods have been removed in 4.0.0. If they were replaced with a different method or function, that is listed as well. Otherwise, the functionality provided will need to be implemented in your site's theme or custom plugin.

- `WP_Auth0_Admin_Advanced->render_custom_signup_fields()`, no replacement provided
- `WP_Auth0_Admin_Generic->render_description()`, no replacement provided
- `WP_Auth0::ready()`, use `wp_auth0_is_ready()`
- `WP_Auth0::get_tenant_region()`, use `wp_auth0_get_tenant_region()`
- `WP_Auth0::get_tenant()`, use `wp_auth0_get_tenant()`
- `WP_Auth0::render_back_to_auth0()` to `WP_Auth0_Lock::render_back_to_lock()`
- `WP_Auth0_InitialSetup->init()`, no replacement provided
- `WP_Auth0_InitialSetup->init_setup()`, no replacement provided
- `WP_Auth0_ErrorManager->insert_auth0_error()`, use `WP_Auth0_ErrorLog::insert_error()`
- `WP_Auth0_ErrorLog->init()`, no replacement provided
- `WP_Auth0_Import_Settings->init()`, no replacement provided
- `WP_Auth0_Import_Settings->show_error()`, no replacement provided
- `WP_Auth0_Profile_Delete_Data->init()`, no replacement provided
- `WP_Auth0_Profile_Change_Email->init()`, no replacement provided
- `WP_Auth0_UsersRepo->delete_auth0_object()`, use `wp_auth0_delete_auth0_object()`
- `WP_Auth0_Profile_Change_Password->init()`, no replacement provided
- `WP_Auth0_Email_Verification->init()`, no replacement provided
- `WP_Auth0_Email_Verification::resend_verification_email()`, use `wp_auth0_ajax_resend_verification_email()`
- `WP_Auth0_Email_Verification::ajax_resend_email()`, use `wp_auth0_ajax_resend_verification_email()`
- `WP_Auth0_Admin->create_account_message()`, no replacement provided
- `WP_Auth0_Admin->init()`, no replacement provided
- `WP_Auth0_Admin->cant_connect_to_auth0()`, no replacement provided
- `WP_Auth0_Admin_Generic->rule_validation()`, no replacement provided
- `WP_Auth0_Admin_Generic->render_a0_switch()`, no replacement provided
- `WP_Auth0_DBManager->get_auth0_users()`, no replacement provided
- `WP_Auth0_DBManager->init()`, no replacement provided
- `WP_Auth0_DBManager->check_update()`, no replacement provided
- `WP_Auth0_DBManager->notice_failed_client_grant()`, no replacement provided
- `WP_Auth0_DBManager->notice_successful_client_grant()`, no replacement provided
- `WP_Auth0_DBManager->notice_successful_grant_types()`, no replacement provided
- `WP_Auth0_DBManager->migrate_users_data()`, no replacement provided
- `WP_Auth0_Routes->init()`, no replacement provided
- `WP_Auth0_Options->can_show_wp_login_form()`, use `wp_auth0_can_show_wp_login_form()`
- `WP_Auth0_Options->set_connection()`, no replacement provided
- `WP_Auth0_Options->get_connection()`, no replacement provided
- `WP_Auth0_Options->get_enabled_connections()`, no replacement provided
- `WP_Auth0_Options->get_client_signing_algorithm()`, use `wp_auth0_get_option('client_signing_algorithm')`
- `WP_Auth0_Options->get_client_secret_as_key()`, no direct replacement provided
- `WP_Auth0_Options->convert_client_secret_to_key()`, no direct replacement provided
- `WP_Auth0_Options::get_cross_origin_loc()`, no replacement provided
- `WP_Auth0_Options::get_logout_url()`, use `WP_Auth0_LoginManager::auth0_logout_url()`
- `WP_Auth0_Api_Operations->update_wordpress_connection()`, no replacement provided
- `WP_Auth0_Api_Operations->toggle_rule()`, no replacement provided
- `WP_Auth0_Api_Operations->disable_signup_wordpress_connection()`, no replacement provided
- `WP_Auth0_Api_Operations->social_validation()`, no replacement provided
- `WP_Auth0_Lock10_Options->get_client_id()`, use `wp_auth0_get_option('client_id')`
- `WP_Auth0_Lock10_Options->get_domain()`, use `wp_auth0_get_option('domain')`
- `WP_Auth0_Lock10_Options->is_registration_enabled()`, use `WP_Auth0_Options->is_wp_registration_enabled()`
- `WP_Auth0_Lock10_Options->show_as_modal()`, no replacement provided
- `WP_Auth0_Lock10_Options->_get_boolean()`, no replacement provided
- `WP_Auth0_Lock10_Options->get_lock_classname()`, no replacement provided
- `WP_Auth0_Lock10_Options->can_show()`, use `WP_Auth0::ready()`
- `WP_Auth0_Lock10_Options->get_cdn_url()`, use `WP_Auth0_Options->get_lock_url()`
- `WP_Auth0_Lock10_Options->get_wordpress_login_enabled()`, use `wp_auth0_get_option('wordpress_login_enabled')`
- `WP_Auth0_Lock10_Options->set_signup_mode()`, no replacement provided
- `WP_Auth0_Lock10_Options->get_lock_show_method()`, no replacement provided
- `WP_Auth0_Lock10_Options->modal_button_name()`, no replacement provided
- `WP_Auth0_Lock10_Options->get_custom_signup_fields()`, no replacement provided
- `WP_Auth0_Lock10_Options->has_custom_signup_fields()`, no replacement provided
- `WP_Auth0_Lock10_Options->get_code_callback_url()`, no replacement provided
- `WP_Auth0_Lock10_Options->get_implicit_callback_url()`, no replacement provided
- `WP_Auth0_Lock10_Options->get_auth0_implicit_workflow()`, no replacement provided
- `WP_Auth0_Lock10_Options->get_sso()`, no replacement provided
- `WP_Auth0_Lock10_Options->get_sso_options()`, no replacement provided
- `WP_Auth0_Lock10_Options->get_custom_css()`, no replacement provided
- `WP_Auth0_Lock10_Options->get_custom_js()`, no replacement provided
- `WP_Auth0_Ip_Check->check_activate()`, no replacement provided
- `WP_Auth0_Ip_Check->init()`, no replacement provided
- `WP_Auth0_Ip_Check->validate_ip()`, no replacement provided
- `WP_Auth0_Ip_Check->get_ranges()`, no replacement provided
- `WP_Auth0_EditProfile->delete_mfa()`, no replacement provided
- `WP_Auth0_EditProfile->show_delete_mfa()`, no replacement provided
- `WP_Auth0_Api_Client_Credentials::get_token_decoded()`, no replacement provided
- `WP_Auth0->check_signup_status()`, no replacement provided
- `WP_Auth0_Api_Abstract->decode_jwt()`, no replacement provided
- `JWT::urlsafeB64Encode()`, replaced with `wp_auth0_url_base64_encode()`
- `JWT::urlsafeB64Decode()`, replaced with `wp_auth0_url_base64_decode()`
- `WP_Auth0_Api_Client::convertCertToPem()`, replaced with `WP_Auth0_JwksFetcher->convertCertToPem()`
- `WP_Auth0_Api_Client::JWKfetch()`, replaced with `WP_Auth0_JwksFetcher->getKeys()`
- `WP_Auth0_Api_Client::get_info_headers()`, replaced with `WP_Auth0_Api_Abstract::get_info_headers()`
- `WP_Auth0_Api_Client::get_token()`, use `WP_Auth0_Api_Client_Credentials`
- `WP_Auth0_Api_Client::get_client_token()`, no replacement provided
- `WP_Auth0_Api_Client::get_user_info()`, no replacement provided
- `WP_Auth0_Api_Client::get_user()`, replaced with `WP_Auth0_Api_Get_User`
- `WP_Auth0_Api_Client::get_client()`, no replacement provided
- `WP_Auth0_Api_Client::update_client()`, no replacement provided
- `WP_Auth0_Api_Client::create_rule()`, no replacement provided
- `WP_Auth0_Api_Client::delete_rule()`, no replacement provided
- `WP_Auth0_Api_Client::get_connection()`, no replacement provided
- `WP_Auth0_Api_Client::update_user()`, replaced with `WP_Auth0_Api_Change_Email`
- `WP_Auth0_Api_Client::GetConsentScopestoShow()`, no replacement provided
- `WP_Auth0_Api_Client::update_guardian()`, no replacement provided
- `WP_Auth0_Api_Client::ro()`, no replacement provided
- `WP_Auth0_Api_Client::validate_user_token()`, no replacement provided
- `WP_Auth0_Api_Client::search_users()`, no replacement provided
- `WP_Auth0_Api_Client::resend_verification_email()`, replaced with `WP_Auth0_Api_Jobs_Verification`
- `WP_Auth0_Api_Client::create_user()`, use `WP_Auth0_Api_Client::signup_user()`
- `WP_Auth0_Api_Client::search_clients()`, no replacement provided
- `WP_Auth0_Api_Client::get_current_user()`, no replacement provided
- `WP_Auth0_Api_Client::delete_connection()`, no replacement provided
- `WP_Auth0_Api_Client::delete_user_mfa()`, no replacement provided
- `WP_Auth0_Api_Client::change_password()`, replaced with `WP_Auth0_Api_Change_Password`
- `WP_Auth0_Api_Client::link_users()`, no replacement provided
- `WP_Auth0_LoginManager->init()`, no replacement provided
- `WP_Auth0_LoginManager->end_session()`, no replacement provided
- `WP_Auth0_LoginManager->login_with_credentials()`, no replacement provided
- `WP_Auth0_LoginManager->dieWithVerifyEmail()`, replaced with `WP_Auth0_Email_Verification::render_die()`
- `WP_Auth0_LoginManager->implicit_login()`, no replacement provided
- `WP_Auth0_LoginManager->auth0_sso_footer()`, no replacement provided
- `WP_Auth0_LoginManager->auth0_singlelogout_footer()`, no replacement provided
- `WP_Auth0_Admin_Features->render_password_policy()`, no replacement provided
- `WP_Auth0_Admin_Features->render_sso()`, no replacement provided
- `WP_Auth0_Admin_Features->render_passwordless_enabled()`, no replacement provided
- `WP_Auth0_Admin_Features->render_mfa()`, no replacement provided
- `WP_Auth0_Admin_Features->render_fullcontact()`, no replacement provided
- `WP_Auth0_Admin_Features->render_fullcontact_apikey()`, no replacement provided
- `WP_Auth0_Admin_Features->render_geo()`, no replacement provided
- `WP_Auth0_Admin_Features->render_income()`, no replacement provided
- `WP_Auth0_Admin_Features->sso_validation()`, no replacement provided
- `WP_Auth0_Admin_Features->security_validation()`, no replacement provided
- `WP_Auth0_Admin_Features->fullcontact_validation()`, no replacement provided
- `WP_Auth0_Admin_Features->mfa_validation()`, no replacement provided
- `WP_Auth0_Admin_Features->georule_validation()`, no replacement provided
- `WP_Auth0_Admin_Features->incomerule_validation()`, no replacement provided
- `WP_Auth0_Admin_Features->render_features_description()`, no replacement provided
- `WP_Auth0_Admin_Basic->render_client_secret_b64_encoded()`, no replacement provided
- `WP_Auth0_Admin_Basic->render_auth0_app_token()`, no replacement provided
- `WP_Auth0_Admin_Basic->render_allow_signup_regular_multisite()`, no replacement provided
- `WP_Auth0_Admin_Basic->render_allow_signup_regular()`, no replacement provided
- `WP_Auth0_Admin_Basic->render_basic_description()`, no replacement provided
- `WP_Auth0_Admin_Basic->render_auth0_app_token()`, replaced with `wp_auth0_ajax_delete_cache_transient()`
- `WP_Auth0_Admin_Advanced->render_auth0_implicit_workflow()`, no replacement provided
- `WP_Auth0_Admin_Appearance->render_social_big_buttons()`, no replacement provided
- `WP_Auth0_Admin_Appearance->render_custom_css()`, no replacement provided
- `WP_Auth0_Admin_Appearance->render_custom_js()`, no replacement provided
- `WP_Auth0_Admin_Appearance->render_language()`, no replacement provided
- `WP_Auth0_Admin_Appearance->render_language_dictionary()`, no replacement provided
- `WP_Auth0_Admin_Appearance->render_appearance_description()`, no replacement provided
- `WP_Auth0_Admin_Advanced->render_connections()`, no replacement provided
- `WP_Auth0_Admin_Advanced->render_custom_cdn_url()`, no replacement provided
- `WP_Auth0_Admin_Advanced->render_cdn_url()`, no replacement provided
- `WP_Auth0_Admin_Advanced->render_link_auth0_users()`, no replacement provided
- `WP_Auth0_Admin_Advanced->render_extra_conf()`, no replacement provided
- `WP_Auth0_Admin_Advanced->link_accounts_validation()`, no replacement provided
- `WP_Auth0_Admin_Advanced->render_ip_range_check()`, no replacement provided
- `WP_Auth0_Admin_Advanced->render_ip_ranges()`, no replacement provided
- `WP_Auth0_Admin_Advanced->render_social_twitter_key()`, no replacement provided
- `WP_Auth0_Admin_Advanced->render_social_twitter_secret()`, no replacement provided
- `WP_Auth0_Admin_Advanced->render_social_facebook_key()`, no replacement provided
- `WP_Auth0_Admin_Advanced->render_social_facebook_secret()`, no replacement provided
- `WP_Auth0_Admin_Advanced->render_passwordless_enabled()`, no replacement provided
- `WP_Auth0_Admin_Advanced->render_passwordless_method()`, no replacement provided
- `WP_Auth0_Admin_Advanced->render_metrics()`, no replacement provided
- `WP_Auth0_Admin_Advanced->render_advanced_description()`, no replacement provided
- `WP_Auth0_Admin_Advanced->connections_validation()`, no replacement provided
- `WP_Auth0_Admin_Advanced->generate_token()`, replaced with `wp_auth0_generate_token()`
- `WP_Auth0_Admin_Advanced->auth0_rotate_migration_token()`, replaced with `wp_auth0_ajax_rotate_migration_token()`
- `WP_Auth0_Admin_Advanced->render_jwt_auth_integration()`, no replacement provided
- `WP_Auth0_InitialSetup->admin_enqueue()`, no replacement provided
- `WP_Auth0_InitialSetup->notify_setup()`, no replacement provided
- `WP_Auth0_InitialSetup_Connections->update_connection()`, no replacement provided
- `WP_Auth0_InitialSetup_Connections->toggle_db()`, no replacement provided
- `WP_Auth0_InitialSetup_Connections->toggle_social()`, no replacement provided
- `WP_Auth0_WooCommerceOverrides->init()`, no replacement provided
- `WP_Auth0_UsersRepo->init()`, no replacement provided
- `WP_Auth0_UsersRepo->getUser()`, no replacement provided
- `WP_Auth0_UsersRepo->tokenHasRequiredScopes()`, no replacement provided

### Classes

The following classes have been removed in 4.0.0. If your site is using one of the classes below, the functionality provided will need to be implemented in your site's theme or custom plugin.

- `WP_Auth0_InitialSetup_EnterpriseConnection`, no replacement provided
- `WP_Auth0`, see section above for new function names
- `WP_Auth0_ErrorManager`, no replacement provided
- `WP_Auth0_Settings_Section`, no replacement provided
- `WP_Auth0_Export_Users`, no replacement provided
- `WP_Auth0_UserProfile`, no replacement provided
- `WP_Auth0_EditProfile`, no replacement provided
- `WP_Auth0_Options_Generic`, use `WP_Auth0_Options` (see [Classes Changed](#classes-changed) above)
- `WP_Auth0_Referer_Check`, no replacement provided
- `WP_Auth0_Api_Delete_User_Mfa`, no replacement provided
- `WP_Auth0_Profile_Delete_Mfa`, no replacement provided
- `WP_Auth0_RulesLib`, no replacement provided
- `WP_Auth0_Id_Token_Validator`, replaced with `WP_Auth0_IdTokenVerifier`
- `JWT`, replaced with `lcobucci/jwt` ([GitHub](https://github.com/lcobucci/jwt))
- `WP_Auth0_Metrics`, no replacement provided
- `WP_Auth0_Lock_Options`, no replacement provided
- `WP_Auth0_Lock10_Options`, use `WP_Auth0_Lock` (see [Classes Changed](#classes-changed) above)
- `WP_Auth0_CustomDBLib`, no replacement provided
- `WP_Auth0_InitialSetup_Migration`, no replacement provided
- `WP_Auth0_InitialSetup_Rules`, no replacement provided
- `WP_Auth0_InitialSetup_Signup`, no replacement provided
- `WP_Auth0_Configure_JWTAUTH`, no replacement provided
- `WP_Auth0_Amplificator`, no replacement provided
- `WP_Auth0_SocialAmplification_Widget`, no replacement provided
- `TwitterAPIExchange`, no replacement provided
- `WP_Auth0_Admin_Dashboard`, no replacement provided
- `WP_Auth0_Dashboard_Plugins_Age`, no replacement provided
- `WP_Auth0_Dashboard_Plugins_Gender`, no replacement provided
- `WP_Auth0_Dashboard_Plugins_Generic`, no replacement provided
- `WP_Auth0_Dashboard_Plugins_IdP`, no replacement provided
- `WP_Auth0_Dashboard_Plugins_Income`, no replacement provided
- `WP_Auth0_Dashboard_Plugins_Location`, no replacement provided
- `WP_Auth0_Dashboard_Plugins_Signups`, no replacement provided
- `WP_Auth0_Dashboard_Widgets`, no replacement provided
