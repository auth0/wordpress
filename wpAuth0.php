<?php

/**
 * Plugin Name:       Auth0
 * Plugin URL:        https://github.com/auth0/wp-auth0
 * Description:       Supercharge WordPress with Auth0, improving security, adding support for multifactor, and enabling social, passwordless and enterprise connections.
 * Version:           5.0.0
 * Author:            Auth0
 * Author URI:        https://auth0.com
 * License:           MIT
 * License URI:       https://github.com/auth0/wp-auth0/blob/master/LICENSE
 * Text Domain:       wp-auth0
 * Domain Path:       /languages
 */

declare(strict_types=1);

define('WP_AUTH0_VERSION', '5.0.0');

// Require loading through WordPress
if (! defined('ABSPATH')) {
    die;
}

// Load dependencies
require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';

// Load plugin class
require_once plugin_dir_path(__FILE__) . 'src/Plugin.php';
require_once plugin_dir_path(__FILE__) . 'src/Actions.php';
require_once plugin_dir_path(__FILE__) . 'src/Actions/Authentication.php';

// Load plugin helper functions
require_once plugin_dir_path(__FILE__) . 'functions.php';

// Register plugin hooks
require_once plugin_dir_path(__FILE__) . 'hooks.php';

// Run plugin functions
wpAuth0()->run();
