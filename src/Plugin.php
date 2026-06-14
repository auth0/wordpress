<?php

declare(strict_types=1);

namespace Auth0\WordPress;

use Auth0\SDK\API\Management\Wrapper\{ManagementClient, ManagementClientOptions};
use Auth0\SDK\Auth0;
use Auth0\SDK\Configuration\SdkConfiguration;
use Auth0\WordPress\Actions\{Authentication as AuthenticationActions, Base as Actions, Configuration as ConfigurationActions, Sync as SyncActions, Tools as ToolsActions, Updates as UpdatesActions};
use Auth0\WordPress\Cache\WpObjectCachePool;
use Auth0\WordPress\Filters\{Authentication as AuthenticationFilters, Base as Filters};
use Auth0\WordPress\Http\Factory;
use Throwable;

use function array_key_exists;
use function defined;
use function is_array;
use function is_int;
use function is_string;

final class Plugin
{
    /**
     * @var array<class-string<Actions>>
     */
    private const ACTIONS = [AuthenticationActions::class, ConfigurationActions::class, SyncActions::class, ToolsActions::class, UpdatesActions::class];

    /**
     * @var array<class-string<Filters>>
     */
    private const FILTERS = [AuthenticationFilters::class];

    /**
     * @var mixed[]
     */
    private array $registry = [];

    private ?ManagementClient $management = null;

    public function __construct(
        private ?Auth0 $auth0,
        private ?SdkConfiguration $sdkConfiguration,
    ) {
    }

    /**
     * Returns a singleton instance of Hooks configured for working with actions.
     */
    public function actions(): Hooks
    {
        static $instance = null;

        if (null === $instance) {
            $instance = new Hooks(Hooks::CONST_ACTION_HOOK);
        }

        return $instance;
    }

    /**
     * Returns a singleton instance of Database.
     */
    public function database(): Database
    {
        static $instance = null;

        if (null === $instance) {
            $instance = new Database();
        }

        return $instance;
    }

    /**
     * Returns a singleton instance of Hooks configured for working with filters.
     */
    public function filters(): Hooks
    {
        static $instance = null;

        if (null === $instance) {
            $instance = new Hooks(Hooks::CONST_ACTION_FILTER);
        }

        return $instance;
    }

