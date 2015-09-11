<?php

class WP_Auth0_Dashboard_Plugins_IdP extends WP_Auth0_Dashboard_Plugins_Generic {

    protected $id = 'auth0_dashboard_widget_idp';
    protected $name = 'Auth0 - Identity Providers';
    protected $type;

    public function __construct(WP_Auth0_Dashboard_Options $dashboard_options) {
      $this->dashboard_options = $dashboard_options;
      $this->type = $this->dashboard_options->get('chart_idp_type');
    }

    protected function getType($user) {
        return $user->get_idp();
    }

    public function render() {
        $data = $this->users;

        if (empty($data)) {
            echo "No data available";
            return;
        }

        $chartData = array();

        if ( $this->type == 'pie' ) {
            foreach ( $data as $key => $value ) {
                $chartData[] = array( $key, $value );
            }
        } else {
            $values = array_values($data);

            array_unshift($values, 'Users count');

            // $chartData[] = $keys;
            $chartData[] = $values;
        }
        $keys = array_keys($data);

        $chartSetup = array(
            'bindto' => '#auth0ChartIdP',
            'data' => array(
                'columns' => $chartData,
                'type' => $this->type,
            ),
            // 'color' => array(
            //   'pattern' => $this->getColors($chartData),
            // ),
            'axis' => array(
                'x' => array(
                    'type' => 'category',
                    'categories' => $keys
                )
            )

        );

        // if ( $this->type == 'bar' ) {
        //     $chartSetup['data']['x'] = 'x';
        // }

        ?>
        <div id="auth0ChartIdP"></div>
        <script type="text/javascript">
            (function(){
                var last_color = 0;
                function getRandomColor() {
                    var colors = ['#F39C12','#2ECC71','#3498DB','#9B59B6','#34495E','#F1C40F','#E67E22','#E74C3C', '#1ABC9C'];
                    last_color = (last_color++ % colors.length);
                    return colors[last_color];

                }
                function getIdpColor(name) {
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

                var setup = <?php echo json_encode($chartSetup);?>;
                setup.data.color = function (color,d){
                    var idp;

                    if (d.index) {
                        idp = setup.axis.x.categories[d.index];
                    }
                    else if (d.split) {
                        idp = d;
                    }

                    if (!idp) return;

                    color = getIdpColor(idp);
                    if (color) return color;

                    color = getRandomColor(idp);
                    return color;
                };
                var chart = c3.generate(setup);
            })();
        </script>
        <?php

    }
}
