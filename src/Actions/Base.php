<?php

declare(strict_types=1);

namespace Auth0\WordPress\Actions;

use Auth0\SDK\Auth0;
use Auth0\WordPress\{Hooks, Plugin};

use function constant;
use function count;
use function defined;
use function is_array;
use function is_string;

abstract class Base
{
    /**
     * @var array<string, array<int, int|string>|string>
     */
    protected array $registry = [];

    public function __construct(private Plugin $plugin)
    {
    }

    final public function addAction(string $event, $method = null): ?Hooks
    {
        $callback = null;
        $method ??= $this->registry[$event] ?? null;
        $arguments = 1;

        if (null !== $method) {
            if (is_string($method)) {
                $callback = $method;
            }

            if (is_array($method) && count($method) >= 1 && is_string($method[0]) && is_numeric($method[1])) {
                $callback = $method[0];
                $arguments = (int) $method[1];
            }

            if (null !== $callback) {
                return $this->plugin->actions()
                    ->add($event, $this, $callback, $this->getPriority($event), $arguments);
            }
        }

        return null;
    }

    final public function getPlugin(): Plugin
    {
        return $this->plugin;
    }

    final public function getPriority(string $event, int $default = 10, string $prefix = 'AUTH0_ACTION_PRIORITY'): int
    {
        $noramlized = strtoupper($prefix . '_' . $event);

        if (defined($noramlized)) {
            $constant = constant($noramlized);

            if (is_numeric($constant)) {
                return (int) $constant;
            }
        }

        return $default;
    }

    final public function getSdk(): Auth0
    {
        return $this->plugin->getSdk();
    }

    final public function isPluginEnabled(): bool
    {
        return $this->plugin
            ->isEnabled();
    }

    final public function isPluginReady(): bool
    {
        return $this->plugin
            ->isReady();
    }

    final public function register(): self
    {
        foreach ($this->registry as $event => $method) {
            $this->addAction($event, $method);
        }

        return $this;
    }

    final public function removeAction(string $event, $method = null): ?Hooks
    {
        $callback = null;
        $method ??= $this->registry[$event] ?? null;
        $arguments = 1;

        if (null !== $method) {
            if (is_string($method)) {
                $callback = $method;
            }

            if (is_array($method) && count($method) >= 1 && is_string($method[0]) && is_numeric($method[1])) {
                $callback = $method[0];
                $arguments = (int) $method[1];
            }

            if (null !== $callback) {
                return $this->plugin->actions()
                    ->remove($event, $this, $callback, $this->getPriority($event), $arguments);
            }
        }

        return null;
    }
}
