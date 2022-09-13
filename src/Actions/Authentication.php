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

        if (! $this->getPlugin()->isEnabled()) {
            return;
        }

        if (is_admin()) {
            return;
        }

        $session = $this->getSdk()->getCredentials();

        // Check if an Auth0 token cookie is available
        if ($session === null) {
            // We have no Auth0 session; reset WP session cookie
            wp_logout();
            return;
        }

        // Verify that the Auth0 token cookie has not expired
        if ($session->accessTokenExpired) {
            try {
                // Token has expired, attempt to renew it.
                $this->getSdk()->renew();
            } catch (StateException $e) {
                // Renewal failed; reset authentication state
                $this->getSdk()->clear();
                wp_logout();
                return;
            }
        }

        // TODO: Verify that user is bound to the sub of the Auth0 token
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
        if ($exchange) {
            // Make sure we don't already have a session
            if ($this->getSdk()->getCredentials() === null) {
                try {
                    // Attempt completion of the authentication flow using
                    $this->getSdk()->exchange(
                        code: sanitize_text_field($exchange->code),
                        state: sanitize_text_field($exchange->state)
                    );
                } catch (\Throwable $th) {
                    // Exchange failed; throw an error
                    var_dump('ERROR', $th->getMessage());
                    echo("<p><a href='/wp-login.php'>Again</a></p>");
                    exit;
                }
            }

            // Do we indeed have a session now?
            if ($this->getSdk()->getCredentials() !== null) {
                // Redirect with 308 to remove exchange parameters from browser history
                wp_redirect(get_site_url(null, 'wp-login.php'), 308);
                exit;
            }
        }

        if (! $exchange && $error) {
            var_dump('ERROR', $error);
            echo("<p<a href='/wp-login.php'>Again</a></p>");
            exit;
        }

        if (! $exchange && ! $error && $this->getSdk()->getCredentials() !== null) {
            $user = $this->resolveIdentity(
                sub: $this->getSdk()->getCredentials()?->user['sub'] ?? null,
                email: $this->getSdk()->getCredentials()?->user['email'] ?? null,
                emailVerified: $this->getSdk()->getCredentials()?->user['email_verified'] ?? null,
            );

            if ($user) {
                wp_set_current_user($user->ID);
                wp_set_auth_cookie($user->ID);
                do_action('wp_login', $user->user_login, $user);
                wp_redirect("/");
                exit;
            }

            return;
        }

        if (! $exchange && ! $error && wp_get_current_user()->ID !== 0) {
            wp_logout();
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
        ?bool $emailVerified = null,
    ): ?\WP_User {
        if ($sub !== null) {
            $sub = sanitize_text_field($sub);

            // Search by Auth0 ID in metadata.
        }

        if ($email !== null && $emailVerified === true) {
            $email = sanitize_email($email);

            // Check if an account matches the email address.
            $found = get_user_by('email', $email);
        }

        if ($found) {
            return $found;
        }

        // wp_create_user()

        exit;
        // get_user_by('email', )
        // // $users = new WP_User_Query(array(
        // //     's' => $yoursearchquery,
        // //     'meta_query' => array(
        // //         'relation' => 'OR',
        // //         array(
        // //             'key' => 'billing_last_name',
        // //             'value' => $yoursearchquery,
        // //             'compare' => 'LIKE'
        // //         ),
        // //         array(
        // //             'key' => 'billing_first_name',
        // //             'value' => $yoursearchquery,
        // //             'compare' => 'LIKE'
        // //         )
        // //     )
        // // ));

        // $matches = new WP_User_Query();

        // if ( ! empty( $user_query->get_results() ) ) {
        //     foreach ( $user_query->get_results() as $user ) {
        //         echo '<p>' . $user->display_name . '</p>';
        //     }
        // } else {
        //     echo 'No users found.';
        // }

        // add_user_meta();
        // update_user_meta();
        // delete_user_meta();
        // WP_User_Query

        // wp_set_current_user();
    }
}
