<?php
// phpcs:ignoreFile
/**
 * Class WP_Auth0_Dashboard_Plugins_Income
 *
 * @deprecated - 3.6.0, the plugin no longer supports the dashboard widgets functionality.
 *
 * @codeCoverageIgnore - Deprecated
 */
class WP_Auth0_Dashboard_Plugins_Income extends WP_Auth0_Dashboard_Plugins_Generic {

	protected $id = 'auth0_dashboard_widget_income';
	protected $name = 'Auth0 - Users Income';

	/**
	 * WP_Auth0_Dashboard_Plugins_Income constructor.
	 *
	 * @deprecated - 3.6.0, the plugin no longer supports the dashboard widgets functionality.
	 */
	public function __construct() {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Class %s is deprecated.', 'wp-auth0' ), __CLASS__ ), E_USER_DEPRECATED );
	}

	public function render() {
?>
        <div id="auth0ChartIncome">
        </div>

        <script type="text/javascript">

        function a0_income_chart(raw_data, filter_callback) {
          var _this = this;
          this.name = 'income';

          var width = jQuery('#auth0ChartIncome').width();

          this.chart = new DualDimentionBars(this.process_data(raw_data),{
            container:"#auth0ChartIncome",
            width: width,
            height:315,
            barHeight: 5,
            labelsWidth: width * 0.2,
            yAxisWidth: width * 0.15,
            labelsTitle: "ZipCode",
            yAxisTitle: "Income",
            xAxisTitle: "# Users",
            colorsPattern: ['#F39C12','#2ECC71','#3498DB','#9B59B6','#34495E','#F1C40F','#E67E22','#E74C3C', '#1ABC9C'],

            onClick: function (selection) {

              _this.filter_selection = selection.map(function(e){
                return e.id;
              });

              if (selection.length === 0) {
                filter_callback( _this, null, null, null );
              } else {
                filter_callback(_this, 'Income', _this.filter_selection, function(e) { if (!e.zipcode) return false; return _this.filter_selection.indexOf(e.zipcode.toString()) > -1; } );
              }
            }

          });
          // this.chart.debug();

          jQuery(window).resize(function(){
            var width = jQuery('#auth0ChartIncome').width();
            _this.chart.resize({
              width: width,
              labelsWidth: width * 0.3,
              yAxisWidth: width * 0.15,
            });

          });
        }

        a0_income_chart.prototype.load = function(raw_data) {
          this.chart.loadData(this.process_data(raw_data));
        }

        a0_income_chart.prototype.process_data = function(raw_data) {
          raw_data = raw_data.filter(function(e) {return e.zipcode !== null && e.income !== null && e.income != 0;});
          var grouped_data = _.groupBy(raw_data, function(e) { return e.zipcode; });

          var data = Object.keys(grouped_data).map(function(key) {
            return {
              id:key,
              label:key,
              x: grouped_data[key] ? grouped_data[key].length : 0,
              y: grouped_data[key] ? grouped_data[key][0].income : 0
            };
          });

          return data;
        }

        </script>

        <?php

	}

}
