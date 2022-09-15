<?php

declare(strict_types=1);

namespace Auth0\WordPress;

use Auth0\SDK\Auth0;
use Auth0\SDK\Configuration\SdkConfiguration;
use Auth0\WordPress\Actions\Authentication as AuthenticationActions;
use Auth0\WordPress\Actions\Configuration as ConfigurationActions;
use Auth0\WordPress\Cache\WpObjectCachePool;
use Auth0\WordPress\Http\Factory;

final class Plugin
{
    private array $actions = [
        AuthenticationActions::class,
        ConfigurationActions::class,
    ];

    private array $filters = [];

    private array $registry = [];

    public function __construct(private ?Auth0 $auth0, private ?SdkConfiguration $sdkConfiguration)
    {
    }

    /**
     * Returns a singleton instance of the Auth0 SDK.
     */
    public function getSdk(): Auth0
    {
        $this->auth0 ??= new Auth0($this->getConfiguration());
        return $this->auth0;
    }

    /**
     * Assign a Auth0\SDK\Auth0 instance for the plugin to use.
     */
    public function setSdk(
        Auth0 $auth0
    ): self {
        $this->auth0 = $auth0;
        return $this;
    }

    /**
     * Returns a singleton instance of SdkConfiguration.
     */
    public function getConfiguration(): SdkConfiguration
    {
        $this->sdkConfiguration ??= $this->importConfiguration();
        return $this->sdkConfiguration;
    }

    /**
     * Assign a Auth0\SDK\Configuration\SdkConfiguration instance for the plugin to use.
     */
    public function setConfiguration(
        SdkConfiguration $sdkConfiguration
    ): self {
        $this->sdkConfiguration = $sdkConfiguration;
        return $this;
    }

    /**
     * Returns a singleton instance of Hooks configured for working with actions.
     */
    public function actions(): Hooks
    {
        static $instance = null;

        $instance ??= $instance ?? new Hooks(Hooks::CONST_ACTION_HOOK, $this);

        return $instance;
    }

    /**
     * Returns a singleton instance of Hooks configured for working with filters.
     */
    public function filters(): Hooks
    {
        static $instance = null;

        $instance ??= $instance ?? new Hooks(Hooks::CONST_ACTION_FILTER, $this);

        return $instance;
    }

    /**
     * Main plugin functionality.
     */
    public function run(): self
    {
        if (is_array($this->filters) && count($this->filters) !== 0) {
            foreach ($this->filters as $filter) {
                call_user_func([ $this->getClassInstance($filter), 'register']);
            }
        }

        if (is_array($this->actions) && count($this->actions) !== 0) {
            foreach ($this->actions as $action) {
                call_user_func([ $this->getClassInstance($action), 'register']);
            }
        }

        return $this;
    }

    /**
     * Returns true if the plugin has a minimum viable configuration.
     */
    public function isReady(): bool
    {
        $config = $this->getConfiguration();

        if (! $config->hasClientId() || ! strlen($config->getClientId())) {
            return false;
        }

        if (! $config->hasClientSecret() || ! strlen($config->getClientSecret())) {
            return false;
        }

        if (! $config->hasDomain() || ! strlen($config->getDomain())) {
            return false;
        }

        if (! $config->hasCookieSecret() || ! strlen($config->getCookieSecret())) {
            return false;
        }

        return true;
    }

    /**
     * Returns true if the plugin has been enabled.
     */
    public function isEnabled(): bool
    {
        $clientOptions = get_option('auth0_state', []);
        $enabled = $clientOptions['enable'] ?? 'false';
        return ($enabled === 'true');
    }

    public function getOption(
        string $group,
        string $key,
        mixed $default = null,
        string $prefix = 'auth0_'
    ): mixed {
        $options = get_option($prefix . $group, []);

        if (isset($options[$key])) {
            return $options[$key];
        }

        return $default;
    }

    /**
     * Import configuration settings from database.
     */
    private function importConfiguration(): SdkConfiguration
    {
        $options = [
            'client' => get_option('auth0_client', []),
            'advanced' => get_option('auth0_advanced', []),
            'tokens' => get_option('auth0_tokens', []),
            'sessions' => get_option('auth0_sessions', []),
            'cookies' => get_option('auth0_cookies', []),
        ];

        $audiences = null;
        $organizations = null;
        $caching = isset($options['tokens']['caching']) ?? null;

        if (isset($options['advanced']['apis']) && is_string($options['advanced']['apis'])) {
            $audiences = array_values(array_unique(explode("\n", trim(($options['advanced']['apis'])))));

            if (count($audiences) === 0) {
                $audiences = null;
            }
        }

        if (isset($options['advanced']['organizations']) && is_string($options['advanced']['organizations'])) {
            $organizations = array_values(array_unique(explode("\n", trim(($options['advanced']['organizations'])))));

            if (count($organizations) === 0) {
                $organizations = null;
            }
        }

        $configuration = new SdkConfiguration(
            strategy: SdkConfiguration::STRATEGY_NONE,
            httpRequestFactory: Factory::getRequestFactory(),
            httpResponseFactory: Factory::getResponseFactory(),
            httpStreamFactory: Factory::getStreamFactory(),
            httpClient: Factory::getClient(),
            domain: $options['client']['domain'] ?? null,
            clientId: $options['client']['id'] ?? null,
            clientSecret: $options['client']['secret'] ?? null,
            customDomain: $options['advanced']['custom_domain'] ?? null,
            audience: $audiences,
            organization: $organizations,
            cookieSecret: $options['cookies']['secret'] ?? null,
            cookieDomain:  $options['cookies']['domain'] ?? null,
            cookiePath: $options['cookies']['path'] ?? '/',
            cookieExpires: $options['cookies']['ttl'] ?? 0,
            cookieSecure: (bool) ($options['cookies']['secure'] ?? is_ssl()),
            cookieSameSite: $options['cookies']['samesite'] ?? null,
            redirectUri: get_site_url(null, 'wp-login.php')
        );

        if ($caching !== 'disable') {
            $cache = new WpObjectCachePool($configuration);
            $configuration->setTokenCache($cache);
        }

        return $configuration;
    }

    private function getClassInstance(
        string $class
    ) {
        if (! array_key_exists($class, $this->registry)) {
            $this->registry[$class] = new $class($this);
        }

        return $this->registry[$class];
    }
}
