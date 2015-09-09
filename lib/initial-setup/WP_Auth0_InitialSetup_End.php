<?php

class WP_Auth0_InitialSetup_End {

    protected $a0_options;

    public function __construct(WP_Auth0_Options $a0_options) {
        $this->a0_options = $a0_options;
    }

    public function render($step) {
      include WPA0_PLUGIN_DIR . 'templates/initial-setup/end.php';
    }

    public function callback() {
    }

  }
