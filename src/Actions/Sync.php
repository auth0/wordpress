<?php

declare(strict_types=1);

namespace Auth0\WordPress\Actions;

final class Sync extends Base
{
    /**
     * @var array<string, string|array<int, int|string>>
     */
    protected array $registry = [
        'a0_cron_hook' => 'onBackgroundSync',
    ];

    public function onBackgroundSync(): void
    {
        error_log("onBackgroundSync");
    }

    public function syncUsers(): void
    {
        error_log("syncUsers");
    }
}
