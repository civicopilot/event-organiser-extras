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

/**
 * Returns a short recurrence occurrence summary such as "5 Tuesdays".
 *
 * @param int $event_id Event post ID.
 * @return string
 */
function eo_extras_get_event_occurrence_text( $event_id ) {
	$event_id = (int) $event_id;

	if ( ! $event_id ) {
		return '';
	}

	// Only recurring events have an occurrence count / weekday summary.
	if ( ! eo_recurs( $event_id ) ) {
		return '';
	}

	// Get all occurrences so we can count how many times the event runs.
	$occurrences  = eo_get_the_occurrences_of( $event_id );
	$total_events = is_array( $occurrences ) ? count( $occurrences ) : 0;

	if ( $total_events < 1 ) {
		return '';
	}

	// Use the first scheduled date to determine the weekday label.
	$first_date  = eo_get_schedule_start( 'Y-m-d', $event_id );
	$day_of_week = date_i18n( 'l', strtotime( $first_date ) );

	return esc_html( $total_events . ' ' . $day_of_week . ( $total_events > 1 ? 's' : '' ) );
}

// Create the [event_occurrence] shortcode.
function eo_extras_event_occurrence_shortcode( $atts = array() ) {
	unset( $atts );

	return eo_extras_get_event_occurrence_text( eo_extras_get_current_event_id() );
}
add_shortcode( 'event_occurrence', 'eo_extras_event_occurrence_shortcode' );

/**
 * Returns the recurrence summary such as "every month on the first Saturday".
 *
 * @param int $event_id Event post ID.
 * @return string
 */
function eo_extras_get_event_recurrence_text( $event_id ) {
	$event_id = (int) $event_id;

	if ( ! $event_id ) {
		return '';
	}

	// A recurrence summary only applies to recurring events.
	if ( ! eo_recurs( $event_id ) ) {
		return '';
	}

	// Use Event Organiser's built-in recurrence summary helper.
	$summary = eo_get_schedule_summary( $event_id );

	// Drop the trailing "until ..." portion for a shorter recurrence label.
	$summary = preg_replace( '/\s+until\s+.+$/i', '', $summary );

	return esc_html( $summary );
}

// Create the [event_recurrence] shortcode.
function eo_extras_event_recurrence_shortcode( $atts = array() ) {
	unset( $atts );

	return eo_extras_get_event_recurrence_text( eo_extras_get_current_event_id() );
}
add_shortcode( 'event_recurrence', 'eo_extras_event_recurrence_shortcode' );

/**
 * Returns a formatted event time range for the current event context.
 *
 * Uses schedule data instead of occurrence-dependent helpers so the output is
 * reliable inside sidebars, widgets, and shortcode rendering outside the loop.
 *
 * @param int  $event_id          Event post ID.
 * @param bool $timezone_enabled  Whether to append timezone abbreviation.
 * @return string
 */
function eo_extras_get_event_time_range( $event_id, $timezone_enabled = true ) {
	$event_id = (int) $event_id;

	if ( ! $event_id ) {
		return '';
	}

	if ( eo_is_all_day( $event_id ) ) {
		return esc_html__( 'All day', 'eventorganiser' );
	}

	$schedule = eo_get_event_schedule( $event_id );

	if ( empty( $schedule['start'] ) || empty( $schedule['end'] ) ) {
		return '';
	}

	$start_time = $schedule['start']->format( 'g:i a' );
	$end_time   = $schedule['end']->format( 'g:i a' );

	if ( empty( $start_time ) && empty( $end_time ) ) {
		return '';
	}

	$time_output = $start_time;

	if ( ! empty( $end_time ) && $end_time !== $start_time ) {
		$time_output .= ' – ' . $end_time;
	}

	if ( $timezone_enabled ) {
		$timezone_abbr = eo_extras_shortcode_timezone_abbreviation();
		if ( $timezone_abbr ) {
			$time_output .= ' ' . $timezone_abbr;
		}
	}

	return esc_html( trim( $time_output ) );
}

/**
 * Returns a formatted event date string for the given event.
 *
 * @param int  $event_id            Event post ID.
 * @param bool $time_enabled        Whether to append the time range.
 * @param bool $timezone_enabled    Whether to include timezone abbreviation.
 * @param bool $occurrence_enabled  Whether to prepend occurrence summary.
 * @return string
 */
