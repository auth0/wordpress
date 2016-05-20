<?php

class WP_Auth0_Metrics {

	protected $a0_options;

	public function __construct( WP_Auth0_Options $a0_options ) {
		$this->a0_options = $a0_options;
	}

	public function init() {
		add_action( 'admin_footer', array( $this, 'render' ) );
	}

	public function render() {

		$enabled_pages = array( 'wpa0', 'wpa0-setup', 'users', 'wpa0-users-export' );
		$screen = get_current_screen();

		if ( ( ! isset( $_REQUEST['page'] ) && empty( $screen ) )
			|| ( isset( $_REQUEST['page'] ) && !in_array( $_REQUEST['page'], $enabled_pages ) && !empty( $screen ) && !in_array( $screen->id, $enabled_pages ) )
		) {
			return;
		}

		$domain = $this->a0_options->get( 'domain' );
		$parts = explode( '.', $domain );

		$tenant = $parts[0];

		if ( strpos( $domain, 'au.auth0.com' ) !== false ) {
			$tenant .= '@au';
		}
		elseif ( strpos( $domain, 'eu.auth0.com' ) !== false ) {
			$tenant .= '@eu';
		}
		elseif ( strpos( $domain, 'auth0.com' ) !== false ) {
			$tenant .= '@us';
		}

		if ( $this->a0_options->get( 'metrics' ) == 1 ) {
?>
      <script src="//cdn.auth0.com/js/m/metrics-1.min.js"></script>
      <script>
        var a0metricsLib = new Auth0Metrics("auth0-for-wordpress", "https://dwh-tracking.it.auth0.com/dwh-metrics", "wp-plugin");
        function metricsTrack(event, trackData, callback) {
          if (typeof(a0metricsLib) === 'undefined') {
            return;
          }

          if (typeof(trackData) === 'function') {
            callback = trackData;
            trackData = null;
          }

          var params = {
            tenant:"<?php echo $tenant; ?>"
          };

          if (trackData) {
            params.trackData = trackData;
          }

          a0metricsLib.track(event, params, callback);
        }
      </script>
    <?php
		}
	}

}
