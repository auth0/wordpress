<?php
class WP_Auth0_Serializer {

	public static function serialize( $o ) {
		return json_encode( $o );
	}
	public static function unserialize( $s ) {
		if ( $s[0] === '{' ) {
			return json_decode( $s );
		}

		return @unserialize( $s );
	}

}
