<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://wijnberg.dev
 * @since      1.0.0
 *
 * @package    Radio_History_Tracker
 * @subpackage Radio_History_Tracker/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Radio_History_Tracker
 * @subpackage Radio_History_Tracker/public
 * @author     Wijnberg Developments <contact@wijnberg.dev>
 */
class Radio_History_Tracker_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/radio-history-tracker-public.css', array(), $this->version, 'all' );

	}
//
//	/**
//	 * Register the JavaScript for the public-facing side of the site.
//	 *
//	 * @since    1.0.0
//	 */
//	public function enqueue_scripts() {
//
//		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/radio-history-tracker-public.js', array( 'jquery' ), $this->version, false );
//
//	}

	public function register_radio_stream_cpt() {
		$options           = get_option( $this->plugin_name . '_option_name' );
		$public_visibility = isset( $options['public_visibility'] ) ? (bool) $options['public_visibility'] : false;

		$args = [
			'public'              => $public_visibility,
			'publicly_queryable'  => $public_visibility,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'exclude_from_search' => true,
			'has_archive'         => $public_visibility,
			'show_in_rest'        => true,
			'rewrite'             => [ 'slug' => 'radio-stream' ],
			'menu_icon'           => 'dashicons-media-audio',
			'labels'              => [
				'name'               => _x( 'Radio Streams', 'post type general name', 'radio-ht' ),
				'singular_name'      => _x( 'Radio Stream', 'post type singular name', 'radio-ht' ),
				'menu_name'          => _x( 'Radio Streams', 'admin menu', 'radio-ht' ),
				'name_admin_bar'     => _x( 'Radio Stream', 'add new on admin bar', 'radio-ht' ),
				'add_new'            => _x( 'Add New', 'radio stream', 'radio-ht' ),
				'add_new_item'       => __( 'Add New Radio Stream', 'radio-ht' ),
				'new_item'           => __( 'New Radio Stream', 'radio-ht' ),
				'edit_item'          => __( 'Edit Radio Stream', 'radio-ht' ),
				'view_item'          => __( 'View Radio Stream', 'radio-ht' ),
				'all_items'          => __( 'All Radio Streams', 'radio-ht' ),
				'search_items'       => __( 'Search Radio Streams', 'radio-ht' ),
				'parent_item_colon'  => __( 'Parent Radio Streams:', 'radio-ht' ),
				'not_found'          => __( 'No radio streams found.', 'radio-ht' ),
				'not_found_in_trash' => __( 'No radio streams found in Trash.', 'radio-ht' )
			],
			'supports'            => [ 'title', 'editor', 'thumbnail' ]
		];

		register_post_type( 'rht_radio_stream', $args );
	}

	public function register_track_cpt() {
		$options           = get_option( $this->plugin_name . '_option_name' );
		$public_visibility = isset( $options['track_public_visibility'] ) ? (bool) $options['track_public_visibility'] : true;

		$args = [
			'public'              => $public_visibility,
			'publicly_queryable'  => $public_visibility,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'exclude_from_search' => ! $public_visibility,
			'has_archive'         => $public_visibility,
			'show_in_rest'        => true,
			'rewrite'             => [ 'slug' => 'track' ],
			'menu_icon'           => 'dashicons-format-audio',
			'labels'              => [
				'name'               => _x( 'Tracks', 'post type general name', 'radio-ht' ),
				'singular_name'      => _x( 'Track', 'post type singular name', 'radio-ht' ),
				'menu_name'          => _x( 'Tracks', 'admin menu', 'radio-ht' ),
				'name_admin_bar'     => _x( 'Track', 'add new on admin bar', 'radio-ht' ),
				'add_new'            => _x( 'Add New', 'track', 'radio-ht' ),
				'add_new_item'       => __( 'Add New Track', 'radio-ht' ),
				'new_item'           => __( 'New Track', 'radio-ht' ),
				'edit_item'          => __( 'Edit Track', 'radio-ht' ),
				'view_item'          => __( 'View Track', 'radio-ht' ),
				'all_items'          => __( 'All Tracks', 'radio-ht' ),
				'search_items'       => __( 'Search Tracks', 'radio-ht' ),
				'parent_item_colon'  => __( 'Parent Tracks:', 'radio-ht' ),
				'not_found'          => __( 'No tracks found.', 'radio-ht' ),
				'not_found_in_trash' => __( 'No tracks found in Trash.', 'radio-ht' ),
			],
			'supports'            => [ 'title', 'editor', 'thumbnail' ],
		];

		register_post_type( 'rht_track', $args );
	}

	public function register_artist_taxonomy() {
		$options           = get_option( $this->plugin_name . '_option_name' );
		$public_visibility = isset( $options['artist_public_visibility'] ) ? (bool) $options['artist_public_visibility'] : true;

		$args = [
			'public'             => $public_visibility,
			'publicly_queryable' => $public_visibility,
			'hierarchical'       => true,
			'show_ui'            => true,
			'show_admin_column'  => true,
			'query_var'          => true,
			'show_in_rest'       => true,
			'rewrite'            => [ 'slug' => 'artist' ],
			'labels'             => [
				'name'              => _x( 'Artists', 'taxonomy general name', 'radio-ht' ),
				'singular_name'     => _x( 'Artist', 'taxonomy singular name', 'radio-ht' ),
				'search_items'      => __( 'Search Artists', 'radio-ht' ),
				'all_items'         => __( 'All Artists', 'radio-ht' ),
				'parent_item'       => __( 'Parent Artist', 'radio-ht' ),
				'parent_item_colon' => __( 'Parent Artist:', 'radio-ht' ),
				'edit_item'         => __( 'Edit Artist', 'radio-ht' ),
				'update_item'       => __( 'Update Artist', 'radio-ht' ),
				'add_new_item'      => __( 'Add New Artist', 'radio-ht' ),
				'new_item_name'     => __( 'New Artist Name', 'radio-ht' ),
				'menu_name'         => __( 'Artists', 'radio-ht' ),
			],
		];

		register_taxonomy( 'rht_artist', [ 'rht_track' ], $args );
	}

	public function register_shortcodes() {
		add_shortcode( 'latest_tracks', array( $this, 'latest_tracks_shortcode' ) );
	}

	public function latest_tracks_shortcode( $atts ) {

		$atts = shortcode_atts( array(
			'number' => 10,
		), $atts, 'latest_tracks' );

		$tracks_query = new WP_Query( array(
			'post_type'      => 'rht_track',
			'posts_per_page' => intval( $atts['number'] ),
			'meta_key'       => 'rht_track_latest_played_at',
			'orderby'        => 'meta_value_num',
			'order'          => 'DESC',
		) );

		ob_start();

		if ( $tracks_query->have_posts() ) {
			include 'partials/latest-tracks-list.php';
		} else {
			echo '<div class="no-tracks-found">' . esc_html__( 'No recently played tracks found.', 'radio-ht' ) . '</div>';
		}

		wp_reset_postdata();

		return ob_get_clean();

	}

}
