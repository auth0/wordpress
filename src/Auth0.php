<?php

namespace Auth0\WordPress;

use Auth0\SDK\Auth0 as Sdk;
use Auth0\SDK\Configuration\SdkConfiguration as Configuration;

final class Plugin
{
    private ?Sdk $sdk = null;

    private ?Configuration $configuration = null;

    public function __construct(
        ?Sdk $sdk,
        ?Configuration $configuration,
    ) {
        $this->sdk = $sdk;
        $this->configuration = $configuration;
    }

    /**
     * Returns a singleton instance of the Auth0 SDK.
     */
    public function getSdk(): Sdk
    {
        $this->sdk = new Sdk($this->getConfiguration());
        return $this->sdk;
    }

    /**
     * Assign a Auth0\SDK\Auth0 instance for the plugin to use.
     */
    public function setSdk(
        Sdk $sdk
    ): self {
        $this->sdk = $sdk;
        return $this;
    }

    /**
     * Returns a singleton instance of SdkConfiguration.
     */
    public function getConfiguration(): Configuration
    {
        $this->configuration ??= $this->importConfiguration();
        return $this->configuration;
    }

    /**
     * Assign a Auth0\SDK\Configuration\SdkConfiguration instance for the plugin to use.
     */
    public function setConfiguration(
        Configuration $configuration
    ): self {
        $this->configuration = $configuration;
        return $this;
    }

    /**
     * Main plugin functionality.
     */
    public function run(): self
    {
        // TODO: Begin plugin functionality.

        return $this;
    }

    /**
     * Import configuration settings from database.
     */
    private function importConfiguration(): Configuration
    {
        // TODO: Import settings from WP database.

        return new Configuration();
    }
}
