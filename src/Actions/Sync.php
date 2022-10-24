<?php

declare(strict_types=1);

namespace Auth0\WordPress\Actions;

use WP_User;
use Auth0\SDK\Utility\HttpResponse;
use Auth0\WordPress\Database;
use Psr\Http\Message\ResponseInterface;

final class Sync extends Base
{
    /**
     * @var string
     */
    public const CONST_JOB_BACKGROUND_SYNC = 'AUTH0_CRON_SYNC';

    /**
     * @var string
     */
    public const CONST_JOB_BACKGROUND_MAINTENANCE = 'AUTH0_CRON_MAINTENANCE';

    /**
     * @var string
     */
    public const CONST_SCHEDULE_BACKGROUND_SYNC = 'AUTH0_SYNC';

    /**
     * @var string
     */
    public const CONST_SCHEDULE_BACKGROUND_MAINTENANCE = 'AUTH0_MAINTENANCE';

    /**
     * @var array<string, string|array<int, int|string>>
     */
    protected array $registry = [
        self::CONST_JOB_BACKGROUND_SYNC => 'onBackgroundSync',
        self::CONST_JOB_BACKGROUND_MAINTENANCE => 'onBackgroundMaintenance',
        'cron_schedules' => 'updateCronSchedule',
    ];

    /**
     * @return mixed[]
     */
    public function updateCronSchedule($schedules): array
    {
        $schedules[self::CONST_SCHEDULE_BACKGROUND_SYNC] = ['interval'  => $this->getPlugin()->getOptionInteger('sync', 'schedule') ?? 3600, 'display'   => 'Plugin Configuration'];

        $schedules[self::CONST_SCHEDULE_BACKGROUND_MAINTENANCE] = ['interval'  => 300, 'display'   => 'Every 5 Minutes'];

        return $schedules;
    }

    public function getDatabaseName(?string $dbConnection): ?string
    {
        static $dbConnectionName = [];

        if (isset($dbConnectionName[$dbConnection])) {
            return $dbConnectionName[$dbConnectionName];
        }

        if (null !== $dbConnection) {
            $response = $this->getResults($this->getSdk()->management()->connections()->get($dbConnection));

            if ($response) {
                $dbConnectionName[$dbConnection] = $response['name'];
                return $response['name'] ?? $dbConnection;
            }
        }

        return null;
    }

    public function onBackgroundSync(): void
    {
        $database = $this->getPlugin()->database();
        $table = $database->getTableName(Database::CONST_TABLE_SYNC);
        $network = get_current_network_id();
        $blog = get_current_blog_id();

        $this->getPlugin()->database()->createTable(Database::CONST_TABLE_SYNC);

        $queue = $database->selectResults('*', $table, 'WHERE `site` = %d AND `blog` = %d ORDER BY created LIMIT 10', [$network, $blog]);

        $enabledEvents = [
            'wp_user_created' => $this->getPlugin()->getOptionBoolean('sync_events', 'user_creation') ?? true,
            'wp_user_deleted' => $this->getPlugin()->getOptionBoolean('sync_events', 'user_deletion') ?? true,
            'wp_user_updated' => $this->getPlugin()->getOptionBoolean('sync_events', 'user_updates') ?? true,
        ];

        $dbConnection = $this->getPlugin()->getOptionString('sync', 'database');

        foreach ($queue as $singleQueue) {
            if (null !== $dbConnection) {
                $payload = json_decode($singleQueue->payload, true, 512, JSON_THROW_ON_ERROR);

                if (isset($payload['event'])) {
                    if ($payload['event'] === 'wp_user_created' && $enabledEvents['wp_user_created']) {
                        $this->eventUserCreated($dbConnection, $payload);
                    }

                    if ($payload['event'] === 'wp_user_deleted' && $enabledEvents['wp_user_deleted']) {
                        $this->eventUserDeleted($dbConnection, $payload);
                    }

                    if ($payload['event'] === 'wp_user_updated' && $enabledEvents['wp_user_updated']) {
                        $this->eventUserUpdated($dbConnection, $payload);
                    }
                }
            }

            $database->deleteRow($table, ['id' => $singleQueue->id], ['%d']);
        }
    }

    public function onBackgroundMaintenance(): void
    {
        $this->cleanupOrphanedConnections();
    }

