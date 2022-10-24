<?php

declare(strict_types=1);

namespace Auth0\WordPress\Actions;

use Auth0\SDK\Exception\StateException;
use Auth0\SDK\Store\CookieStore;
use Auth0\WordPress\Database;
use Throwable;
use WP_Error;
use WP_User;

final class Authentication extends Base
{
    /**
     * @var array<string, string|array<int, int|string>>
     */
    protected array $registry = [
        'init' => 'onInit',
        'auth_cookie_malformed' => ['onAuthCookieMalformed', 2],
        'auth_cookie_expired' => 'onAuthCookieExpired',
        'auth_cookie_bad_username' => 'onAuthCookieBadUsername',
        'auth_cookie_bad_session_token' => 'onAuthCookieBadSessionToken',
        'auth_cookie_bad_hash' => 'onAuthCookieBadHash',

        'login_form_login' => 'onLogin',
        'auth0_login_callback' => 'onLogin',

        'login_form_logout' => 'onLogout',
        'auth0_logout' => 'onLogout',

        'before_signup_header' => 'onRegistration',

        'edit_user_created_user' => ['onCreatedUser', 2],
        'deleted_user' => 'onDeletedUser',
        'profile_update' => ['onUpdatedUser', 2],
    ];

    public function onInit(): void
    {
        if (! $this->getPlugin()->isReady() || ! $this->getPlugin()->isEnabled()) {
            return;
        }

        $session = $this->getSdk()->getCredentials();
        $expired = $session?->accessTokenExpired ?? true;
        $wordpress = wp_get_current_user();

        // Paired sessions enforced
        if ($this->getPlugin()->getOption('authentication', 'pair_sessions', 0) !== 2) {
            // ... for all but admins?
            if ($this->getPlugin()->getOption('authentication', 'pair_sessions', 0) === 0 && is_admin()) {
                return;
            }

            // Is an Auth0 session available?
            if (! is_object($session)) {
                wp_logout();

                return;
            }

            // Is an WP session available?
            if ($wordpress->ID === 0) {
                $this->getSdk()->clear();

                return;
            }

            // Verify the WordPress user signed in is linked to the Auth0 Connection 'sub'.
            $sub = $session->user['sub'] ?? null;
            if ($sub !== null) {
                $match = $this->getAccountByConnection($sub);

                if (! $match instanceof WP_User || $match->ID !== $wordpress->ID) {
                    $this->getSdk()->clear();
                    wp_logout();
                    return;
                }
            }

            // Verify that the Auth0 token cookie has not expired
            if ($expired && $this->getPlugin()->getOption('sessions', 'refresh_tokens') === 'true') {
                try {
                    // Token has expired, attempt to refresh it.
                    $this->getSdk()->renew();
                    return;
                } catch (StateException) {
                    // Refresh failed.
                }

                // Invalidation authentication state.
                $this->getSdk()->clear();
                wp_logout();
                return;
            }
        }

        if ($this->getPlugin()->getOption('authentication', 'rolling_sessions') !== 'false') {
            $store = $this->getSdk()->configuration()->getSessionStorage();

            /**
             * @var CookieStore $store
             */

            $store->setState(true);
            wp_set_auth_cookie($wordpress->ID, true);
        }
    }

    /**
     * Fires when 'auth_cookie_malformed' is triggered by WordPress.
     *
     * @link https://developer.wordpress.org/reference/hooks/auth_cookie_malformed/
     */
    public function onAuthCookieMalformed(string $cookie, ?string $scheme = null): void
    {
        if ($cookie === '') {
            return;
        }

        $this->getSdk()
            ->clear();
    }

    /**
     * Fires when 'auth_cookie_expired' is triggered by WordPress.
     *
     * @link https://developer.wordpress.org/reference/hooks/auth_cookie_expired/
     * @param array<mixed> $cookieElements
     */
    public function onAuthCookieExpired(array $cookieElements): void
    {
        $this->getSdk()
            ->clear();
    }

    /**
     * Fires when 'auth_cookie_bad_username' is triggered by WordPress.
     *
     * @link https://developer.wordpress.org/reference/hooks/auth_cookie_bad_username/
     * @param array<mixed> $cookieElements
     */
    public function onAuthCookieBadUsername(array $cookieElements): void
    {
        $this->getSdk()
            ->clear();
    }

