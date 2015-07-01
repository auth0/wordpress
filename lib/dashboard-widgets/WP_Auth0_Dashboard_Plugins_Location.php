<?php

class WP_Auth0_Dashboard_Plugins_Location implements WP_Auth0_Dashboard_Plugins_Interface {

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
			if (isset($user->app_metadata) && isset($user->app_metadata->geoip)) {
				$data[] = $user->app_metadata->geoip;
			}
			if (isset($user->user_metadata) && isset($user->user_metadata->geoip)) {
				$data[] = $user->user_metadata->geoip;
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

		?>
		<div id="auth0ChartLocations" style="height: 320px;"></div>
		<script type="text/javascript">

			(function(){
				function initialize() {
					var geocoder = new google.maps.Geocoder();
					var bounds = new google.maps.LatLngBounds();

			        var map = new google.maps.Map(document.getElementById('auth0ChartLocations'), {
			          minZoom: 1,
			          maxZoom: 15,
			          streetViewControl: false,
			          mapTypeControl:false,
			          styles:[{"featureType":"landscape.natural","elementType":"geometry.fill","stylers":[{"visibility":"on"},{"color":"#e0efef"}]},{"featureType":"poi","elementType":"geometry.fill","stylers":[{"visibility":"on"},{"hue":"#1900ff"},{"color":"#c0e8e8"}]},{"featureType":"road","elementType":"geometry","stylers":[{"lightness":100},{"visibility":"simplified"}]},{"featureType":"road","elementType":"labels","stylers":[{"visibility":"off"}]},{"featureType":"transit.line","elementType":"geometry","stylers":[{"visibility":"on"},{"lightness":700}]},{"featureType":"water","elementType":"all","stylers":[{"color":"#7dcdcd"}]}]	,
			          mapTypeId: google.maps.MapTypeId.ROADMAP
			        });

					function codeAddress(latitude, longitude) {
						var marker = new google.maps.Marker({
							map: map,
							position: new google.maps.LatLng(latitude, longitude)
						});
						bounds.extend(marker.position);
					}

					var data = <?php echo json_encode($data);?>;

				    data.forEach(function(d){
			    		codeAddress(d.latitude, d.longitude);
				    });

				    map.fitBounds(bounds);
			    }

		        google.maps.event.addDomListener(window, 'load', initialize);
			})();

		</script>
		<?php
	}

}
