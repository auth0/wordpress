<?php

declare(strict_types=1);

namespace Auth0\WordPress\Actions;

use Auth0\WordPress\Http\Factory;
use Auth0\WordPress\Plugin;

final class Authentication
{
    private ?Plugin $plugin = null;

    public function __construct(
        ?Plugin $plugin,
    ) {
        $this->plugin = $plugin;
    }

    public function handle() {
        if (is_user_logged_in()) {
            wp_redirect(admin_url());
        }

        $configuration = new \Auth0\SDK\Configuration\SdkConfiguration(
            httpRequestFactory: Factory::getRequestFactory(),
            httpResponseFactory: Factory::getResponseFactory(),
            httpStreamFactory: Factory::getStreamFactory(),
            httpClient: Factory::getClient()
        );

        $config = wpAuth0()->setConfiguration($configuration);
        $sdk = wpAuth0()->getSdk();

        $location = $sdk->login();

        wp_redirect($location);
        exit;
    }
}
