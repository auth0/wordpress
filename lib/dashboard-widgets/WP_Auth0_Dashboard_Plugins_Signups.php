<?php

class WP_Auth0_Dashboard_Plugins_Signups extends WP_Auth0_Dashboard_Plugins_Generic {

    protected $id = 'auth0_dashboard_widget_signups';
    protected $name = 'Auth0 - User\'s Signups';

    protected function getType($user) {
        $created_at = $user->get_created_at();
        if ( ! $created_at ) return;

        $limitDate = strtotime('1 months ago');

        $created_at = strtotime($created_at);
        if ($created_at > $limitDate) {
            return date('Y-m-d',$created_at);
        }

        return null;
    }

    public function render() {

        ?>
        <div id="auth0ChartSignups"></div>
        <script type="text/javascript">
            function a0_signup_chart(raw_data) {
              var _this = this;
              this.name = 'signups';

              var setup = {
                  bindto: '#auth0ChartSignups',
                  data: {
                      x: 'x',
                      columns: this.process_data(raw_data),
                      type: 'spline'
                  },
                  axis: {
                      x: {
                          type: 'timeseries',
                          tick: {
                              format: '%Y-%m-%d'
                          }
                      },
                      y:{
                          tick:{
                              format:function(x){return (x == Math.floor(x)) ? x: "";}
                          }
                      }
                  }
              };
              // setup.data.onmouseover = function (d, i) { var selected_day = d.x.toISOString().substr(0,10); filter_callback(_this, function(e) { return e.created_at_day == selected_day; } ); },
              // setup.data.onmouseout = function (d, i) { filter_callback(_this, null); },
              this.chart = c3.generate(setup);
            }

            a0_signup_chart.prototype.load = function(raw_data) {
              this.chart.load({
                columns: this.process_data(raw_data)
              });
            }

            a0_signup_chart.prototype.process_data = function(raw_data) {
              var limitDate = new Date();
              limitDate.setMonth(limitDate.getMonth() - 1);
              raw_data = raw_data.filter(function(e){
                e.created_at_day_obj = new Date(e.created_at_day);
                return e.created_at_day_obj>limitDate;
              })
              var grouped_data = _.groupBy(raw_data, function(e) { return e.created_at_day; });

              var data = [];
              var keys = Object.keys(grouped_data);

              var values = keys.map(function(key) {
                return grouped_data[key].length;
              });

              keys.unshift('x');
              values.unshift('Signups');

              data.push(keys);
              data.push(values);

              return data;
            }

        </script>
        <?php

    }

}
