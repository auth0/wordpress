<?php
/**
 * Contains Class TestRenderForm.
 *
 * @package WP-Auth0
 *
 * @since 3.8.0
 */

class RenderFormTest extends WP_Auth0_Test_Case {

	public function testThatRenderFormPassesThroughIfPluginNotReady() {
		$this->assertEquals( '__initial_html__', wp_auth0_render_lock_form( '__initial_html__' ) );
	}

	public function testThatHtmlIsIgnoredIfAuth0ParamSet() {
		$this->auth0Ready();
		$_GET['auth0'] = 1;
		$this->assertNotEquals( '__initial_html__', wp_auth0_render_lock_form( '__initial_html__' ) );
	}

	public function testThatHtmlIsIgnoredIfLostpasswordParamSet() {
		$this->auth0Ready();
		$_GET['action'] = 'lostpassword';
		$this->assertNotEquals( '__initial_html__', wp_auth0_render_lock_form( '__initial_html__' ) );
	}

	public function testThatHtmlIsIgnoredWhenAuth0IsReady() {
		$this->auth0Ready();
		$this->assertNotEquals( '__initial_html__', wp_auth0_render_lock_form( '__initial_html__' ) );
	}
}
