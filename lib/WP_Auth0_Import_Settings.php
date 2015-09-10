<?php

class WP_Auth0_Import_Settings {

  protected $a0_options;

  public function __construct(WP_Auth0_Options $a0_options) {
    $this->a0_options = $a0_options;
  }

  public function init() {
    add_action( 'admin_action_wpauth0_export_settings', array($this, 'export_settings') );
    add_action( 'admin_action_wpauth0_import_settings', array($this, 'import_settings') );
  }

  public function render_import_settings_page() {

    include WPA0_PLUGIN_DIR . 'templates/import_settings.php';

  }

  public function import_settings() {
    $settings_json = stripslashes($_POST['settings-json']);
    $settings = json_decode($settings_json, true);

    foreach ($settings as $key => $value) {
      $this->a0_options->set($key, $value);
    }

    exit( wp_redirect( admin_url( 'admin.php?page=wpa0' ) ) );

  }

  public function export_settings() {
		header('Content-Type: application/json');
		$name = urlencode(get_bloginfo('name'));
		header("Content-Disposition: attachment; filename=auth0_for_wordpress_settings-$name.json");
		header('Pragma: no-cache');


		$settings = $this->a0_options->get_options();
		echo json_encode($settings);
		exit;
	}

}
