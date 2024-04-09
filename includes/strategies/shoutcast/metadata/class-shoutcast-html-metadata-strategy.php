<?php

require_once plugin_dir_path( __FILE__ ) . '../../../class-track-manager.php';
require_once plugin_dir_path( __FILE__ ) . '../../class-metadata-request.php';

class Shoutcast_Html_Metadata_Strategy implements Shoutcast_Metadata_Strategy_Interface {
	protected $track_manager;

	public function __construct() {
		$this->track_manager = new Track_Manager();
	}

	public function get_track_history( $url, $username = null, $password = null ) {
		$response = Metadata_Request::get_with_optional_basic_auth( $url, $username, $password );

		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) != 200 ) {
			return new WP_Error( 'shoutcast_metadata_error', __( 'Unable to retrieve metadata.', 'radio-ht' ) );
		}

		$body = wp_remote_retrieve_body( $response );

		$dom = new DOMDocument();
		@$dom->loadHTML( $body );

		$xpath = new DOMXPath( $dom );

		$tables = $xpath->query( '//table[.//*[contains(translate(text(),"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"song")]]' );

		if ( $tables->length > 0 ) {
			$table = $tables->item( 0 );
			$rows  = $xpath->query( './/tr', $table );

			$tracks = [];
			foreach ( $rows as $row ) {
				$cells = $row->getElementsByTagName( 'td' );
				if ( $cells->length > 1 ) {
					$played_at  = $cells->item( 0 )->textContent;
					$full_title = $cells->item( 1 )->textContent;
					if ( empty( $played_at ) || empty( $full_title ) ) {
						continue;
					}
					$date = $this->track_manager->convert_play_time_to_timestamp( $played_at );
					if ( ! $date ) {
						continue;
					}

					$track_info = explode( ' - ', $full_title, 2 );
					$title      = $this->track_manager->get_title( $track_info[0] );
					$artist     = null;
					if ( isset( $track_info[1] ) ) {
						$artist = trim( $track_info[1] );
					}

					$tracks[] = $this->track_manager->format_track( $artist, $title, $date );
				}
			}

			return ! empty( $tracks ) ? $tracks : false;
		}

		return false;
	}
}