<?php

declare(strict_types=1);

namespace Auth0\WordPress;

use Auth0\WordPress\Plugin;

final class Actions
{
    private array $listeners = [];

    public function __construct(private ?Plugin $plugin)
    {
    }

    public function add(
      string $hook,
      string $class,
      string $method = 'handle',
      int $priority = 10
    ): self {
        add_action($hook, [$this->getClassInstance($class), $method], $priority);
        return $this;
    }

    public function remove(
        string $hook,
        string $class,
        string $method = 'handle',
        int $priority = 10
    ): self {
        remove_action($hook, [$this->getClassInstance($class), $method], $priority);
        return $this;
    }

    public function do(
        string $hook,
        array $arguments = []
    ): self {
        do_action($hook, $arguments);
        return $this;
    }

    private function getClassInstance(
        string $class
    ) {
        if (! array_key_exists($class, $this->listeners)) {
            $this->listeners[$class] = new $class($this->plugin);
        }

        return $this->listeners[$class];
    }
}
