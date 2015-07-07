<?php

class WP_Auth0_Dashboard_Plugins_Income extends WP_Auth0_Dashboard_Plugins_Generic {

    protected $id = 'auth0_dashboard_widget_income';
    protected $name = 'Auth0 - Identity Income';

    public function addUser($user) {

        $geoip = null;
        if (isset($user->app_metadata) && isset($user->app_metadata->geoip)) {
            $geoip = $user->app_metadata->geoip;
        }elseif (isset($user->user_metadata) && isset($user->user_metadata->geoip)) {
            $geoip = $user->user_metadata->geoip;
        }

        if ($geoip) {
            if (isset($geoip->postal_code)){

                $postal_code = $geoip->postal_code;
                $country_name = $geoip->country_name;
                $country_code = $geoip->country_code;
                $income = null;
                if (isset($user->app_metadata->zipcode_income))
                {
                    $income = $user->app_metadata->zipcode_income;
                }
                elseif (isset($user->user_metadata->zipcode_income))
                {
                    $income = $user->user_metadata->zipcode_income;
                }

                $key = "$country_name - $postal_code";

                if (!isset($this->users[$key])) {
                    $this->users[$key] = array('postal_code' => $postal_code, 'country' => $country_name, 'income' => $income, 'country_code' => $country_code, 'count' => 0);
                }

                $this->users[$key]['count']++;
                if ($income && $this->users[$key]['income'] != $income)
                {
                    $this->users[$key]['income'] = $income;
                }
            }
        }

    }

    public function render() {

        $data = $this->users;

        if (empty($data)) {
            echo "No income data available";
            return;
        }

        $chartData = array_values($data);

        $jsonData = json_encode($chartData);
        ?>
        <div id="auth0ChartIncome"></div>

        <script type="text/javascript">

        (function(){

            var data = <?php echo $jsonData; ?>;

            loadChart(data);
            function loadChart(data) {

                var x_arr = ['x'];
                var zipcodes_arr = ['users count'];
                var incomes_arr = ['Median household income (in 1000)'];

                data = data.filter(function(d){ return d.income; });

                data.forEach(function(d){
                    zipcodes_arr.push(d.count)
                    incomes_arr.push( d.income ? d.income/1000 : 0)
                    x_arr.push(d.country + ' - ' +d.postal_code)
                });

                var chart = c3.generate({
                    bindto: '#auth0ChartIncome',
                    data: {
                        x:'x',
                        columns: [
                            x_arr,
                            zipcodes_arr,
                            incomes_arr
                        ],
                        type: 'bar',
                        colors:{
                            zipcodes:'#3498DB',
                            incomes:'#2ECC71'
                        }
                    },
                    bar: {
                        width: {
                            ratio: 0.5
                        }
                    },
                    axis: {
                        x: {
                            type: 'category'
                        },
                        y:{
                            tick:{
                                format:function(x){
                                    if (x == Math.floor(x)) return x;
                                    return "";
                                }
                            }
                        }
                    }
                });
            }

        })();
        </script>

        <?php

    }

}
