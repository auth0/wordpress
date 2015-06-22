<?php

class WP_Auth0_Dashboard_Plugins_Age implements WP_Auth0_Dashboard_Plugins_Interface {
	
	public function getId() {
		return 'auth0_dashboard_widget_age';
	}

	public function getName() {
		return 'Auth0 Age Chart';
	}

	protected $users = array();

	public function __construct($users) {
		$this->users = $users;
	}

	protected function getAge($user){
		if (isset($user->age_range)) {
			return $user->age_range->min;
		}

		return 'unknown';
	}

	protected function processData() {
		foreach ($this->users as $user) {
			$age = $this->getAge($user);

			if (!isset($data[$age])) $data[$age] = 0;

			$data[$age] ++;
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
		<div id="auth0ChartAge"></div>
		<script type="text/javascript">
			var chart = c3.generate({
				bindto: '#auth0ChartAge',
			    data: {
			        columns: <?php echo json_encode($chartData); ?>,
			        type : 'pie'
			    }
			});
		</script>	
		<?php

	}

}