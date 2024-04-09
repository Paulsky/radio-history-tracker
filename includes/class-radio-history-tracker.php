<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://wijnberg.dev
 * @since      1.0.0
 *
 * @package    Radio_History_Tracker
 * @subpackage Radio_History_Tracker/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Radio_History_Tracker
 * @subpackage Radio_History_Tracker/includes
 * @author     Wijnberg Developments <contact@wijnberg.dev>
 */
class Radio_History_Tracker {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Radio_History_Tracker_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'RADIO_HISTORY_TRACKER_VERSION' ) ) {
			$this->version = RADIO_HISTORY_TRACKER_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'radio-history-tracker';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_public_hooks();
		$this->define_admin_hooks();
		$this->define_cron_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Radio_History_Tracker_Loader. Orchestrates the hooks of the plugin.
	 * - Radio_History_Tracker_i18n. Defines internationalization functionality.
	 * - Radio_History_Tracker_Admin. Defines all hooks for the admin area.
	 * - Radio_History_Tracker_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-radio-history-tracker-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-radio-history-tracker-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-radio-history-tracker-admin.php';


		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/class-radio-stream-meta-boxes.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/class-track-meta-boxes.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-radio-history-tracker-cron.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-radio-history-tracker-public.php';

		$this->loader = new Radio_History_Tracker_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Radio_History_Tracker_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Radio_History_Tracker_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Radio_History_Tracker_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'wp_ajax_rht_test_metadata_url_ajax', $plugin_admin, 'rht_test_metadata_url_ajax' );
		$this->loader->add_action( 'wp_ajax_nopriv_rht_test_metadata_ajax', $plugin_admin, 'rht_test_metadata_url_ajax' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'rht_add_admin_menu_item' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'rht_register_settings' );

		$radio_stream_meta_boxes = new Radio_Stream_Meta_Boxes( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'add_meta_boxes', $radio_stream_meta_boxes, 'add_meta_boxes' );
		$this->loader->add_action( 'save_post', $radio_stream_meta_boxes, 'save_post', 10 );
		$this->loader->add_action( 'save_post', $plugin_admin, 'rht_sort_and_update_track_metadata_on_save', 20, 3 );

		$track_meta_boxes = new Track_Meta_Boxes( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'add_meta_boxes', $track_meta_boxes, 'add_meta_boxes' );
		$this->loader->add_action( 'save_post', $track_meta_boxes, 'save_post' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Radio_History_Tracker_Public( $this->get_plugin_name(), $this->get_version() );

//		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
//		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		//11 because of dynamic CPT settings
		$this->loader->add_action( 'init', $plugin_public, 'register_radio_stream_cpt', 11 );
		$this->loader->add_action( 'init', $plugin_public, 'register_track_cpt', 11 );
		$this->loader->add_action( 'init', $plugin_public, 'register_artist_taxonomy', 11 );

	}

	/**
	 * Register all of the hooks related to the cron functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_cron_hooks() {

		$plugin_cron = new Radio_History_Tracker_Cron( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'rht_process_history', $plugin_cron, 'process_history' );
		$this->loader->add_action( 'wp', $plugin_cron, 'schedule_cron_jobs' );
		$this->loader->add_filter( 'cron_schedules', $plugin_cron, 'add_cron_interval' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @return    string    The name of the plugin.
	 * @since     1.0.0
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @return    Radio_History_Tracker_Loader    Orchestrates the hooks of the plugin.
	 * @since     1.0.0
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @return    string    The version number of the plugin.
	 * @since     1.0.0
	 */
	public function get_version() {
		return $this->version;
	}

}
