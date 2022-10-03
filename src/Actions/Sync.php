<?php

declare(strict_types=1);

namespace Auth0\WordPress\Actions;

final class Sync extends Base
{
    /**
     * @var array<string, string|array<int, int|string>>
     */
    protected array $registry = [
        'init' => 'onInit',
    ];

    public function onBackgroundSync(): void
    {
        //
    }

    public function syncUsers(): void
    {
        //
    }
}
