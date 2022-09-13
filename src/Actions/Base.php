<?php

declare(strict_types=1);

namespace Auth0\WordPress\Actions;

use Auth0\SDK\Auth0;
use Auth0\WordPress\Plugin;

abstract class Base
{
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

    public function register(): self {
        if (isset($this->registry) && is_array($this->registry) && count($this->registry) !== 0) {
            foreach ($this->registry as $event => $methods) {
                if (is_string($methods)) {
                    $this->getPlugin()->actions()->add($event, $this, $methods, $this->getPriority($event));
                    continue;
                }

                if (is_array($methods) && count($methods) !== 0) {
                    foreach ($methods as $method) {
                        $callback = null;
                        $arguments = 1;

                        if (is_string($method)) {
                            $callback = $method;
                        }

                        if (is_array($method) && count($method) >= 1) {
                            $callback = $method[0];
                            $arguments = (int) $method[1] ?? 1;
                        }

                        if ($callback !== null) {
                            $this->getPlugin()->actions()->add($event, $this, $callback, $this->getPriority($event), $arguments);
                        }
                    }
                    continue;
                }
            }
        }

        return $this;
    }

    public function getPriority(
        string $event,
        int $default = 10,
        string $prefix = 'AUTH0_ACTION_PRIORITY'
    ): int {
        $noramlized = strtoupper($prefix . '_' . $event);

        if (defined($noramlized)) {
            $constant = constant($noramlized);

            if (is_numeric($constant)) {
                return (int) $constant;
            }
        }

        return $default;
    }
}