    /**
     * Fires when 'auth_cookie_bad_session_token' is triggered by WordPress.
     *
     * @link https://developer.wordpress.org/reference/hooks/auth_cookie_bad_session_token/
     * @param array<mixed> $cookieElements
     */
    public function onAuthCookieBadSessionToken(array $cookieElements): void
    {
        $this->getSdk()
            ->clear();
    }

    /**
     * Fires when 'auth_cookie_bad_hash' is triggered by WordPress.
     *
     * @link https://developer.wordpress.org/reference/hooks/auth_cookie_bad_hash/
     * @param array<mixed> $cookieElements
     */
    public function onAuthCookieBadHash(array $cookieElements): void
    {
        $this->getSdk()
            ->clear();
    }

    public function onLogin(): void
    {
        if (! $this->getPlugin()->isReady()) {
            return;
        }

        if (! $this->getPlugin()->isEnabled()) {
            return;
        }

        // Don't allow caching of this route
        nocache_headers();

        // Check if authentication flow parameters are present (?code and ?state)
        $code = $this->getSdk()->getRequestParameter('code');
        $state = $this->getSdk()->getRequestParameter('state');
        $exchangeParameters = $code !== null && $state !== null;

        // Check if authentication flow error parameter is present (?error)
        $error = $this->getSdk()
            ->getRequestParameter('error');

        // Are token exchange parameters present?
        if ($exchangeParameters) {
            try {
                // Attempt completion of the authentication flow using
                $this->getSdk()
                    ->exchange(
                        code: \sanitize_text_field($code),
                        state: \sanitize_text_field($state)
                    );
            } catch (Throwable $throwable) {
                // Exchange failed; throw an error
                die($throwable->getMessage());
            }

            $session = $this->getSdk()
                ->getCredentials();

            // Do we indeed have a session now?
            if ($session !== null) {
                $sub = \sanitize_text_field($session->user['sub'] ?? '');
                $email = \sanitize_email($session->user['email'] ?? '');
                $verified = $session->user['email_verified'] ?? null;

                if ($email === '') {
                    $email = null;
                    $verified = null;
                }

                $wpUser = $this->resolveIdentity(sub: $sub, email: $email, verified: $verified);

                if ($wpUser !== null) {
                    if ($sub !== '') {
                        $this->createAccountConnection($wpUser, $sub);
                    }

                    if ($email !== null && $verified === true && $email !== $wpUser->user_email) {
                        $this->removeAction('profile_update');
                        $this->setAccountEmail($wpUser, $email);
                        $this->addAction('profile_update');
                    }

                    wp_set_current_user($wpUser->ID);
                    wp_set_auth_cookie($wpUser->ID, true);
                    do_action('wp_login', $wpUser->user_login, $wpUser);
                    wp_redirect('/');
                    exit;
                }

                // TODO: Display an error here. No matches and could not create account, or account creating was disabled.
            }
        }

        if ($error !== null) {
            wp_redirect('/');
            exit;
        }

        if ($exchangeParameters && $error === null && (wp_get_current_user()->ID !== 0 || $this->getSdk()->getCredentials() !== null)) {
            wp_redirect('/');
            exit;
        }

        wp_redirect($this->getSdk()->login());
        exit;
    }

    public function onLogout(): void
    {
        wp_logout();
        wp_redirect($this->getSdk()->logout(get_site_url()));
        exit;
    }

    public function onRegistration(): void
    {
        // Block registration attempts from the API?
        exit;
    }

    /**
     * Note that this ONLY fires for users created via WordPress' UI, like the "Add New" button from the Admin -> Users page.
     */
    public function onCreatedUser($userId, $notify = null): void
    {
        if (! is_int($userId)) {
            return;
        }

        $network = get_current_network_id();
        $blog = get_current_blog_id();
        $database = $this->getPlugin()->database();
        $table = $database->getTableName(Database::CONST_TABLE_SYNC);

        $this->prepDatabase(Database::CONST_TABLE_SYNC);

        $payload = json_encode([
            'event' => 'wp_user_created',
            'user' => $userId
        ]);
        $checksum = hash('sha256', $payload);

        // TODO: Optimize this by creating an InsertIgnoreRow() method with a custom query.
        $dupe = $database->selectRow('id', $table, 'WHERE `hashsum` = "%s";', $checksum);

        if (! $dupe) {
            $database->insertRow($table, [
                'site' => $network,
                'blog' => $blog,
                'created' => time(),
                'payload' => $payload,
                'hashsum' => hash('sha256', $payload),
                'locked' => 0,
            ], [
                '%d',
                '%d',
                '%d',
                '%s',
                '%s',
                '%d',
            ]);
        }
    }

