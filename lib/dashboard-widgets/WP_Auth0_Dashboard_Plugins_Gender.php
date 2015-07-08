<?php

class WP_Auth0_Dashboard_Plugins_Gender extends WP_Auth0_Dashboard_Plugins_Generic {

    protected $id = 'auth0_dashboard_widget_gender';
    protected $name = 'Auth0 - User\'s Gender';

    protected $users = array('female' => 0, 'male' => 0, self::UNKNOWN_KEY => 0);

    protected $type;

    public function __construct($type) {
        $this->type = $type;
    }


    protected function getType($user) {

        if (isset($user->gender)) {
            $genderName = strtolower($user->gender);
        }
        elseif (isset($user->user_metadata) && isset($user->user_metadata->fullContactInfo) && isset($user->user_metadata->fullContactInfo->demographics) && isset($user->user_metadata->fullContactInfo->demographics->gender)) {
            $genderName = strtolower($user->user_metadata->fullContactInfo->demographics->gender);
        }
        elseif (isset($user->app_metadata) && isset($user->app_metadata->fullContactInfo) && isset($user->app_metadata->fullContactInfo->demographics) && isset($user->user_metadata->fullContactInfo->demographics->gender)) {
            $genderName = strtolower($user->app_metadata->fullContactInfo->demographics->gender);
        }
        else {
            $genderName = self::UNKNOWN_KEY;
        }

        return $genderName;
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
            $keys = array_keys($data);
            $values = array_values($data);

            array_unshift($keys, 'x');
            array_unshift($values, 'Users count');

            $chartData[] = $keys;
            $chartData[] = $values;
        }

        $chartSetup = array(
            'bindto' => '#auth0ChartGender',
            'data' => array(
                'columns' => $chartData,
                'type' => $this->type,
            ),
            'color' => array(
              'pattern' => array('#ff4282','#3e68ef', '#CACACA','#1ABC9C','#2ECC71','#3498DB','#9B59B6','#34495E','#F1C40F','#E67E22','#E74C3C','#F39C12'),
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
            (function(){
                var chart = c3.generate(<?php echo json_encode($chartSetup);?>);
            })();
        </script>
        <?php
    }

}
