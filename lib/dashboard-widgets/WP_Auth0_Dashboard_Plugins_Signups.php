<?php

class WP_Auth0_Dashboard_Plugins_Signups {

    public function getId() {
        return 'auth0_dashboard_widget_signups';
    }

    public function getName() {
        return 'Auth0 - User\'s Signups';
    }

    protected $users = array();

    public function __construct($users) {
        $this->users = $users;
    }

    protected function processData() {
        $data = array();
        $limitDate = strtotime('2 months ago');

        foreach ($this->users as $user) {
            $created_at = strtotime($user->created_at);
            if ($created_at > $limitDate) {
                $day = date('Y-m-d',$created_at);

                if (!isset($data[$day])) $data[$day] = 0;

                $data[$day]++;
            }

        }
        return $data;
    }

    public function render() {

        $data = $this->processData();
        ksort($data);
        if (empty($data)) {
            echo "No data available";
            return;
        }

        $chartData = array();

        foreach ($data as $key => $value) {
            $chartData[] = array($key, $value);
        }

        ?>
        <div id="auth0ChartSignups"></div>
        <script type="text/javascript">
            (function(){
                var signups = <?php echo json_encode($chartData); ?>;

                var x_arr = ['x'];
                var data_arr = ['Signups'];

                signups.forEach(function(d){
                    x_arr.push(d[0]);
                    data_arr.push(d[1]);
                });

                var chart = c3.generate({
                    bindto: '#auth0ChartSignups',
                    data: {
                        x: 'x',
                        columns: [
                            x_arr,
                            data_arr
                        ]
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
                });
            })();
        </script>
        <?php

    }

}
