<?php

declare(strict_types=1);

namespace Auth0\WordPress\Actions;

use Auth0\SDK\Utility\HttpResponse;
use Auth0\WordPress\Utilities\{Render, Sanitize};

use function array_slice;
use function count;
use function is_array;
use function is_string;
use function strlen;

final class Configuration extends Base
{
    /**
     * @var array<string, array<string, array<string, array<string, array<string, array<string, array<int, string>|array<string, string>|string>>|array<string, array<string, array<int, string>|array<string, string>|string>|array<string, string|string[]>>|array<string, array<string, array<string, string>|string>>|array<string, array<string, array<string, string>|string|string[]>>|array<string, array<string, string>>|string>>|array<string, array<string, array<string, array<string, array<string, string>|string>>|array<string, array<string, array<string, string>|string|string[]>>|array<string, array<string, string>>|string>>|array<string, array<string, array<string, array<string, array<string, string>|string|string[]>|array<string, string>>|string>>|string>>
     */
    private const PAGES = [
        self::CONST_PAGE_GENERAL => [
            'title' => 'Auth0 — Options',
            'sections' => [
                'state' => [
                    'title' => '',
                    'description' => '',
                    'options' => [
                        'enable' => [
                            'title' => 'Enable Authentication',
                            'type' => 'boolean',
                            'enabled' => 'isPluginReady',
                            'description' => ['getOptionDescription', 'enable'],
                            'select' => [
                                'false' => 'Disabled',
                                'true' => 'Enabled',
                            ],
                        ],
                    ],
                ],
                'accounts' => [
                    'title' => 'WordPress Users Management',
                    'description' => '',
                    'options' => [
                        'matching' => [
                            'title' => 'Connection Matching',
                            'type' => 'text',
                            'enabled' => 'isPluginReady',
                            'description' => '<b>Flexible</b> allows users to sign in using more than one connection type.<br /><b>Strict</b> is more secure, but may lead to confusion for users who forget their sign in method.',
                            'select' => [
                                'flexible' => 'Flexible: Match Verified Email Addresses to Users',
                                'strict' => 'Strict: Match Unique Connections to Users',
                            ],
                        ],
                        'missing' => [
                            'title' => 'Missing Users',
                            'type' => 'text',
                            'enabled' => 'isPluginReady',
                            'description' => 'What to do after a successful sign in, but there is no matching WordPress account.<br />For Database Connections, the "Disable Sign Ups" setting takes priority over this option.',
                            'select' => [
                                'reject' => 'Deny access',
                                'create' => 'Create account',
                            ],
                        ],
                        'default_role' => [
                            'title' => 'Default Role',
                            'type' => 'text',
                            'enabled' => 'isPluginReady',
                            'description' => 'The role to assign new WordPress users created by the plugin.',
                            'select' => 'getRoleOptions',
                        ],
                        'passwordless' => [
                            'title' => 'Allow Passwordless',
                            'type' => 'boolean',
                            'enabled' => 'isPluginReady',
                            'description' => 'You must <a href="https://auth0.com/docs/authenticate/passwordless" target="_blank">enable Passwordless Connections</a> to use this.',
                            'select' => [
                                'true' => 'Enabled',
                                'false' => 'Disabled',
                            ],
                        ],
                    ],
                ],
                'client' => [
                    'title' => 'Application Configuration',
                    'description' => 'The appropriate values for these settings can be found in your <a href="https://manage.auth0.com">Auth0 Dashboard</a>.',
                    'options' => [
                        'id' => [
                            'title' => 'Client ID',
                            'type' => 'text',
                            'sanitizer' => 'string',
                            'description' => 'Required. Must be configured as a <a href="https://auth0.com/docs/get-started/applications" target="_blank">Regular Web Application</a>.',
                        ],
                        'secret' => [
                            'title' => 'Client Secret',
                            'type' => 'password',
                            'sanitizer' => 'string',
                            'description' => 'Required.',
                        ],
                        'domain' => [
                            'title' => 'Domain',
                            'type' => 'text',
                            'sanitizer' => 'domain',
                            'description' => 'Required.',
                        ],
                    ],
                ],
            ],
        ],
        self::CONST_PAGE_SYNC => [
            'title' => 'Auth0 — Sync Options',
            'sections' => [
                'sync' => [
                    'title' => '',
                    'description' => '',
                    'options' => [
                        'database' => [
                            'title' => 'Database Connection',
                            'type' => 'text',
                            'enabled' => 'isPluginReady',
                            'sanitizer' => 'string',
                            'description' => 'Database Connection to sync WordPress with for non-social connections. Must begin with <code>con_</code>.',
                        ],
                        'schedule' => [
                            'title' => 'Sync Frequency',
                            'type' => 'number',
                            'enabled' => 'isPluginReady',
                            'description' => ['getOptionDescription', 'sync_enable'],
                            'select' => [
                                0 => 'Disabled',
                                300 => '5 minutes',
                                900 => '15 minutes',
                                1800 => '30 minutes',
                                3600 => '1 hour',
                                3600 * 6 => '6 hours',
                                3600 * 12 => '12 hours',
                                3600 * 24 => '1 day',
                                86400 * 2 => '2 days',
                                86400 * 4 => '4 days',
                                86400 * 7 => '1 week',
                            ],
                        ],
                        'push' => [
                            'title' => 'On-Demand Changes',
                            'type' => 'boolean',
                            'enabled' => 'isPluginReady',
                            'description' => 'Pushes changes to Auth0 Database as they are made. This may degrade performance.',
                            'select' => [
                                'disable' => 'Disabled',
                                'enable_email' => 'Enabled for email addresses',
                                'enable' => 'Enabled for all changes',
                            ],
                        ],
                    ],
                ],
                'sync_events' => [
                    'title' => 'Synchronised Events',
                    'description' => '',
                    'options' => [
                        'user_creation' => [
                            'title' => 'User Creation',
                            'type' => 'boolean',
                            'enabled' => 'isPluginReady',
                            'description' => 'Create matching Auth0 user when a WordPress user is created (and vice versa.)',
                            'select' => [
                                'true' => 'Enabled',
                                'false' => 'Disabled',
                            ],
                        ],
                        'user_deletion' => [
                            'title' => 'User Deletion',
                            'type' => 'boolean',
                            'enabled' => 'isPluginReady',
                            'description' => 'Delete matching Auth0 user when a WordPress user is deleted (and vice versa.)',
                            'select' => [
                                'true' => 'Enabled',
                                'false' => 'Disabled',
                            ],
                        ],
                        'user_updates' => [
                            'title' => 'User Updates',
                            'type' => 'boolean',
                            'enabled' => 'isPluginReady',
                            'description' => 'Update matching Auth0 user when a WordPress user is updated (and vice versa.)',
                            'select' => [
                                'true' => 'Enabled',
                                'false' => 'Disabled',
                            ],
                        ],
                    ],
                ],
            ],
        ],
        self::CONST_PAGE_ADVANCED => [
            'title' => 'Auth0 — Advanced Options',
            'sections' => [
                'authentication' => [
                    'title' => 'Authentication',
                    'description' => '',
                    'options' => [
                        'pair_sessions' => [
                            'title' => 'Pair Sessions',
                            'type' => 'int',
                            'enabled' => 'isPluginReady',
                            'description' => 'Affected users must reauthenticate if either their WordPress or Auth0 session are invalid.',
                            'select' => [
                                0 => 'Enabled for Non-Administrators',
                                1 => 'Enabled for All (Recommended)',
                                2 => 'Disabled',
                            ],
                        ],
                        'allow_fallback' => [
                            'title' => 'WordPress Login Fallback',
                            'type' => 'boolean',
                            'enabled' => 'isPluginReady',
                            'description' => 'Allows signing in with the standard WordPress login form using a secret link.',
                            'select' => [
                                'true' => 'Enabled',
                                'false' => 'Disabled',
                            ],
                        ],
                        'fallback_secret' => [
                            'title' => 'Fallback Secret',
                            'type' => 'password',
                            'enabled' => 'isPluginReady',
                            'description' => ['getOptionDescription', 'fallback_secret'],
                        ],
                    ],
                ],
                'client_advanced' => [
                    'title' => 'Additional Application Configuration',
                    'description' => 'The appropriate values for these settings can be found in your <a href="https://manage.auth0.com">Auth0 Dashboard</a>.',
                    'options' => [
                        'custom_domain' => [
                            'title' => 'Custom Domain',
                            'type' => 'text',
                            'enabled' => 'isPluginReady',
                            'sanitizer' => 'domain',
                            'description' => 'Configure to authenticate using a <a href="https://auth0.com/docs/customize/custom-domains" target="_blank">custom domain</a>.',
                        ],
                        'apis' => [
                            'title' => 'API Audiences',
                            'type' => 'textarea',
                            'enabled' => 'isPluginReady',
                            'sanitizer' => 'string',
                            'description' => 'A list of <a href="https://auth0.com/docs/get-started/apis" target="_blank">Auth0 API Audiences</a> to allow, each on its own line. The top entry will be used by default.',
                        ],
                        'organizations' => [
                            'title' => 'Organizations',
                            'type' => 'textarea',
                            'enabled' => 'isPluginReady',
                            'sanitizer' => 'orgs',
                            'description' => 'A list of <a href="https://auth0.com/docs/manage-users/organizations" target="_blank">Organization IDs</a> to allow, each on its own line beginning with <code>org_</code>. The top entry will be used by default.',
                        ],
                    ],
                ],
                'tokens' => [
                    'title' => 'Token Handling',
                    'description' => 'JSON Web Tokens are used to facilitate authentication with Auth0. <a href="https://auth0.com/docs/secure/tokens/json-web-tokens" target="_blank">Learn more.</a>',
                    'options' => [
                        'caching' => [
                            'title' => 'JWKS Caching',
                            'type' => 'text',
                            'enabled' => 'isPluginReady',
                            'description' => 'Disabling caching will negatively affect performance.',
                            'select' => [
                                'wp_object_cache' => 'WP_Object_Cache (Recommended)',
                                'disable' => 'Disabled',
                            ],
                        ],
                    ],
                ],
                'sessions' => [
                    'title' => 'Sessions',
                    'description' => 'These settings control how user authentication states are persisted on devices between requests.',
                    'options' => [
                        'method' => [
                            'title' => 'Device Storage Method',
                            'type' => 'text',
                            'enabled' => 'isPluginReady',
                            'description' => 'PHP Sessions require external configuration to work <a href="https://cheatsheetseries.owasp.org/cheatsheets/PHP_Configuration_Cheat_Sheet.html#php-session-handling" target="_blank">securely</a> and <a href="https://www.php.net/manual/en/features.session.security.management.php" target="_blank">reliably</a>.',
                            'select' => [
                                'cookies' => 'Encrypted Cookies',
                                'sessions' => 'PHP Native Sessions (Recommended)',
                            ],
                        ],
                        'session_ttl' => [
                            'title' => 'Session Expires',
                            'type' => 'int',
                            'enabled' => 'isPluginReady',
                            'description' => 'How long before WordPress prompts users to reauthenticate.',
                            'select' => [
                                0 => 'Default',
                                1800 => '30 minutes',
                                3600 => '1 hour',
                                3600 * 6 => '6 hours',
                                3600 * 12 => '12 hours',
                                3600 * 24 => '1 day',
                                86400 * 2 => '2 days',
                                86400 * 4 => '4 days',
                                86400 * 7 => '1 week',
                                86400 * 14 => '2 weeks',
                                86400 * 30 => '1 month',
                            ],
                        ],
                        'rolling_sessions' => [
                            'title' => 'Use Rolling Sessions',
                            'type' => 'boolean',
                            'enabled' => 'isPluginReady',
                            'description' => 'Session expirations will be updated on each request, extending their lifetime.',
                            'select' => [
                                'true' => 'Enabled',
                                'false' => 'Disabled',
                            ],
                        ],
                        'refresh_tokens' => [
                            'title' => 'Use Refresh Tokens',
                            'type' => 'boolean',
                            'enabled' => 'isPluginReady',
                            'description' => 'Must select "Allow Offline Access" in your Auth0 API Settings.',
                            'select' => [
                                'false' => 'Disabled',
                                'true' => 'Enabled',
                            ],
                        ],
                    ],
                ],
                'cookies' => [
                    'title' => 'Session Cookies',
                    'description' => 'These options customize how sessions are stored on user devices by the storage method above.',
                    'options' => [
                        'secret' => [
                            'title' => 'Secret',
                            'type' => 'password',
                            'description' => 'Required. Changes will log all users out.',
                        ],
                        'domain' => [
                            'title' => 'Domain',
                            'type' => 'text',
                            'enabled' => 'isPluginReady',
                            'description' => ['getOptionDescription', 'cookie_domain'],
                            'placeholder' => ['getOptionPlaceholder', 'cookie_domain'],
                        ],
                        'path' => [
                            'title' => 'Path',
                            'type' => 'text',
                            'enabled' => 'isPluginReady',
                            'description' => 'Defaults to <code>/</code>.',
                            'placeholder' => '/',
                        ],
                        'secure' => [
                            'title' => 'Require SSL',
                            'type' => 'boolean',
                            'enabled' => 'isPluginReady',
                            'description' => 'Enable this if your site is <b>exclusively</b> served over HTTPS.',
                            'select' => [
                                'false' => 'Disabled',
                                'true' => 'Enabled',
                            ],
                        ],
                        'samesite' => [
                            'title' => 'Same-Site',
                            'type' => 'text',
                            'enabled' => 'isPluginReady',
                            'select' => [
                                'lax' => 'Lax (Suggested)',
                                'strict' => 'Strict',
                                'none' => 'None',
                            ],
                        ],
                        'ttl' => [
                            'title' => 'Expires',
                            'type' => 'number',
                            'enabled' => 'isPluginReady',
                            'select' => [
                                0 => 'Immediately',
                                1800 => '30 minutes',
                                3600 => '1 hour',
                                3600 * 6 => '6 hours',
                                3600 * 12 => '12 hours',
                                3600 * 24 => '1 day',
                                86400 * 2 => '2 days',
                                86400 * 4 => '4 days',
                                86400 * 7 => '1 week',
                                86400 * 14 => '2 weeks',
                                86400 * 30 => '1 month',
                            ],
                        ],
                    ],
                ],
                'backchannel_logout' => [
                    'title' => 'Back-Channel Logout',
                    'description' => 'You must configure your <a href="https://auth0.com/docs/authenticate/login/logout/back-channel-logout/configure-back-channel-logout" target="_blank">Auth0 tenant</a> to enable this feature.',
                    'options' => [
                        'enabled' => [
                            'title' => 'Enabled',
                            'type' => 'boolean',
                            'enabled' => 'isPluginReady',
                            'description' => 'Enable this if your site is <b>exclusively</b> served over HTTPS.',
                            'select' => [
                                'false' => 'Disabled',
                                'true' => 'Enabled',
                            ],
                        ],
                        'ttl' => [
                            'title' => 'Logout Expiration',
                            'type' => 'int',
                            'enabled' => 'isPluginReady',
                            'description' => 'How long before unclaimed Back-Channel Logout tokens expire.',
                            'select' => [
                                0 => 'Default (1 month)',
                                1800 => '30 minutes',
                                3600 => '1 hour',
                                3600 * 6 => '6 hours',
                                3600 * 12 => '12 hours',
                                3600 * 24 => '1 day',
                                86400 * 2 => '2 days',
                                86400 * 4 => '4 days',
                                86400 * 7 => '1 week',
                                86400 * 14 => '2 weeks',
                                86400 * 30 => '1 month',
                            ],
                        ],
                        'secret' => [
                            'title' => 'Secret',
                            'type' => 'password',
                            'enabled' => 'isPluginReady',
                            'description' => ['getOptionDescription', 'backchannel_logout_secret'],
                        ],
                    ],
                ],
            ],
        ],
        self::CONST_PAGE_TOOLS => [
            'title' => 'Auth0 — Tools',
            'sections' => []
        ],
    ];

