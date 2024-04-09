<?php

require_once plugin_dir_path( __FILE__ ) . '../../../class-track-manager.php';
require_once plugin_dir_path( __FILE__ ) . '../../class-metadata-request.php';

class Shoutcast_Xml_Metadata_Strategy implements Shoutcast_Metadata_Strategy_Interface {

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
		$xml  = simplexml_load_string( $body );

		$tracks = [];
		if ( ! empty( $xml->SONGHISTORY ) && ! empty( $xml->SONGHISTORY->SONG ) ) {
			foreach ( $xml->SONGHISTORY->SONG as $track ) {
				$timestamp  = (string) $track->PLAYEDAT;
				$full_title = $track->TITLE;
				if ( empty( $timestamp ) || empty( $full_title ) ) {
					continue;
				}

				$artistIndex = 0;
				$titleIndex  = 1;
				//sometimes the title is displayed like this:
				//artist - title
				//but sometimes the title is displayed like this:
				//title - artist
				if ( ! empty( $track->METADATA ) ) {
					$artistIndex = 1;
					$titleIndex  = 0;
				}

				$track_info = explode( ' - ', $full_title, 2 );

				$artist = '';
				if ( count( $track_info ) > 1 ) {
					$artist = trim( $track_info[ $artistIndex ] );
					$title  = $this->track_manager->get_title( $track_info[ $titleIndex ] );
				} else {
					$title = $this->track_manager->get_title( $track_info[0] );
				}

				$tracks[] = $this->track_manager->format_track( $artist, $title, $timestamp );
			}
		}

		return ! empty( $tracks ) ? $tracks : false;
	}
}