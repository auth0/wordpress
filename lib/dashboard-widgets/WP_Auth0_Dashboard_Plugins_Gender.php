<?php

class WP_Auth0_Dashboard_Plugins_Gender implements WP_Auth0_Dashboard_Plugins_Interface {

	/*
		this handles:
			facebook gender field
	*/

	public function getId() {
		return 'auth0_dashboard_widget_gender';
	}

	public function getName() {
		return 'Auth0 - User\'s Gender';
	}

	protected $users = array();

	public function __construct($users) {

		$this->users = $users;

	}

	protected function processData() {
		$data = array('unknown' => 0);

		foreach ($this->users as $user) {
			if (isset($user->gender)) {
				if (!isset($data[$user->gender])) {
					$data[$user->gender] = 0;
				}
				$data[$user->gender] ++;
			}
			else {
				$data['unknown'] ++;
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
		<div id="auth0ChartGender"></div>
		<script type="text/javascript">
			var chart = c3.generate({
				bindto: '#auth0ChartGender',
			    data: {
			        columns: <?php echo json_encode($chartData); ?>,
			        type : 'pie'
			    }
			});
		</script>	
		<?php
	}

}