<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes the shared dependencies used by the plugin
 * and wires up its core hooks.
 *
 * @link              https://civicopilot.com
 * @since             1.0.0
 * @package           Event_Organiser_Extras
 *
 * @wordpress-plugin
 * Plugin Name:       Event Organiser Extras
 * Plugin URI:        https://github.com/civicopilot/event-organiser-extras
 * Description:       Reusable extras and shortcodes for WPCV Event Organiser.
 * Version:           1.0.0
 * Requires Plugins:  wpcv-event-organiser
 * Author:            Andy Burns
 * Author URI:        https://civicopilot.com/
 * Text Domain:       event-organiser-extras
 * Domain Path:       /languages
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
define( 'EVENT_ORGANISER_EXTRAS_VERSION', '1.0.0' );

/**
 * Load the plugin text domain for translation.
 */
function eo_extras_load_plugin_textdomain() {
	load_plugin_textdomain(
		'event-organiser-extras',
		false,
		dirname( plugin_basename( __FILE__ ) ) . '/languages/'
	);
}
add_action( 'plugins_loaded', 'eo_extras_load_plugin_textdomain' );

/**
 * Register front-end styles for plugin-rendered event components.
 */
function eo_extras_register_styles() {
	wp_register_style(
		'event-organiser-extras',
		plugin_dir_url( __FILE__ ) . 'assets/css/event-organiser-extras.css',
		array(),
		EVENT_ORGANISER_EXTRAS_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'eo_extras_register_styles' );

/**
 * Returns the current event ID for event-related shortcodes.
 *
 * This prefers the queried object so widgets/sidebars still resolve the
 * current single event even when the global loop context is thin.
 *
 * @return int
 */
function eo_extras_get_current_event_id() {
	$event_id = 0;

	if ( is_singular( 'event' ) ) {
		$event_id = (int) get_queried_object_id();
	}

	if ( ! $event_id ) {
		$event_id = (int) get_the_ID();
	}

	if ( $event_id && 'event' !== get_post_type( $event_id ) ) {
		return 0;
	}

	return $event_id;
}

// Event registration link helpers for single and recurring events.
require_once plugin_dir_path( __FILE__ ) . 'includes/event-register-links.php';

// Date/time/occurrence shortcodes and related event helpers.
require_once plugin_dir_path( __FILE__ ) . 'includes/event-date-shortcodes.php';

// Sidebar meta shortcode and theme-override template loading.
require_once plugin_dir_path( __FILE__ ) . 'includes/event-sidebar-shortcode.php';
