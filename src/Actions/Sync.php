<?php

declare(strict_types=1);

namespace Auth0\WordPress\Actions;

use Auth0\SDK\API\Management\Exceptions\Auth0ApiException;
use Auth0\SDK\API\Management\Tickets\Requests\{ChangePasswordTicketRequestContent, VerifyEmailTicketRequestContent};
use Auth0\SDK\API\Management\Users\Requests\{CreateUserRequestContent, ListUsersByEmailRequestParameters, UpdateUserRequestContent};
use Auth0\WordPress\Database;
use JsonSerializable;
use Psr\Http\Client\ClientExceptionInterface;
use Throwable;
use WP_User;

use function is_array;

final class Sync extends Base
{
    /**
     * @var string
     */
    public const CONST_JOB_BACKGROUND_MAINTENANCE = 'AUTH0_CRON_MAINTENANCE';

    /**
     * @var string
     */
    public const CONST_JOB_BACKGROUND_SYNC = 'AUTH0_CRON_SYNC';

    /**
     * @var string
     */
    public const CONST_SCHEDULE_BACKGROUND_MAINTENANCE = 'AUTH0_MAINTENANCE';

    /**
     * @var string
     */
    public const CONST_SCHEDULE_BACKGROUND_SYNC = 'AUTH0_SYNC';

    /**
     * @var array<string, array<int, int|string>|string>
     */
    protected array $registry = [
        self::CONST_JOB_BACKGROUND_SYNC => 'onBackgroundSync',
        self::CONST_JOB_BACKGROUND_MAINTENANCE => 'onBackgroundMaintenance',
        'cron_schedules' => 'updateCronSchedule',
    ];

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
        if (! is_array($users)) {
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

    public function eventUserCreated(string $dbConnection, array $event): void
    {
        if (isset($event['user'])) {
            $user = $event['user'] ?? null;

            if (null === $user) {
                return;
            }

            $user = get_user_by('ID', $user);

            if ($user) {
                $byEmail = $this->getManagement()->users->listUsersByEmail(new ListUsersByEmailRequestParameters([
                    'email' => $user->user_email,
                ]));

                if (! is_array($byEmail) || [] === $byEmail) {
                    $dbConnectionName = $this->getDatabaseName($dbConnection);

                    $created = $this->getManagement()->users->create(new CreateUserRequestContent([
                        'connection' => $dbConnectionName,
                        'email' => $user->user_email,
                        'name' => $user->display_name,
                        'nickname' => $user->nickname,
                        'givenName' => $user->user_firstname,
                        'familyName' => $user->user_lastname,
                        'password' => wp_generate_password(random_int(12, 123), true, true),
                    ]));

                    $response = $this->results($created);

                    if (null !== $response && isset($response['user_id'])) {
                        // Trigger a password change email to let them set their password
                        $this->getManagement()->tickets->changePassword(new ChangePasswordTicketRequestContent([
                            'userId' => $response['user_id'],
                        ]));

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

                if (! $wpUser instanceof WP_User) {
                    // Determine if the Auth0 counterpart account still exists
                    $api = $this->results($this->getManagement()->users->get($connection));

                    if (null !== $api) {
                        // Delete the Auth0 counterpart account
                        $this->getManagement()->users->delete($connection);
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
                    $api = $this->results($this->getManagement()->users->get($connection->auth0));

                    if (null !== $api) {
                        $connectionId = $api['user_id'] ?? null;

                        if (null === $connectionId) {
                            continue;
                        }

                        $currentEmail = $api['email'] ?? '';

                        $this->getManagement()->users->update($connectionId, new UpdateUserRequestContent([
                            'email' => $user->user_email,
                            'name' => $user->display_name,
                            'nickname' => $user->nickname,
                            'givenName' => $user->user_firstname,
                            'familyName' => $user->user_lastname,
                        ]));

                        if ($user->user_email !== $currentEmail) {
                            $this->getManagement()->tickets->verifyEmail(new VerifyEmailTicketRequestContent([
                                'userId' => $connectionId,
                            ]));
                        }
                    }
                }
            }
        }
    }

    public function getDatabaseName(?string $dbConnection): ?string
    {
        static $dbConnectionName = [];

        if (isset($dbConnectionName[$dbConnection])) {
            return $dbConnectionName[$dbConnection];
        }

        if (null !== $dbConnection) {
            $response = $this->results($this->getManagement()->connections->get($dbConnection));

            if (null !== $response && isset($response['name'])) {
                $dbConnectionName[$dbConnection] = $response['name'];

                return $response['name'];
            }
        }

        return null;
    }

    public function onBackgroundMaintenance(): void
    {
        $this->cleanupOrphanedConnections();
    }

    public function onBackgroundSync(): void
    {
        // Leave the queue intact until configured, rather than failing per item.
        if (! $this->isPluginReady()) {
            return;
        }

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
                try {
                    $payload = json_decode($singleQueue->payload, true, 512, JSON_THROW_ON_ERROR);

                    if (isset($payload['event'])) {
                        if ('wp_user_created' === $payload['event'] && $enabledEvents['wp_user_created']) {
                            $this->eventUserCreated($dbConnection, $payload);
                        }

                        if ('wp_user_deleted' === $payload['event'] && $enabledEvents['wp_user_deleted']) {
                            $this->eventUserDeleted($dbConnection, $payload);
                        }

                        if ('wp_user_updated' === $payload['event'] && $enabledEvents['wp_user_updated']) {
                            $this->eventUserUpdated($dbConnection, $payload);
                        }
                    }
                } catch (Auth0ApiException $auth0ApiException) {
                    $status = $auth0ApiException->getCode();

                    // Keep the row for the next cron pass on transient failures.
                    if (429 === $status || $status >= 500) {
                        error_log($auth0ApiException->getMessage());

                        continue;
                    }

                    error_log($auth0ApiException->getMessage());
                } catch (ClientExceptionInterface $clientException) {
                    // Transport failures are transient, so keep the row for retry.
                    error_log($clientException->getMessage());

                    continue;
                } catch (Throwable $throwable) {
                    error_log($throwable->getMessage());
                }
            }

            $database->deleteRow($table, ['id' => $singleQueue->id], ['%d']);
        }
    }

    /**
     * @param mixed $schedules
     *
     * @return mixed[]
     */
    public function updateCronSchedule($schedules): array
    {
        $schedules[self::CONST_SCHEDULE_BACKGROUND_SYNC] = ['interval' => $this->getPlugin()->getOptionInteger('sync', 'schedule') ?? 3600, 'display' => 'Plugin Configuration'];

        $schedules[self::CONST_SCHEDULE_BACKGROUND_MAINTENANCE] = ['interval' => 300, 'display' => 'Every 5 Minutes'];

        return $schedules;
    }

    private function authentication(): Authentication
    {
        return $this->getPlugin()->getClassInstance(Authentication::class);
    }

    /**
     * Normalize a v9 Management response object into the snake_case array shape
     * the rest of this class consumes. v9 endpoints return differently-typed
     * response classes, but every one exposes a uniform jsonSerialize() that
     * matches the v8 decoded-body shape, so serializing is the regeneration-safe
     * way to read fields rather than relying on per-endpoint typed getters.
     *
     * @return ?array<string, mixed>
     */
    private function results(?JsonSerializable $response): ?array
    {
        if (! $response instanceof JsonSerializable) {
            return null;
        }

        $serialized = $response->jsonSerialize();

        return is_array($serialized) ? $serialized : null;
    }
}
