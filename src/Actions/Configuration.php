<?php

declare(strict_types=1);

namespace Auth0\WordPress\Actions;

final class Configuration extends Base
{
    public const CONST_SECTION_PREFIX = 'auth0';
    public const CONST_PAGE_GENERAL = 'auth0_configuration';
    public const CONST_PAGE_SYNC = 'auth0_sync';
    public const CONST_PAGE_ADVANCED = 'auth0_advanced';

    protected array $registry = [
        'admin_init' => 'onSetup',
        'admin_menu' => 'onMenu',
        'auth0_ui_configuration' => 'renderConfiguration',
        'auth0_ui_sync' => 'renderSyncConfiguration',
        'auth0_ui_advanced' => 'renderAdvancedConfiguration',
    ];

    protected array $pages = [
        self::CONST_PAGE_GENERAL => [
            'title' => 'Auth0 — Options',
            'callback' => 'onUpdate',
            'sections' => [
                'state' => [
                    'title' => '',
                    'description' => '',
                    'options' => [
                        'enable' => [
                            'title' => 'Manage Authentication',
                            'type' => 'boolean',
                            'enabled' => 'isPluginReady',
                            'description' => ['getOptionDescription', 'enable'],
                            'select' => [
                                'false' => 'Disabled',
                                'true' => 'Enabled',
                            ]
                        ]
                    ]
                ],
                'accounts' => [
                    'title' => 'WordPress Account Management',
                    'description' => '',
                    'options' => [
                        'matching' => [
                            'title' => 'Connection Matching',
                            'type' => 'text',
                            'enabled' => 'isPluginReady',
                            'description' => '<b>Flexible</b> allows users to sign in using more than one connection type.<br /><b>Strict</b> is more secure, but may lead to confusion for users who forget their sign in method.',
                            'select' => [
                                'flexible' => 'Flexible: Match Verified Email Addresses to Accounts',
                                'strict' => 'Strict: Match Unique Connections to Accounts',
                            ]
                        ],
                        'missing' => [
                            'title' => 'Absentee Accounts',
                            'type' => 'text',
                            'enabled' => 'isPluginReady',
                            'description' => 'What to do after a successful sign in, but there is no matching WordPress account.<br />For Database Connections, the "Disable Sign Ups" setting will be honored prior to this.',
                            'select' => [
                                'reject' => 'Deny access',
                                'create' => 'Create account',
                            ]
                        ],
                        'default_role' => [
                            'title' => 'Default Role',
                            'type' => 'text',
                            'enabled' => 'isPluginReady',
                            'description' => 'The role to assign new WordPress accounts created by the plugin.',
                            'select' => 'getRoleOptions'
                        ],
                        'passwordless' => [
                            'title' => 'Allow Passwordless',
                            'type' => 'boolean',
                            'enabled' => 'isPluginReady',
                            'description' => 'You must <a href="https://auth0.com/docs/authenticate/passwordless" target="_blank">enable Passwordless Connections</a> to use this.',
                            'select' => [
                                'true' => 'Enabled',
                                'false' => 'Disabled',
                            ]
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
                            'description' => 'Required. Must be configured as a <a href="https://auth0.com/docs/get-started/applications" target="_blank">Regular Web Application</a>.'
                        ],
                        'secret' => [
                            'title' => 'Client Secret',
                            'type' => 'password',
                            'sanitizer' => 'string',
                            'description' => 'Required.'
                        ],
                        'domain' => [
                            'title' => 'Domain',
                            'type' => 'text',
                            'sanitizer' => 'domain',
                            'description' => 'Required.'
                        ]
                    ]
                ]
            ]
        ],
        self::CONST_PAGE_SYNC => [
            'title' => 'Auth0 — Sync Options',
            'callback' => 'onUpdate',
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
                            'description' => 'The ID of a Database Connection to synchronise WordPresss with. Should begin with <code>con_</code>.'
                        ],
                        'schedule' => [
                            'title' => 'Background Frequency',
                            'type' => 'boolean',
                            'enabled' => 'isPluginReady',
                            'description' => ['getOptionDescription', 'sync_enable'],
                            'select' => [
                                'disabled' => 'Disabled',
                                'hourly' => 'Hourly',
                                'daily' => 'Daily',
                                'weekly' => 'Weekly',
                            ]
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
                            ]
                        ]
                    ]
                ],
            ]
        ],
        self::CONST_PAGE_ADVANCED => [
            'title' => 'Auth0 — Advanced Options',
            'callback' => 'onUpdate',
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
                            ]
                        ],
                        'allow_fallback' => [
                            'title' => 'WordPress Login Fallback',
                            'type' => 'boolean',
                            'enabled' => 'isPluginReady',
                            'description' => 'Allows signing in with the standard WordPress login form using a secret link.',
                            'select' => [
                                'true' => 'Enabled',
                                'false' => 'Disabled',
                            ]
                        ],
                    ]
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
                            'description' => 'Configure to authenticate using a <a href="https://auth0.com/docs/customize/custom-domains" target="_blank">custom domain</a>.'
                        ],
                        'apis' => [
                            'title' => 'API Audiences',
                            'type' => 'textarea',
                            'enabled' => 'isPluginReady',
                            'sanitizer' => 'string',
                            'description' => 'A list of <a href="https://auth0.com/docs/get-started/apis" target="_blank">Auth0 API Audiences</a> to allow, each on its own line. The top entry will be used by default.'
                        ],
                        'organizations' => [
                            'title' => 'Organizations',
                            'type' => 'textarea',
                            'enabled' => 'isPluginReady',
                            'sanitizer' => 'orgs',
                            'description' => 'A list of <a href="https://auth0.com/docs/manage-users/organizations" target="_blank">Organization IDs</a> to allow, each on its own line beginning with <code>org_</code>. The top entry will be used by default.'
                        ],
                    ]
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
                                'disable' => 'Disabled'
                            ]
                        ],
                    ]
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
                                'sessions' => 'PHP Native Sessions (Recommended)'
                            ]
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
                            ]
                        ],
                        'rolling_sessions' => [
                            'title' => 'Use Rolling Sessions',
                            'type' => 'boolean',
                            'enabled' => 'isPluginReady',
                            'description' => 'Session expirations will be updated on each request, extending their lifetime.',
                            'select' => [
                                'true' => 'Enabled',
                                'false' => 'Disabled',
                            ]
                        ],
                        'refresh_tokens' => [
                            'title' => 'Use Refresh Tokens',
                            'type' => 'boolean',
                            'enabled' => 'isPluginReady',
                            'description' => 'Must select "Allow Offline Access" in your Auth0 API Settings.',
                            'select' => [
                                'false' => 'Disabled',
                                'true' => 'Enabled',
                            ]
                        ],
                    ]
                ],
                'cookies' => [
                    'title' => 'Session Cookies',
                    'description' => 'These options customize how sessions are stored on user devices by the storage method above.',
                    'options' => [
                        'secret' => [
                            'title' => 'Secret',
                            'type' => 'password',
                            'description' => 'Required. Changes will log all users out.'
                        ],
                        'domain' => [
                            'title' => 'Domain',
                            'type' => 'text',
                            'enabled' => 'isPluginReady',
                            'description' => ['getOptionDescription', 'cookie_domain'],
                            'placeholder' => ['getOptionPlaceholder', 'cookie_domain']
                        ],
                        'path' => [
                            'title' => 'Path',
                            'type' => 'text',
                            'enabled' => 'isPluginReady',
                            'description' => 'Defaults to <code>/</code>.',
                            'placeholder' => '/'
                        ],
                        'secure' => [
                            'title' => 'Require SSL',
                            'type' => 'boolean',
                            'enabled' => 'isPluginReady',
                            'description' => 'Enable this if your site is <b>exclusively</b> served over HTTPS.',
                            'select' => [
                                'false' => 'Disabled',
                                'true' => 'Enabled',
                            ]
                        ],
                        'samesite' => [
                            'title' => 'Same-Site',
                            'type' => 'text',
                            'enabled' => 'isPluginReady',
                            'select' => [
                                'lax' => 'Lax (Suggested)',
                                'strict' => 'Strict',
                                'none' => 'None',
                            ]
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
                            ]
                        ],
                    ]
                ],
            ]
        ],
    ];

    public function onSetup(): void
    {
        foreach ($this->pages as $pageId => $page) {
            foreach ($page['sections'] as $sectionId => $section) {
                $sectionId = self::CONST_SECTION_PREFIX . '_' . $sectionId;
                $sectionType = $section['type'] ?? 'array';

                register_setting(
                    option_group: $pageId,
                    option_name: $sectionId,
                    args: [
                        'type' => $sectionType,
                        'sanitize_callback' => [$this, $page['callback'] . str_replace(' ', '', ucwords(str_replace(['auth0_', '_'], ' ', $sectionId)))] ?? '',
                        'show_in_rest' => false
                    ]
                );

                add_settings_section(
                    id: $sectionId,
                    title: $section['title'],
                    callback: function () use ($section) {
                        echo $section['description'] ?? '';
                    },
                    page: $pageId
                );

                $optionValues = null;

                if ($sectionType === 'array') {
                    $optionValues = get_option($sectionId, []);
                }

                if ($sectionType === 'boolean') {
                    $optionValues = get_option($sectionId, false);
                }

                foreach ($section['options'] as $optionId => $option) {
                    $elementId = md5($pageId . '_' . $optionId);
                    $optionType = $option['type'];
                    $optionValue = $optionValues[$optionId] ?? null;
                    $optionName = $sectionId . '[' . $optionId . ']';
                    $optionDescription = $option['description'] ?? '';
                    $optionPlaceholder = $option['placeholder'] ?? '';
                    $optionSelections = $option['select'] ?? null;
                    $optionDisabled = $option['disabled'] ?? null;
                    $optionEnabled = $option['enabled'] ?? null;

                    if (is_array($optionDescription)) {
                        $optionDescription = call_user_func_array([$this, $optionDescription[0]], array_slice($optionDescription, 1));
                    }

                    if (is_array($optionPlaceholder)) {
                        $optionPlaceholder = call_user_func_array([$this, $optionPlaceholder[0]], array_slice($optionPlaceholder, 1));
                    }

                    if (is_string($optionDisabled)) {
                        $optionDisabled = (call_user_func([$this, $optionDisabled]) === true);
                    }

                    if (is_string($optionEnabled)) {
                        $optionDisabled = (call_user_func([$this, $optionEnabled]) === false);
                    }

                    if (is_string($optionSelections)) {
                        $optionSelections = call_user_func([$this, $optionSelections]) ?? [];
                    }

                    add_settings_field(
                        id: $elementId,
                        title:  $option['title'],
                        callback: function () use ($elementId, $optionName, $optionType, $optionDescription, $optionPlaceholder, $optionValue, $optionSelections, $optionDisabled) {
                            $this->renderOption(
                                element: $elementId,
                                name: $optionName,
                                type: $optionType,
                                description: $optionDescription,
                                placeholder: $optionPlaceholder,
                                value: $optionValue,
                                select: $optionSelections,
                                disabled: $optionDisabled
                            );
                        },
                        page: $pageId,
                        section: $sectionId,
                        args: [
                            'label_for' => $elementId,
                            'description' => $option['description'] ?? ''
                        ]
                    );
                }
            }
        }
    }

    public function onUpdateState(?array $input): ?array
    {
        if ($input === null) {
            return null;
        }

        $sanitized = [
            'enable' => $this->sanitizeBoolean((string) $input['enable'] ?? '') ?? '',
        ];

        return array_filter($sanitized, fn ($value) => !is_null($value) && $value !== '');
    }

    public function onUpdateAccounts(?array $input): ?array
    {
        if ($input === null) {
            return null;
        }

        $sanitized = [
            'matching' => $this->sanitizeString($input['matching'] ?? '') ?? '',
            'missing' => $this->sanitizeString($input['missing'] ?? '') ?? '',
            'default_role' => $this->sanitizeString($input['default_role'] ?? '') ?? '',
            'passwordless' => $this->sanitizeBoolean((string) $input['passwordless'] ?? '') ?? '',
        ];

        return array_filter($sanitized, fn ($value) => !is_null($value) && $value !== '');
    }

    public function onUpdateClient(?array $input): ?array
    {
        if ($input === null) {
            return null;
        }

        $sanitized = [
            'id' => $this->sanitizeString($input['id'] ?? '') ?? '',
            'secret' => $this->sanitizeString($input['secret'] ?? '') ?? '',
            'domain' => $this->sanitizeDomain($input['domain'] ?? '') ?? ''
        ];

        return array_filter($sanitized, fn ($value) => !is_null($value) && $value !== '');
    }

    public function onUpdateSync(?array $input): ?array
    {
        if ($input === null) {
            return null;
        }

        $sanitized = [
            'database' => $this->sanitizeString($input['database'] ?? '') ?? '',
            'schedule' => $this->sanitizeString($input['schedule'] ?? '') ?? '',
            'push' => $this->sanitizeString($input['push'] ?? '') ?? ''
        ];

        return array_filter($sanitized, fn ($value) => !is_null($value) && $value !== '');
    }

    public function onUpdateAuthentication(?array $input): ?array
    {
        if ($input === null) {
            return null;
        }

        $sanitized = [
            'pair_sessions' => $this->sanitizeInteger((string) ($input['pair_sessions'] ?? 0), 2, 0) ?? 0,
            'allow_fallback' => $this->sanitizeBoolean((string) $input['allow_fallback'] ?? '') ?? '',
        ];

        return array_filter($sanitized, fn ($value) => !is_null($value) && $value !== '');
    }

    public function onUpdateClientAdvanced(?array $input): ?array
    {
        if ($input === null) {
            return null;
        }

        $sanitized = [
            'custom_domain' => $this->sanitizeDomain($input['custom_domain'] ?? '') ?? '',
            'apis' => $this->sanitizeString($input['apis'] ?? '') ?? '',
            'organizations' => $this->sanitizeString($input['organizations'] ?? '') ?? ''
        ];

        return array_filter($sanitized, fn ($value) => !is_null($value) && $value !== '');
    }

    public function onUpdateTokens(?array $input): ?array
    {
        if ($input === null) {
            return null;
        }

        $sanitized = [
            'caching' => $this->sanitizeString($input['caching'] ?? '') ?? ''
        ];

        return array_filter($sanitized, fn ($value) => !is_null($value) && $value !== '');
    }

    public function onUpdateSessions(?array $input): ?array
    {
        if ($input === null) {
            return null;
        }

        $sanitized = [
            'method' => $this->sanitizeString($input['method'] ?? '') ?? '',
            'session_ttl' => $this->sanitizeInteger((string) ($input['session_ttl'] ?? 0), 2592000, 0) ?? 0,
            'rolling_sessions' => $this->sanitizeBoolean((string) $input['rolling_sessions'] ?? '') ?? '',
            'refresh_tokens' => $this->sanitizeBoolean((string) $input['refresh_tokens'] ?? '') ?? '',
        ];

        return array_filter($sanitized, fn ($value) => !is_null($value) && $value !== '');
    }

    public function onUpdateCookies(?array $input): ?array
    {
        if ($input === null) {
            return null;
        }

        $sanitized = [
            'secret' => $this->sanitizeString($input['secret'] ?? '') ?? '',
            'domain' => $this->sanitizeDomain($input['domain'] ?? '') ?? '',
            'path' => $this->sanitizeCookiePath($input['path'] ?? '') ?? '',
            'secure' => $this->sanitizeBoolean((string) $input['secure'] ?? '') ?? '',
            'samesite' => $this->sanitizeDomain($input['samesite'] ?? '') ?? '',
            'ttl' => $this->sanitizeInteger((string) ($input['ttl'] ?? 0), 2592000, 0) ?? 0,
        ];

        if (strlen($sanitized['domain']) >= 1) {
            $allowed = explode('.', $this->sanitizeDomain(site_url()));
            $assigned = explode('.', $sanitized['domain']);
            $matched = null;

            if (count($allowed) >= 2 && count($assigned) >= 2) {
                $allowed = implode('.', array_slice($allowed, -2));
                $assigned = implode('.', array_slice($assigned, -2));

                if ($assigned === $allowed) {
                    $matched = true;
                }
            }

            if ($matched !== true) {
                $sanitized['domain'] = '';
            }
        }

        return array_filter($sanitized, fn ($value) => !is_null($value) && $value !== '');
    }

    public function onMenu(): void
    {
        add_menu_page(
            'Auth0 — Options', // Page title
            'Auth0', // Menu title
            'manage_options', // User capability necessary to see
            'auth0', // Unique menu slug
            function () {
                do_action('auth0_ui_configuration');
            },
            'dashicons-shield-alt', // Dashicon class name for font icon, or a base64-encoded SVG beginning with "data:image/svg+xml;base64,".
            $this->getPriority('MENU_POSITION', 70, 'AUTH0_ADMIN') // Position of the menu item in the admin UI
        );

        add_submenu_page(
            'auth0',
            'Auth0 — Options',
            'Options',
            'manage_options',
            'auth0',
            '',
            $this->getPriority('MENU_POSITION_GENERAL', 0, 'AUTH0_ADMIN')
        );

        add_submenu_page(
            'auth0',
            'Auth0 — Sync Options',
            'Sync',
            'manage_options',
            'auth0_sync',
            function () {
                do_action('auth0_ui_sync');
            },
            $this->getPriority('MENU_POSITION_SYNC', 1, 'AUTH0_ADMIN')
        );

        add_submenu_page(
            'auth0',
            'Auth0 — Advanced Options',
            'Advanced',
            'manage_options',
            'auth0_advanced',
            function () {
                do_action('auth0_ui_advanced');
            },
            $this->getPriority('MENU_POSITION_ADVANCED', 2, 'AUTH0_ADMIN')
        );
    }

    public function isPluginReady(): bool
    {
        return $this->getPlugin()->isReady();
    }

    public function isPluginEnabled(): bool
    {
        return $this->getPlugin()->isEnabled();
    }

    public function renderConfiguration(): void
    {
        $this->renderPageBegin(self::CONST_PAGE_GENERAL);

        settings_fields(self::CONST_PAGE_GENERAL);
        do_settings_sections(self::CONST_PAGE_GENERAL);
        submit_button();

        $this->renderPageEnd();
    }

    public function renderSyncConfiguration(): void
    {
        $this->renderPageBegin(self::CONST_PAGE_SYNC);

        settings_fields(self::CONST_PAGE_SYNC);
        do_settings_sections(self::CONST_PAGE_SYNC);
        submit_button();

        $this->renderPageEnd();
    }

    public function renderAdvancedConfiguration(): void
    {
        $this->renderPageBegin(self::CONST_PAGE_ADVANCED);

        settings_fields(self::CONST_PAGE_ADVANCED);
        do_settings_sections(self::CONST_PAGE_ADVANCED);
        submit_button();

        $this->renderPageEnd();
    }

    private function renderOption(
        string $element,
        string $name,
        string $type = 'text',
        string $description = '',
        string $placeholder = '',
        mixed $value,
        ?array $select = null,
        ?bool $disabled = null
    ): void {
        if (strlen($placeholder) >= 1) {
            $placeholder = ' placeholder="' . $placeholder . '"';
        }

        $treatAsText = ['color', 'date', 'datetime-local', 'email', 'password', 'month', 'number', 'search', 'tel', 'text', 'time', 'url', 'week'];
        $disabledString = '';

        if ($disabled !== null) {
            if ($disabled === true) {
                $disabledString = ' disabled';
            }
        }

        if ($select !== null && count($select) >= 1) {
            if ($disabled) {
                echo '<input type="hidden" name="' . $name . '" value="' . $value . '">';
                echo '<select id="' . $element . '"' . $disabledString . '>';
            } else {
                echo '<select name="' . $name . '" id="' . $element . '"' . $disabledString . '>';
            }

            foreach ($select as $optVal => $optText) {
                $selected = '';

                if ($optVal === $value) {
                    $selected = ' selected';
                }

                echo '<option value="' . $optVal . '"' . $selected . '>' . $optText . '</option>';
            }

            echo '</select>';

            if (strlen($description) >= 1) {
                echo '<p class="description">' . $description . '</p>';
            }

            return;
        }

        if (in_array($type, $treatAsText, true)) {
            echo '<input name="' . $name . '" type="' . $type . '" id="' . $element . '" value="' . (string) $value . '" class="regular-text"' . $placeholder . $disabledString . ' />';

            if (strlen($description) >= 1) {
                echo '<p class="description">' . $description . '</p>';
            }

            return;
        }

        if ($type === 'textarea') {
            echo '<textarea name="' . $name . '" id="' . $element . '" rows="10" cols="50" spellcheck="false" class="large-text code"' . $placeholder . $disabledString . '>' . $value . '</textarea>';

            if (strlen($description) >= 1) {
                echo '<p class="description">' . $description . '</p>';
            }

            return;
        }

        if ($type === 'boolean') {
            echo '<input name="' . $name . '" type="checkbox" id="' . $element . '" value="true" ' . checked((bool) $value, 'true') . $disabledString . '/> ' . $description;

            return;
        }
    }

    private function renderPageBegin(
        string $pageId,
        string $formAction = 'options.php'
    ): void {
        echo '<div class="wrap">';
        echo '<h1>' . $this->pages[$pageId]['title'] . '</h1>';

        if ($formAction) {
            echo '<form method="post" action="' . $formAction . '">';
        }
    }

    private function renderPageEnd(): void
    {
        echo '</form>';
        echo '</div>';
    }

    private function getOptionDescription(...$args): string
    {
        if (count($args) !== 0) {
            if ($args[0] === 'cookie_domain') {
                return sprintf('Must include origin domain of <code>`%s`</code>', $this->sanitizeDomain(site_url()));
            }

            if ($args[0] === 'enable') {
                if ($this->isPluginReady()) {
                    return  'Manage WordPress authentication with Auth0.';
                }

                return  'Plugin requires configuration.';
            }

            if ($args[0] === 'sync_enable') {
                if ($this->isPluginReady()) {
                    return  'If enabled, configuration of <a href="https://developer.wordpress.org/plugins/cron/hooking-wp-cron-into-the-system-task-scheduler/" target="_blank">WP-Cron</a> is recommended for best performance.';
                }

                return  'Plugin requires configuration.';
            }
        }

        return '';
    }

    private function getOptionPlaceholder(...$args): string
    {
        if (count($args) !== 0) {
            if ($args[0] === 'cookie_domain') {
                return $this->sanitizeDomain(site_url());
            }
        }

        return '';
    }

    private function getRoleOptions(): ?array
    {
        $roles = get_editable_roles();
        $response = [];

        foreach ($roles as $roleId => $role) {
            $response[$roleId] = $role['name'];
        }

        return array_reverse($response, true);
    }

    private function sanitizeInteger(
        string $string,
        int $max = 10,
        int $min = 0
    ): ?int {
        $string = trim(sanitize_text_field($string));

        if (strlen($string) === 0) {
            return null;
        }

        if (! is_numeric($string)) {
            return null;
        }

        $int = intval($string);

        if ($int < $min) {
            return 0;
        }

        if ($int > $max) {
            return $max;
        }

        return $int;
    }

    private function sanitizeBoolean(
        string $string
    ): ?string {
        $string = trim(sanitize_text_field($string));

        if (strlen($string) === 0) {
            return null;
        }

        if ($string === 'true' || $string === '1') {
            return 'true';
        }

        return 'false';
    }

    private function sanitizeString(
        string $string
    ): ?string {
        $string = trim(sanitize_text_field($string));

        if (strlen($string) === 0) {
            return null;
        }

        return $string;
    }

    private function sanitizeCookiePath(
        string $path
    ): ?string {
        $path = trim(sanitize_text_field($path));
        $path = trim(str_replace(['../', './'], '', $path));
        $path = trim($path, "/ \t\n\r\0\x0B");

        if (strlen($path) !== 0) {
            $path = '/' . $path;
        }

        return $path;
    }

    private function sanitizeDomain(
        string $path
    ): ?string {
        $path = $this->sanitizeString($path);

        if (is_string($path) && strlen($path) === 0 || $path === null) {
            return null;
        }

        $scheme = parse_url($path, PHP_URL_SCHEME);

        if ($scheme === null) {
            return $this->sanitizeDomain('http://' . $path);
        }

        $host = parse_url($path, PHP_URL_HOST);

        if (! is_string($host) || strlen($host) === 0) {
            return null;
        }

        $parts = explode('.', $host);

        if (count($parts) < 2) {
            return null;
        }

        $tld = end($parts);

        if (! is_string($tld) || strlen($tld) < 2) {
            return null;
        }

        return $host;
    }
}
