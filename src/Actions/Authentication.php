<?php

declare(strict_types=1);

namespace Auth0\WordPress\Actions;

use Auth0\SDK\Exception\StateException;

final class Authentication extends Base
{
    protected array $registry = [
        'init' => 'onInit',
        'auth_cookie_valid' => ['onAuthCookieValid', 2],
        'auth_cookie_malformed' => ['onAuthCookieMalformed', 2],
        'auth_cookie_expired' => 'onAuthCookieExpired',
        'auth_cookie_bad_username' => 'onAuthCookieBadUsername',
        'auth_cookie_bad_session_token' => 'onAuthCookieBadSessionToken',
        'auth_cookie_bad_hash' => 'onAuthCookieBadHash',

        'login_form_login' => 'onLogin',
        'auth0_login_callback' => 'onLogin',

        'login_form_logout' => 'onLogout',
        'auth0_logout' => 'onLogout',

        // https://developer.wordpress.org/reference/hooks/wp_login/
        // 'wp_login' => 'onLoginComplete',

        // https://developer.wordpress.org/reference/hooks/set_current_user/
        // 'set_current_user' => 'onSetCurrentUser',

        // https://developer.wordpress.org/reference/hooks/set_logged_in_cookie/
        // 'set_logged_in_cookie' => 'onSetCookie',

        // https://developer.wordpress.org/reference/hooks/clear_auth_cookie/
        // 'clear_auth_cookie' => 'onClearCookie',

        'before_signup_header' => 'onRegistration',
    ];

    public function onInit(): void
    {
        if (! $this->getPlugin()->isReady()) {
            return;
        }

        $session = $this->getSdk()->getCredentials();
        $wordpress = wp_get_current_user();

        if (! $this->getPlugin()->isEnabled()) {
            if ($session !== null) {
                $this->getSdk()->clear();
            }

            return;
        }

        // Paired sessions enforced
        if ($this->getPlugin()->getOption('authentication', 'pair_sessions', 0) !== 2) {
            // ... for all but admins?
            if ($this->getPlugin()->getOption('authentication', 'pair_sessions', 0) === 0 && is_admin()) {
                return;
            }

            // Is an Auth0 session available?
            if ($session === null) {
                // No; is there a WordPress session?
                if ($wordpress->ID !== 0) {
                    // There is. Invalidate the WP session.
                    wp_logout();
                }

                return;
            }

            // Is an WP session available?
            if ($wordpress->ID === 0) {
                // No; is there an Auth0 session?
                if ($session !== null) {
                    // There is. Invalidate the WP session.
                    $this->getSdk()->clear();
                }

                return;
            }

            // Verify the WordPress user signed in is linked to the Auth0 Connection 'sub'.
            if ($wordpress->ID !== 0) {
                $sub = $session->user['sub'] ?? null;

                if ($sub !== null) {
                    $match = $this->getAccountByConnection($sub);

                    if (! $match instanceof \WP_User || $match->ID !== $wordpress->ID) {
                        $this->getSdk()->clear();
                        wp_logout();
                        return;
                    }
                }
            }

            // Verify that the Auth0 token cookie has not expired
            if ($session->accessTokenExpired === true) {
                if ($this->getPlugin()->getOption('sessions', 'refresh_tokens') === 'true') {
                    try {
                        // Token has expired, attempt to refresh it.
                        $this->getSdk()->renew();
                        return;
                    } catch (StateException $e) {
                        // Refresh failed.
                    }

                    // Invalidation authentication state.
                    $this->getSdk()->clear();
                    wp_logout();
                    return;
                }
            }
        }

        if ($this->getPlugin()->getOption('authentication', 'rolling_sessions') !== 'false') {
            // TODO: Update PHP SDK rolling session state.
            // $this->getSdk()->renewSession();
            wp_set_auth_cookie($wordpress->ID, true);
        }
    }

    public function onAuthCookieValid(
        array $cookieElements,
        ?\WP_User $user = null
    ): void {
        // TODO: Refresh rolling session cookie.
        // var_dump($cookieElements);
        // exit;
    }

    public function onAuthCookieMalformed(
        string $cookie,
        ?string $scheme = null
    ): void {
        if ($cookie === '') {
            return;
        }

        $this->getSdk()->clear();
    }

    public function onAuthCookieExpired(
        array $cookieElements
    ): void {
        $this->getSdk()->clear();
    }

    public function onAuthCookieBadUsername(
        array $cookieElements
    ): void {
        $this->getSdk()->clear();
    }

    public function onAuthCookieBadSessionToken(
        array $cookieElements
    ): void {
        $this->getSdk()->clear();
    }

    public function onAuthCookieBadHash(
        array $cookieElements
    ): void {
        $this->getSdk()->clear();
    }

