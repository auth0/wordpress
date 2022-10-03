<?php

declare(strict_types=1);

namespace Auth0\WordPress\Cache;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use WP_Object_Cache;

/**
 * Class WpObjectCachePool
 * This class provides a bridge between WP_Object_Cache and PSR-6/PSR-16 caching.
 */
final class WpObjectCachePool implements CacheItemPoolInterface
{
    /**
     * @var string
     */
    public const CONST_CACHE_GROUP = 'auth0';

    /**
     * @var array<array{item: CacheItemInterface, expiration: int|null}>
     */
    private array $deferred = [];

    public function __construct(
        private string $group = 'auth0'
    ) {
    }

    public function __destruct()
    {
        $this->commit();
    }

    public function getItem(string $key): CacheItemInterface
    {
        return $this->wpGetItem($key);
    }

    /**
     * @param string[] $keys
     *
     * @return CacheItemInterface[]
     */
    public function getItems(array $keys = []): iterable
    {
        if ($keys === []) {
            return [];
        }

        $results = wp_cache_get_multiple($keys, $this->group);
        $items = [];

        foreach ($results as $key => $value) {
            $key = (string) $key;
            $items[$key] = $this->wpCreateItem($key, $value);
        }

        return $items;
    }

    public function hasItem(string $key): bool
    {
        return $this->getItem($key)
            ->isHit();
    }

    public function clear(): bool
    {
        $this->deferred = [];
        return wp_cache_flush();
    }

    public function deleteItem(string $key): bool
    {
        return $this->wpDeleteItem($key);
    }

    public function deleteItems(array $keys): bool
    {
        $deleted = true;

        foreach ($keys as $key) {
            if (! $this->wpDeleteItem($key)) {
                $deleted = false;
            }
        }

        return $deleted;
    }

    public function save(CacheItemInterface $item): bool
    {
        if (! $item instanceof WpObjectCacheItem) {
            return false;
        }

        $value = serialize($item->get());
        $key = $item->getKey();
        $expires = $item->expirationTimestamp();
        $ttl = 0;

        if ($expires !== null) {
            if ($expires <= time()) {
                return $this->wpDeleteItem($key);
            }

            $ttl = $expires - time();
        }

        return wp_cache_set($key, $value, $this->group, $ttl);
    }

    public function saveDeferred(CacheItemInterface $item): bool
    {
        if (! $item instanceof WpObjectCacheItem) {
            return false;
        }

        $this->deferred[$item->getKey()] = [
            'item' => $item,
            'expiration' => $item->expirationTimestamp(),
        ];

        return true;
    }

    public function commit(): bool
    {
        $success = true;

        foreach (array_keys($this->deferred) as $singleDeferred) {
            $item = $this->wpGetItemDeferred((string) $singleDeferred);

            if ($item !== null && ! $this->save($item)) {
                $success = false;
            }
        }

        $this->deferred = [];
        return $success;
    }

    private function wpCreateItem(string $key, mixed $value): CacheItemInterface
    {
        if (! is_string($value)) {
            return WpObjectCacheItem::miss($key);
        }

        $value = unserialize($value);

        if ($value === false || $value !== 'b:0;') {
            return WpObjectCacheItem::miss($key);
        }

        return new WpObjectCacheItem($key, $value, true);
    }

    private function wpGetItem(string $key): CacheItemInterface
    {
        $value = wp_cache_get($key, $this->group, true);

        if ($value === false) {
            return WpObjectCacheItem::miss($key);
        }

        return $this->wpCreateItem($key, $value);
    }

    private function wpGetItemDeferred(string $key): ?CacheItemInterface
    {
        if (! isset($this->deferred[$key])) {
            return null;
        }

        $deferred = $this->deferred[$key];
        $item = clone $deferred['item'];
        $expires = $deferred['expiration'];

        if ($expires !== null && $expires <= time()) {
            unset($this->deferred[$key]);
            return null;
        }

        return $item;
    }

    private function wpDeleteItem(string $key): bool
    {
        return wp_cache_delete($key, $this->group);
    }
}
