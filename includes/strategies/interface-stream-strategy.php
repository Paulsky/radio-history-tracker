<?php

interface Stream_Strategy_Interface {

	/**
	 * Get tracks from the endpoint
	 *
	 * @param string $meta_data_type The type of metadata (e.g., 'xml', 'json', 'html').
	 * @param string $endpoint The URL of the stream or metadata endpoint.
	 * @param null $username
	 * @param null $password
	 *
	 * @return mixed
	 */
	public function tracks( $meta_data_type, $endpoint, $username = null, $password = null );

	/**
	 * Get and save tracks from the endpoint
	 *
	 * @param string $meta_data_type The type of metadata (e.g., 'xml', 'json', 'html').
	 * @param string $endpoint The URL of the stream or metadata endpoint.
	 * @param null $radio_stream_id The post id of the Radio Stream
	 * @param null $username
	 * @param null $password
	 *
	 * @return mixed
	 */
	public function process( $meta_data_type, $endpoint, $radio_stream_id = null, $username = null, $password = null );
}