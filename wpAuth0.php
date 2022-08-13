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

define('WP_AUTH0_VERSION', '5.0.0');

if (! defined('WPINC')) {
    die;
}

require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';
require_once plugin_dir_path(__FILE__) . 'src/Auth0.php';
require_once plugin_dir_path(__FILE__) . 'functions.php';

wpAuth0()->run();
