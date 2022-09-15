<?php

/**
 * Plugin Name:       Auth0
 * Plugin URL:        https://github.com/auth0/wp-auth0
 * Description:       Supercharge your WordPress website with Auth0. Improve account security, add support for multifactor, enable social, passwordless and enterprise connections, and much more.
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

// Load plugin helper functions
require_once plugin_dir_path(__FILE__) . 'functions.php';

// Run plugin functions
wpAuth0()->run();
