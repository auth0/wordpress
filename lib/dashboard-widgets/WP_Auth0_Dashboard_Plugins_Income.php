<?php

class WP_Auth0_Dashboard_Plugins_Income extends WP_Auth0_Dashboard_Plugins_Generic {

    protected $id = 'auth0_dashboard_widget_income';
    protected $name = 'Auth0 - Income';

    public function render() {
        ?>
        <div id="auth0ChartIncome">
          <div id="tooltip"><div></div></div>
        </div>

        <script type="text/javascript">

        function a0_income_chart(raw_data, filter_callback) {
          var _this = this;
          this.name = 'income';

          this.chart = new ParallelCoordinates(this.process_data(raw_data),{
            container:"#auth0ChartIncome",
            scale:"linear",
            columns:["zipcode","count","income"],
            ref:"lang_usage",
            title_column:"zipcode",
            scale_map:{
              "zipcode":"ordinal",
              "count":"ordinal",
              "income":"ordinal"
            },
            use:{
      				"name":"count"
      			},
            sorting:{
              "count":d3.ascending
            },
            dimensions:["count","income","zipcode"],
            column_map:{
              "zipcode":"zipcode",
              "income":"income",
              "count":"count"
            },
            formats:{
            },
            help:{
              "zipcode":"<h4>Zipcode</h4>This is the users zipcode based on their IP.",
              "income":"<h4>Income</h4>This is the Zipcode median household income based on the last US census.",
              "count":"<h4>Count</h4>Amount of users that login in the related zipcode."
            },
            duration:1000,
            onmouseover: function (d) { filter_callback(_this, function(e) { return e.zipcode == d.key; } ); },
            onmouseout: function (d) { filter_callback(_this, null); }
          });

        }

        a0_income_chart.prototype.load = function(raw_data) {
          this.chart.loadData(this.process_data(raw_data));
        }

        a0_income_chart.prototype.process_data = function(raw_data) {
          raw_data = raw_data.filter(function(e) {return e.zipcode !== null;});
          var grouped_data = _.groupBy(raw_data, function(e) { return e.zipcode; });

          var data = Object.keys(grouped_data).map(function(key) {
            return {
              zipcode:key,
              count: grouped_data[key] ? grouped_data[key].length : 0,
              income: grouped_data[key] ? grouped_data[key][0].income : 0
            };
          });

          return data;
        }





        </script>

        <?php

    }

}
