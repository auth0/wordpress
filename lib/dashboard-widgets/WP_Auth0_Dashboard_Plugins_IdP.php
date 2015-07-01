<?php

class WP_Auth0_Dashboard_Plugins_IdP {

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

		if (empty($data)) {
            echo "No data available";
            return;
        }

		$chartData = array();

		foreach ($data as $key => $value) {
			$chartData[] = array($key, $value);
		}

		?>
		<div id="auth0ChartIdP"></div>
		<script type="text/javascript">
			(function(){
				var chart = c3.generate({
					bindto: '#auth0ChartIdP',
				    data: {
				        columns: <?php echo json_encode($chartData); ?>,
				        type : 'pie'
				    },
					color: {
					  pattern: <?php echo json_encode($this->getColors($chartData)); ?>
					}
				});
			})();
		</script>
		<?php

	}

	protected function getColors($data) {
		$unknownColor = '#CACACA';
		$palete = array('#F39C12','#2ECC71','#3498DB','#9B59B6','#34495E','#F1C40F','#E67E22','#E74C3C', '#1ABC9C');
		$colorIndex = 0;
		$colors = array();
		$paleteLength = count($palete);

		foreach($data as $category) {
			$ipdColor = $this->getColor($category[0]);

			$colors[] = ($ipdColor ? $ipdColor : $palete[($colorIndex++) % $paleteLength]);
		}

		return $colors;
	}

	protected function getColor($name) {
		$name = explode('-', $name);
		$name = $name[0];

		$colors = array(
			'amazon' => '#ff9900',
			'auth0' => '#16214D',
			'google' => '#dd4b39',
			'facebook' => '#3b5998',
			'windowslive' => '#00bcf2',
			'linkedin' => '#0077b5',
			'github' => '#999999',
			'paypal' => '#003087',
			'twitter' => '#55acee',
			'yandex' => '#ffcc00',
			'yahoo' => '#400191',
			'box' => '#447EC3',
			'salesforce' => '#1798c1',
			'fitbit' => '#4cc2c4',
			'baidu' => '#de0f17',
			'aol' => '#ff0b00',
			'shopify' => '#96bf48',
			'wordpress' => '#21759b',
			'soundcloud' => '#ff8800',
			'instagram' => '#3f729b',
			'evernote' => '#7ac142',
		);

		return isset($colors[$name]) ? $colors[$name] : null;
	}

}
