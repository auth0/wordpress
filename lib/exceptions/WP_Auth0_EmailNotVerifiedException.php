<?php

class WP_Auth0_EmailNotVerifiedException extends Exception {

	public $userinfo;
	public $id_token;
	public $access_token;

	public function __construct( $userinfo, $id_token, $access_token ) {
		$this->userinfo = $userinfo;
		$this->id_token = $id_token;
		$this->access_token = $access_token;
	}

}
