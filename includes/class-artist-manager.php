<?php

class Artist_Manager {
	public function find_or_create_artist( $artist_name ) {

		if ( empty( $artist_name ) ) {
			return null;
		}

		$term = term_exists( $artist_name, 'rht_artist' );

		if ( ! $term ) {
			$term = wp_insert_term( $artist_name, 'rht_artist' );

			if ( is_wp_error( $term ) ) {
				return null;
			} else {
				return $term['term_id'];
			}
		} else {
			return is_array( $term ) ? $term['term_id'] : $term;
		}
	}

	public function get_artist_name( $term_id ) {
		$name = '';
		if ( $term_id ) {
			$term = get_term( $term_id, 'rht_artist' );

			if ( ! is_wp_error( $term ) && $term ) {
				$name = $term->name;
			}
		}

		return $name;
	}

	public function get_first_artist_for_track( $track_id ) {
		$terms = wp_get_post_terms( $track_id, 'rht_artist', array( 'orderby' => 'term_id', 'order' => 'ASC' ) );


		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return null;
		}

		return $terms[0];
	}
}