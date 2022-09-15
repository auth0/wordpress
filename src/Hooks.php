<?php

declare(strict_types=1);

namespace Auth0\WordPress;

final class Hooks
{
    /**
     * @var int
     */
    public const CONST_ACTION_HOOK = 0;

    /**
     * @var int
     */
    public const CONST_ACTION_FILTER = 2;

    public function __construct(public int $hookType = self::CONST_ACTION_HOOK)
    {
    }

    public function add(
        string $hook,
        object $class,
        string $method,
        int $priority = 10,
        int $arguments = 1
    ): self {
        if ($this->hookType === self::CONST_ACTION_HOOK) {
            add_action($hook, [$class, $method], $priority);
        }

        if ($this->hookType === self::CONST_ACTION_FILTER) {
            add_filter($hook, [$class, $method], $priority, $arguments);
        }

        return $this;
    }

    public function remove(string $hook, object $class, string $method, int $priority = 10): self
    {
        if ($this->hookType === self::CONST_ACTION_HOOK) {
            remove_action($hook, [$class, $method], $priority);
        }

        if ($this->hookType === self::CONST_ACTION_FILTER) {
            remove_filter($hook, [$class, $method], $priority);
        }

        return $this;
    }
}
