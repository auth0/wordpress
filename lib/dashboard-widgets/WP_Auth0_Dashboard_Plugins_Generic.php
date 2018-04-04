<?php
// TODO: Deprecate
class WP_Auth0_Dashboard_Plugins_Generic {

	protected function gettype( $user ) {

	}

	const UNKNOWN_KEY = 'unknown';

	protected $id = null;
	protected $name = null;

	public function getId() {
		return $this->id;
	}

	public function getName() {
		return $this->name;
	}

	protected $users = array();

	public function addUser( $user ) {
		$types = $this->getType( $user );

		if ( $types === null ) {
			return;
		}

		if ( ! is_array( $types ) ) {
			$types = array( $types );
		}
		foreach ( $types as $type ) {
			if ( ! isset( $this->users[$type] ) ) {
				$this->users[$type] = 0;
			}
		}
		$this->users[$type]++;
	}

}