    public function getClassInstance(string $class): mixed
    {
        if (! array_key_exists($class, $this->registry)) {
            $this->registry[$class] = new $class($this);
        }

        return $this->registry[$class];
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
     * @psalm-param 0|null $default
     *
     * @param string $group
     * @param string $key
     * @param ?int   $default
     * @param string $prefix
     */
    public function getOption(string $group, string $key, ?int $default = null, string $prefix = 'auth0_'): mixed
    {
        $options = get_option($prefix . $group, []);

        if (is_array($options) && isset($options[$key])) {
            return $options[$key];
        }

        return $default;
    }

    public function getOptionBoolean(string $group, string $key, string $prefix = 'auth0_'): ?bool
    {
        $result = $this->getOption($group, $key, null, $prefix);

        if (is_string($result)) {
            return 'true' === $result || '1' === $result;
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

    public function getOptionString(string $group, string $key, string $prefix = 'auth0_'): ?string
    {
        $result = $this->getOption($group, $key, null, $prefix);

        if (is_string($result)) {
            return $result;
        }

        return null;
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
     * Returns a v9 Management API client built from the plugin's existing
     * configuration. Use this instead of the v8-style `getSdk()->management()`,
     * which is non-functional in auth0-php v9.
     *
     * The wrapper fetches and caches a client-credentials token internally. The
     * plugin's own PSR-18 HTTP client is injected so the scoped build is used
     * rather than runtime discovery, and the token is cached in the same
     * WP_Object_Cache pool the SDK config uses (unless caching is disabled).
     */
    public function getManagement(): ManagementClient
    {
        if (! $this->management instanceof ManagementClient) {
            $configuration = $this->getConfiguration();

            // The 'auth0' cache group is not registered as global, so in a
            // multisite network WordPress prefixes its keys with the current
            // blog id automatically. That keeps each site's Management token
            // isolated without any per-site keying here.
            $tokenCache = 'disable' !== $this->getOption('tokens', 'caching')
                ? new WpObjectCachePool()
                : null;

            $this->management = new ManagementClient(new ManagementClientOptions(
                domain: (string) $configuration->getDomain(),
                clientId: $configuration->getClientId(),
                clientSecret: $configuration->getClientSecret(),
                httpClient: Factory::getClient(),
                tokenCache: $tokenCache,
            ));
        }

        return $this->management;
    }

    /**
     * Returns true if the plugin has been enabled.
     */
    public function isEnabled(): bool
    {
        return 'true' === $this->getOptionString('state', 'enable');
    }

    /**
     * Returns true if the plugin has a minimum viable configuration.
     */
    public function isReady(): bool
    {
        try {
            $config = $this->getConfiguration();
        } catch (Throwable) {
            return false;
        }

        if (! $config->hasClientId()) {
            return false;
        }

        if ('' === (string) $config->getClientId()) {
            return false;
        }

        if (! $config->hasClientSecret()) {
            return false;
        }

        if ('' === (string) $config->getClientSecret()) {
            return false;
        }

        if (! $config->hasDomain()) {
            return false;
        }

        if ('' === $config->getDomain()) {
            return false;
        }

        if (! $config->hasCookieSecret()) {
            return false;
        }

        return '' !== (string) $config->getCookieSecret();
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
            $callback();
        }

        foreach (self::ACTIONS as $action) {
            $callback = [$this->getClassInstance($action), 'register'];

            /**
             * @var callable $callback
             */
            $callback();
        }

        return $this;
    }

    /**
     * Assign a Auth0\SDK\Configuration\SdkConfiguration instance for the plugin to use.
     *
     * @param SdkConfiguration $sdkConfiguration
     */
    public function setConfiguration(SdkConfiguration $sdkConfiguration): self
    {
        $this->sdkConfiguration = $sdkConfiguration;

        return $this;
    }

    /**
     * Assign a Auth0\SDK\Auth0 instance for the plugin to use.
     *
     * @param Auth0 $auth0
     */
    public function setSdk(Auth0 $auth0): self
    {
        $this->auth0 = $auth0;

        return $this;
    }

    /**
     * Import configuration settings from database.
     */
    private function importConfiguration(): SdkConfiguration
    {
        $audiences = $this->getOptionString('client_advanced', 'apis') ?? '';
        $organizations = $this->getOptionString('client_advanced', 'organizations') ?? '';
        $caching = $this->getOption('tokens', 'caching');

        $audiences = array_filter(array_values(array_unique(explode("\n", trim($audiences)))));
        $organizations = array_filter(array_values(array_unique(explode("\n", trim($organizations)))));
        $secure = $this->getOptionBoolean('cookies', 'secure') ?? is_ssl();
        $expires = $this->getOptionInteger('cookies', 'ttl') ?? 0;

        if (defined('DOING_CRON')) {
            // When invoked from a WP_Cron task, just use a minimum configuration for those needs (namely, no sessions invoked.)
            $sdkConfiguration = new SdkConfiguration(
                strategy: SdkConfiguration::STRATEGY_NONE,
                httpRequestFactory: Factory::getRequestFactory(),
                httpResponseFactory: Factory::getResponseFactory(),
                httpStreamFactory: Factory::getStreamFactory(),
                httpClient: Factory::getClient(),
                domain: $this->getOptionString('client', 'domain'),
                clientId: $this->getOptionString('client', 'id'),
                clientSecret: $this->getOptionString('client', 'secret'),
                audience: [] !== $audiences ? $audiences : null,
                organization: [] !== $organizations ? $organizations : null,
            );
        } else {
            $sdkConfiguration = new SdkConfiguration(
                strategy: SdkConfiguration::STRATEGY_REGULAR,
                httpRequestFactory: Factory::getRequestFactory(),
                httpResponseFactory: Factory::getResponseFactory(),
                httpStreamFactory: Factory::getStreamFactory(),
                httpClient: Factory::getClient(),
                domain: $this->getOptionString('client', 'domain'),
                clientId: $this->getOptionString('client', 'id'),
                clientSecret: $this->getOptionString('client', 'secret'),
                customDomain: $this->getOptionString('client_advanced', 'custom_domain'),
                audience: [] !== $audiences ? $audiences : null,
                organization: [] !== $organizations ? $organizations : null,
                cookieSecret: $this->getOptionString('cookies', 'secret'),
                cookieDomain: $this->getOptionString('cookies', 'domain'),
                cookiePath: $this->getOptionString('cookies', 'path') ?? '/',
                cookieExpires: $expires,
                cookieSecure: (bool) $secure,
                cookieSameSite: $this->getOptionString('cookies', 'samesite'),
                redirectUri: get_site_url(null, 'wp-login.php'),
            );
        }

        if ('disable' !== $caching) {
            $wpObjectCachePool = new WpObjectCachePool();
            $sdkConfiguration->setTokenCache($wpObjectCachePool);
            $sdkConfiguration->setBackchannelLogoutCache($wpObjectCachePool);
        }

        return $sdkConfiguration;
    }
}
