<?php
/**
 * Modify existing or add new settings fields.
 *
 * @param array  $options - array of options for a specific settings tab.
 * @param string $id      - settings tab id.
 *
 * @return array
 *
 * @see WP_Auth0_Admin_Generic::init_option_section()
 */
function example_auth0_settings_fields( $options, $id ) {
	switch ( $id ) {
		case 'basic':
			$options[] = [
				'name'     => __( 'A Custom Basic Setting', 'wp-auth0' ),
				'opt'      => 'custom_basic_opt_name',
				'id'       => 'wpa0_custom_basic_opt_name',
				'function' => 'example_render_custom_basic_opt_name',
			];
			break;
		case 'features':
			break;
		case 'appearance':
			break;
		case 'advanced':
			break;
	}
	return $options;
}
 add_filter( 'auth0_settings_fields', 'example_auth0_settings_fields', 10, 2 );

/**
 * Callback for add_settings_field
 *
 * @param array $args - 'label_for' = id attr, 'opt_name' = option name
 *
 * @see example_auth0_settings_fields()
 */
function example_render_custom_basic_opt_name( $args ) {
	$options = WP_Auth0_Options::Instance();
	printf(
		'<input type="text" name="%s[%s]" id="%s" value="%s">',
		esc_attr( $options->get_options_name() ),
		esc_attr( $args['opt_name'] ),
		esc_attr( $args['label_for'] ),
		esc_attr( $options->get( $args['opt_name'] ) )
	);
}
