<?php

require_once plugin_dir_path( __FILE__ ) . '../../../class-track-manager.php';
require_once plugin_dir_path( __FILE__ ) . '../../class-metadata-request.php';

class Shoutcast_Json_Metadata_Strategy implements Shoutcast_Metadata_Strategy_Interface {

	protected $track_manager;

	public function __construct() {
		$this->track_manager = new Track_Manager();
	}

	public function get_track_history( $url, $username = null, $password = null ) {
		$response = Metadata_Request::get_with_optional_basic_auth($url, $username, $password);

		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) != 200 ) {
			return new WP_Error( 'shoutcast_metadata_error', __( 'Unable to retrieve metadata.', 'radio-ht' ) );
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body );


		$tracks = [];
		if ( ! empty( $data )) {
			foreach ( $data as $track ) {
				$timestamp  = (string) $track->playedat;
				$full_title = $track->title;
				if ( empty( $timestamp ) || empty( $full_title ) ) {
					continue;
				}

				$track_info = explode( ' - ', $full_title, 2 );
				$artist = '';
				if ( count( $track_info ) > 1 ) {
					$artist = trim($track_info[0]);
					$title  = $this->track_manager->get_title( $track_info[1] );
				} else {
					$title = $this->track_manager->get_title( $track_info[0] );
				}

				$tracks[] = $this->track_manager->format_track( $artist, $title, $timestamp );
			}
		}

		return ! empty( $tracks ) ? $tracks : false;
	}
}