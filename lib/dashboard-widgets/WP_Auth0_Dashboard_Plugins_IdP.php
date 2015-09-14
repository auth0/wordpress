<?php

class WP_Auth0_Dashboard_Plugins_IdP extends WP_Auth0_Dashboard_Plugins_Generic {

    protected $id = 'auth0_dashboard_widget_idp';
    protected $name = 'Auth0 - Identity Providers';
    protected $type;

    public function __construct(WP_Auth0_Dashboard_Options $dashboard_options) {
      $this->dashboard_options = $dashboard_options;
      $this->type = $this->dashboard_options->get('chart_idp_type');
    }

    public function render() {

        $chartSetup = array(
            'bindto' => '#auth0ChartIdP',
            'data' => array(
                'type' => $this->type,
            ),
            'axis' => array(
                'x' => array(
                    'type' => 'category'
                )
            )

        );


        ?>
        <div id="auth0ChartIdP"></div>
        <script type="text/javascript">

        var a0_last_color = 0;
        function a0_get_random_color() {
            var colors = ['#F39C12','#2ECC71','#3498DB','#9B59B6','#34495E','#F1C40F','#E67E22','#E74C3C', '#1ABC9C'];
            last_color = (a0_last_color++ % colors.length);
            return colors[a0_last_color];

        }
        function a0_get_idp_color(name) {
            name = name.split('-')[0];

            var idpColors = {
                "amazon" : "#ff9900",
                "auth0" : "#16214D",
                "google" : "#dd4b39",
                "facebook" : "#3b5998",
                "windowslive" : "#00bcf2",
                "linkedin" : "#0077b5",
                "github" : "#999999",
                "paypal" : "#003087",
                "twitter" : "#55acee",
                "yandex" : "#ffcc00",
                "yahoo" : "#400191",
                "box" : "#447EC3",
                "salesforce" : "#1798c1",
                "fitbit" : "#4cc2c4",
                "baidu" : "#de0f17",
                "aol" : "#ff0b00",
                "shopify" : "#96bf48",
                "wordpress" : "#21759b",
                "soundcloud" : "#ff8800",
                "instagram" : "#3f729b",
                "evernote" : "#7ac142"
            };

            return idpColors[name] ? idpColors[name] : null;
        }

        function a0_idp_chart(raw_data) {
          var _this = this;
          this.name = 'idp';

          var setup = <?php echo json_encode($chartSetup);?>;
          setup.data.columns = this.process_data(raw_data);
          setup.axis.x.categories = setup.data.columns[1];
          setup.data.onmouseover = function (d, i) { filter_callback(_this, function(e) { return e.idp.indexOf(d.id) !== -1; } ); },
          setup.data.onmouseout = function (d, i) { filter_callback(_this, null); },
          setup.data.color = function (color,d){
              var idp;

              if (d.index) {
                  idp = setup.axis.x.categories[d.index];
              }
              else if (d.split) {
                  idp = d;
              }

              if (!idp) return;

              color = a0_get_idp_color(idp);
              if (color) return color;

              color = a0_get_random_color(idp);
              return color;
          };
          this.chart = c3.generate(setup);
        }

        a0_idp_chart.prototype.load = function(raw_data) {
          this.chart.load({
            columns: this.process_data(raw_data)
          });
        }

        a0_idp_chart.prototype.process_data = function(raw_data) {
          var grouped_data = _.groupBy(raw_data, function(e) { return e.idp[0]; });

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