function eo_extras_get_event_date_text( $event_id, $time_enabled = false, $timezone_enabled = false, $occurrence_enabled = false ) {
	$event_id = (int) $event_id;

	if ( ! $event_id ) {
		return '';
	}

	if ( ! eo_recurs( $event_id ) ) {
		$output = eo_get_schedule_start( 'F j, Y', $event_id );

		if ( $time_enabled ) {
			$time_output = eo_extras_get_event_time_range( $event_id, $timezone_enabled );
			if ( '' !== $time_output ) {
				$output .= ' @ ' . $time_output;
			}
		}

		return $output;
	}

	$start_year = eo_get_schedule_start( 'Y', $event_id );
	$end_year   = eo_get_schedule_last( 'Y', $event_id );
	$start_date = ( $start_year === $end_year ) ? eo_get_schedule_start( 'F j', $event_id ) : eo_get_schedule_start( 'F j, Y', $event_id );
	$end_date   = eo_get_schedule_last( 'F j, Y', $event_id );
	$output     = $start_date . ' – ' . $end_date;

	if ( $occurrence_enabled ) {
		$occurrence = eo_extras_get_event_occurrence_text( $event_id );
		if ( '' !== $occurrence ) {
			$output = $occurrence . ' | ' . $output;
		}
	}

	if ( $time_enabled ) {
		$time_output = eo_extras_get_event_time_range( $event_id, $timezone_enabled );
		if ( '' !== $time_output ) {
			$output .= ' @ ' . $time_output;
		}
	}

	return esc_html( $output );
}

// Create the [event_times] shortcode.
function eo_extras_event_times_shortcode( $atts = array() ) {
	$atts = shortcode_atts(
		array(
			'timezone' => 'true',
		),
		$atts,
		'event_times'
	);

	$event_id = eo_extras_get_current_event_id();

	if ( ! $event_id ) {
		return '';
	}

	$timezone_enabled = eo_extras_shortcode_attribute_is_enabled( $atts['timezone'] );

	return eo_extras_get_event_time_range( $event_id, $timezone_enabled );
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
	$event_id           = eo_extras_get_current_event_id();

	if ( ! $event_id ) {
		return '';
	}

	$output = eo_extras_get_event_date_text( $event_id, $time_enabled, $timezone_enabled, $occurrence_enabled );

	return '<div class="eo-event-meta ' . esc_attr( eo_recurs( $event_id ) ? 'eo-event-meta-recurring' : 'eo-event-meta-single' ) . '">' . $output . '</div>';
}
add_shortcode( 'event_date', 'eo_extras_event_date_shortcode' );

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

/**
 * Locates the sidebar template, preferring child-theme overrides.
 *
 * @return string
 */
function eo_extras_get_event_sidebar_template_path() {
	$template_names = array(
		'event-meta-event-single-sidebar.php',
		'event-organiser-extras/event-meta-event-single-sidebar.php',
	);

	$template = locate_template( $template_names, false, false );

	if ( ! empty( $template ) ) {
		return $template;
	}

	return plugin_dir_path( __FILE__ ) . 'templates/event-meta-event-single-sidebar.php';
}

/**
 * Create the [eo_extras_sidebar_meta] shortcode.
 *
 * Loads a default sidebar meta template from the plugin, but allows a child
 * theme override so client-specific sidebar presentation can live in the theme.
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function eo_extras_event_sidebar_meta_shortcode( $atts = array() ) {
	unset( $atts );

	$event_id = eo_extras_get_current_event_id();

	if ( ! $event_id ) {
		return '';
	}

	$template_path = eo_extras_get_event_sidebar_template_path();

	if ( ! file_exists( $template_path ) ) {
		return '';
	}

	$event_post = get_post( $event_id );

	if ( ! $event_post instanceof WP_Post ) {
		return '';
	}

	$previous_post = $GLOBALS['post'] ?? null;
	$GLOBALS['post'] = $event_post;
	setup_postdata( $event_post );

	ob_start();
	include $template_path;
	$output = ob_get_clean();

	if ( $previous_post instanceof WP_Post ) {
		$GLOBALS['post'] = $previous_post;
		setup_postdata( $previous_post );
	} else {
		unset( $GLOBALS['post'] );
		wp_reset_postdata();
	}

	return $output;
}
add_shortcode( 'eo_extras_sidebar_meta', 'eo_extras_event_sidebar_meta_shortcode' );
