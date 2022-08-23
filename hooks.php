<?php

declare(strict_types=1);

namespace Auth0\WordPress\Actions;

/**
 * Fires when the login form is initialized.
 *
 * @link https://developer.wordpress.org/reference/hooks/login_init/
 */
wpAuth0()->actions()->add(
  hook: 'login_init',
  class: Authentication::class,
  priority: defined('AUTH0_ACTION_PRIORITY_LOGIN_INIT') ? (int) constant('AUTH0_ACTION_PRIORITY_LOGIN_INIT') : 10
);
