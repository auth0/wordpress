<?php

class WP_Auth0_Dashboard_Plugins_Location implements WP_Auth0_Dashboard_Plugins_Interface {

	/*
		this handles:
			linkedin location field
			facebook location field
	*/

	public function getId() {
		return 'auth0_dashboard_widget_Location';
	}

	public function getName() {
		return 'Auth0 - User\'s Location';
	}

	protected $users = array();

	public function __construct($users) {
		wp_enqueue_script( 'auth0-dashboard-gmaps-js', 'https://maps.googleapis.com/maps/api/js' );

		$this->users = $users;
	}

	protected function processData() {
		$data = array();

		foreach ($this->users as $user) {
			
			if (isset($user->location) && $user->location instanceof stdClass) {

				if(isset($user->location->name)) {
					$data[] = $user->location->name;
				}

			}
			elseif(isset($user->location))
			{
				$data[] = $user->location;
			}
		}

		return $data;
	}

	public function render() {
		$data = $this->processData();

		?>
		<div id="auth0ChartLocations" style="height: 320px;"></div>
		<script type="text/javascript">

			function initialize() {
				var center = new google.maps.LatLng(0, 0);
				var geocoder = new google.maps.Geocoder();
		        var map = new google.maps.Map(document.getElementById('auth0ChartLocations'), {
		          zoom: 1,
		          center: center,
		          streetViewControl: false,
		          mapTypeId: google.maps.MapTypeId.ROADMAP
		        });

				function codeAddress(address) {
					geocoder.geocode( { 'address': address}, function(results, status) {
						if (status == google.maps.GeocoderStatus.OK) {
							var marker = new google.maps.Marker({
								map: map,
								position: results[0].geometry.location
							});
						}
					});
				}

				var data = <?php echo json_encode($data);?>;

			    data.forEach(function(d){
		    		codeAddress(d);
			    });				
		    }

	        google.maps.event.addDomListener(window, 'load', initialize);

		</script>	
		<?php
	}

}