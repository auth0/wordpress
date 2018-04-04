<?php
// TODO: Deprecate
class WP_Auth0_Dashboard_Plugins_Location extends WP_Auth0_Dashboard_Plugins_Generic {

	protected $id = 'auth0_dashboard_widget_Location';
	protected $name = 'Auth0 - User\'s Location';

	public function __construct() {
		wp_enqueue_script( 'auth0-dashboard-gmaps-js', 'https://maps.googleapis.com/maps/api/js' );
		wp_enqueue_script( 'auth0-markerclusterer', trailingslashit( plugin_dir_url( WPA0_PLUGIN_FILE ) ) . 'assets/lib/markerclusterer.js' );
	}

	public function render() {

?>
        <div id="auth0ChartLocations" style="height: 320px;"></div>
        <script type="text/javascript">

          function a0_location_chart(raw_data) {
            var _this = this;
            this.name = 'location';

            this.bounds = new google.maps.LatLngBounds();

            this.map = new google.maps.Map(document.getElementById('auth0ChartLocations'), {
              minZoom: 1,
              maxZoom: 15,
              streetViewControl: false,
              mapTypeControl:false,
              styles:[{"featureType":"landscape.natural","elementType":"geometry.fill","stylers":[{"visibility":"on"},{"color":"#e0efef"}]},{"featureType":"poi","elementType":"geometry.fill","stylers":[{"visibility":"on"},{"hue":"#1900ff"},{"color":"#c0e8e8"}]},{"featureType":"road","elementType":"geometry","stylers":[{"lightness":100},{"visibility":"simplified"}]},{"featureType":"road","elementType":"labels","stylers":[{"visibility":"off"}]},{"featureType":"transit.line","elementType":"geometry","stylers":[{"visibility":"on"},{"lightness":700}]},{"featureType":"water","elementType":"all","stylers":[{"color":"#7dcdcd"}]}]    ,
              mapTypeId: google.maps.MapTypeId.ROADMAP
            });

            this.load(raw_data);
          }

          a0_location_chart.prototype.load = function(raw_data) {
            if (this.markerCluster) {
              this.markerCluster.clearMarkers();
            }

            var data = this.process_data(raw_data);

            var _this = this;
            function addMarker(latitude, longitude) {
                var marker = new google.maps.Marker({
                    position: new google.maps.LatLng(latitude, longitude)
                });
                _this.bounds.extend(marker.position);
                return marker;
            }

            var markers = data.filter(function(d){
              return (d.location.latitude && d.location.longitude);
            }).map(function(d){
              return addMarker(d.location.latitude, d.location.longitude);
            });

            this.markerCluster = new MarkerClusterer(this.map, markers);

            this.map.fitBounds(this.bounds);
          }

          a0_location_chart.prototype.process_data = function(raw_data) {
            var grouped_data = _.groupBy(raw_data, function(e) { return e.location.latitude+"|"+e.location.longitude; });

            var data = Object.keys(grouped_data).map(function(key) {
              return grouped_data[key][0];
            });

            return data;
          }

        </script>
        <?php
	}

}
