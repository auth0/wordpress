<?php

class WP_Auth0_Dashboard_Plugins_Gender extends WP_Auth0_Dashboard_Plugins_Generic {

    protected $id = 'auth0_dashboard_widget_gender';
    protected $name = 'Auth0 - User\'s Gender';

    protected $users = array('female' => 0, 'male' => 0, self::UNKNOWN_KEY => 0);

    protected $type;
    protected $has_data = false;

    public function __construct(WP_Auth0_Dashboard_Options $dashboard_options) {
      $this->dashboard_options = $dashboard_options;
      $this->type = $this->dashboard_options->get('chart_gender_type');
    }

    public function render() {

        $chartSetup = array(
            'bindto' => '#auth0ChartGender',
            'data' => array(
                'type' => $this->type,
            ),
            'color' => array(
              'pattern' => array('#ff4282','#3e68ef', '#1ABC9C','#2ECC71','#3498DB','#9B59B6','#34495E','#F1C40F','#E67E22','#E74C3C','#F39C12'),
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
        <div id="auth0ChartGender"></div>
        <script type="text/javascript">

          function a0_gender_chart(raw_data, filter_callback) {
            var _this = this;
            this.name = 'gender';

            var setup = <?php echo json_encode($chartSetup);?>;
            setup.data.columns = this.process_data(raw_data);
            setup.data.onmouseover = function (d, i) { filter_callback(_this, function(e) { return e.gender == d.id; } ); },
            setup.data.onmouseout = function (d, i) { filter_callback(_this, null); },
            setup.data.color = function (color, d) {
              return (d === '<?php echo WP_Auth0_Dashboard_Widgets::UNKNOWN_KEY; ?>') ? '#CACACA' : color;
            };
            this.chart = c3.generate(setup);
          }

          a0_gender_chart.prototype.load = function(raw_data) {
            this.chart.load({
              columns: this.process_data(raw_data)
            });
          }

          a0_gender_chart.prototype.process_data = function(raw_data) {
            var grouped_data = _.groupBy(raw_data, function(e) { return e.gender; });

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
