<?php

class Artwork_Manager {

	public function fetch_artwork( $artist, $title ) {
		$base_url    = 'https://itunes.apple.com/search?';
		$search_term = $artist . ' ' . $title;
		$params      = [
			'term'   => $search_term,
			'media'  => 'music',
			'entity' => 'song',
			//'country' => 'US',
			'limit'  => 1,
		];

		$url = $base_url . http_build_query( $params );

		$response = wp_remote_get( $url );

		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) != 200 ) {
			return new WP_Error( 'rht_itunes_artwork_error', __( 'Unable to retrieve artwork.', "radio-ht" ) );
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( ! empty( $data['results'] ) && isset( $data['results'][0] ) ) {
			$first_result = $data['results'][0];

			$artwork_url = isset( $first_result['artworkUrl100'] ) ? str_replace( '100x100bb', '600x600bb', $first_result['artworkUrl100'] ) : null;

			return [
				'artwork_url' => $artwork_url,
//				'artist'      => isset( $first_result['artistName'] ) ? $first_result['artistName'] : 'Unknown Artist',
//				'title'       => isset( $first_result['trackName'] ) ? $first_result['trackName'] : 'Unknown Title',
//				'album'       => isset( $first_result['collectionName'] ) ? $first_result['collectionName'] : 'Unknown Album',
			];
		}

		return false;
	}
}