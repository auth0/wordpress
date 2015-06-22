<?php

class WP_Auth0_Dashboard_Plugins_IdP implements WP_Auth0_Dashboard_Plugins_Interface {
	
	public function getId() {
		return 'auth0_dashboard_widget_idp';
	}

	public function getName() {
		return 'Auth0 - Identity Providers';
	}

	protected $users = array();

	public function __construct($users) {

		$this->users = $users;

	}

	protected function processData() {
		$data = array();
		foreach ($this->users as $user) {
			foreach ($user->identities as $identity) {

				if (!isset($data[$identity->provider])) $data[$identity->provider] = 0;
				$data[$identity->provider] ++;

			}
		}
		return $data;
	}

	public function render() {
		$data = $this->processData();
		$chartData = array();

		foreach ($data as $key => $value) {
			$chartData[] = array($key, $value);
		}

		?>
		<div id="auth0ChartIdP"></div>
		<script type="text/javascript">
			var chart = c3.generate({
				bindto: '#auth0ChartIdP',
			    data: {
			        columns: <?php echo json_encode($chartData); ?>,
			        type : 'pie'
			    }
			});
		</script>	
		<?php

	}

}