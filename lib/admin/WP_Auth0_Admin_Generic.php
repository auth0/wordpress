<?php 

class WP_Auth0_Admin_Generic {

  protected $a0_options;

  protected $actions_middlewares = array();

  public function __construct(WP_Auth0_Options $a0_options) {
    $this->a0_options = $a0_options;
  }

  protected function init_option_section($sectionName, $id, $settings) {
    $options_name = $this->a0_options->get_options_name() . '_' . strtolower($id);

    add_settings_section(
      "wp_auth0_{$id}_settings_section",
      __( $sectionName, WPA0_LANG ),
      array( $this, "render_{$id}_description" ),
      $options_name
    );

    foreach ( $settings as $setting ) {
      add_settings_field(
        $setting['id'],
        __( $setting['name'], WPA0_LANG ),
        array( $this, $setting['function'] ),
        $options_name,
        "wp_auth0_{$id}_settings_section",
        array( 'label_for' => $setting['id'] )
      );
    }
  }

  public function input_validator( $input ){

    $old_options = $this->a0_options->get_options();

    foreach ($this->actions_middlewares as $action) {
      $input = $this->$action($old_options, $input);
    }

    return $input;
  }

  protected function add_validation_error( $error ) {
    add_settings_error(
      $this->a0_options->get_options_name(),
      $this->a0_options->get_options_name(),
      $error,
      'error'
    );
  }

  protected function rule_validation( $old_options, $input, $key, $rule_name, $rule_script ) {
    $input[$key] = ( isset( $input[$key] ) ? $input[$key] : null );


    if ($input[$key] !== null && $old_options[$key] === null || $input[$key] === null && $old_options[$key] !== null) {
      try {

        $operations = new WP_Auth0_Api_Operations($this->a0_options);
        $input[$key] = $operations->toggle_rule ( $this->a0_options->get( 'auth0_app_token' ), (is_null($input[$key]) ? $old_options[$key] : null), $rule_name, $rule_script );

      } catch (Exception $e) {
        $this->add_validation_error( $e->getMessage() );
        $input[$key] = null;
      }
    }

    return $input;
  }


  protected function render_a0_switch($id, $name, $value, $checked) {
    ?>

    <div class="a0-switch">
      <input type="checkbox" name="<?php echo $this->a0_options->get_options_name(); ?>[<?php echo $name; ?>]" id="<?php echo $id; ?>" value="<?php echo $value; ?>" <?php echo checked( $checked ); ?>/>
      <label for="<?php echo $id; ?>"></label>
    </div>

    <?php
  }

}