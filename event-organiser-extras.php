<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://civicopilot.com
 * @since             1.0.0
 * @package           Event_Organiser_Extras
 *
 * @wordpress-plugin
 * Plugin Name:       Event Organiser Extras
 * Plugin URI:        https://github.com/civicopilot/event-organiser-extras
 * Description:       Reusable extras and shortcodes for the Event Organiser plugin.
 * Version:           1.0.0
 * Requires Plugins:  event-organiser
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
 * The code that runs during plugin activation.
 * This action is documented in includes/class-event-organiser-extras-activator.php
 */
function activate_event_organiser_extras() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-event-organiser-extras-activator.php';
	Event_Organiser_Extras_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-event-organiser-extras-deactivator.php
 */
function deactivate_event_organiser_extras() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-event-organiser-extras-deactivator.php';
	Event_Organiser_Extras_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_event_organiser_extras' );
register_deactivation_hook( __FILE__, 'deactivate_event_organiser_extras' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-event-organiser-extras.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_event_organiser_extras() {

	$plugin = new Event_Organiser_Extras();
	$plugin->run();

}
run_event_organiser_extras();

/**
 * Helper showing what shortcodes we have.
 * [event_occurrence] -> 5 Tuesdays
 * [event_recurrence] -> every month on the second Thursday
 * [event_date] -> April 8 - May 6, 2025
 * [event_date time="true"] -> April 8 - May 6, 2025 @ 7:00 pm – 8:30 pm
 * [event_date time="true" timezone="true" occurrence="true"] -> 5 Tuesdays | April 8 - May 6, 2025 @ 7:00 pm – 8:30 pm EDT
 * [event_times] -> 7:00 pm – 8:30 pm EDT
 */

/**
 * Returns the site timezone abbreviation, if available.
 *
 * @return string
 */
function eo_extras_shortcode_timezone_abbreviation() {
	$timezone_string = get_option( 'timezone_string' );

	if ( empty( $timezone_string ) ) {
		return '';
	}

	$datetime = new DateTime( 'now', new DateTimeZone( $timezone_string ) );

	return $datetime->format( 'T' );
}

/**
 * Normalizes shortcode true/false attribute values.
 *
 * @param mixed $value Value to normalize.
 * @return bool
 */
function eo_extras_shortcode_attribute_is_enabled( $value ) {
	return 'true' === strtolower( (string) $value );
}

// Create the [event_occurrence] shortcode.
function eo_extras_event_occurrence_shortcode( $atts = array() ) {
	// Only recurring events have an occurrence count / weekday summary.
	if ( ! eo_recurs() ) {
		return '';
	}

	// Get all occurrences so we can count how many times the event runs.
	$occurrences  = eo_get_the_occurrences_of( get_the_ID() );
	$total_events = is_array( $occurrences ) ? count( $occurrences ) : 0;

	if ( $total_events < 1 ) {
		return '';
	}

	// Use the first scheduled date to determine the weekday label.
	$first_date  = eo_get_schedule_start( 'Y-m-d' );
	$day_of_week = date_i18n( 'l', strtotime( $first_date ) );

	return esc_html( $total_events . ' ' . $day_of_week . ( $total_events > 1 ? 's' : '' ) );
}
add_shortcode( 'event_occurrence', 'eo_extras_event_occurrence_shortcode' );

// Create the [event_recurrence] shortcode.
function eo_extras_event_recurrence_shortcode( $atts = array() ) {
	// A recurrence summary only applies to recurring events.
	if ( ! eo_recurs() ) {
		return '';
	}

	// Use Event Organiser's built-in recurrence summary helper.
	$summary = eo_get_schedule_summary( get_the_ID() );

	// Drop the trailing "until ..." portion for a shorter recurrence label.
	$summary = preg_replace( '/\s+until\s+.+$/i', '', $summary );

	return esc_html( $summary );
}
add_shortcode( 'event_recurrence', 'eo_extras_event_recurrence_shortcode' );

// Create the [event_times] shortcode.
function eo_extras_event_times_shortcode( $atts = array() ) {
	$atts = shortcode_atts(
		array(
			'timezone' => 'true',
		),
		$atts,
		'event_times'
	);

	$timezone_enabled = eo_extras_shortcode_attribute_is_enabled( $atts['timezone'] );
	$timezone_abbr    = $timezone_enabled ? eo_extras_shortcode_timezone_abbreviation() : '';
	$timezone_suffix  = $timezone_abbr ? ' ' . $timezone_abbr : '';

	if ( ! eo_recurs() ) {
		// Single event: use the event's actual start and end times.
		$start_time = eo_get_the_start( 'g:i a' );
		$end_time   = eo_get_the_end( 'g:i a' );
	} else {
		// Recurring event: use the schedule start plus the event end time.
		$start_time = eo_get_schedule_start( 'g:i a' );
		$end_time   = eo_get_the_end( 'g:i a' );
	}

	return esc_html( $start_time . ' – ' . $end_time . $timezone_suffix );
}
add_shortcode( 'event_times', 'eo_extras_event_times_shortcode' );

// Create the [event_date] shortcode.
function eo_extras_event_date_shortcode( $atts = array() ) {
	// Allow one shortcode to handle plain dates or more detailed combined output.
	$atts = shortcode_atts(
		array(
			'time'       => 'false',
			'timezone'   => 'false',
			'occurrence' => 'false',
		),
		$atts,
		'event_date'
	);

	$time_enabled       = eo_extras_shortcode_attribute_is_enabled( $atts['time'] );
	$timezone_enabled   = eo_extras_shortcode_attribute_is_enabled( $atts['timezone'] );
	$occurrence_enabled = eo_extras_shortcode_attribute_is_enabled( $atts['occurrence'] );

	if ( ! eo_recurs() ) {
		// Single (non-recurring) event.
		$output = eo_get_schedule_start( 'F j, Y' );

		// Optionally append the time range.
		if ( $time_enabled ) {
			$output .= ' @ ' . eo_extras_event_times_shortcode(
				array(
					'timezone' => $timezone_enabled ? 'true' : 'false',
				)
			);
		}

		return '<div class="eo-event-meta eo-event-meta-single">' . esc_html( $output ) . '</div>';
	}

	// Recurring event: build the date range first.
	$start_year = eo_get_schedule_start( 'Y' );
	$end_year   = eo_get_schedule_last( 'Y' );
	$start_date = ( $start_year === $end_year ) ? eo_get_schedule_start( 'F j' ) : eo_get_schedule_start( 'F j, Y' );
	$end_date   = eo_get_schedule_last( 'F j, Y' );
	$output     = $start_date . ' – ' . $end_date;

	// Optionally prepend the occurrence summary such as "5 Tuesdays".
	if ( $occurrence_enabled ) {
		$occurrence = eo_extras_event_occurrence_shortcode();
		if ( ! empty( $occurrence ) ) {
			$output = $occurrence . ' | ' . $output;
		}
	}

	// Optionally append the time range.
	if ( $time_enabled ) {
		$output .= ' @ ' . eo_extras_event_times_shortcode(
			array(
				'timezone' => $timezone_enabled ? 'true' : 'false',
			)
		);
	}

	return '<div class="eo-event-meta eo-event-meta-recurring">' . esc_html( $output ) . '</div>';
}
add_shortcode( 'event_date', 'eo_extras_event_date_shortcode' );
