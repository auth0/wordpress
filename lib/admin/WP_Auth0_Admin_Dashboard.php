<?php

class WP_Auth0_Admin_Dashboard extends WP_Auth0_Admin_Generic {

  const DASHBOARD_DESCRIPTION = 'Settings related to the dashboard widgets.';

  protected $actions_middlewares = array(
    'basic_validation',
  );

  public function init() {

    $this->init_option_section( '', 'dashboard', array(

      array( 'id' => 'wpa0_chart_age_type', 'name' => 'Age', 'function' => 'render_age_chart_type' ),
      array( 'id' => 'wpa0_chart_idp_type', 'name' => 'Identity providers', 'function' => 'render_idp_chart_type' ),
      array( 'id' => 'wpa0_chart_gender_type', 'name' => 'Gender', 'function' => 'render_gender_chart_type' ),

      array( 'id' => 'wpa0_chart_age_from', 'name' => 'Age buckets start', 'function' => 'render_age_from' ),
      array( 'id' => 'wpa0_chart_age_to', 'name' => 'Age buckets end', 'function' => 'render_age_to' ),
      array( 'id' => 'wpa0_chart_age_step', 'name' => 'Age buckets step', 'function' => 'render_age_step' ),

    ) );

    $options_name = $this->options->get_options_name();
    register_setting( $options_name . '_dashboard', $options_name, array( $this, 'input_validator' ) );

  }

  public function render_dashboard_description() {
    ?>

    <p class=\"a0-step-text\"><?php echo self::DASHBOARD_DESCRIPTION; ?></p>

    <?php
  }

  public function render_age_from() {
    $v = absint($this->options->get( 'chart_age_from' ));
    ?>

    <input type="number" name="<?php echo $this->options->get_options_name() ?>[chart_age_from]" id="wpa0_auth0_age_from" value="<?php echo $v; ?>" />

    <?php
  }

  public function render_age_to() {
    $v = absint($this->options->get( 'chart_age_to' ));
    ?>

    <input type="number" name="<?php echo $this->options->get_options_name() ?>[chart_age_to]" id="wpa0_auth0_age_to" value="<?php echo $v; ?>" />

    <?php
  }

  public function render_age_step() {
    $v = absint($this->options->get( 'chart_age_step' ));
    ?>

    <input type="number" name="<?php echo $this->options->get_options_name() ?>[chart_age_step]" id="wpa0_auth0_age_step" value="<?php echo $v; ?>" />

    <?php
  }

  public function render_age_chart_type() {
    $v = $this->options->get( 'chart_age_type' );

    ?>

    <input type="radio" name="<?php echo $this->options->get_options_name() ?>[chart_age_type]" id="wpa0_auth0_age_chart_type_donut" value="donut" <?php echo checked( $v, 'donut', false ); ?>/>
    <label for="wpa0_auth0_age_chart_type_donut"><?php echo __( 'Donut', WPA0_LANG ); ?></label>

    <input type="radio" name="<?php echo $this->options->get_options_name() ?>[chart_age_type]" id="wpa0_auth0_age_chart_type_pie" value="pie" <?php echo checked( $v, 'pie', false ); ?>/>
    <label for="wpa0_auth0_age_chart_type_pie"><?php echo __( 'Pie', WPA0_LANG ); ?></label>
    &nbsp;
    <input type="radio" name="<?php echo $this->options->get_options_name() ?>[chart_age_type]" id="wpa0_auth0_age_chart_type_bar" value="bar" <?php echo checked( $v, 'bar', false ); ?>/>
    <label for="wpa0_auth0_age_chart_type_bars"><?php echo __( 'Bars', WPA0_LANG ); ?></label>

    <?php
  }

  public function render_idp_chart_type() {
    $v = $this->options->get( 'chart_idp_type' );

    ?>

    <input type="radio" name="<?php echo $this->options->get_options_name() ?>[chart_idp_type]" id="wpa0_auth0_idp_chart_type_donut" value="donut" <?php echo checked( $v, 'donut', false ); ?>/>
    <label for="wpa0_auth0_idp_chart_type_donut"><?php echo __( 'Donut', WPA0_LANG ); ?></label>

    <input type="radio" name="<?php echo $this->options->get_options_name() ?>[chart_idp_type]" id="wpa0_auth0_idp_chart_type_pie" value="pie" <?php echo checked( $v, 'pie', false ); ?>/>
    <label for="wpa0_auth0_idp_chart_type_pie"><?php echo __( 'Pie', WPA0_LANG ); ?></label>
    &nbsp;
    <input type="radio" name="<?php echo $this->options->get_options_name() ?>[chart_idp_type]" id="wpa0_auth0_idp_chart_type_bar" value="bar" <?php echo checked( $v, 'bar', false ); ?>/>
    <label for="wpa0_auth0_idp_chart_type_bars"><?php echo __( 'Bars', WPA0_LANG ); ?></label>

    <?php
  }

  public function render_gender_chart_type() {
    $v = $this->options->get( 'chart_gender_type' );

    ?>

    <input type="radio" name="<?php echo $this->options->get_options_name() ?>[chart_gender_type]" id="wpa0_auth0_gender_chart_type_donut" value="donut" <?php echo checked( $v, 'donut', false ); ?>/>
    <label for="wpa0_auth0_gender_chart_type_donut"><?php echo __( 'Donut', WPA0_LANG ); ?></label>
    
    <input type="radio" name="<?php echo $this->options->get_options_name() ?>[chart_gender_type]" id="wpa0_auth0_gender_chart_type_pie" value="pie" <?php echo checked( $v, 'pie', false ); ?>/>
    <label for="wpa0_auth0_gender_chart_type_pie"><?php echo __( 'Pie', WPA0_LANG ); ?></label>
    &nbsp;
    <input type="radio" name="<?php echo $this->options->get_options_name() ?>[chart_gender_type]" id="wpa0_auth0_gender_chart_type_bar" value="bar" <?php echo checked( $v, 'bar', false ); ?>/>
    <label for="wpa0_auth0_gender_chart_type_bars"><?php echo __( 'Bars', WPA0_LANG ); ?></label>

    <?php
  }

  protected function validate_chart_type($type) {
    $validChartTypes = array('pie','bar','donut');

    if ( in_array( $type, $validChartTypes ) ) {
        return $type;
    }

    return $validChartTypes[0];
  }

  public function basic_validation( $old_options, $input ) {
    $input['chart_gender_type'] = $this->validate_chart_type( $input['chart_gender_type'] );
    $input['chart_idp_type'] = $this->validate_chart_type( $input['chart_idp_type'] );
    $input['chart_age_type'] = $this->validate_chart_type( $input['chart_age_type'] );

    return $input;
  }


}
