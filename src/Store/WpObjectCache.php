<?php

declare(strict_types=1);

namespace Auth0\WordPress\Store;

use Auth0\SDK\Configuration\SdkConfiguration;
use Auth0\SDK\Contract\StoreInterface;
use Auth0\SDK\Utility\Toolkit;

/**
 * Class WpObjectCache
 * This class provides a layer to persist data using PHP Sessions.
 */
final class WpObjectCache implements StoreInterface
{
    public const CONST_CACHE_GROUP = 'auth0';

    /**
     * Instance of SdkConfiguration, for shared configuration across classes.
     */
    private SdkConfiguration $configuration;

    /**
     * Session base name, configurable on instantiation.
     */
    private string $sessionPrefix;

    /**
     * SessionStore constructor.
     *
     * @param SdkConfiguration $configuration Base configuration options for the SDK. See the SdkConfiguration class constructor for options.
     * @param string           $sessionPrefix A string to prefix session keys with.
     */
    public function __construct(
        SdkConfiguration $configuration,
        string $sessionPrefix = 'auth0'
    ) {
        [$sessionPrefix] = Toolkit::filter([$sessionPrefix])->string()->trim();

        Toolkit::assert([
            [$sessionPrefix, \Auth0\SDK\Exception\ArgumentException::missing('sessionPrefix')],
        ])->isString();

        $this->configuration = $configuration;
        $this->sessionPrefix = $sessionPrefix ?? 'auth0';

        $this->start();
    }

    /**
     * This has no effect when using sessions as the storage medium.
     *
     * @param bool $deferring Whether to defer persisting the storage state.
     *
     * @codeCoverageIgnore
     */
    public function defer(
        bool $deferring
    ): void {
        return;
    }

    /**
     * Persists $value on $_SESSION, identified by $key.
     *
     * @param string $key   Session key to set.
     * @param mixed  $value Value to use.
     */
    public function set(
        string $key,
        $value
    ): void {
        $normalizedKey = $this->getSessionName($key);
        $success = wp_cache_set($normalizedKey, $value, session_id(), 0);

        if ($success) {
            $this->updateSessionIndex($normalizedKey, true);
        }
    }

    /**
     * Gets persisted values identified by $key.
     * If the value is not set, returns $default.
     *
     * @param string $key     Session key to set.
     * @param mixed  $default Default to return if nothing was found.
     *
     * @return mixed
     */
    public function get(
        string $key,
        $default = null
    ) {
        $value = wp_cache_get($this->getSessionName($key), session_id());

        if ($value !== false) {
            return $value;
        }

        return $default;
    }

    /**
     * Removes all persisted values.
     */
    public function purge(): void
    {
        $index = $this->getSessionIndex();

        if (is_array($index) && count($index) !== 0) {
            while ($indexKey = key($index)) {
                wp_cache_delete($indexKey, session_id());
                next($index);
            }
        }

        $this->deleteSessionIndex();
    }

    /**
     * Removes a persisted value identified by $key.
     *
     * @param string $key Session key to delete.
     */
    public function delete(
        string $key
    ): void {
        $normalizedKey = $this->getSessionName($key);
        $success = wp_cache_delete($normalizedKey, session_id());

        if ($success) {
            $this->updateSessionIndex($normalizedKey, false);
        }
    }

    /**
     * Constructs a session key name.
     *
     * @param string $key Session key name to prefix and return.
     */
    public function getSessionName(
        string $key
    ): string {
        [$key] = Toolkit::filter([$key])->string()->trim();

        Toolkit::assert([
            [$key, \Auth0\SDK\Exception\ArgumentException::missing('key')],
        ])->isString();

        return $this->sessionPrefix . '_' . ($key ?? '');
    }

    /**
     * This basic implementation of BaseAuth0 SDK uses PHP Sessions to store volatile data.
     */
    private function start(): void
    {
        $sessionId = session_id();

        if ($sessionId === '' || $sessionId === false) {
            // @codeCoverageIgnoreStart
            if (! defined('AUTH0_TESTS_DIR')) {
                session_set_cookie_params([
                    'lifetime' => $this->configuration->getCookieExpires(),
                    'domain' => $this->configuration->getCookieDomain(),
                    'path' => $this->configuration->getCookiePath(),
                    'secure' => $this->configuration->getCookieSecure(),
                    'httponly' => true,
                    'samesite' => $this->configuration->getResponseMode() === 'form_post' ? 'None' : 'Lax',
                ]);
            }
            // @codeCoverageIgnoreEnd

            session_register_shutdown();

            session_start();
        }
    }

    private function getSessionIndex(
        ?bool &$found = null
    ): ?array {
        $found = false;
        $index = wp_cache_get($this->getSessionName('index'), session_id(), true, $found);

        if ($found === true) {
            return unserialize(json_decode(base64_decode($index), true, 512, JSON_THROW_ON_ERROR));
        }

        return null;
    }

    private function updateSessionIndex(
        string $key,
        bool $present
    ): void {
        $key = $this->getSessionName($key);
        $found = false;
        $data = $this->getSessionIndex($found) ?? [];

        if ($present) {
            $data[$key] = true;
        }

        if (! $present && isset($data[$key])) {
            unset($data[$key]);
        }

        if (count($data) === 0) {
            $this->deleteSessionIndex();
            return;
        }

        wp_cache_set($this->getSessionName('index'), base64_encode(json_encode(serialize($data), JSON_THROW_ON_ERROR)), session_id(), 0);
    }

    private function deleteSessionIndex(): void
    {
        wp_cache_delete($this->getSessionName('index'), session_id());
        return;
    }
}