    public function eventUserCreated(string $dbConnection, array $event): void
    {
        if (isset($event['user'])) {
            $user = $event['user'] ?? null;

            if (null === $user) {
                return;
            }

            $user = get_user_by('ID', $user);

            if ($user) {
                $exists = $this->getResults($this->getSdk()->management()->usersByEmail()->get($user->user_email));

                if (! is_array($exists) || [] === $exists) {
                    $dbConnectionName = $this->getDatabaseName($dbConnection);

                    $response = $this->getSdk()->management()->users()->create($dbConnectionName, [
                        'name' => $user->display_name,
                        'nickname' => $user->nickname,
                        'given_name' => $user->user_firstname,
                        'family_name' => $user->user_lastname,
                        'email' => $user->user_email,
                        'password' => wp_generate_password(random_int(12, 123), true, true)
                    ]);

                    $response = $this->getResults($response, 201);

                    if (null !== $response) {
                        // Trigger a password change email to let them set their password
                        // TODO: This needs to be optional
                        $this->getSdk()->management()->tickets()->createPasswordChange([
                            'user_id' => $response['user_id']
                        ]);

                        $this->authentication()->createAccountConnection($user, $response['user_id']);
                    }
                }
            }
        }
    }

    public function eventUserDeleted(string $dbConnection, array $event): void
    {
        if (isset($event['user'])) {
            $user = $event['user'] ?? null;
            $connection = $event['connection'] ?? null;

            if (null !== $user && null !== $connection) {
                // Verify that the connection has not been claimed by another account already
                $wpUser = $this->authentication()->getAccountByConnection($connection);

                if (!$wpUser instanceof WP_User) {
                    // Determine if the Auth0 counterpart account still exists
                    $api = $this->getResults($this->getSdk()->management()->users()->get($connection));

                    if (null !== $api) {
                        // Delete the Auth0 counterpart account
                        $this->getSdk()->management()->users()->delete($connection);
                    }
                }
            }
        }
    }

    public function eventUserUpdated(string $dbConnection, array $event): void
    {
        if (isset($event['user'])) {
            $user = $event['user'] ?? null;
            $connection = $event['connection'] ?? null;

            if (null === $user && null === $connection) {
                return;
            }

            $user = get_user_by('ID', $user);

            if (! $user) {
                return;
            }

            $connections = $this->authentication()->getAccountConnections($user->ID);

            if (null !== $connections) {
                foreach ($connections as $connection) {
                    $api = $this->getResults($this->getSdk()->management()->users()->get($connection->auth0));

                    if (null !== $api) {
                        $connectionId = $api['user_id'] ?? null;

                        if (null === $connectionId) {
                            continue;
                        }

                        $currentEmail = $api['email'] ?? '';

                        $this->getSdk()->management()->users()->update($connectionId, [
                            'name' => $user->display_name,
                            'nickname' => $user->nickname,
                            'given_name' => $user->user_firstname,
                            'family_name' => $user->user_lastname,
                            'email' => $user->user_email
                        ]);

                        if ($user->user_email !== $currentEmail) {
                            $this->getSdk()->management()->tickets()->createEmailVerification($connectionId);
                        }
                    }
                }
            }
        }
    }

    /**
     * In the event of an issue during the WP account deletion hooks, connections might be left in the accounts table that point to missing WP accounts.
     * This clears out those 'orphaned' connections for re-use by other WP accounts, or for use in creating a new WP account.
     */
    public function cleanupOrphanedConnections(): void
    {
        $database = $this->getPlugin()->database();
        $table = $database->getTableName(Database::CONST_TABLE_ACCOUNTS);
        $network = get_current_network_id();
        $blog = get_current_blog_id();

        $this->getPlugin()->database()->createTable(Database::CONST_TABLE_ACCOUNTS);

        $users = $database->selectDistinctResults('user', $table, 'WHERE `site` = %d AND `blog` = %d', [$network, $blog]);
        if (!is_array($users)) {
            return;
        }

        if ([] === $users) {
            return;
        }

        foreach ($users as $user) {
            $found = get_user_by('ID', $user->user);

            if (! $found) {
                $this->authentication()->deleteAccountConnections((int) $user->user);
            }
        }
    }

    private function authentication(): Authentication
    {
        return $this->getPlugin()->getClassInstance(Authentication::class);
    }

    private function getResults(ResponseInterface $response, int $expectedStatusCode = 200): ?array
    {
        if (HttpResponse::wasSuccessful($response, $expectedStatusCode)) {
            return HttpResponse::decodeContent($response);
        }

        return null;
    }
}
