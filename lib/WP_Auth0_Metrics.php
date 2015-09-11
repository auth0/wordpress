<?php

class WP_Auth0_Metrics {

  protected $a0_options;

  public function __construct(WP_Auth0_Options $a0_options) {
    $this->a0_options = $a0_options;
  }

  public function init() {
    add_action( 'admin_footer', array($this,'render') );
  }

  public function render() {

    $enabled_pages = array('wpa0', 'wpa0-setup', 'users', 'wpa0-users-export');
    $screen = get_current_screen();

    if ( (! isset( $_REQUEST['page'] ) && empty($screen) )
          || (isset( $_REQUEST['page'] ) && !in_array( $_REQUEST['page'], $enabled_pages ) && !in_array( $screen->id, $enabled_pages ) )
    ) {
			return;
		}

    if ($this->a0_options->get('metrics') == 1) {
    ?>
      <script src="//cdn.auth0.com/js/m/metrics-1.min.js"></script>
      <script>
        var a0metricsLib = new Auth0Metrics("auth0-for-wordpress", "http://auth0-metrics-server.herokuapp.com/dwh-endpoint", "wp-plugin");
      </script>
    <?php
      //a0metricsLib.track(event, data);
    }
  }

}
