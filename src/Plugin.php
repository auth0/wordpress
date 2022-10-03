<?php

declare(strict_types=1);

namespace Auth0\WordPress;

use Auth0\SDK\Auth0;
use Auth0\SDK\Configuration\SdkConfiguration;
use Auth0\WordPress\Actions\Authentication as AuthenticationActions;
use Auth0\WordPress\Actions\Base as Actions;
use Auth0\WordPress\Actions\Configuration as ConfigurationActions;
use Auth0\WordPress\Cache\WpObjectCachePool;
use Auth0\WordPress\Filters\Authentication as AuthenticationFilters;
use Auth0\WordPress\Filters\Base as Filters;
use Auth0\WordPress\Http\Factory;

final class Plugin
{
    /**
     * @var array<class-string<Actions>>
     */
    private const ACTIONS = [AuthenticationActions::class, ConfigurationActions::class];

    /**
     * @var array<class-string<Filters>>
     */
    private const FILTERS = [AuthenticationFilters::class];

    /**
     * @var mixed[]
     */
    private array $registry = [];

    public function __construct(
        private ?Auth0 $auth0,
        private ?SdkConfiguration $sdkConfiguration
    ) {
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
    public function setSdk(Auth0 $auth0): self
    {
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
    public function setConfiguration(SdkConfiguration $sdkConfiguration): self
    {
        $this->sdkConfiguration = $sdkConfiguration;
        return $this;
    }

    /**
     * Returns a singleton instance of Hooks configured for working with actions.
     */
    public function actions(): Hooks
    {
        static $instance = null;

        if ($instance === null) {
            $instance = new Hooks(Hooks::CONST_ACTION_HOOK);
        }

        return $instance;
    }

    /**
     * Returns a singleton instance of Hooks configured for working with filters.
     */
    public function filters(): Hooks
    {
        static $instance = null;

        if ($instance === null) {
            $instance = new Hooks(Hooks::CONST_ACTION_FILTER);
        }

        return $instance;
    }

    /**
     * Main plugin functionality.
     */
    public function run(): self
    {
        foreach (self::FILTERS as $filter) {
            $callback = [$this->getClassInstance($filter), 'register'];

            /**
             * @var callable $callback
             */

            call_user_func($callback);
        }

        foreach (self::ACTIONS as $action) {
            $callback = [$this->getClassInstance($action), 'register'];

            /**
             * @var callable $callback
             */

            call_user_func($callback);
        }

        return $this;
    }

    /**
     * Returns true if the plugin has a minimum viable configuration.
     */
    public function isReady(): bool
    {
        $config = $this->getConfiguration();
        if (! $config->hasClientId()) {
            return false;
        }

        if ((string) $config->getClientId() === '') {
            return false;
        }

        if (! $config->hasClientSecret()) {
            return false;
        }

        if ((string) $config->getClientSecret() === '') {
            return false;
        }

        if (! $config->hasDomain()) {
            return false;
        }

        if ($config->getDomain() === '') {
            return false;
        }

        if (! $config->hasCookieSecret()) {
            return false;
        }

        return (string) $config->getCookieSecret() !== '';
    }

    /**
     * Returns true if the plugin has been enabled.
     */
    public function isEnabled(): bool
    {
        return $this->getOptionString('state', 'enable') === 'true';
    }

    /**
     * @param int|null $default
     *
     * @psalm-param 0|null $default
     */
    public function getOption(string $group, string $key, ?int $default = null, string $prefix = 'auth0_'): mixed
    {
        $options = get_option($prefix . $group, []);

        /**
         * @var array<mixed> $options
         */

        if (isset($options[$key])) {
            return $options[$key];
        }

        return $default;
    }

    public function getOptionString(string $group, string $key, string $prefix = 'auth0_'): ?string
    {
        $result = $this->getOption($group, $key, null, $prefix);

        if (is_string($result)) {
            return $result;
        }

        return null;
    }

    public function getOptionBoolean(string $group, string $key, string $prefix = 'auth0_'): ?bool
    {
        $result = $this->getOption($group, $key, null, $prefix);

        if (is_string($result)) {
            return $result === 'true' || $result === '1';
        }

        return null;
    }

    public function getOptionInteger(string $group, string $key, string $prefix = 'auth0_'): ?int
    {
        $result = $this->getOption($group, $key, null, $prefix);

        if (is_int($result)) {
            return $result;
        }

        if (is_numeric($result)) {
            return (int) $result;
        }

        return null;
    }

    /**
     * Import configuration settings from database.
     */
    private function importConfiguration(): SdkConfiguration
    {
        $audiences = $this->getOptionString('advanced', 'apis') ?? '';
        $organizations = $this->getOptionString('advanced', 'organizations') ?? '';
        $caching = $this->getOption('tokens', 'caching');

        $audiences = array_filter(array_values(array_unique(explode("\n", trim($audiences)))));
        $organizations = array_filter(array_values(array_unique(explode("\n", trim($organizations)))));
        $secure = $this->getOptionBoolean('cookies', 'secure') ?? \is_ssl();
        $expires = $this->getOptionInteger('cookies', 'ttl') ?? 0;

        $sdkConfiguration = new SdkConfiguration(
            strategy: SdkConfiguration::STRATEGY_NONE,
            httpRequestFactory: Factory::getRequestFactory(),
            httpResponseFactory: Factory::getResponseFactory(),
            httpStreamFactory: Factory::getStreamFactory(),
            httpClient: Factory::getClient(),
            domain: $this->getOptionString('client', 'domain'),
            clientId: $this->getOptionString('client', 'id'),
            clientSecret: $this->getOptionString('client', 'secret'),
            customDomain: $this->getOptionString('advanced', 'custom_domain'),
            audience: $audiences !== [] ? $audiences : null,
            organization: $organizations !== [] ? $organizations : null,
            cookieSecret: $this->getOptionString('cookies', 'secret'),
            cookieDomain: $this->getOptionString('cookies', 'domain'),
            cookiePath: $this->getOptionString('cookies', 'path') ?? '/',
            cookieExpires: $expires,
            cookieSecure: $secure ? true : false,
            cookieSameSite: $this->getOptionString('cookies', 'samesite'),
            redirectUri: get_site_url(null, 'wp-login.php')
        );

        if ($caching !== 'disable') {
            $wpObjectCachePool = new WpObjectCachePool();
            $sdkConfiguration->setTokenCache($wpObjectCachePool);
        }

        return $sdkConfiguration;
    }

    private function getClassInstance(string $class): mixed
    {
        if (! array_key_exists($class, $this->registry)) {
            $this->registry[$class] = new $class($this);
        }

        return $this->registry[$class];
    }
}
