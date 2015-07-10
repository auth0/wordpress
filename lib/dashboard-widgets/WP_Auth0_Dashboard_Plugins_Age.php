<?php

class WP_Auth0_Dashboard_Plugins_Age extends WP_Auth0_Dashboard_Plugins_Generic {

    protected $id = 'auth0_dashboard_widget_age';
    protected $name = 'Auth0 - User\'s Age';
    protected $type;

    public function __construct($type) {
        $this->type = $type;
    }

    protected function getType($user) {
        $age = $user->get_age();
        return $age ? $age : self::UNKNOWN_KEY;
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

            usort($chartData, array(__CLASS__, 'sortAges'));
        } else {
            $keys = array_keys($data);
            $values = array_values($data);

            array_unshift($keys, 'x');
            array_unshift($values, 'Users count');

            $chartData[] = $keys;
            $chartData[] = $values;
        }

        $chartSetup = array(
            'bindto' => '#auth0ChartAge',
            'data' => array(
                'columns' => $chartData,
                'type' => $this->type,
            ),
            'color' => array(
              'pattern' => $this->getColors($chartData),
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
            (function(){
                var chart = c3.generate(<?php echo json_encode($chartSetup);?>);
            })();
        </script>
        <?php

    }

    protected static function getColors($data) {
        $unknownColor = '#CACACA';
        $palete = array('#F39C12','#2ECC71','#3498DB','#9B59B6','#34495E','#F1C40F','#E67E22','#E74C3C', '#1ABC9C');
        $colorIndex = 0;
        $colors = array();
        $paleteLength = count($palete);

        foreach($data as $category) {
            $colors[] = ($category[0] == self::UNKNOWN_KEY ? $unknownColor : $palete[($colorIndex++) % $paleteLength]);
        }

        return $colors;
    }

    public static function sortAges($a,$b) {
        if ($a[0] == 'unknown') return 1;
        if ($b[0] == 'unknown') return -1;

        if ($a[0] == $b[0]) {
            return 0;
        }
        return ($a[0] < $b[0]) ? -1 : 1;
    }

}
