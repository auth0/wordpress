<?php
/**
 * Contains Class TestEmbedWidget.
 *
 * @package WP-Auth0
 *
 * @since 3.11.1
 */

class EmbedWidgetTest extends WP_Auth0_Test_Case {

	use HookHelpers;

	use UsersHelper;

	public function testThatWidgetNameIsCorrect() {
		$widget = new WP_Auth0_Embed_Widget();
		$this->assertEquals( 'Auth0 Login', $widget->name );
	}

	public function testThatWidgetDescriptionIsCorrect() {
		$widget = new WP_Auth0_Embed_Widget();
		$this->assertEquals( 'Shows Auth0 login form in your sidebar', $widget->widget_options['description'] );
	}

	public function testThatWidgetIdIsCorrect() {
		$widget = new WP_Auth0_Embed_Widget();
		$this->assertEquals( 'wp_auth0_widget', $widget->id_base );
	}

	public function testThatInvalidDictJsonIsRevertedToPreviousValue() {
		$widget    = new WP_Auth0_Embed_Widget();
		$new_opts  = [
			'dict'       => uniqid(),
			'extra_conf' => '',
		];
		$old_opts  = [ 'dict' => uniqid() ];
		$validated = $widget->update( $new_opts, $old_opts );

		$this->assertEquals( $old_opts['dict'], $validated['dict'] );
	}

	public function testThatEmptyDictJsonIsAccepted() {
		$widget    = new WP_Auth0_Embed_Widget();
		$new_opts  = [
			'dict'       => '',
			'extra_conf' => '',
		];
		$old_opts  = [ 'dict' => uniqid() ];
		$validated = $widget->update( $new_opts, $old_opts );

		$this->assertEmpty( $validated['dict'] );
	}

	public function testThatInvalidConfJsonIsRevertedToPreviousValue() {
		$widget    = new WP_Auth0_Embed_Widget();
		$new_opts  = [
			'extra_conf' => uniqid(),
			'dict'       => '',
		];
		$old_opts  = [ 'extra_conf' => uniqid() ];
		$validated = $widget->update( $new_opts, $old_opts );

		$this->assertEquals( $old_opts['extra_conf'], $validated['extra_conf'] );
	}

	public function testThatEmptyConfJsonIsAccepted() {
		$widget    = new WP_Auth0_Embed_Widget();
		$new_opts  = [
			'extra_conf' => '',
			'dict'       => '',
		];
		$old_opts  = [ 'extra_conf' => uniqid() ];
		$validated = $widget->update( $new_opts, $old_opts );

		$this->assertEmpty( $validated['extra_conf'] );
	}

	public function testThatInvalidRedirectUrlIsSetToDefault() {
		$widget    = new WP_Auth0_Embed_Widget();
		$new_opts  = [
			'redirect_to' => 'https://auth0.com',
			'extra_conf'  => '',
			'dict'        => '',
		];
		$old_opts  = [ 'redirect_to' => site_url() . '/path' ];
		$validated = $widget->update( $new_opts, $old_opts );

		$this->assertEquals( $old_opts['redirect_to'], $validated['redirect_to'] );
	}

	public function testThatEmptyRedirectUrlIsAccepted() {
		$widget    = new WP_Auth0_Embed_Widget();
		$new_opts  = [
			'redirect_to' => '',
			'extra_conf'  => '',
			'dict'        => '',
		];
		$old_opts  = [ 'redirect_to' => site_url() ];
		$validated = $widget->update( $new_opts, $old_opts );

		$this->assertEmpty( $validated['redirect_to'] );
	}
}
