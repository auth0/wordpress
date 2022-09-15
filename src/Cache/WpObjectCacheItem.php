<?php

declare(strict_types=1);

namespace Auth0\WordPress\Cache;

use Psr\Cache\CacheItemInterface;

final class WpObjectCacheItem implements CacheItemInterface
{
    private mixed $value;

    private string $key;

    private bool $is_hit;

    private ?int $expiration_timestamp = null;

    public function __construct(string $key, $value, bool $is_hit)
    {
        $this->key = $key;
        $this->is_hit = $is_hit;
        $this->value = $value;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function get()
    {
        return $this->value;
    }

    public function isHit(): bool
    {
        return $this->is_hit;
    }

    public function set(mixed $value): static
    {
        $this->value = $value;
        $this->is_hit = true;
        return $this;
    }

    public function expiresAt(?\DateTimeInterface $expiration): static
    {
        if ($expiration instanceof \DateTimeInterface) {
            $this->expiration_timestamp = $expiration->getTimestamp();
        } elseif (null === $expiration) {
            $this->expiration_timestamp = $expiration;
        }

        return $this;
    }

    public function expiresAfter(int|\DateInterval|null $time): static
    {
        if (null === $time) {
            $this->expiration_timestamp = null;
        } elseif ($time instanceof \DateInterval) {
            $date = new \DateTime();
            $date->add($time);
            $this->expiration_timestamp = $date->getTimestamp();
        } elseif (is_int($time)) {
            $this->expiration_timestamp = time() + $time;
        }

        return $this;
    }

    public function expirationTimestamp(): ?int
    {
        return $this->expiration_timestamp;
    }

    public static function miss(string $key): self
    {
        return new self($key, null, false);
    }
}