    /**
     * @var string
     */
    public const CONST_PAGE_TOOLS = 'auth0_tools';

    /**
     * @var string
     */
    public const CONST_PAGE_ADVANCED = 'auth0_advanced';

    /**
     * @var string
     */
    public const CONST_PAGE_GENERAL = 'auth0_configuration';

    /**
     * @var string
     */
    public const CONST_PAGE_SYNC = 'auth0_sync';

    /**
     * @var string
     */
    public const CONST_SECTION_PREFIX = 'auth0';

    /**
     * @var array<string, array<int, int|string>|string>
     */
    protected array $registry = [
        'admin_init' => 'onSetup',
        'admin_menu' => 'onMenu',
        'auth0_ui_configuration' => 'renderConfiguration',
        'auth0_ui_sync' => 'renderSyncConfiguration',
        'auth0_ui_advanced' => 'renderAdvancedConfiguration',
        'auth0_ui_tools' => 'renderToolsConfiguration',
    ];

    public function onMenu(): void
    {
        add_menu_page(
            page_title: 'Auth0 — Options',
            menu_title: 'Auth0',
            capability: 'manage_options',
            menu_slug: 'auth0',
            callback: static function (): void {
                do_action('auth0_ui_configuration');
            },
            icon_url: 'dashicons-shield-alt',
            position: $this->getPriority('MENU_POSITION', 70, 'AUTH0_ADMIN'),
        );

        add_submenu_page(
            parent_slug: 'auth0',
            page_title: 'Auth0 — Options',
            menu_title: 'Options',
            capability: 'manage_options',
            menu_slug: 'auth0',
            position: $this->getPriority('MENU_POSITION_GENERAL', 0, 'AUTH0_ADMIN'),
        );

        add_submenu_page(
            parent_slug: 'auth0',
            page_title: 'Auth0 — Sync Options',
            menu_title: 'Sync',
            capability: 'manage_options',
            menu_slug: 'auth0_sync',
            callback: static function (): void {
                do_action('auth0_ui_sync');
            },
            position: $this->getPriority('MENU_POSITION_SYNC', 1, 'AUTH0_ADMIN'),
        );

        add_submenu_page(
            parent_slug: 'auth0',
            page_title: 'Auth0 — Advanced Options',
            menu_title: 'Advanced',
            capability: 'manage_options',
            menu_slug: 'auth0_advanced',
            callback: static function (): void {
                do_action('auth0_ui_advanced');
            },
            position: $this->getPriority('MENU_POSITION_ADVANCED', 2, 'AUTH0_ADMIN'),
        );

        add_submenu_page(
            parent_slug: 'auth0',
            page_title: 'Auth0 — Tools',
            menu_title: 'Tools',
            capability: 'manage_options',
            menu_slug: 'auth0_tools',
            callback: static function (): void {
                do_action('auth0_ui_tools');
            },
            position: $this->getPriority('MENU_POSITION_ADVANCED', 3, 'AUTH0_ADMIN'),
        );
    }

