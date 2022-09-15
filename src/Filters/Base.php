<?php

declare(strict_types=1);

namespace Auth0\WordPress\Filters;

use Auth0\SDK\Auth0;
use Auth0\WordPress\Plugin;

abstract class Base
{
    protected array $registry = [];

    public function __construct(private Plugin $plugin)
    {
    }

    public function getPlugin(): Plugin
    {
        return $this->plugin;
    }

    public function getSdk(): Auth0
    {
        return $this->getPlugin()->getSdk();
    }

    public function register(): self
    {
        if (isset($this->registry) && is_array($this->registry) && count($this->registry) !== 0) {
            foreach ($this->registry as $event => $method) {
                if (is_string($method)) {
                    $this->getPlugin()->filters()->add($event, $this, $method, $this->getPriority($event));
                    continue;
                }

                if (is_array($method) && count($method) !== 0) {
                    if (isset($method['method'])) {
                        $arguments = $method['arguments'] ?? 1;
                        $this->getPlugin()->filters()->add($event, $this, $method['method'], $this->getPriority($event), $arguments);
                        continue;
                    }

                    continue;
                }
            }
        }

        return $this;
    }

    public function getPriority(
        string $event,
        int $default = 10
    ): int {
        $noramlized = 'AUTH0_FILTER_PRIORITY_' . strtoupper($event);

        if (defined($noramlized)) {
            $constant = constant($noramlized);

            if (is_numeric($constant)) {
                return (int) $constant;
            }
        }

        return $default;
    }
}
