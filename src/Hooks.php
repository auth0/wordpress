<?php

declare(strict_types=1);

namespace Auth0\WordPress;

final class Hooks
{
    /**
     * @var int
     */
    public const CONST_ACTION_FILTER = 1;

    /**
     * @var int
     */
    public const CONST_ACTION_HOOK = 0;

    public function __construct(public int $hookType = self::CONST_ACTION_HOOK)
    {
    }

    public function add(
        string $hook,
        object $class,
        string $method,
        int $priority = 10,
        int $arguments = 1,
    ): self {
        $callback = [$class, $method];

        /**
         * @var callable $callback
         */
        if (self::CONST_ACTION_HOOK === $this->hookType) {
            add_action($hook, $callback, $priority, $arguments);
        }

        if (self::CONST_ACTION_FILTER === $this->hookType) {
            add_filter($hook, $callback, $priority, $arguments);
        }

        return $this;
    }

    public function remove(string $hook, object $class, string $method, int $priority = 10, int $arguments = 1): self
    {
        $callback = [$class, $method];

        /**
         * @var callable $callback
         */
        if (self::CONST_ACTION_HOOK === $this->hookType) {
            remove_action($hook, $callback, $priority, $arguments);
        }

        if (self::CONST_ACTION_FILTER === $this->hookType) {
            remove_filter($hook, $callback, $priority, $arguments);
        }

        return $this;
    }
}
