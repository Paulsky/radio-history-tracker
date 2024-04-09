<?php


require_once plugin_dir_path( dirname( __FILE__ ) ) . 'strategies/shoutcast/class-shoutcast-strategy.php';

class Stream_Strategy_Manager {

	/**
	 * Returns the strategy instance for the given stream type.
	 *
	 * @param string $stream_type The type of the stream (e.g., 'shoutcast').
	 *
	 * @return Stream_Strategy_Interface
	 * @throws Exception When an unsupported stream type is provided.
	 */
	public function get_strategy( $stream_type ) {
		switch ( $stream_type ) {
			case 'shoutcast':
				return new Shoutcast_Strategy();
			default:
				throw new Exception( __( "Unsupported stream type: ", "radio-ht" ) . $stream_type );
		}
	}
}