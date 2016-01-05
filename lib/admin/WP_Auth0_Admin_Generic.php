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

  protected function render_a0_switch($id, $name, $value, $checked) {
    ?>

    <div class="a0-switch">
      <input type="checkbox" name="<?php echo $this->a0_options->get_options_name(); ?>[<?php echo $name; ?>]" id="<?php echo $id; ?>" value="<?php echo $value; ?>" <?php echo checked( $checked ); ?>/>
      <label for="<?php echo $id; ?>"></label>
    </div>

    <?php
  }

}