<?php

declare(strict_types=1);

namespace Auth0\WordPress\Actions;

final class Configuration extends Base
{
    const CONST_SECTION_PREFIX = 'auth0';
    const CONST_PAGE_GENERAL = 'auth0_configuration';
    const CONST_PAGE_USERS = 'auth0_users';
    const CONST_PAGE_ADVANCED = 'auth0_advanced';

    protected array $registry = [
        'admin_init' => 'onSetup',
        'admin_menu' => 'onMenu',
        'auth0_ui_configuration' => 'renderConfiguration',
        'auth0_ui_users' => 'renderUsersConfiguration',
        'auth0_ui_advanced' => 'renderAdvancedConfiguration',
    ];

    protected array $pages = [
        self::CONST_PAGE_GENERAL => [
            'title' => 'Auth0 — Configuration',
            'callback' => 'onUpdate',
            'sections' => [
                'state' => [
                    'title' => '',
                    'description' => '',
                    'options' => [
                        'enable' => [
                            'title' => 'Handle Authentication',
                            'type' => 'boolean',
                            'disabled' => 'isPluginReady',
                            'description' => ['getOptionDescription', 'enable'],
                            'select' => [
                                'false' => 'Disabled',
                                'true' => 'Enabled',
                            ]
                        ]
                    ]
                ],
                'client_options' => [
                    'title' => 'Application',
                    'description' => 'The appropriate values for these settings can be found in your <a href="https://manage.auth0.com">Auth0 Dashboard</a>.',
                    'options' => [
                        'client_id' => [
                            'title' => 'Client ID*',
                            'type' => 'text',
                            'sanitizer' => 'string',
                            'description' => 'Application must be configured as a <a href="https://auth0.com/docs/get-started/applications" target="_blank">Regular Web Application</a>.'
                        ],
                        'client_secret' => [
                            'title' => 'Client Secret*',
                            'type' => 'text',
                            'sanitizer' => 'string'
                        ],
                        'client_domain' => [
                            'title' => 'Domain*',
                            'type' => 'text',
                            'sanitizer' => 'domain'
                        ]
                    ]
                ]
            ]
        ],
        self::CONST_PAGE_USERS => [
            'title' => 'Auth0 — User Management',
            'sections' => []
        ],
        self::CONST_PAGE_ADVANCED => [
            'title' => 'Auth0 — Advanced Configuration',
            'callback' => 'onUpdate',
            'sections' => [
                'advanced_client_options' => [
                    'title' => 'Application',
                    'description' => 'The appropriate values for these settings can be found in your <a href="https://manage.auth0.com">Auth0 Dashboard</a>.',
                    'options' => [
                        'custom_domain' => [
                            'title' => 'Custom Domain',
                            'type' => 'text',
                            'sanitizer' => 'domain',
                            'description' => 'Configure to authenticate using a <a href="https://auth0.com/docs/customize/custom-domains" target="_blank">custom domain</a>.'
                        ],
                        'api_identifier' => [
                            'title' => 'API Audience',
                            'type' => 'text',
                            'sanitizer' => 'string',
                            'description' => 'Configure to authenticate with an <a href="https://auth0.com/docs/get-started/apis" target="_blank">Auth0 API</a>.'
                        ],
                        'allow_admin_override' => [
                            'title' => 'Allow Admin Override',
                            'type' => 'boolean',
                            'description' => 'Enables signing into admin accounts with normal WordPress authentication.',
                            'select' => [
                                'true' => 'Enabled',
                                'false' => 'Disabled',
                            ]
                        ],
                    ]
                ],
                'cookie_options' => [
                    'title' => 'Cookies',
                    'description' => 'These settings affect how authentication details are stored on user devices.',
                    'options' => [
                        'cookie_secret' => [
                            'title' => 'Secret*',
                            'type' => 'text',
                            'description' => 'Changing this will require all users to reauthenticate, including yourself.'
                        ],
                        'cookie_domain' => [
                            'title' => 'Domain',
                            'type' => 'text',
                            'description' => ['getOptionDescription', 'cookie_domain'],
                            'placeholder' => ['getOptionPlaceholder', 'cookie_domain']
                        ],
                        'cookie_path' => [
                            'title' => 'Path',
                            'type' => 'text',
                            'description' => 'Defaults to <code>/</code>.',
                            'placeholder' => '/'
                        ],
                        'cookie_secure' => [
                            'title' => 'Require SSL',
                            'type' => 'boolean',
                            'description' => 'Enable this if your site is <b>exclusively</b> served over HTTPS.',
                            'select' => [
                                'false' => 'Disabled',
                                'true' => 'Enabled',
                            ]
                        ],
                        'cookie_samesite' => [
                            'title' => 'Same-Site',
                            'type' => 'text',
                            'select' => [
                                'lax' => 'Lax (Suggested)',
                                'strict' => 'Strict',
                                'none' => 'None',
                            ]
                        ],
                        'cookie_ttl' => [
                            'title' => 'Expires',
                            'type' => 'number',
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
                            ]
                        ],
                    ]
                ]
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
                    callback: function() use ($section) { echo $section['description'] ?? ''; },
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

                    if (is_array($optionDescription)) {
                        $optionDescription = call_user_func_array([$this, $optionDescription[0]], array_slice($optionDescription, 1));
                    }

                    if (is_array($optionPlaceholder)) {
                        $optionPlaceholder = call_user_func_array([$this, $optionPlaceholder[0]], array_slice($optionPlaceholder, 1));
                    }

                    if (is_string($optionDisabled)) {
                        $optionDisabled = (call_user_func([$this, $optionDisabled]) === false);
                    }

                    add_settings_field(
                        id: $elementId,
                        title:  $option['title'],
                        callback: function() use ($elementId, $optionName, $optionType, $optionDescription, $optionPlaceholder, $optionValue, $optionSelections, $optionDisabled) {
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

    public function onUpdateState(array $input): array
    {
        $sanitized = [
            'enable' => $this->sanitizeBoolean((string) $input['enable'] ?? 'false') ?? 'false',
        ];

        return $sanitized;
    }

    public function onUpdateClientOptions(array $input): array
    {
        $sanitized = [
            'client_id' => $this->sanitizeString($input['client_id'] ?? '') ?? '',
            'client_secret' => $this->sanitizeString($input['client_secret'] ?? '') ?? '',
            'client_domain' => $this->sanitizeDomain($input['client_domain'] ?? '') ?? ''
        ];

        return $sanitized;
    }

    public function onUpdateAdvancedClientOptions(array $input): array
    {
        $sanitized = [
            'allow_admin_override' => $this->sanitizeBoolean((string) $input['allow_admin_override'] ?? 'true') ?? 'true',
            'custom_domain' => $this->sanitizeDomain($input['custom_domain'] ?? '') ?? '',
            'api_identifier' => $this->sanitizeString($input['api_identifier'] ?? '') ?? ''
        ];

        return $sanitized;
    }

    public function onUpdateCookieOptions(array $input): array
    {
        $sanitized = [
            'cookie_secret' => $this->sanitizeString($input['cookie_secret'] ?? '') ?? '',
            'cookie_domain' => $this->sanitizeDomain($input['cookie_domain'] ?? '') ?? '',
            'cookie_path' => $this->sanitizeCookiePath($input['cookie_path'] ?? '') ?? '',
            'cookie_ttl' => $this->sanitizeInteger((string) ($input['cookie_ttl'] ?? 0), 1209600, 0) ?? 0,
        ];

        if (strlen($sanitized['cookie_domain']) >= 1) {
            $allowed = explode('.', $this->sanitizeDomain(site_url()));
            $assigned = explode('.', $sanitized['cookie_domain']);
            $matched = null;

            if (count($allowed) >= 2 && count($assigned) >= 2) {
                $allowed = implode('.', array_slice($allowed, -2));
                $assigned = implode('.', array_slice($assigned, -2));

                if ($assigned === $allowed) {
                    $matched = true;
                }
            }

            if ($matched !== true) {
                $sanitized['cookie_domain'] = '';
            }
        }

        return $sanitized;
    }

    public function onMenu(): void
    {
        add_menu_page(
            'Auth0 Configuration', // Page title
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
            'Auth0 Configuration',
            'Configuration',
            'manage_options',
            'auth0',
            '',
            $this->getPriority('MENU_POSITION_GENERAL', 0, 'AUTH0_ADMIN')
        );

        add_submenu_page(
            'auth0',
            'Auth0 Users Configuration',
            'Users',
            'manage_options',
            'auth0_users',
            function () {
                do_action('auth0_ui_users');
            },
            $this->getPriority('MENU_POSITION_USERS', 1, 'AUTH0_ADMIN')
        );

        add_submenu_page(
            'auth0',
            'Auth0 Advanced Configuration',
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

    public function renderUsersConfiguration(): void
    {
        $this->renderPageBegin(self::CONST_PAGE_USERS);

        var_dump("Users configuration.");

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

        if ($type === 'boolean') {
            echo '<input name="' . $name . '" type="checkbox" id="' . $element . '" value="true" ' . checked((bool) $value, 'true') . $disabledString . '/> ' . $description;
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

    private function renderPageEnd(): void {
        echo '</form>';
        echo '</div>';
    }

    private function getOptionDescription(...$args): string {
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
        }

        return '';
    }

    private function getOptionPlaceholder(...$args): string {
        if (count($args) !== 0) {
            if ($args[0] === 'cookie_domain') {
                return $this->sanitizeDomain(site_url());
            }
        }

        return '';
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
