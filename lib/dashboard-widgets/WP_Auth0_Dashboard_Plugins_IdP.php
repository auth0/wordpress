<?php
/**
 * Class WP_Auth0_Dashboard_Plugins_IdP
 *
 * @deprecated - 3.6.0, the plugin no longer supports the dashboard widgets functionality.
 *
 * @codeCoverageIgnore - Deprecated
 */
class WP_Auth0_Dashboard_Plugins_IdP extends WP_Auth0_Dashboard_Plugins_Generic {

	protected $id = 'auth0_dashboard_widget_idp';
	protected $name = 'Auth0 - Identity Providers';
	protected $type;

	/**
	 * WP_Auth0_Dashboard_Plugins_IdP constructor.
	 *
	 * @deprecated - 3.6.0, the plugin no longer supports the dashboard widgets functionality.
	 *
	 * @param WP_Auth0_Options $a0_options
     *
     * @codeCoverageIgnore - Deprecated
	 */
	public function __construct( WP_Auth0_Options $a0_options ) {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Class %s is deprecated.', 'wp-auth0' ), __CLASS__ ), E_USER_DEPRECATED );
		$this->a0_options = $a0_options;
		$this->type = $this->a0_options->get( 'chart_idp_type' );
	}

	public function render() {

		$chartSetup = array(
			'bindto' => '#auth0ChartIdP',
			'data' => array(
				'type' => $this->type,
				'selection' => array(
					'enabled' => true
				),
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

          var setup = <?php echo json_encode( $chartSetup );?>;
          setup.data.columns = this.process_data(raw_data);

          setup.data.onclick = function (d, i) {
            var selection = this.selected();

            _this.filter_selection = selection.map(function(e){

              <?php if ( $this->type === 'pie' || $this->type === 'donut' ) {?>
                return e.id;
              <?php } else {?>
                return _this.categories[e.index];
              <?php } ?>

            });

            if (selection.length === 0) {
              filter_callback( _this, null, null, null );
            } else {
              filter_callback(_this, 'Provider:', _this.filter_selection, function(e) { return _this.filter_selection.indexOf(e.idp[0]) > -1; } );
            }

            _this.chart.flush();
          };

          setup.data.color = function (color,d){
              var idp;

              if (typeof(d) === 'string') {
                idp = d;
              } else {
                idp = d.id;
              }

              if (_this.filter_selection && _this.filter_selection.length > 0 && _this.filter_selection.indexOf(idp) === -1) {
                return '#DDDDDD';
              }

              new_color = a0_get_idp_color(idp);
              if (new_color) {
                return new_color;
              }

              new_color = a0_get_random_color(idp);
              return new_color || color;
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

          if ( ! this.categories) {
            this.categories = Object.keys(grouped_data);
          }

          var keys = _.clone(this.categories);
          keys = _.sortBy(keys);

        <?php if ( $this->type === 'pie' || $this->type === 'donut' ) {?>
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
