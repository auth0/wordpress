<?php

declare(strict_types=1);

use Isolated\Symfony\Component\Finder\Finder;

return [
    'prefix' => 'Auth0\\WordPress\\Vendor',

    'finders' => [
        // Allowlist the plugin's own source; root files ship via append() below.
        Finder::create()
            ->files()
            ->ignoreVCS(true)
            ->in('src'),

        // jetbrains/phpstorm-stubs declares PHP internals (incl. assert()) as
        // global functions; scoping them emits an illegal assert() wrapper that
        // fatals scoper-autoload.php. Dev-only, stripped by --no-dev regardless.
        Finder::create()
            ->files()
            ->ignoreVCS(true)
            ->exclude([
                'doc',
                'test',
                'test_old',
                'tests',
                'Tests',
                'vendor-bin',
                'jetbrains',
            ])
            ->in('vendor'),

        // Shippable root files (composer.json drives autoloading; build.sh strips it).
        Finder::create()->append([
            'composer.json',
            'CHANGELOG.md',
            'LICENSE.md',
            'README.md',
            'UPGRADING.md',
            'updates.json',
        ]),
    ],

    'exclude-namespaces' => [
        '/^Auth0\\\\WordPress\\\\/',
        '/^Auth0\\\\WordPress/',
        '/^Psr\\\/',
    ],

    'exclude-classes' => [
        '/^WP_/',
        'wpdb',
        'Walker',
        'WP_Widget',
    ],

    'exclude-functions' => [
        '/^wp_/',
        '/^is_/',
        '/^get_/',
        '/^add_/',
        '/^remove_/',
        '/^do_/',
        '/^apply_/',
        '/^has_/',
        '/^current_/',
        '/^esc_/',
        '/^__/',
        '/^_e/',
        '/^_x/',
        '/^_n/',
        '/^auth_redirect/',
        '/^check_/',
        '/^sanitize_/',
        '/^absint/',
        '/^home_url/',
        '/^admin_url/',
        '/^site_url/',
        '/^plugin_/',
        '/^register_/',
        '/^settings_/',
        '/^update_/',
        '/^delete_/',
        '/^set_/',
    ],

	'expose-global-constants' => true,
	'expose-global-classes'   => true,
	'expose-global-functions' => true,

    'patchers' => [],
];