    public function onSetup(): void
    {
        /**
         * @var array<mixed> $page
         */
        foreach (self::PAGES as $pageId => $page) {
            $sections = (isset($page['sections']) && is_array($page['sections'])) ? $page['sections'] : [];

            /**
             * @var array<mixed> $section
             */
            foreach ($sections as $sectionId => $section) {
                $sectionId = self::CONST_SECTION_PREFIX . '_' . $sectionId;
                $sectionType = (isset($section['type']) && is_string($section['type'])) ? $section['type'] : 'array';
                $sectionCallback = [
                    $this,
                    'onUpdate' . str_replace(' ', '', ucwords(str_replace(['auth0_', '_'], ' ', $sectionId))),
                ];

                /**
                 * @var callable $sectionCallback
                 */
                register_setting(
                    option_group: $pageId,
                    option_name: $sectionId,
                    args: [
                        'type' => $sectionType,
                        'sanitize_callback' => $sectionCallback,
                        'show_in_rest' => false,
                    ],
                );

                add_settings_section(
                    id: $sectionId,
                    title: $section['title'],
                    callback: static function () use ($section): void {
                        echo $section['description'] ?? '';
                    },
                    page: $pageId,
                );

                // $optionValues = null;

                // if ($sectionType === 'array') {
                //     $optionValues = get_option($sectionId, []);
                // }

                // if ($sectionType === 'boolean') {
                //     $optionValues = get_option($sectionId, false);

                //     if (! is_bool($optionValues)) {

                //     }
                // }

                /** @var array<string, mixed> $optionValues */
                $optionValues = get_option($sectionId, []);
                $options = (isset($section['options']) && is_array($section['options'])) ? $section['options'] : [];

                /**
                 * @var array<string, array{title: string, type: string, description?: array<string>|string, placeholder?: array<string>|string, select?: array<mixed>|string, disabled?: bool|string, enabled?: bool|string}> $options
                 */
                foreach ($options as $optionId => $option) {
                    $elementId = uniqid();
                    $optionType = $option['type'];
                    $optionValue = $optionValues[$optionId] ?? null;
                    $optionName = $sectionId . '[' . $optionId . ']';
                    $optionDescription = $option['description'] ?? '';
                    $optionPlaceholder = $option['placeholder'] ?? '';
                    $optionSelections = $option['select'] ?? null;
                    $optionDisabled = $option['disabled'] ?? null;
                    $optionEnabled = $option['enabled'] ?? null;

                    if (is_array($optionDescription)) {
                        $callback = [$this, $optionDescription[0]];

                        /** @var callable $callback */
                        $optionDescription = $callback(...array_slice($optionDescription, 1));
                    }

                    if (is_array($optionPlaceholder)) {
                        $callback = [$this, $optionPlaceholder[0]];

                        /** @var callable $callback */
                        $optionPlaceholder = $callback(...array_slice($optionPlaceholder, 1));
                    }

                    if (is_string($optionDisabled)) {
                        $callback = [$this, $optionDisabled];
                        /** @var callable $callback */
                        $optionDisabled = (true === $callback());
                    }

                    if (is_string($optionEnabled)) {
                        $callback = [$this, $optionEnabled];
                        /** @var callable $callback */
                        $optionDisabled = (false === $callback());
                    }

                    if (is_string($optionSelections)) {
                        $callback = [$this, $optionSelections];
                        /** @var callable $callback */
                        $optionSelections = $callback() ?? [];
                    }

                    if (null !== $optionValue && 'auth0_advanced' === $pageId && ('organizations' === $optionId || 'apis' === $optionId)) {
                        $optionValue = implode("\n", explode(' ', $optionValue));
                    }

                    /**
                     * @var string                      $optionDescription
                     * @var string                      $optionPlaceholder
                     * @var null|array<bool|int|string> $optionSelections
                     * @var bool|int|string             $optionValue
                     */
                    add_settings_field(
                        id: $elementId,
                        title: $option['title'],
                        callback: static function () use (
                            $elementId,
                            $optionName,
                            $optionType,
                            $optionDescription,
                            $optionPlaceholder,
                            $optionValue,
                            $optionSelections,
                            $optionDisabled
                        ): void {
                            Render::option(
                                element: $elementId,
                                name: $optionName,
                                type: $optionType,
                                description: $optionDescription,
                                placeholder: $optionPlaceholder,
                                value: $optionValue,
                                select: $optionSelections,
                                disabled: $optionDisabled,
                            );
                        },
                        page: $pageId,
                        section: $sectionId,
                        args: [
                            'label_for' => $elementId,
                            'description' => $option['description'] ?? '',
                        ],
                    );
                }
            }
        }
    }

