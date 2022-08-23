<?php

declare(strict_types=1);

namespace Auth0\WordPress\Actions;

use Auth0\WordPress\Plugin;

abstract class Base
{
    public function __construct(private ?Plugin $plugin)
    {
    }

    public function getPlugin(): ?Plugin
    {
        return $this->plugin;
    }
}
