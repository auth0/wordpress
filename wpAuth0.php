<?php

/**
 * Plugin Name:       Auth0
 * Plugin URL:        https://github.com/auth0/wordpress
 * Description:       Supercharge your WordPress website with Auth0. Improve account security, add support for multifactor, enable social, passwordless and enterprise connections, and much more.
 * Version:           5.2.0
 * Requires at least: 6.0
 * Tested up to:      6.4
 * Stable tag:        5.2.0
 * Requires PHP:      8.1
 * Author:            Auth0
 * Author URI:        https://auth0.com
 * License:           MIT
 * License URI:       https://github.com/auth0/wordpress/blob/master/LICENSE
 * Text Domain:       wp-auth0
 * Domain Path:       /languages
 */

declare(strict_types=1);

use Auth0\WordPress\Plugin;
use Auth0\SDK\Auth0 as Sdk;
use Auth0\SDK\Configuration\SdkConfiguration as Configuration;

define('WP_AUTH0_VERSION', '5.2.0');

// Require loading through WordPress
if (! defined('ABSPATH')) {
    die;
}

// Load dependencies
if (file_exists(plugin_dir_path(__FILE__) . '/vendor/autoload.php')) {
    require_once plugin_dir_path(__FILE__) . '/vendor/autoload.php';
}

register_activation_hook(
    __FILE__,
    function () {
        $cookies = get_option('auth0_cookies', []);

        if (! is_array($cookies) || [] === $cookies || ! isset($cookies['secret'])) {
            add_option('auth0_cookies', [
                'secret' => bin2hex(random_bytes(64))
            ]);
        }

        $backchannelLogout = get_option('auth0_backchannel_logout', []);

        if (! is_array($backchannelLogout) || [] === $backchannelLogout || ! isset($backchannelLogout['secret'])) {
            add_option('auth0_backchannel_logout', [
                'secret' => bin2hex(random_bytes(64))
            ]);
        }

        $authentication = get_option('auth0_authentication', []);

        if (! is_array($authentication) || [] === $authentication || ! isset($authentication['fallback_secret'])) {
            add_option('auth0_authentication', [
                'fallback_secret' => bin2hex(random_bytes(64))
            ]);
        }
    }
);

// Run plugin functions
wpAuth0()->run();

/**
 * Return a configured singleton of the Auth0 WP plugin.
 *
 * @param null|Plugin $plugin Optional. An existing instance of Auth0\WordPress\Plugin to use.
 * @param null|Auth0\SDK\Auth0 $sdk Optional. An existing instance of Auth0\SDK\Auth0 to use.
 * @param null|Auth0\SDK\Configuration\SdkConfiguration $configuration Optional. An existing instance of Auth0\SDK\Configuration\SdkConfiguration to use.
 */
function wpAuth0(
    ?Plugin $plugin = null,
    ?Sdk $sdk = null,
    ?Configuration $configuration = null,
): Plugin {
    static $instance = null;

    $instance ??= $instance ?? $plugin ?? new Plugin($sdk, $configuration);

    return $instance;
}