    /**
     * @param null|array<null|bool|int|string> $input
     *
     * @return null|array<mixed>
     */
    public function onUpdateAccounts(?array $input): ?array
    {
        if (null === $input) {
            return null;
        }

        $sanitized = [
            'matching' => Sanitize::string((string) ($input['matching'] ?? '')) ?? '',
            'missing' => Sanitize::string((string) ($input['missing'] ?? '')) ?? '',
            'default_role' => Sanitize::string((string) ($input['default_role'] ?? '')) ?? '',
            'passwordless' => Sanitize::boolean((string) ($input['passwordless'] ?? '')) ?? '',
        ];

        return array_filter($sanitized, static fn ($value): bool => '' !== $value);
    }

    /**
     * @param null|array<null|bool|int|string> $input
     *
     * @return null|array<mixed>
     */
    public function onUpdateAuthentication(?array $input): ?array
    {
        if (null === $input) {
            return null;
        }

        $sanitized = [
            'pair_sessions' => Sanitize::integer((string) ($input['pair_sessions'] ?? 0), 2, 0) ?? 0,
            'allow_fallback' => Sanitize::boolean((string) ($input['allow_fallback'] ?? '')) ?? '',
            'fallback_secret' => Sanitize::string((string) ($input['fallback_secret'] ?? '')) ?? '',
        ];

        if ($sanitized['fallback_secret'] === '') {
            $sanitized['fallback_secret'] = bin2hex(random_bytes(64));
        }

        set_site_transient('auth0_updated_fallback', true, 60);

        return array_filter($sanitized, static fn ($value): bool => '' !== $value);
    }

