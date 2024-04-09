<?php

require_once plugin_dir_path( __FILE__ ) . '../interface-stream-strategy.php';
require_once plugin_dir_path( __FILE__ ) . 'metadata/interface-shoutcast-metadata-strategy.php';
require_once plugin_dir_path( __FILE__ ) . 'metadata/class-shoutcast-xml-metadata-strategy.php';
require_once plugin_dir_path( __FILE__ ) . 'metadata/class-shoutcast-json-metadata-strategy.php';
require_once plugin_dir_path( __FILE__ ) . 'metadata/class-shoutcast-html-metadata-strategy.php';
require_once plugin_dir_path( __FILE__ ) . 'metadata/class-shoutcast-stream-metadata-strategy.php';
require_once plugin_dir_path( __FILE__ ) . '../../class-track-manager.php';

class Shoutcast_Strategy implements Stream_Strategy_Interface {

	/**
	 * @throws Exception If the metadata type is unsupported.
	 */
	public function tracks( $meta_data_type, $endpoint, $username = null, $password = null ) {
		$strategy = $this->get_strategy( $meta_data_type );

		return $strategy->get_track_history( $endpoint, $username, $password );
	}

	/**
	 * @throws Exception If the metadata type is unsupported.
	 */
	public function process( $meta_data_type, $endpoint, $radio_stream_id = null, $username = null, $password = null ) {
		$strategy = $this->get_strategy( $meta_data_type );

		$tracks = $strategy->get_track_history( $endpoint, $username, $password );

		$processed = [];

		if ( empty( $tracks ) || is_wp_error( $tracks ) ) {
			return $processed;
		}

		$parser = new Track_Manager();

		foreach ( $tracks as $track_data ) {

			$post_id = $parser->find_or_create_track( $track_data['title'], $track_data['artist_id'] );

			if ( $post_id ) {
				$parser->update_played_at( $post_id, $track_data['played_at'] );
				$parser->update_artwork( $post_id, $track_data['artwork_url'] );

				if ( isset( $radio_stream_id ) ) {
					$parser->update_radio_stream( $post_id, $radio_stream_id );
				}

				$processed[] = $post_id;
			}
		}
	}

	/**
	 * @param string $meta_data_type
	 *
	 * @return Shoutcast_Html_Metadata_Strategy|Shoutcast_Json_Metadata_Strategy|Shoutcast_Steam_Metadata_Strategy|Shoutcast_Xml_Metadata_Strategy
	 * @throws Exception
	 */
	private function get_strategy( string $meta_data_type ): Shoutcast_Xml_Metadata_Strategy|Shoutcast_Html_Metadata_Strategy|Shoutcast_Steam_Metadata_Strategy|Shoutcast_Json_Metadata_Strategy {
		switch ( $meta_data_type ) {
			case 'xml':
				$strategy = new Shoutcast_Xml_Metadata_Strategy();
				break;
			case 'json':
				$strategy = new Shoutcast_Json_Metadata_Strategy();
				break;
			case 'html':
				$strategy = new Shoutcast_Html_Metadata_Strategy();
				break;
			case 'stream':
				$strategy = new Shoutcast_Steam_Metadata_Strategy();
				break;
			default:
				throw new Exception( __( "Unsupported metadata type: ", "radio-ht" ) . $meta_data_type );
		}

		return $strategy;
	}
}