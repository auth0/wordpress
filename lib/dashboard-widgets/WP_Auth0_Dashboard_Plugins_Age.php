<?php

class WP_Auth0_Dashboard_Plugins_Age extends WP_Auth0_Dashboard_Plugins_Generic {

    protected $id = 'auth0_dashboard_widget_age';
    protected $name = 'Auth0 - User\'s Age';

    protected function getType($user) {

        if (isset($user->age)) {
            return $user->age;
        }
        if (isset($user->user_metadata) && isset($user->user_metadata->fullContactInfo) && isset($user->user_metadata->fullContactInfo->age)) {
            return $user->user_metadata->fullContactInfo->age;
        }
        if (isset($user->app_metadata) && isset($user->app_metadata->fullContactInfo) && isset($user->app_metadata->fullContactInfo->age)) {
            return $user->app_metadata->fullContactInfo->age;
        }
        if (isset($user->user_metadata) && isset($user->user_metadata->fullContactInfo) && isset($user->user_metadata->fullContactInfo->demographics) && isset($user->user_metadata->fullContactInfo->demographics->age)) {
            return $user->user_metadata->fullContactInfo->demographics->age;
        }
        if (isset($user->app_metadata) && isset($user->app_metadata->fullContactInfo) && isset($user->app_metadata->fullContactInfo->demographics) && isset($user->user_metadata->fullContactInfo->demographics->age)) {
            return $user->user_metadata->app_metadata->demographics->age;
        }

        if (isset($user->user_metadata) && isset($user->user_metadata->fullContactInfo) && isset($user->user_metadata->fullContactInfo->birthDate)) {
            $birthDate = explode("-", $user->user_metadata->fullContactInfo->birthDate);

            $age = (date("md", date("U", mktime(0, 0, 0, $birthDate[3], $birthDate[1], $birthDate[0]))) > date("md")
                ? ((date("Y") - $birthDate[0]) - 1)
                : (date("Y") - $birthDate[0]));
            return $age;
        }
        if (isset($user->app_metadata) && isset($user->app_metadata->fullContactInfo) && isset($user->app_metadata->fullContactInfo->birthDate)) {
            $birthDate = explode("-", $user->app_metadata->fullContactInfo->birthDate);

            $age = (date("md", date("U", mktime(0, 0, 0, $birthDate[3], $birthDate[1], $birthDate[0]))) > date("md")
                ? ((date("Y") - $birthDate[0]) - 1)
                : (date("Y") - $birthDate[0]));
            return $age;
        }

        if (isset($user->dateOfBirth)) {

            $birthDate = explode("-", $user->birthday);

            $age = (date("md", date("U", mktime(0, 0, 0, $birthDate[3], $birthDate[1], $birthDate[0]))) > date("md")
                ? ((date("Y") - $birthDate[0]) - 1)
                : (date("Y") - $birthDate[0]));
            return $age;
        }
        if (isset($user->birthday)) {

            $birthDate = explode("/", $user->birthday);

            $age = (date("md", date("U", mktime(0, 0, 0, $birthDate[0], $birthDate[1], $birthDate[2]))) > date("md")
                ? ((date("Y") - $birthDate[2]) - 1)
                : (date("Y") - $birthDate[2]));
            return $age;
        }

        return self::UNKNOWN_KEY;
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

        usort($chartData, array(__CLASS__, 'sortAges'));

        ?>
        <div id="auth0ChartAge"></div>
        <script type="text/javascript">

            var chartType = 'pie';
            var chart = c3.generate({
                bindto: '#auth0ChartAge',
                data: {
                    columns: <?php echo json_encode($chartData); ?>,
                    type : chartType
                },
                color: {
                  pattern: <?php echo json_encode($this->getColors($chartData)); ?>
                }
            });

            function toggleAgeChartType() {
                chartType = chartType == 'pie' ? 'bars' : chartType;
                chart.transform(chartType);
            }

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