    /**
     * @param null|array<null|bool|int|string> $input
     *
     * @return null|array<mixed>
     */
    public function onUpdateClient(?array $input): ?array
    {
        if (null === $input) {
            return null;
        }

        $sanitized = [
            'id' => Sanitize::string((string) ($input['id'] ?? '')) ?? '',
            'secret' => Sanitize::string((string) ($input['secret'] ?? '')) ?? '',
            'domain' => Sanitize::domain((string) ($input['domain'] ?? '')) ?? '',
        ];

        return array_filter($sanitized, static fn ($value): bool => '' !== $value);
    }

    /**
     * @param null|array<null|bool|int|string> $input
     *
     * @return null|array<mixed>
     */
    public function onUpdateClientAdvanced(?array $input): ?array
    {
        if (null === $input) {
            return null;
        }

        $sanitized = [
            'custom_domain' => Sanitize::domain((string) ($input['custom_domain'] ?? '')) ?? '',
            'apis' => Sanitize::textarea((string) ($input['apis'] ?? '')) ?? '',
            'organizations' => Sanitize::textarea((string) ($input['organizations'] ?? '')) ?? '',
        ];

        $apis = explode("\n", Sanitize::alphanumeric($sanitized['apis'], "a-z0-9\\-_\n"));
        $orgs = explode("\n", Sanitize::alphanumeric($sanitized['organizations'], "A-Za-z0-9_\n"));

        $filteredApis = [];
        $filteredOrgs = [];
        $apisCount = is_countable($apis) ? count($apis) : 0;

        for ($i = 0; $i < $apisCount; ++$i) {
            $apis[$i] = trim($apis[$i]);

            if (strlen($apis[$i]) >= 3 && strlen($apis[$i]) <= 64 && 1 === preg_match('#^[a-z0-9]#', $apis[$i])) {
                $filteredApis[] = $apis[$i];
            }
        }

        $orgsCount = is_countable($orgs) ? count($orgs) : 0;

        for ($i = 0; $i < $orgsCount; ++$i) {
            $orgs[$i] = trim($orgs[$i]);

            if (strlen($orgs[$i]) >= 4 && strlen($orgs[$i]) <= 64 && str_starts_with($orgs[$i], 'org_')) {
                $filteredOrgs[] = $orgs[$i];
            }
        }

        $sanitized['apis'] = trim(implode(' ', Sanitize::arrayUnique($filteredApis)));
        $sanitized['organizations'] = trim(implode(' ', Sanitize::arrayUnique($filteredOrgs)));

        return array_filter($sanitized, static fn ($value): bool => '' !== $value);
    }

