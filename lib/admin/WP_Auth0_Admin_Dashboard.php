<?php
// phpcs:ignoreFile
/**
 * Class WP_Auth0_Admin_Dashboard
 *
 * @deprecated - 3.6.0, the plugin no longer supports the dashboard widgets functionality.
 *
 * @codeCoverageIgnore - Deprecated
 */
class WP_Auth0_Admin_Dashboard extends WP_Auth0_Admin_Generic {

	const DASHBOARD_DESCRIPTION = 'Settings related to the dashboard widgets.';

	protected $_description;

	protected $actions_middlewares = array(
		'basic_validation',
	);

	/**
	 * WP_Auth0_Admin_Dashboard constructor.
	 *
	 * @deprecated - 3.6.0, the plugin no longer supports the dashboard widgets functionality.
	 *
	 * @param WP_Auth0_Options $options
	 */
	public function __construct( WP_Auth0_Options $options ) {
		// phpcs:ignore
		@trigger_error( sprintf( __( 'Class %s is deprecated.', 'wp-auth0' ), __CLASS__ ), E_USER_DEPRECATED );
		parent::__construct( $options );
		$this->_description = __( 'Settings related to the dashboard widgets', 'wp-auth0' );
	}

	public function init() {

		$this->init_option_section(
			'', 'dashboard', array(

				array(
					'id'       => 'wpa0_chart_age_type',
					'name'     => 'Age',
					'function' => 'render_age_chart_type',
				),
				array(
					'id'       => 'wpa0_chart_idp_type',
					'name'     => 'Identity providers',
					'function' => 'render_idp_chart_type',
				),
				array(
					'id'       => 'wpa0_chart_gender_type',
					'name'     => 'Gender',
					'function' => 'render_gender_chart_type',
				),

				array(
					'id'       => 'wpa0_chart_age_from',
					'name'     => 'Age buckets start',
					'function' => 'render_age_from',
				),
				array(
					'id'       => 'wpa0_chart_age_to',
					'name'     => 'Age buckets end',
					'function' => 'render_age_to',
				),
				array(
					'id'       => 'wpa0_chart_age_step',
					'name'     => 'Age buckets step',
					'function' => 'render_age_step',
				),

			)
		);

	}

	public function render_dashboard_description() {
?>

	<p class=\"a0-step-text\"><?php echo self::DASHBOARD_DESCRIPTION; ?></p>

	<?php
	}

	public function render_age_from() {
		$v = absint( $this->options->get( 'chart_age_from' ) );
?>

	<input type="number" name="<?php echo $this->options->get_options_name(); ?>[chart_age_from]" id="wpa0_auth0_age_from" value="<?php echo $v; ?>" />

	<?php
	}

	public function render_age_to() {
		$v = absint( $this->options->get( 'chart_age_to' ) );
?>

	<input type="number" name="<?php echo $this->options->get_options_name(); ?>[chart_age_to]" id="wpa0_auth0_age_to" value="<?php echo $v; ?>" />

	<?php
	}

	public function render_age_step() {
		$v = absint( $this->options->get( 'chart_age_step' ) );
?>

	<input type="number" name="<?php echo $this->options->get_options_name(); ?>[chart_age_step]" id="wpa0_auth0_age_step" value="<?php echo $v; ?>" />

	<?php
	}

	public function render_age_chart_type() {
		$v = $this->options->get( 'chart_age_type' );

?>

	<input type="radio" name="<?php echo $this->options->get_options_name(); ?>[chart_age_type]" id="wpa0_auth0_age_chart_type_donut" value="donut" <?php echo checked( $v, 'donut', false ); ?>/>
	<label for="wpa0_auth0_age_chart_type_donut"><?php echo __( 'Donut', 'wp-auth0' ); ?></label>

	<input type="radio" name="<?php echo $this->options->get_options_name(); ?>[chart_age_type]" id="wpa0_auth0_age_chart_type_pie" value="pie" <?php echo checked( $v, 'pie', false ); ?>/>
	<label for="wpa0_auth0_age_chart_type_pie"><?php echo __( 'Pie', 'wp-auth0' ); ?></label>
	&nbsp;
	<input type="radio" name="<?php echo $this->options->get_options_name(); ?>[chart_age_type]" id="wpa0_auth0_age_chart_type_bar" value="bar" <?php echo checked( $v, 'bar', false ); ?>/>
	<label for="wpa0_auth0_age_chart_type_bars"><?php echo __( 'Bars', 'wp-auth0' ); ?></label>

	<?php
	}

	public function render_idp_chart_type() {
		$v = $this->options->get( 'chart_idp_type' );

?>

	<input type="radio" name="<?php echo $this->options->get_options_name(); ?>[chart_idp_type]" id="wpa0_auth0_idp_chart_type_donut" value="donut" <?php echo checked( $v, 'donut', false ); ?>/>
	<label for="wpa0_auth0_idp_chart_type_donut"><?php echo __( 'Donut', 'wp-auth0' ); ?></label>

	<input type="radio" name="<?php echo $this->options->get_options_name(); ?>[chart_idp_type]" id="wpa0_auth0_idp_chart_type_pie" value="pie" <?php echo checked( $v, 'pie', false ); ?>/>
	<label for="wpa0_auth0_idp_chart_type_pie"><?php echo __( 'Pie', 'wp-auth0' ); ?></label>
	&nbsp;
	<input type="radio" name="<?php echo $this->options->get_options_name(); ?>[chart_idp_type]" id="wpa0_auth0_idp_chart_type_bar" value="bar" <?php echo checked( $v, 'bar', false ); ?>/>
	<label for="wpa0_auth0_idp_chart_type_bars"><?php echo __( 'Bars', 'wp-auth0' ); ?></label>

	<?php
	}

	public function render_gender_chart_type() {
		$v = $this->options->get( 'chart_gender_type' );

?>

	<input type="radio" name="<?php echo $this->options->get_options_name(); ?>[chart_gender_type]" id="wpa0_auth0_gender_chart_type_donut" value="donut" <?php echo checked( $v, 'donut', false ); ?>/>
	<label for="wpa0_auth0_gender_chart_type_donut"><?php echo __( 'Donut', 'wp-auth0' ); ?></label>

	<input type="radio" name="<?php echo $this->options->get_options_name(); ?>[chart_gender_type]" id="wpa0_auth0_gender_chart_type_pie" value="pie" <?php echo checked( $v, 'pie', false ); ?>/>
	<label for="wpa0_auth0_gender_chart_type_pie"><?php echo __( 'Pie', 'wp-auth0' ); ?></label>
	&nbsp;
	<input type="radio" name="<?php echo $this->options->get_options_name(); ?>[chart_gender_type]" id="wpa0_auth0_gender_chart_type_bar" value="bar" <?php echo checked( $v, 'bar', false ); ?>/>
	<label for="wpa0_auth0_gender_chart_type_bars"><?php echo __( 'Bars', 'wp-auth0' ); ?></label>

	<?php
	}

	protected function validate_chart_type( $type ) {
		$validChartTypes = array( 'pie', 'bar', 'donut' );

		if ( in_array( $type, $validChartTypes ) ) {
			return $type;
		}

		return $validChartTypes[0];
	}

	public function basic_validation( $old_options, $input ) {
		$input['chart_gender_type'] = $this->validate_chart_type( $input['chart_gender_type'] );
		$input['chart_idp_type']    = $this->validate_chart_type( $input['chart_idp_type'] );
		$input['chart_age_type']    = $this->validate_chart_type( $input['chart_age_type'] );

		return $input;
	}


}
