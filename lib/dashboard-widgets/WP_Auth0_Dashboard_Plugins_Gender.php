<?php

class WP_Auth0_Dashboard_Plugins_Gender extends WP_Auth0_Dashboard_Plugins_Generic {

    protected $id = 'auth0_dashboard_widget_gender';
    protected $name = 'Auth0 - User\'s Gender';

    protected $users = array('female' => 0, 'male' => 0, self::UNKNOWN_KEY => 0);

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

        foreach ($data as $key => $value) {
            $chartData[] = array($key, $value);
        }

        ?>
        <div id="auth0ChartGender"></div>
        <script type="text/javascript">
            (function(){
                var chart = c3.generate({
                    bindto: '#auth0ChartGender',
                    data: {
                        columns: <?php echo json_encode($chartData); ?>,
                        type : 'pie'
                    },
                    color: {
                      pattern: ['#ff4282','#3e68ef', '#CACACA','#1ABC9C','#2ECC71','#3498DB','#9B59B6','#34495E','#F1C40F','#E67E22','#E74C3C','#F39C12']
                    }
                });
            })();
        </script>
        <?php
    }

}
