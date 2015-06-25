<?php

class WP_Auth0_Dashboard_Plugins_Age implements WP_Auth0_Dashboard_Plugins_Interface {

	public function getId() {
		return 'auth0_dashboard_widget_age';
	}

	public function getName() {
		return 'Auth0 - User\'s Age';
	}

	protected $users = array();

	public function __construct($users) {
		$this->users = $users;
	}

	protected function getAge($user){
		if (isset($user->age)) {
			return $user->age;
		}
		if (isset($user->dateOfBirth)) {

			$birthDate = explode("-", $user->birthday);

			$age = (date("md", date("U", mktime(0, 0, 0, $birthDate[3], $birthDate[1], $birthDate[0]))) > date("md")
				? ((date("Y") - $birthDate[0]) - 1)
				: (date("Y") - $birthDate[0]));
			return $age;
		}
		if (isset($user->birthday)) {

			$birthDate = explode("/", $user->birthday);

			$age = (date("md", date("U", mktime(0, 0, 0, $birthDate[0], $birthDate[1], $birthDate[2]))) > date("md")
				? ((date("Y") - $birthDate[2]) - 1)
				: (date("Y") - $birthDate[2]));
			return $age;
		}

		return 'unknown';
	}

	protected function processData() {
		$data = array();

		foreach ($this->users as $user) {
			$age = $this->getAge($user);

			if (!isset($data[$age])) $data[$age] = 0;

			$data[$age] ++;
		}
		return $data;
	}

	public function render() {

		$data = $this->processData();

		if (empty($data)) {
            echo "No data available";
            return;
        }

		$chartData = array();

		foreach ($data as $key => $value) {
			$chartData[] = array($key, $value);
		}

		?>
		<div id="auth0ChartAge"></div>
		<script type="text/javascript">
			(function(){
				var chart = c3.generate({
					bindto: '#auth0ChartAge',
				    data: {
				        columns: <?php echo json_encode($chartData); ?>,
				        type : 'pie'
				    }
				});
			})();
		</script>
		<?php

	}

}
