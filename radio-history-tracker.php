<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://wijnberg.dev
 * @since             1.0.0
 * @package           Radio_History_Tracker
 *
 * @wordpress-plugin
 * Plugin Name:       Radio History Tracker
 * Plugin URI:        https://wijnberg.dev
 * Description:       Track and manage your radio station's playlist history efficiently with the Radio History Tracker plugin.
 * Version:           1.0.0
 * Author:            Wijnberg Developments
 * Author URI:        https://wijnberg.dev/
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       radio-history-tracker
 * Domain Path:       /languages
 * Requires at least:   6.0
 * Tested up to:        6.5
 * Requires PHP:        7.2
 */


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'RADIO_HISTORY_TRACKER_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-radio-history-tracker-activator.php
 */
function activate_radio_history_tracker() {
	//require_once plugin_dir_path( __FILE__ ) . 'includes/class-radio-history-tracker-activator.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-radio-history-tracker-cron.php';
	Radio_History_Tracker_Cron::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-radio-history-tracker-deactivator.php
 */
function deactivate_radio_history_tracker() {
//	require_once plugin_dir_path( __FILE__ ) . 'includes/class-radio-history-tracker-deactivator.php';
//	Radio_History_Tracker_Deactivator::deactivate();
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-radio-history-tracker-cron.php';
	Radio_History_Tracker_Cron::deactivate();
}

register_activation_hook( __FILE__, 'activate_radio_history_tracker' );
register_deactivation_hook( __FILE__, 'deactivate_radio_history_tracker' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-radio-history-tracker.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_radio_history_tracker() {

	$plugin = new Radio_History_Tracker();
	$plugin->run();

}

run_radio_history_tracker();
