<?php

class Metadata_Request {

	public static function get_with_optional_basic_auth( $url, $username = null, $password = null ) {
		if ( $username && $password ) {
			$auth = base64_encode( "$username:$password" );
			$args = array(
				'headers' => array(
					'Authorization' => 'Basic ' . $auth,
				),
			);

			return wp_remote_get( $url, $args );
		}

		return wp_remote_get( $url );
	}
}