    public function onLogin(): void
    {
        if (! $this->getPlugin()->isReady() || ! $this->getPlugin()->isEnabled()) {
            return;
        }

        // Don't allow caching of this route
        nocache_headers();

        // Check if authentication flow parameters are present (?code and ?state)
        $exchange = $this->getSdk()->getExchangeParameters();

        // Check if authentication flow error parameter is present (?error)
        $error = $this->getSdk()->getRequestParameter('error');

        // Are token exchange parameters present?
        if ($exchange !== null) {
            try {
                // Attempt completion of the authentication flow using
                $success = $this->getSdk()->exchange(
                    code: sanitize_text_field($exchange->code),
                    state: sanitize_text_field($exchange->state)
                );
            } catch (\Throwable $th) {
                // Exchange failed; throw an error
                var_dump('ERROR', $th->getMessage());
                echo("<p><a href='/wp-login.php'>Again</a></p>");
                exit;
            }

            $session = $this->getSdk()->getCredentials();

            // Do we indeed have a session now?
            if ($session !== null) {
                $sub = sanitize_text_field($session->user['sub'] ?? null);
                $email = sanitize_email($session->user['email'] ?? '');
                $verified = $session->user['email_verified'] ?? null;

                if (strlen($email) === 0) {
                    $email = null;
                    $verified = null;
                }

                $user = $this->resolveIdentity(
                    sub: $sub,
                    email: $email,
                    verified: $verified,
                );

                if ($user) {
                    if ($sub !== null) {
                        $this->addAccountConnection($user, $sub);
                    }

                    if ($email !== null && $verified === true && $email !== $user->user_email) {
                        $this->setAccountEmail($user, $email);
                    }

                    wp_set_current_user($user->ID);
                    wp_set_auth_cookie($user->ID, true);
                    do_action('wp_login', $user->user_login, $user);
                    wp_redirect("/");
                    exit;
                }

                // TODO: Display an error here. No matches and could not create account, or account creating was disabled.
            }
        }

        if ($exchange === null && $error !== null) {
            var_dump('ERROR', $error);
            echo("<p<a href='/wp-login.php'>Again</a></p>");
            exit;
        }

        if ($exchange === null && $error === null) {
            if (wp_get_current_user()->ID !== 0 || $this->getSdk()->getCredentials() !== null) {
                wp_redirect("/");
                exit;
            }
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
        var_dump("TEST");
        exit;
    }

    public function onLoginComplete(
        string $user_login,
        \WP_User $user
    ): void {
        var_dump($user_login);
        var_dump($user);
        exit;
    }

    private function resolveIdentity(
        ?string $sub = null,
        ?string $email = null,
        ?bool $verified = null,
    ): ?\WP_User {
        $email = sanitize_email(filter_var($email, FILTER_SANITIZE_EMAIL, FILTER_NULL_ON_FAILURE));

        if ($sub !== null) {
            $sub = sanitize_text_field($sub);
            $found = $this->getAccountByConnection($sub);

            if ($found instanceof \WP_User) {
                return $found;
            }
        }

        // If an email is not marked as verified by the connection, dismiss it.
        if ($verified !== true || ! is_string($email) || strlen($email) === 0) {
            $email = null;
        }

        if ($email !== null) {
            $found = get_user_by('email', $email);

            if ($found instanceof \WP_User) {
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
            }
        }

        if ($this->getPlugin()->getOption('accounts', 'missing') === 'create') {
            $username = ($email !== null) ? explode('@', $email, 2)[0] : explode('|', $sub ?? '', 2)[1];
            $user = wp_create_user($username, wp_generate_password(rand(12, 64), true, true), $email ?? '');

            if (! $user instanceof \WP_Error) {
                $user = get_user_by('ID', $user);
                $role = $this->getPlugin()->getOption('accounts', 'default_role', get_option('default_role'));

                if ($user->role !== $role) {
                    $user->set_role($role);
                    wp_update_user($user);
                }

                return $user;
            }
        }

        return null;
    }

    private function addAccountConnection(
        \WP_User $user,
        string $sub
    ): void {
        $connections = get_user_meta($user->ID, 'auth0_connections', true);

        if (! is_array($connections)) {
            $connections = [];
        }

        if (! in_array($sub, $connections, true)) {
            $connections[] = $sub;
            update_user_meta($user->ID, 'auth0_connections', array_values(array_unique($connections)));
        }
    }

    private function setAccountEmail(
        \WP_User $user,
        string $email
    ): ?\WP_User {
        if ($user->user_email !== $email) {
            $user->user_email = $email;
            $status = wp_update_user($user);

            if ($status instanceof \WP_Error) {
                return null;
            }
        }

        return $user;
    }

    private function getAccountByConnection(
        string $connection
    ): ?\WP_User {
        $query = [
            ['key' => 'auth0_connections', 'value' => '"' . $connection . '"', 'compare' => 'LIKE'],
        ];
        $found = get_users(['number' => 1, 'meta_query' => $query]);

        if (is_array($found) && count($found) >= 1) {
            return $found[0];
        }

        return null;
    }
}
