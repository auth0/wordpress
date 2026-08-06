# How to upgrade

## 5.x to 6.x

The 6.0 release moves the plugin onto [auth0-php v9](https://github.com/auth0/auth0-php/tree/v9), which rewrites the Management API. The authentication surface (login, logout, callback, session handling) is unchanged, so most sites only need the environment updates below.

Check that your environment is compatible with 6.0's requirements before upgrading:

| | 5.x | 6.x |
|---|---|---|
| **PHP** | `^8.1` | `^8.2` |
| **auth0/auth0-php** | `^8.19` | `^9.0` |

- Please ensure you are running PHP 8.2 or newer. Support for PHP 8.1 has been dropped.
- The plugin bundles its dependencies, so updating the plugin pulls in `auth0/auth0-php` v9 automatically. No package rename is required; the package remains `auth0/auth0-php`.

Update your customizations, if necessary:

- **No changes are required for the plugin's built-in features.** Login, logout, the callback handler, and user session handling continue to work as they did in 5.x. The settings stored in the WordPress admin are unchanged.
- **User Sync now retries a failed event instead of always discarding it.** In 5.x a sync event that failed at the Management API was dropped from the queue regardless of the reason. In 6.x, a transient failure (an HTTP 429 rate limit, a 5xx server error, or a network error) keeps the event in the queue so the next cron pass retries it. Permanent failures (such as an HTTP 400 from an invalid profile) are still dropped, since retrying them would never succeed. No configuration change is needed, but a queue that previously drained to empty on every pass may now hold a retrying event until it succeeds.
- **Custom code that calls the Management API has changed.** In v9 the old `wpAuth0()->getSdk()->management()` entry point is non-functional and will throw a `TypeError`. Use the new `wpAuth0()->getManagement()` accessor instead. It builds a Management client from the Domain, Client ID, and Client Secret you already configure in the plugin settings, and fetches and caches a client credentials token for you automatically.

  ```php
  // 5.x
  $management = wpAuth0()->getSdk()->management();
  $response = $management->users()->getAll(['per_page' => 25]);
  $users = HttpResponse::decodeContent($response);

  // 6.x
  use Auth0\SDK\API\Management\Users\Requests\ListUsersRequestParameters;

  $management = wpAuth0()->getManagement();
  $users = $management->users->list(
      new ListUsersRequestParameters(['perPage' => 25, 'includeTotals' => true])
  );
  foreach ($users as $user) {
      echo $user->getEmail();
  }
  ```

  Inside a plugin action or filter class (anything extending `Auth0\WordPress\Actions\Base` or `Auth0\WordPress\Filters\Base`), the same client is available as `$this->getManagement()`.

- If your custom code calls the Management API directly, review the [auth0-php v9 migration guide](https://github.com/auth0/auth0-php/blob/v9/v9_MIGRATION_GUIDE.md) for the full set of changes. The most common adjustments are:
  - Sub-resources are reached by property access, not method calls: `->users->list()` rather than `->users()->getAll()`.
  - `users->list()` returns a `Pager` you iterate with `foreach`. It is a lazy iterator that makes further requests as you consume it, so `count()` and array access will not behave the way a plain array would.
  - Responses are typed objects instead of raw PSR-7 responses. Call `$response->jsonSerialize()` to get the same snake_case array the v8 `HttpResponse::decodeContent()` returned, or use the typed getters (`$response->getEmail()`).
  - Errors throw exceptions. A non-2xx response raises `Auth0\SDK\API\Management\Exceptions\Auth0ApiException` (use `getCode()` for the HTTP status), and transport-level failures raise `Auth0\SDK\API\Management\Exceptions\Auth0Exception`. There is no more `HttpResponse::wasSuccessful()` check.
  - Request parameters are camelCase typed objects. For example, creating a user takes a `CreateUserRequestContent` whose keys are `givenName` and `familyName` (not `given_name` / `family_name`), and the `connection` is set inside that object rather than passed as a separate argument.
  - The `includeTotals` parameter only controls whether the response envelope carries the count fields (`total`, `start`, `limit`, `length`). It defaults to `true` and does not affect whether users are returned, so you get results either way.
  - Some ticket methods were renamed: `tickets()->createPasswordChange()` is now `tickets->changePassword()`, and `tickets()->createEmailVerification()` is now `tickets->verifyEmail()`. The user id moves inside the request object as `userId`.
