<?php

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/strategies/class-stream-strategy-manager.php';

class Radio_History_Tracker_Cron {

	protected $plugin_name;
	protected $version;

	private static $schedule_name = 'rht_x_minutes';


	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	public static function activate() {
		self::schedule_event();
	}

	public static function deactivate() {
		wp_clear_scheduled_hook( 'rht_process_history' );
	}

	public static function schedule_event() {
		if ( ! wp_next_scheduled( 'rht_process_history' ) ) {
			wp_schedule_event( time(), self::$schedule_name, 'rht_process_history' );
		}
	}

	public function schedule_cron_jobs() {
		self::schedule_event();
	}

	public function add_cron_interval( $schedules ) {
		$options  = get_option( $this->plugin_name . '_option_name' );
		$interval = isset( $options['cron_interval'] ) ? intval( $options['cron_interval'] ) : 3;

		if ( $interval < 1 ) {
			$interval = 3;
		}

		$schedules[ self::$schedule_name ] = array(
			'interval' => $interval * 60,
			'display'  => sprintf( __( 'Every %d minutes', 'radio-ht' ), $interval )
		);

		return $schedules;
	}

	public function process_history() {
		$args = array(
			'post_type'      => 'rht_radio_stream',
			'posts_per_page' => - 1,
			'post_status'    => 'publish',
		);

		$query   = new WP_Query( $args );
		$manager = new Stream_Strategy_Manager();

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$post_id = get_the_ID();

				$stream_type       = get_post_meta( $post_id, 'rht_stream_type', true );
				$metadata_type     = get_post_meta( $post_id, 'rht_stream_metadata_type', true );
				$metadata_endpoint = get_post_meta( $post_id, 'rht_stream_metadata_endpoint', true );

				if ( ! empty( $stream_type ) && ! empty( $metadata_type ) && ! empty( $metadata_endpoint ) ) {
					try {
						$metadata_username = get_post_meta( $post_id, 'rht_stream_username', true );
						$metadata_username = ( '' === $metadata_username ) ? null : $metadata_username;
						$metadata_password = get_post_meta( $post_id, 'rht_stream_password', true );
						$metadata_password = ( '' === $metadata_password ) ? null : $metadata_password;

						$strategy = $manager->get_strategy( $stream_type );

						$strategy->process( $metadata_type, $metadata_endpoint, $post_id, $metadata_username, $metadata_password );
					} catch ( Exception $e ) {
						continue;
					}
				}
			}
		}

		wp_reset_postdata();
	}
}