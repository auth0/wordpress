<?php

declare(strict_types=1);

use Auth0\WordPress\Plugin;
use Auth0\SDK\Auth0 as Sdk;
use Auth0\SDK\Configuration\SdkConfiguration as Configuration;

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