    /**
     * @param null|array<null|bool|int|string> $input
     *
     * @return null|array<mixed>
     */
    public function onUpdateCookies(?array $input): ?array
    {
        if (null === $input) {
            return null;
        }

        $sanitized = [
            'secret' => Sanitize::string((string) ($input['secret'] ?? '')) ?? '',
            'domain' => Sanitize::domain((string) ($input['domain'] ?? '')) ?? '',
            'path' => Sanitize::cookiePath((string) ($input['path'] ?? '')),
            'secure' => Sanitize::boolean((string) ($input['secure'] ?? '')) ?? '',
            'samesite' => Sanitize::domain((string) ($input['samesite'] ?? '')) ?? '',
            'ttl' => Sanitize::integer((string) ($input['ttl'] ?? 0), 2_592_000, 0) ?? 0,
        ];

        if ('' !== $sanitized['domain']) {
            $allowed = explode('.', (string) Sanitize::domain(site_url()));
            $assigned = explode('.', $sanitized['domain']);
            $matched = null;

            if (count($allowed) >= 2 && count($assigned) >= 2) {
                $allowed = implode('.', array_slice($allowed, -2));
                $assigned = implode('.', array_slice($assigned, -2));

                if ($assigned === $allowed) {
                    $matched = true;
                }
            }

            if (true !== $matched) {
                $sanitized['domain'] = '';
            }
        }

        return array_filter($sanitized, static fn ($value): bool => '' !== $value);
    }

