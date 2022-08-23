<?php

declare(strict_types=1);

namespace Auth0\WordPress\Actions;

use Auth0\SDK\Configuration\SdkConfiguration;
use Auth0\WordPress\Http\Factory;

final class Authentication extends Base
{
    public function handle(): void {
        if (is_user_logged_in()) {
            wp_redirect(admin_url());
        }

        $sdkConfiguration = new SdkConfiguration(
            httpRequestFactory: Factory::getRequestFactory(),
            httpResponseFactory: Factory::getResponseFactory(),
            httpStreamFactory: Factory::getStreamFactory(),
            httpClient: Factory::getClient()
        );

        wpAuth0()->setConfiguration($sdkConfiguration);
        $auth0 = wpAuth0()->getSdk();

        $location = $auth0->login();

        wp_redirect($location);
        exit;
    }
}
