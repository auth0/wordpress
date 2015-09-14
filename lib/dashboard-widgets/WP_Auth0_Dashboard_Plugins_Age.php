<?php

class WP_Auth0_Dashboard_Plugins_Age extends WP_Auth0_Dashboard_Plugins_Generic {

    protected $id = 'auth0_dashboard_widget_age';
    protected $name = 'Auth0 - User\'s Age';
    protected $type;

    public function __construct(WP_Auth0_Dashboard_Options $dashboard_options) {
      $this->dashboard_options = $dashboard_options;
      $this->type = $this->dashboard_options->get('chart_age_type');
    }

    public function render() {

        $chartSetup = array(
            'bindto' => '#auth0ChartAge',
            'data' => array(
                'type' => $this->type
            ),
            'color' => array(
              'pattern' => array('#F39C12','#2ECC71','#3498DB','#9B59B6','#34495E','#F1C40F','#E67E22','#E74C3C', '#1ABC9C'),
            ),
            'axis' => array(
                'x' => array(
                    'type' => 'category'
                )
            )

        );

        if ( $this->type == 'bar' ) {
            $chartSetup['data']['x'] = 'x';
        }

        ?>
        <div id="auth0ChartAge"></div>

        <script type="text/javascript">

          function a0_age_chart(raw_data) {
            var _this = this;
            this.name = 'age';

            var setup = <?php echo json_encode($chartSetup);?>;
            setup.data.columns = this.process_data(raw_data);
            setup.data.onmouseover = function (d, i) { filter_callback(_this, function(e) { return e.agebucket == d.id; } ); },
            setup.data.onmouseout = function (d, i) { filter_callback(_this, null); },
            setup.data.color = function (color, d) {
              return (d === '<?php echo WP_Auth0_Dashboard_Widgets::UNKNOWN_KEY; ?>') ? '#CACACA' : color;
            };
            this.chart = c3.generate(setup);
          }

          a0_age_chart.prototype.load = function(raw_data) {
            this.chart.load({
              columns: this.process_data(raw_data)
            });
          }

          a0_age_chart.prototype.process_data = function(raw_data) {
            var grouped_data = _.groupBy(raw_data, function(e) { return e.agebucket; });

          <?php if($this->type === 'pie') {?>
            var data = Object.keys(grouped_data).map(function(key) {
              return [key, grouped_data[key].length];
            });
          <?php } else {?>
            var data = [];
            var keys = Object.keys(grouped_data);

            var values = keys.map(function(key) {
              return grouped_data[key].length;
            });

            keys.unshift('x');
            values.unshift('Users count');

            data.push(keys);
            data.push(values);
          <?php } ?>

            return data;
          }

        </script>
        <?php

    }


}
