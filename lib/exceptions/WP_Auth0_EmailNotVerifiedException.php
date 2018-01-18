<?php

class WP_Auth0_EmailNotVerifiedException extends Exception {

	public $userinfo;
	public $id_token;

	public function __construct( $userinfo, $id_token ) {
		$this->userinfo = $userinfo;
		$this->id_token = $id_token;
	}

}
