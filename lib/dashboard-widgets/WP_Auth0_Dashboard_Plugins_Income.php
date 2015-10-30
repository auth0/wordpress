<?php

class WP_Auth0_Dashboard_Plugins_Income extends WP_Auth0_Dashboard_Plugins_Generic {

    protected $id = 'auth0_dashboard_widget_income';
    protected $name = 'Auth0 - Users Income';

    public function render() {
        ?>
        <div id="auth0ChartIncome">
        </div>

        <script type="text/javascript">

        function a0_income_chart(raw_data, filter_callback) {
          var _this = this;
          this.name = 'income';

          this.chart = new DualDimentionBars(this.process_data(raw_data),{
            container:"#auth0ChartIncome",
            width: jQuery('#auth0ChartIncome').width(),
            height:400,
            barHeight: 5,
            labelsWidth:150,
            yAxisWidth: 70,
            labelsTitle: "ZipCode",
            yAxisTitle: "Income",
            xAxisTitle: "# Users"
          });
          // this.chart.debug();
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
