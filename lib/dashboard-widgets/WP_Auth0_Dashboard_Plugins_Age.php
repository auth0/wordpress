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
                'type' => $this->type,
                'selection' => array(
                   'enabled' => true
                ),
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

          function a0_age_chart(raw_data, filter_callback) {
            var _this = this;
            this.name = 'age';

            var setup = <?php echo json_encode($chartSetup);?>;
            setup.data.columns = this.process_data(raw_data);

            setup.data.onclick = function (d, i) {
              var selection = this.selected();

              _this.filter_selection = selection.map(function(e){

                <?php if($this->type === 'pie' || $this->type === 'donut') {?>
                  return e.id;
                <?php } else {?>
                  return _this.categories[e.index];
                <?php } ?>

              });

              if (selection.length === 0) {
                filter_callback( _this, null, null, null );
              } else {
                filter_callback(_this, 'Age', _this.filter_selection, function(e) { return _this.filter_selection.indexOf(e.agebucket) > -1; } );
              }

              _this.chart.flush();
            };

            setup.data.color = function (color, d) {
              var bucket;

              if (typeof(d) === 'string') {
                bucket = d;
              } else {
                bucket = d.id;
              }

              if (_this.filter_selection && _this.filter_selection.length > 0 && _this.filter_selection.indexOf(bucket) === -1) {
                return '#DDDDDD';
              }

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

            if ( ! this.categories) {
              this.categories = Object.keys(grouped_data);
            }

            var keys = _.clone(this.categories);
            keys = _.sortBy(keys);

          <?php if($this->type === 'pie' || $this->type === 'donut') {?>
            var data = keys.map(function(key) {
              return [key, (grouped_data[key] ? grouped_data[key].length : 0)];
            });
          <?php } else {?>
            var data = [];

            var values = keys.map(function(key) {
              return (grouped_data[key] ? grouped_data[key].length : 0);
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
