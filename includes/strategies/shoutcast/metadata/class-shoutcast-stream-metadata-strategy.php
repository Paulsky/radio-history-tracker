<?php

require_once plugin_dir_path( __FILE__ ) . '../../../class-track-manager.php';

class Shoutcast_Steam_Metadata_Strategy implements Shoutcast_Metadata_Strategy_Interface {
	protected $track_manager;

	public function __construct() {
		$this->track_manager = new Track_Manager();
	}

	public function get_track_history( $url, $username = null, $password = null ) {

		$full_title = $this->get_track_title( $url );

		if ( is_wp_error( $full_title ) || $full_title === false ) {
			return new WP_Error( 'shoutcast_metadata_error', __( 'Unable to retrieve metadata.', 'radio-ht' ) );
		}

		$date       = time();
		$track_info = explode( ' - ', $full_title, 2 );
		$artist     = $track_info[0];
		$title      = $this->track_manager->get_title( $track_info[1] );

		return [ $this->track_manager->format_track( $artist, $title, $date ) ];
	}

	private function get_track_title( $stream_url ) {
		$context = $this->create_stream_context();
		$stream  = @fopen( $stream_url, 'r', false, $context );

		if ( ! $stream ) {
			return new WP_Error( 'shoutcast_metadata_error', __( 'Unable to retrieve metadata.', 'radio-ht' ) );
		}

		$icy_metaint = $this->get_icy_metaint( $stream );
		if ( is_wp_error( $icy_metaint ) ) {
			fclose( $stream );

			return $icy_metaint;
		}

		$result = $this->find_stream_title( $stream, $icy_metaint );
		fclose( $stream );

		return $result;
	}

	private function create_stream_context() {
		$opts = [
			'http' => [
				'method'     => 'GET',
				'header'     => 'Icy-MetaData: 1',
				'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.212 Safari/537.36'
			],
			'ssl'  => [
				'allow_self_signed' => true,
				'verify_peer'       => false,
				'verify_peer_name'  => false,
			]
		];

		return stream_context_create( $opts );
	}

	private function get_icy_metaint( $stream ) {
		$meta_data = stream_get_meta_data( $stream );
		if ( isset( $meta_data['wrapper_data'] ) ) {
			foreach ( $meta_data['wrapper_data'] as $header ) {
				if ( strpos( strtolower( $header ), 'icy-metaint' ) !== false ) {
					list( , $icy_metaint ) = explode( ':', $header );

					return trim( $icy_metaint );
				}
			}
		}

		return new WP_Error( 'shoutcast_metadata_error', __( 'Unable to retrieve metadata.', 'radio-ht' ) );
	}

	private function find_stream_title( $stream, $icy_metaint ) {
		$buffer = stream_get_contents( $stream, 300, $icy_metaint );
		$needle = 'StreamTitle=';
		if ( strpos( $buffer, $needle ) !== false ) {
			$title = explode( $needle, $buffer );
			$title = trim( $title[1] );
			$title = str_replace( '&amp;', '&', $title );
			if ( $title !== '' ) {
				return substr( $title, 1, strpos( $title, ';' ) - 2 );
			}
		}

		return false;
	}
}