    public function onDeletedUser($userId): void
    {
        $connections = $this->deleteAccountConnections($userId);

        if (null !== $connections && [] !== $connections) {
            $network = get_current_network_id();
            $blog = get_current_blog_id();
            $database = $this->getPlugin()->database();
            $table = $database->getTableName(Database::CONST_TABLE_SYNC);

            $this->prepDatabase(Database::CONST_TABLE_SYNC);

            foreach ($connections as $connection) {
                $payload = json_encode([
                    'event' => 'wp_user_deleted',
                    'user' => $userId,
                    'connection' => $connection->auth0
                ]);
                $checksum = hash('sha256', $payload);

                // TODO: Optimize this by creating an InsertIgnoreRow() method with a custom query.
                $dupe = $database->selectRow('id', $table, 'WHERE `hashsum` = "%s";', $checksum);

                if (! $dupe) {
                    $database->insertRow($table, [
                        'site' => $network,
                        'blog' => $blog,
                        'created' => time(),
                        'payload' => $payload,
                        'hashsum' => hash('sha256', $payload),
                        'locked' => 0,
                    ], [
                        '%d',
                        '%d',
                        '%d',
                        '%s',
                        '%s',
                        '%d',
                    ]);
                }
            }
        }
    }

    public function onUpdatedUser($userId, $previousUserData = null): void
    {
        if (! is_int($userId)) {
            return;
        }

        $network = get_current_network_id();
        $blog = get_current_blog_id();
        $database = $this->getPlugin()->database();
        $table = $database->getTableName(Database::CONST_TABLE_SYNC);

        $this->prepDatabase(Database::CONST_TABLE_SYNC);

        $payload = json_encode([
            'event' => 'wp_user_updated',
            'user' => $userId
        ]);
        $checksum = hash('sha256', $payload);

        // TODO: Optimize this by creating an InsertIgnoreRow() method with a custom query.
        $dupe = $database->selectRow('id', $table, 'WHERE `hashsum` = "%s";', $checksum);

        if (! $dupe) {
            $database->insertRow($table, [
                'site' => $network,
                'blog' => $blog,
                'created' => time(),
                'payload' => $payload,
                'hashsum' => hash('sha256', $payload),
                'locked' => 0,
            ], [
                '%d',
                '%d',
                '%d',
                '%s',
                '%s',
                '%d',
            ]);
        }
    }

    private function resolveIdentity(
        ?string $sub = null,
        ?string $email = null,
        ?bool $verified = null,
    ): ?WP_User {
        $email = \sanitize_email(filter_var($email ?? '', FILTER_SANITIZE_EMAIL, FILTER_NULL_ON_FAILURE) ?? '');

        if ($sub !== null) {
            $sub = \sanitize_text_field($sub);
            $found = $this->getAccountByConnection($sub);

            if ($found instanceof WP_User) {
                return $found;
            }
        }

        // If an email is not marked as verified by the connection, dismiss it.
        if ($verified !== true || $email === '') {
            $email = null;
        }

        if ($email !== null) {
            $found = get_user_by('email', $email);

            if ($found instanceof WP_User) {
                // Are we allowed to match loosely by email?
                if ($this->getPlugin()->getOption('accounts', 'matching') !== 'strict') {
                    return $found;
                }

                // Are administrators allowed to bypass the check as a failsafe for configuration issues?
                if ($this->getPlugin()->getOption('authentication', 'pair_sessions', 0) === 0) {
                    $roles = $found->roles;

                    if (in_array('administrator', $roles, true)) {
                        return $found;
                    }
                }

                return null;
            }
        }

        if ($this->getPlugin()->getOption('accounts', 'missing') === 'create') {
            $username = ($email !== null) ? explode('@', $email, 2)[0] : explode('|', $sub ?? '', 2)[1];
            $user = wp_create_user($username, wp_generate_password(random_int(12, 123), true, true), $email ?? '');

            if (! $user instanceof WP_Error) {
                $user = get_user_by('ID', $user);

                if ($user instanceof WP_User) {
                    $role = $this->getPlugin()->getOptionString('accounts', 'default_role');

                    if (is_string($role) && ! in_array($role, $user->roles, true)) {
                        $user->set_role($role);
                        wp_update_user($user);
                    }

                    return $user;
                }
            }
        }

        return null;
    }