    /**
     * @param null|array<null|bool|int|string> $input
     *
     * @return null|array<mixed>
     */
    public function onUpdateSessions(?array $input): ?array
    {
        if (null === $input) {
            return null;
        }

        $sanitized = [
            'method' => Sanitize::string((string) ($input['method'] ?? '')) ?? '',
            'session_ttl' => Sanitize::integer((string) ($input['session_ttl'] ?? 0), 2_592_000, 0) ?? 0,
            'rolling_sessions' => Sanitize::boolean((string) ($input['rolling_sessions'] ?? '')) ?? '',
            'refresh_tokens' => Sanitize::boolean((string) ($input['refresh_tokens'] ?? '')) ?? '',
        ];

        return array_filter($sanitized, static fn ($value): bool => '' !== $value);
    }

    /**
     * @param null|array<null|bool|int|string> $input
     *
     * @return null|array<mixed>
     */
    public function onUpdateState(?array $input): ?array
    {
        if (null === $input) {
            return null;
        }

        $sanitized = [
            'enable' => Sanitize::boolean((string) ($input['enable'] ?? '')) ?? '',
        ];

        return array_filter($sanitized, static fn ($value): bool => '' !== $value);
    }

    /**
     * @param null|array<null|bool|int|string> $input
     *
     * @return null|array<mixed>
     */
    public function onUpdateSync(?array $input): ?array
    {
        if (null === $input) {
            return null;
        }

        $sanitized = [
            'database' => Sanitize::string((string) ($input['database'] ?? '')) ?? '',
            'schedule' => Sanitize::integer((string) ($input['schedule'] ?? 0), 2_592_000, 0) ?? 0,
            'push' => Sanitize::string((string) ($input['push'] ?? '')) ?? '',
        ];

        $filteredDatabase = '';

        if ('' !== $sanitized['database']) {
            $database = Sanitize::alphanumeric($sanitized['database'], 'A-Za-z0-9\\-_');

            if (strlen($database) >= 3 && strlen($database) <= 64 && str_starts_with($database, 'con_')) {
                $filteredDatabase = $database;
            }
        }

        // Check if connection is valid

        $response = $this->getSdk()->management()->connections()->get($filteredDatabase);

        if (! HttpResponse::wasSuccessful($response)) {
            $filteredDatabase = '';
        }

        // Setup background sync task: -----

        $nextCronRun = wp_next_scheduled(Sync::CONST_JOB_BACKGROUND_SYNC);

        if (false !== $nextCronRun) {
            wp_unschedule_event($nextCronRun, Sync::CONST_JOB_BACKGROUND_SYNC);
        }

        if ('' !== $filteredDatabase && 0 !== $input['schedule']) {
            $next = wp_schedule_event(time(), Sync::CONST_SCHEDULE_BACKGROUND_SYNC, Sync::CONST_JOB_BACKGROUND_SYNC);
        }

        $sanitized['database'] = $filteredDatabase;

        // Setup background maintenance task: -----

        $nextCronRun = wp_next_scheduled(Sync::CONST_JOB_BACKGROUND_MAINTENANCE);

        if (false !== $nextCronRun) {
            wp_unschedule_event($nextCronRun, Sync::CONST_JOB_BACKGROUND_MAINTENANCE);
        }

        wp_schedule_event(time(), Sync::CONST_SCHEDULE_BACKGROUND_MAINTENANCE, Sync::CONST_JOB_BACKGROUND_MAINTENANCE);

        return array_filter($sanitized, static fn ($value): bool => '' !== $value);
    }

    /**
     * @param null|array<null|bool|int|string> $input
     *
     * @return null|array<mixed>
     */
    public function onUpdateSyncEvents(?array $input): ?array
    {
        if (null === $input) {
            return null;
        }

        $sanitized = [
            'user_creation' => Sanitize::boolean((string) ($input['user_creation'] ?? '')) ?? '',
            'user_deletion' => Sanitize::boolean((string) ($input['user_deletion'] ?? '')) ?? '',
            'user_updates' => Sanitize::boolean((string) ($input['user_updates'] ?? '')) ?? '',
        ];

        return array_filter($sanitized, static fn ($value): bool => '' !== $value);
    }

    /**
     * @param null|array<null|bool|int|string> $input
     *
     * @return null|array<mixed>
     */
    public function onUpdateTokens(?array $input): ?array
    {
        if (null === $input) {
            return null;
        }

        $sanitized = [
            'caching' => Sanitize::string((string) ($input['caching'] ?? '')) ?? '',
        ];

        return array_filter($sanitized, static fn ($value): bool => '' !== $value);
    }

