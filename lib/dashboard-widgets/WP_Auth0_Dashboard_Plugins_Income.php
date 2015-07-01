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

	protected function processData() {
		$data = array();

		foreach ($this->users as $user) {

			if (isset($user->app_metadata) && isset($user->app_metadata->geoip)) {
				if (isset($user->app_metadata->geoip->postal_code)){
					$postal_code = $user->app_metadata->geoip->postal_code;
					$country_name = $user->app_metadata->geoip->country_name;
					$country_code = $user->app_metadata->geoip->country_code;

					$key = "$country_name - $postal_code";

					if (!isset($data[$key])) $data[$key] = array('postal_code' => $postal_code, 'country' => $country_name, 'country_code' => $country_code, 'count' => 0);

					$data[$key]['count']++;
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

		$chartData = array_values($data);

		$jsonData = json_encode($chartData);
		?>
		<div id="auth0ChartIncome"></div>
		<span class="auth0Note">Income is shown in hundreds of dollars.</span>

		<script type="text/javascript">

		(function(){

			var data = <?php echo $jsonData; ?>;

			jQuery.ajax({
				    	url:'http://assets.auth0.com/zip-income/agizip.json',
				    	dataType:'json',
				    	success:function(incomes){
				    		loadChart(data, incomes);
					    }
				    })

			function loadChart(data, incomes) {

				var x_arr = ['x'];
				var zipcodes_arr = ['Zipcodes'];
				var incomes_arr = ['Incomes'];

				data.forEach(function(d){
					zipcodes_arr.push(d.count)
					incomes_arr.push( (d.postal_code && d.country_code == 'US' && incomes[d.postal_code.toString()]) ? incomes[d.postal_code.toString()]/100000 : 0)
					x_arr.push(d.country + ' - ' +d.postal_code)
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
				            ratio: 0.5
				        }
				    },
				     axis: {
				        x: {
				            type: 'category'
				        }
				    }
				});
			}

		})();
		</script>

		<?php

	}

}
