<?php

class WP_Auth0_Dashboard_Plugins_Signups extends WP_Auth0_Dashboard_Plugins_Generic {

    protected $id = 'auth0_dashboard_widget_signups';
    protected $name = 'Auth0 - User\'s Signups';

    protected function getType($user) {
        $created_at = $user->get_created_at();
        if ( ! $created_at ) return;

        $limitDate = strtotime('1 months ago');

        $created_at = strtotime($created_at);
        if ($created_at > $limitDate) {
            return date('Y-m-d',$created_at);
        }

        return null;
    }

    public function render() {

        $data = $this->users;
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
                    type: 'spline',
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
