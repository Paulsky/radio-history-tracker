<?php

require_once plugin_dir_path( __FILE__ ) . 'class-artwork-manager.php';
require_once plugin_dir_path( __FILE__ ) . 'class-artist-manager.php';

class Track_Manager {

	protected $artwork_manager;

	protected $artist_manager;

	public function __construct() {
		$this->artwork_manager = new Artwork_Manager();
		$this->artist_manager  = new Artist_Manager();
	}

	public static function get_slug( $artist, $title ) {
		$artist = trim( $artist );
		$title  = trim( $title );

		$slug_base = ( ! empty( $artist ) ? $artist . ' ' : '' ) . $title;
		$slug      = sanitize_title( $slug_base );

		return $slug;
	}

	public function convert_play_time_to_timestamp( $played_at ) {
		$played_time = DateTime::createFromFormat( 'H:i:s', $played_at );

		if ( ! $played_time ) {
			return false;
		}

		$current_time = new DateTime();
		if ( $played_time > $current_time ) {
			$playedDate = ( clone $current_time )->sub( new DateInterval( 'P1D' ) )->setTime( $played_time->format( 'H' ), $played_time->format( 'i' ), $played_time->format( 's' ) );
		} else {
			$playedDate = ( clone $current_time )->setTime( $played_time->format( 'H' ), $played_time->format( 'i' ), $played_time->format( 's' ) );
		}

		return $playedDate->getTimestamp();
	}

	public function get_title( $string ) {
		$trimmedString = trim( $string );

		if ( substr( $trimmedString, - 2 ) === " -" ) {
			$trimmedString = substr( $trimmedString, 0, - 2 );
		}

		return ! empty( $trimmedString ) ? $trimmedString : __( 'Unknown title', 'radio-ht' );
	}

	public function format_track( $artist, $title, $timestamp ) {
		$artwork_url = null;
		if ( $title !== __( 'Unknown title', 'radio-ht' ) ) {
			$artwork = $this->artwork_manager->fetch_artwork( $artist, $title );
			if ( ! is_wp_error( $artwork ) && $artwork !== false ) {
				$artwork_url = $artwork ? $artwork['artwork_url'] : null;
			}

		}

		$artist_id = null;
		if ( isset( $artist ) ) {
			$artist_id = $this->artist_manager->find_or_create_artist( $artist );
		}

		return [
			'played_at'   => $timestamp,
			'artist'      => $artist,
			'title'       => wp_strip_all_tags( $title ),
			'artwork_url' => $artwork_url,
			'artist_id'   => $artist_id,
		];
	}


	public function find_or_create_track( $title, $artist_id ) {
		if ( empty( $title ) ) {
			return false;
		}
		if ( $title !== __( 'Unknown title', 'radio-ht' ) ) {
			return false;
		}


		$artist = $this->artist_manager->get_artist_name( $artist_id );

		$slug = self::get_slug( $artist, $title );

		$args = array(
			'post_type'      => 'rht_track',
			'posts_per_page' => 1,
			'meta_query'     => array(
				array(
					'key'     => 'rht_track_slug',
					'value'   => $slug,
					'compare' => '='
				)
			)
		);

		$query = new WP_Query( $args );

		if ( ! empty( $query->posts ) ) {
			$post = $query->posts[0];

			if ( ! empty( $artist ) ) {
				wp_set_object_terms( $post->ID, [ (int) $artist_id ], 'rht_artist', false );
			}

			return $post->ID;
		}


		$post_id = wp_insert_post( array(
			'post_title'  => $title,
			'post_type'   => 'rht_track',
			'post_status' => 'publish',
		) );

		if ( is_wp_error( $post_id ) ) {
			return false;
		}

//		add_post_meta( $post_id, 'rht_track_artist', $artist );
//		$this->sort_and_update_track_metadata( $post_id );

		if ( ! empty( $artist ) ) {
			wp_set_object_terms( $post_id, [ (int) $artist_id ], 'rht_artist', false );
		}

		return $post_id;

	}

