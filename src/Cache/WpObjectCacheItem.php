<?php

declare(strict_types=1);

namespace Auth0\WordPress\Cache;

use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use Psr\Cache\CacheItemInterface;

final class WpObjectCacheItem implements CacheItemInterface
{
    private ?int $expires = null;

    public function __construct(
        private string $key,
        private mixed $value,
        private bool $is_hit,
    ) {
    }

    public function expirationTimestamp(): ?int
    {
        return $this->expires;
    }

    /**
     * @param null|DateInterval|int $time
     */
    public function expiresAfter(DateInterval | int | null $time): static
    {
        if (null === $time) {
            $this->expires = null;

            return $this;
        }

        if ($time instanceof DateInterval) {
            $dateTime = new DateTimeImmutable();
            $dateTime->add($time);
            $this->expires = $dateTime->getTimestamp();

            return $this;
        }

        $this->expires = time() + $time;

        return $this;
    }

    public function expiresAt(?DateTimeInterface $expiration): static
    {
        if ($expiration instanceof DateTimeInterface) {
            $this->expires = $expiration->getTimestamp();

            return $this;
        }

        $this->expires = $expiration;

        return $this;
    }

    public function get(): mixed
    {
        return $this->value;
    }

    public function getKey(): string
    {
        return $this->key;
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

    public static function miss(string $key): self
    {
        return new self($key, null, false);
    }
}
