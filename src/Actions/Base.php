<?php

declare(strict_types=1);

namespace Auth0\WordPress\Actions;

use Auth0\SDK\Auth0;
use Auth0\WordPress\Hooks;
use Auth0\WordPress\Plugin;

abstract class Base
{
    /**
     * @var array<string, string|array<int, int|string>>
     */
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
        return $this->plugin->getSdk();
    }

    public function isPluginReady(): bool
    {
        return $this->plugin
            ->isReady();
    }

    public function isPluginEnabled(): bool
    {
        return $this->plugin
            ->isEnabled();
    }

    public function register(): self
    {
        foreach ($this->registry as $event => $method) {
            $this->addAction($event, $method);
        }

        return $this;
    }

    public function addAction(string $event, $method = null): ?Hooks
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

            if ($callback !== null) {
                return $this->plugin->actions()
                    ->add($event, $this, $callback, $this->getPriority($event), $arguments);
            }
        }

        return null;
    }

    public function removeAction(string $event, $method = null): ?Hooks
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

            if ($callback !== null) {
                return $this->plugin->actions()
                    ->remove($event, $this, $callback, $this->getPriority($event), $arguments);
            }
        }

        return null;
    }

    public function getPriority(string $event, int $default = 10, string $prefix = 'AUTH0_ACTION_PRIORITY'): int
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
}
