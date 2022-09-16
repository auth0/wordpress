<?php

declare(strict_types=1);

namespace Auth0\WordPress\Cache;

use DateInterval;
use DateTime;
use DateTimeInterface;
use Psr\Cache\CacheItemInterface;

final class WpObjectCacheItem implements CacheItemInterface
{
    private ?int $expiration_timestamp = null;

    public function __construct(
        private string $key,
        private mixed $value,
        private bool $is_hit
    ) {
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function get(): mixed
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


    public function expiresAt(?DateTimeInterface $dateTime): static
    {
        if ($dateTime instanceof DateTimeInterface) {
            $this->expiration_timestamp = $dateTime->getTimestamp();
        } elseif ($dateTime === null) {
            $this->expiration_timestamp = $dateTime;
        }

        return $this;
    }

    public function expiresAfter(int|DateInterval|null $time): static
    {
        if ($time === null) {
            $this->expiration_timestamp = null;
        } elseif ($time instanceof DateInterval) {
            $dateTime = new DateTime();
            $dateTime->add($time);
            $this->expiration_timestamp = $dateTime->getTimestamp();
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
