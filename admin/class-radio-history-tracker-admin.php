<?php

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/strategies/class-stream-strategy-manager.php';
require_once plugin_dir_path( __FILE__ ) . '../includes/class-track-manager.php';

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://wijnberg.dev
 * @since      1.0.0
 *
 * @package    Radio_History_Tracker
 * @subpackage Radio_History_Tracker/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Radio_History_Tracker
 * @subpackage Radio_History_Tracker/admin
 * @author     Wijnberg Developments <contact@wijnberg.dev>
 */
class Radio_History_Tracker_Admin {

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
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles( $hook_suffix ) {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/radio-history-tracker-admin.css', array(), $this->version, 'all' );
		$cpt = 'rht_radio_stream';

		if ( in_array( $hook_suffix, array( 'post.php', 'post-new.php' ) ) ) {
			$screen = get_current_screen();

			if ( is_object( $screen ) && $cpt == $screen->post_type ) {
				wp_enqueue_style( 'micromodal', plugin_dir_url( __FILE__ ) . 'vendor/micromodal-0_4_10/micromodal.css', array(), '0.4.10', 'all' );
			}
		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts( $hook_suffix ) {
		if ( in_array( $hook_suffix, array( 'post.php', 'post-new.php' ) ) ) {
			$screen = get_current_screen();

			if ( is_object( $screen ) ) {
				if ( 'rht_radio_stream' == $screen->post_type ) {
					wp_enqueue_script( 'micromodal', plugin_dir_url( __FILE__ ) . 'vendor/micromodal-0_4_10/micromodal.min.js', [], '0.4.10', false );

					$handle = 'rht-radio-stream-admin';
					wp_register_script( $handle, plugin_dir_url( __FILE__ ) . 'js/radio-stream-admin.js', array(
						'jquery',
						'micromodal'
					), $this->version, false );

					$radio_stream_data = array(
						'admin_url' => admin_url( 'admin-ajax.php' ),
						'action'    => 'rht_test_metadata_url_ajax',
						'l10n'      => array(
							'no_data_available'        => __( 'No data available.', 'radio-ht' ),
							'something_went_wrong'     => __( 'Something went wrong.', 'radio-ht' ),
							'loading'                  => __( 'Loading...', 'radio-ht' ),
							'couldnt_process_metadata' => __( 'Couldn\'t process metadata.', 'radio-ht' ),
							'history_caption'          => __( 'History', 'radio-ht' ),
							'played_at'                => __( 'Played at', 'radio-ht' ),
							'artist'                   => __( 'Artist', 'radio-ht' ),
							'title'                    => __( 'Title', 'radio-ht' ),
							'artwork'                  => __( 'Artwork', 'radio-ht' ),
							'na'                       => __( 'N/A', 'radio-ht' )
						)
					);

					wp_localize_script( $handle, 'radio_stream_data', $radio_stream_data );

					wp_enqueue_script( $handle );
				} else {
					if ( 'rht_track' == $screen->post_type ) {
						$handle = 'rht-track-admin';
						wp_enqueue_script( $handle, plugin_dir_url( __FILE__ ) . 'js/track-admin.js', [ 'jquery' ], $this->version, false );
					}
				}
			}


		}
	}

	public function rht_test_metadata_url_ajax() {
		check_ajax_referer( 'rht_stream_save_meta_box_data', 'nonce' );

		$stream_type       = isset( $_POST['stream_type'] ) ? sanitize_text_field( $_POST['stream_type'] ) : '';
		$metadata_type     = isset( $_POST['metadata_type'] ) ? sanitize_text_field( $_POST['metadata_type'] ) : '';
		$metadata_endpoint = isset( $_POST['metadata_endpoint'] ) ? esc_url_raw( $_POST['metadata_endpoint'] ) : '';
		$metadata_username = ( isset( $_POST['metadata_username'] ) && ! empty( $_POST['metadata_username'] ) ) ? sanitize_text_field( $_POST['metadata_username'] ) : null;
		$metadata_password = ( isset( $_POST['metadata_password'] ) && ! empty( $_POST['metadata_password'] ) ) ? sanitize_text_field( $_POST['metadata_password'] ) : null;

		$manager = new Stream_Strategy_Manager();
		try {
			$strategy = $manager->get_strategy( $stream_type );
			$tracks   = $strategy->tracks( $metadata_type, $metadata_endpoint, $metadata_username, $metadata_password );
			if ( empty( $tracks ) || is_wp_error( $tracks ) ) {
				wp_send_json_error( array( 'message' => 'No tracks found.' ) );
			} else {
				wp_send_json_success( $tracks );
			}

		} catch ( Exception $e ) {
			wp_send_json_error( $e );
		}

		wp_die();
	}

	public function rht_add_admin_menu_item() {
		add_options_page(
			__( 'Radio History Tracker settings', 'radio-ht' ),
			__( 'Radio History Tracker', 'radio-ht' ),
			'manage_options',
			$this->plugin_name,
			array( $this, 'rht_display_setup_page' )
		);
	}

	public function rht_display_setup_page() {
		include_once( 'partials/radio-history-tracker-admin-display.php' );
	}

	public function rht_register_settings() {
		register_setting( $this->plugin_name . '_options_group', $this->plugin_name . '_option_name' );

		add_settings_section(
			$this->plugin_name . '_settings_section',
			__( 'Post type settings', 'radio-ht' ),
			array( $this, 'rht_settings_section_cb' ),
			$this->plugin_name
		);

		add_settings_field(
			$this->plugin_name . '_public_visibility',
			__( 'Make Radio Stream publicly visible', 'radio-ht' ),
			array( $this, 'rht_public_visibility_cb' ),
			$this->plugin_name,
			$this->plugin_name . '_settings_section'
		);

		add_settings_field(
			$this->plugin_name . '_track_public_visibility',
			__( 'Make Tracks publicly visible', 'radio-ht' ),
			array( $this, 'rht_track_public_visibility_cb' ),
			$this->plugin_name,
			$this->plugin_name . '_settings_section'
		);

		add_settings_field(
			$this->plugin_name . '_artist_public_visibility',
			__( 'Make Artists publicly visible', 'radio-ht' ),
			array( $this, 'rht_artist_public_visibility_cb' ),
			$this->plugin_name,
			$this->plugin_name . '_settings_section'
		);

		add_settings_section(
			$this->plugin_name . '_cron_settings_section',
			__( 'Cron settings', 'radio-ht' ),
			array( $this, 'rht_cron_settings_section_cb' ),
			$this->plugin_name
		);

		add_settings_field(
			$this->plugin_name . '_cron_interval',
			__( 'History fetch interval (in minutes)', 'radio-ht' ),
			array( $this, 'rht_cron_interval_cb' ),
			$this->plugin_name,
			$this->plugin_name . '_cron_settings_section'
		);
	}

	public function rht_settings_section_cb() {
		//echo '<p>' . __( "Post type settings", "radio-ht" ) . '</p>';
	}

	public function rht_cron_settings_section_cb() {
		//echo '<p>' . __( "Cron settings.", "radio-ht" ) . '</p>';
	}

	public function rht_public_visibility_cb() {
		$options           = get_option( $this->plugin_name . '_option_name' );
		$public_visibility = isset( $options['public_visibility'] ) ? $options['public_visibility'] : '';
		echo "<input type='checkbox' name='" . $this->plugin_name . "_option_name[public_visibility]' " . checked( $public_visibility, 1, false ) . " value='1' />";
		echo "<label for='" . $this->plugin_name . "_option_name[public_visibility]'>" . __( "Make Radio Stream publicly visible", "radio-ht" ) . "</label>";
	}

	public function rht_track_public_visibility_cb() {
		$options                 = get_option( $this->plugin_name . '_option_name' );
		$track_public_visibility = isset( $options['track_public_visibility'] ) ? $options['track_public_visibility'] : true;
		echo "<input type='checkbox' name='" . $this->plugin_name . "_option_name[track_public_visibility]' " . checked( $track_public_visibility, 1, false ) . " value='1' />";
		echo "<label for='" . $this->plugin_name . "_option_name[track_public_visibility]'>" . __( "Make Tracks publicly visible", "radio-ht" ) . "</label>";
	}

	public function rht_artist_public_visibility_cb() {
		$options                  = get_option( $this->plugin_name . '_option_name' );
		$artist_public_visibility = isset( $options['artist_public_visibility'] ) ? $options['artist_public_visibility'] : true;
		echo "<input type='checkbox' name='" . $this->plugin_name . "_option_name[artist_public_visibility]' " . checked( $artist_public_visibility, 1, false ) . " value='1' />";
		echo "<label for='" . $this->plugin_name . "_option_name[artist_public_visibility]'>" . __( "Make Artists publicly visible", "radio-ht" ) . "</label>";
	}

	public function rht_cron_interval_cb() {
		$options       = get_option( $this->plugin_name . '_option_name' );
		$cron_interval = isset( $options['cron_interval'] ) ? esc_attr( $options['cron_interval'] ) : 3;
		echo "<input type='number' min='1' name='" . $this->plugin_name . "_option_name[cron_interval]' value='$cron_interval' />";
		echo "<p class='description'>" . __( "Set the interval in minutes at which the history should be fetched.", "radio-ht" ) . "</p>";
	}

	public function rht_sort_and_update_track_metadata_on_save( $post_id, $post, $update ) {
		$track_manager = new Track_Manager();
		$track_manager->sort_and_update_track_metadata( $post_id );
	}

}