    /**
     * @param null|array<null|bool|int|string> $input
     *
     * @return null|array<mixed>
     */
    public function onUpdateBackchannelLogout(?array $input): ?array
    {
        if (null === $input) {
            return null;
        }

        $sanitized = [
            'enabled' => Sanitize::string((string) ($input['secret'] ?? '')) ?? '',
            'secret' => Sanitize::string((string) ($input['secret'] ?? '')) ?? '',
            'ttl' => Sanitize::integer((string) ($input['ttl'] ?? 0), 2_592_000, 0) ?? 0,
        ];

        if ($sanitized['secret'] === '') {
            $sanitized['secret'] = bin2hex(random_bytes(64));
        }

        set_site_transient('auth0_updated_backchannel', true, 60);

        return array_filter($sanitized, static fn ($value): bool => '' !== $value);
    }

    public function renderToolsConfiguration(): void
    {
        Render::pageBegin(self::PAGES[self::CONST_PAGE_TOOLS]['title']);

        // settings_fields(self::CONST_PAGE_ADVANCED);
        // do_settings_sections(self::CONST_PAGE_ADVANCED);
        // submit_button();

        Render::pageEnd();
    }

    public function renderAdvancedConfiguration(): void
    {
        Render::pageBegin(self::PAGES[self::CONST_PAGE_ADVANCED]['title']);

        settings_fields(self::CONST_PAGE_ADVANCED);
        do_settings_sections(self::CONST_PAGE_ADVANCED);
        submit_button();

        Render::pageEnd();
    }

    public function renderConfiguration(): void
    {
        Render::pageBegin(self::PAGES[self::CONST_PAGE_GENERAL]['title']);

        settings_fields(self::CONST_PAGE_GENERAL);
        do_settings_sections(self::CONST_PAGE_GENERAL);
        submit_button();

        Render::pageEnd();
    }

    public function renderSyncConfiguration(): void
    {
        Render::pageBegin(self::PAGES[self::CONST_PAGE_SYNC]['title']);

        settings_fields(self::CONST_PAGE_SYNC);
        do_settings_sections(self::CONST_PAGE_SYNC);
        submit_button();

        Render::pageEnd();
    }

    private function getOptionDescription(string $context): string
    {
        if ('cookie_domain' === $context) {
            return sprintf('Must include origin domain of <code>`%s`</code>', Sanitize::domain(site_url()) ?? '');
        }

        if ('fallback_secret' === $context) {
            if ($this->isPluginReady()) {
                $fallbackAllowed = $this->getPlugin()->getOption('authentication', 'allow_fallback', 0);

                if (1 === $fallbackAllowed) {
                    $updated = get_site_transient('auth0_updated_fallback');

                    if (! $updated) {
                        return 'Save your changes to view your fallback URI. Erase the secret to generate a new one.';
                    } else {
                        delete_site_transient('auth0_updated_fallback');
                    }

                    $fallbackSecret = $this->getPlugin()->getOption('authentication', 'fallback_secret');

                    if (null !== $fallbackSecret) {
                        return sprintf('Your fallback URI is <code>`%s?auth0_fb=%s`</code>', wp_login_url(), $fallbackSecret);
                    }
                }
            }
        }

        if ('backchannel_logout_secret' === $context) {
            if ($this->isPluginReady()) {
                $backchannelLogoutEnabled = $this->getPlugin()->getOption('backchannel_logout', 'enabled', 0);

                if (0 !== $backchannelLogoutEnabled) {
                    $updated = get_site_transient('auth0_updated_backchannel');

                    if (! $updated) {
                        return 'Save your changes to view your Back-Channel Logout URI. Erase the secret to generate a new one.';
                    } else {
                        delete_site_transient('auth0_updated_backchannel');
                    }

                    $backchannelLogoutSecret = $this->getPlugin()->getOption('backchannel_logout', 'secret');

                    if (null !== $backchannelLogoutSecret) {
                        return sprintf('Your Back-Channel Logout URI is <code>`%s?auth0_bcl=%s`</code>', wp_login_url(), $backchannelLogoutSecret);
                    }
                }
            }
        }

        if ('enable' === $context) {
            if ($this->isPluginReady()) {
                return 'Manage WordPress authentication with Auth0.';
            }

            return 'Plugin requires configuration.';
        }

        if ('sync_enable' === $context) {
            if ($this->isPluginReady()) {
                return 'If enabled, configuration of <a href="https://developer.wordpress.org/plugins/cron/hooking-wp-cron-into-the-system-task-scheduler/" target="_blank">WP-Cron</a> is recommended for best performance.';
            }

            return 'Plugin requires configuration.';
        }

        return '';
    }

    private function getOptionPlaceholder(string $context): string
    {
        if ('cookie_domain' === $context) {
            return Sanitize::domain(site_url()) ?? '';
        }

        return '';
    }

    /**
     * Returns an array of role tags (as strings) identifying all available role options.
     *
     * @return mixed[]
     */
    private function getRoleOptions(): array
    {
        $roles = get_editable_roles();
        $response = [];

        foreach ($roles as $roleId => $role) {
            $response[$roleId] = (string) $role['name'];
        }

        return array_reverse($response, true);

        /** @var string[] $response */
    }
}