	public function update_played_at( $post_id, $played_at ) {
		if ( empty( $played_at ) ) {
			return false;
		}

		$timestamps = get_post_meta( $post_id, 'rht_track_played_at_timestamps', true );
		if ( ! is_array( $timestamps ) ) {
			$timestamps = [];
		}

		if ( in_array( $played_at, $timestamps ) ) {
			return false;
		}

		$now             = current_time( 'timestamp' );
		$ten_minutes_ago = $now - ( 10 * 60 );

		$is_recent_played = false;
		foreach ( $timestamps as $timestamp ) {
			if ( $timestamp >= $ten_minutes_ago ) {
				$is_recent_played = true;
				break;
			}
		}

		if ( ! $is_recent_played ) {
			$timestamps[] = $played_at;
			update_post_meta( $post_id, 'rht_track_played_at_timestamps', $timestamps );
			$this->sort_and_update_track_metadata( $post_id );
		}

		return true;
	}


	public function update_artwork( $post_id, $artwork_url ) {
		if ( empty( $artwork_url ) || has_post_thumbnail( $post_id ) ) {
			return false;
		}

		add_filter( 'wp_insert_attachment_data', [ $this, 'clear_attachment_fields' ], 10, 2 );
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/media.php' );
		$attach_id = media_sideload_image( $artwork_url, $post_id, null, 'id' );
		remove_filter( 'wp_insert_attachment_data', [ $this, 'clear_attachment_fields' ], 10 );

		if ( is_wp_error( $attach_id ) ) {
			return false;
		}

		set_post_thumbnail( $post_id, $attach_id );

		return true;
	}

	public function update_radio_stream( $post_id, $radio_stream_id ) {
		update_post_meta( $post_id, 'rht_track_radio_stream_id', $radio_stream_id );
	}

	public function sort_and_update_track_metadata( $post_id ) {
		$post = get_post( $post_id );
		if ( $post->post_type != 'rht_track' ) {
			return;
		}

		// Generate slug from artist and title
		//$artist     = get_post_meta( $post_id, 'rht_track_artist', true );
		$artist_name = '';
		$artist      = $this->artist_manager->get_first_artist_for_track( $post_id );
		if ( $artist ) {
			$artist_name = $artist->name;
		}

		$slug_base  = Track_Manager::get_slug( $artist_name, $post->post_title );
		$track_slug = sanitize_title( $slug_base );

		// Save the generated slug
		update_post_meta( $post_id, 'rht_track_slug', $track_slug );

		$timestamps = get_post_meta( $post_id, 'rht_track_played_at_timestamps', true ) ?: [];

		$hasTimestamps = false;
		if ( is_array( $timestamps ) ) {
			if ( ! empty( $timestamps ) ) {
				$hasTimestamps = true;
				usort( $timestamps, function ( $a, $b ) {
					return $b - $a;
				} );

				update_post_meta( $post_id, 'rht_track_played_at_timestamps', $timestamps );
				update_post_meta( $post_id, 'rht_track_latest_played_at', $timestamps[0] );
				update_post_meta( $post_id, 'rht_track_played_count', count( $timestamps ) );
			}
		}

		if ( ! $hasTimestamps ) {
			delete_post_meta( $post_id, 'rht_track_latest_played_at' );
			delete_post_meta( $post_id, 'rht_track_played_count' );
		}
	}

	public function clear_attachment_fields( $data, $postarr ) {
		$post_parent_id = $postarr['post_parent'];
		if ( $post_parent_id ) {
			$post_parent = get_post( $post_parent_id );
			if ( $post_parent ) {
				$data['post_title']   = $post_parent->post_title;
				$data['post_excerpt'] = '';
				$data['post_content'] = '';
			}
		}

		return $data;
	}
}