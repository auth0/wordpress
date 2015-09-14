<?php

class WP_Auth0_Dashboard_Widgets  {

	protected $db_manager;
	protected $dashboard_options;

	const UNKNOWN_KEY = 'unknown';

	public function __construct(WP_Auth0_Dashboard_Options $dashboard_options, WP_Auth0_DBManager $db_manager) {
		$this->db_manager = $db_manager;
		$this->dashboard_options = $dashboard_options;
	}

	public function init() {
		add_action( 'wp_dashboard_setup', array( $this, 'set_up' ) );
	  add_action( 'admin_footer', array($this,'render') );
	}

	protected function get_buckets($from, $to, $step) {

		$buckets = array();

		$buckets[] = array(
			'from' => 0,
			'to' => $from - 1,
			'name' => $step == 1 ? ($from - 1) : ('< ' . ($from - 1)),
		);

		for ($a = $from; $a < $to; $a += $step) {
			$buckets[] = array(
				'from' => $a,
				'to' => $a + $step - 1,
				'name' => $step == 1 ? $a : ($a . '-' . ($a + $step - 1)),
			);
		}

		$buckets[] = array(
			'from' => $a,
			'to' => 200,
			'name' => $step == 1 ? $a : ('>= ' . $a),
		);

		return $buckets;

	}

	public function render() {
		global $current_user;

		if ( ! in_array( 'administrator', $current_user->roles ) ) {
			return;
		}

    $screen = get_current_screen();
		if ($screen->id !== 'dashboard') {
			return;
		}

		$users = $this->db_manager->get_auth0_users();

		$this->buckets = $this->get_buckets(
			$this->dashboard_options->get('chart_age_from'),
			$this->dashboard_options->get('chart_age_to'),
			$this->dashboard_options->get('chart_age_step')
		);

		$usersData = array();

		foreach ($users as $user) {
			$userObj = new WP_Auth0_UserProfile($user->auth0_obj);
			$userData = $userObj->get();

			if ( ! $userData['age'] ) {
				$userData['age'] = self::UNKNOWN_KEY;
			} else {
				foreach($this->buckets as $bucket) {
					if ($userData['age'] >= $bucket['from'] && $userData['age'] <= $bucket['to']) {
						$userData['agebucket'] = $bucket['name'];
					}
				}
			}

			$usersData[] = $userData;
		}

		?>
		<script type="text/javascript">
			var users_data = <?php echo json_encode($usersData); ?>;
			var charts = [];

			function filter_callback(chart, callback) {

				if (callback === null) {
					var data = users_data;
				} else {
					var data = users_data.filter(callback);
				}

				charts.forEach(function(c){
					if (c.name !== chart.name) {
						c.load(data);
					}
				});

			}

			if (typeof(a0_age_chart) !== 'undefined') {
				charts.push(new a0_age_chart(users_data, filter_callback));
			}
			if (typeof(a0_gender_chart) !== 'undefined') {
				charts.push(new a0_gender_chart(users_data, filter_callback));
			}
			if (typeof(a0_idp_chart) !== 'undefined') {
				charts.push(new a0_idp_chart(users_data, filter_callback));
			}
			if (typeof(a0_location_chart) !== 'undefined') {
				charts.push(new a0_location_chart(users_data));
			}
		</script>

		<?php

	}

	public function set_up() {
		global $current_user;

		if ( ! in_array( 'administrator', $current_user->roles ) ) {
			return;
		}

		wp_enqueue_style( 'auth0-dashboard-c3-css', trailingslashit( plugin_dir_url( WPA0_PLUGIN_FILE ) ) . 'assets/lib/c3/c3.min.css' );
		wp_enqueue_style( 'auth0-dashboard-css', trailingslashit( plugin_dir_url( WPA0_PLUGIN_FILE ) ) . 'assets/css/dashboard.css' );

		wp_enqueue_script( 'auth0-dashboard-d3', trailingslashit( plugin_dir_url( WPA0_PLUGIN_FILE ) ) . 'assets/lib/d3/d3.min.js' );
		wp_enqueue_script( 'auth0-dashboard-c3-js', trailingslashit( plugin_dir_url( WPA0_PLUGIN_FILE ) ) . 'assets/lib/c3/c3.min.js' );
		wp_enqueue_script( 'auth0-markerclusterer', trailingslashit( plugin_dir_url( WPA0_PLUGIN_FILE ) ) . 'assets/lib/markerclusterer.js' );
		wp_enqueue_script( 'auth0-lodash', trailingslashit( plugin_dir_url( WPA0_PLUGIN_FILE ) ) . 'assets/lib/lodash.min.js' );


		$widgets = array(
			new WP_Auth0_Dashboard_Plugins_Age($this->dashboard_options),
			new WP_Auth0_Dashboard_Plugins_Gender($this->dashboard_options),
			new WP_Auth0_Dashboard_Plugins_IdP($this->dashboard_options),
			new WP_Auth0_Dashboard_Plugins_Location(),
			new WP_Auth0_Dashboard_Plugins_Income(),
			new WP_Auth0_Dashboard_Plugins_Signups(),
		);

		foreach ( $widgets as $widget ) {
			wp_add_dashboard_widget( $widget->getId(), $widget->getName(), array( $widget, 'render' ) );
		}
	}

}
