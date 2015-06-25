<?php

class WP_Auth0_Dashboard_Plugins_Income implements WP_Auth0_Dashboard_Plugins_Interface {
	
	public function getId() {
		return 'auth0_dashboard_widget_income';
	}

	public function getName() {
		return 'Auth0 - User\'s Income';
	}

	protected $users = array();

	public function __construct($users) {
		$this->users = $users;
	}

	protected function getAge($user){
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
			
			if (isset($user->app_metadata) && isset($user->app_metadata->geoip)) {
				if (isset($user->app_metadata->geoip->postal_code)){
					$postal_code = $user->app_metadata->geoip->postal_code;
					
					if (!isset($data[$postal_code])) $data[$postal_code] = 0;

					$data[$postal_code]++;
				}
			}

		}

		return $data;
	}

	public function render() {

		$data = $this->processData();

		if (empty($data)) {
			echo "No income data available";
			return;
		}

		$chartData = array();

		foreach ($data as $key => $value) {
			$chartData[] = array('postal_code' => $key, 'count' => $value);
		}

		$jsonData = json_encode($chartData);
		?>
		<div id="auth0ChartIncome"></div>

		<script type="text/javascript">

		var data = <?php echo $jsonData; ?>;

		jQuery.ajax({
			    	url:'<?php echo trailingslashit(plugin_dir_url(WPA0_PLUGIN_FILE) ) . "assets/data/agizip.json"; ?>',
			    	dataType:'json',
			    	success:function(incomes){
			    		loadChart(data, incomes);
				    }
			    })

		function loadChart(data, incomes) {

			var zipcodes_arr = ['x'];
			var incomes_arr = ['Zipcodes'];
			var x_arr = ['Incomes'];

			data.forEach(function(d){
				zipcodes_arr.push(d.count)
				incomes_arr.push( incomes[d.postal_code] ? incomes[d.postal_code] : 0)
				x_arr.push(d.postal_code)
			});

			var chart = c3.generate({
				bindto: '#auth0ChartIncome',
			    data: {
			    	x:'x',
			        columns: [
			        	x_arr,
			            zipcodes_arr,
			            incomes_arr
			        ],
			        type: 'bar'
			    },
			    bar: {
			        width: {
			            ratio: 0.5 // this makes bar width 50% of length between ticks
			        }
			    },
			     axis: {
			        x: {
			            type: 'category' // this needed to load string x value
			        }
			    }
			});
		}
		</script>
		
		<?php

	}

}