    private function prepDatabase(string $databaseName)
    {
        // $cacheKey = 'auth0_db_check_' . hash('sha256', $databaseName);

        // $found = false;
        // wp_cache_get($cacheKey, '', false, $found);

        // if (! $found && false === get_transient($cacheKey)) {
        //     set_transient($cacheKey, true, 1800);
        //     wp_cache_set($cacheKey, true, 1800);

        return $this->getPlugin()->database()->createTable($databaseName);
        // }
    }

    public function setAccountEmail(WP_User $wpUser, string $email): ?WP_User
    {
        if ($wpUser->user_email !== $email) {
            $wpUser->user_email = $email;
            $status = wp_update_user($wpUser);

            if ($status instanceof WP_Error) {
                return null;
            }
        }

        return $wpUser;
    }

    public function createAccountConnection(WP_User $wpUser, string $connection): void
    {
        $network = get_current_network_id();
        $blog = get_current_blog_id();
        $cacheKey = 'auth0_account_' . hash('sha256', $connection . '::' . $network . '!' . $blog);

        $found = false;
        wp_cache_get($cacheKey, '', false, $found);

        if (! $found) { //  && false === get_transient($cacheKey)
            $database = $this->getPlugin()->database();
            $table = $database->getTableName(Database::CONST_TABLE_ACCOUNTS);
            $found = null;

            $this->prepDatabase(Database::CONST_TABLE_ACCOUNTS);

            $found = $database->selectRow('*', $table, 'WHERE `user` = %d AND `site` = %d AND `blog` = %d AND `auth0` = "%s" LIMIT 1', $wpUser->ID, $network, $blog, $connection);

            if (null === $found) {
                // set_transient($cacheKey, $wpUser->ID, 120);
                wp_cache_set($cacheKey, $found, 120);

                $database->insertRow($table, [
                    'user' => $wpUser->ID,
                    'site' => $network,
                    'blog' => $blog,
                    'auth0' => $connection
                ], [
                    '%d',
                    '%d',
                    '%d',
                    '%s'
                ]);
            }
        }
    }

    public function getAccountByConnection(string $connection): ?WP_User
    {
        $network = get_current_network_id();
        $blog = get_current_blog_id();
        $cacheKey = 'auth0_account_' . hash('sha256', $connection . '::' . $network . '!' . $blog);

        $found = false;
        $user = wp_cache_get($cacheKey, '', false, $found);

        if ($found) {
            $found = $user;
        }

        if (! $found) {
            // $found = get_transient($cacheKey);

            if (false === $found) {
                $database = $this->getPlugin()->database();
                $table = $database->getTableName(Database::CONST_TABLE_ACCOUNTS);

                $this->prepDatabase(Database::CONST_TABLE_ACCOUNTS);
                $found = $database->selectRow('user', $table, 'WHERE `site` = %d AND `blog` = %d AND `auth0` = "%s" LIMIT 1', $network, $blog, $connection);

                if (null === $found) {
                    return null;
                }

                $found = $found->user;
            }
        }

        if ($found) {
            // set_transient($cacheKey, $found, 120);
            wp_cache_set($cacheKey, $found, 120);

            $user = get_user_by('ID', $found);
        }

        if (false === $user) {
            return null;
        }

        return $user;
    }

    public function getAccountConnections(int $userId): ?array
    {
        $database = $this->getPlugin()->database();
        $table = $database->getTableName(Database::CONST_TABLE_ACCOUNTS);
        $network = get_current_network_id();
        $blog = get_current_blog_id();

        $this->prepDatabase(Database::CONST_TABLE_ACCOUNTS);

        $connections = $database->selectResults('auth0', $table, 'WHERE `site` = %d AND `blog` = %d AND `user` = "%s" LIMIT 1', $network, $blog, $userId);

        if ($connections) {
            return $connections;
        }

        return null;
    }

    public function deleteAccountConnections(int $userId): ?array
    {
        $database = $this->getPlugin()->database();
        $table = $database->getTableName(Database::CONST_TABLE_ACCOUNTS);
        $network = get_current_network_id();
        $blog = get_current_blog_id();

        $this->prepDatabase(Database::CONST_TABLE_ACCOUNTS);

        $connections = $database->selectResults('auth0', $table, 'WHERE `site` = %d AND `blog` = %d AND `user` = "%s" LIMIT 1', $network, $blog, $userId);

        if ($connections) {
            $database->deleteRow($table, ['user' => $userId, 'site' => $network, 'blog' => $blog], ['%d', '%s', '%s']);
            wp_cache_flush();
            return $connections;
        }

        return null;
    }
}
