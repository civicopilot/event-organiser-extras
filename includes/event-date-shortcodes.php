<?php
/**
 * Event date and time shortcodes.
 *
 * @package Event_Organiser_Extras
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Returns the site timezone abbreviation, if available.
 *
 * @return string
 */
function eox_shortcode_timezone_abbreviation() {
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
function eox_shortcode_attribute_is_enabled( $value ) {
	return 'true' === strtolower( (string) $value );
}

/**
 * Returns whether an event is in-person or virtual based on venue address data.
 *
 * Events with a city listed for their venue are treated as in person. Those without a city, such as a venue named "Live via Zoom," are treated as virtual.
 *
 * @param int|null $event_id Event post ID. Defaults to the current event context.
 * @return string
 */
function eox_get_event_format_label( $event_id = null ) {
	$event_id = $event_id ? (int) $event_id : eox_get_current_event_id();

	if ( ! $event_id ) {
		return '';
	}

	$venue_id = eo_get_venue( $event_id );
	$address  = $venue_id ? eo_get_venue_address( $venue_id ) : array();

	$in_person = ! empty( $address['city'] );

	return $in_person ? esc_html__( 'In person', 'event-organiser-extras' ) : esc_html__( 'Virtual', 'event-organiser-extras' );
}

// Create the [eox_event_format] shortcode.
function eox_event_format_shortcode( $atts = array() ) {
	unset( $atts );

	return eox_get_event_format_label( eox_get_current_event_id() );
}
add_shortcode( 'eox_event_format', 'eox_event_format_shortcode' );

/**
 * Returns a short recurrence occurrence summary such as "5 Tuesdays".
 *
 * @param int $event_id Event post ID.
 * @return string
 */
function eox_get_event_occurrence_text( $event_id ) {
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

// Create the [eox_event_occurrence] shortcode.
function eox_event_occurrence_shortcode( $atts = array() ) {
	unset( $atts );

	return eox_get_event_occurrence_text( eox_get_current_event_id() );
}
add_shortcode( 'eox_event_occurrence', 'eox_event_occurrence_shortcode' );

/**
 * Returns the recurrence summary such as "every month on the first Saturday".
 *
 * @param int $event_id Event post ID.
 * @return string
 */
function eox_get_event_recurrence_text( $event_id ) {
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

// Create the [eox_event_recurrence] shortcode.
function eox_event_recurrence_shortcode( $atts = array() ) {
	unset( $atts );

	return eox_get_event_recurrence_text( eox_get_current_event_id() );
}
add_shortcode( 'eox_event_recurrence', 'eox_event_recurrence_shortcode' );

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
function eox_get_event_time_range( $event_id, $timezone_enabled = true ) {
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
		$timezone_abbr = eox_shortcode_timezone_abbreviation();
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
function eox_get_event_date_text( $event_id, $time_enabled = false, $timezone_enabled = false, $occurrence_enabled = false ) {
	$event_id = (int) $event_id;

	if ( ! $event_id ) {
		return '';
	}

	if ( ! eo_recurs( $event_id ) ) {
		$output = eo_get_schedule_start( 'F j, Y', $event_id );

		if ( $time_enabled ) {
			$time_output = eox_get_event_time_range( $event_id, $timezone_enabled );
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
		$occurrence = eox_get_event_occurrence_text( $event_id );
		if ( '' !== $occurrence ) {
			$output = $occurrence . ' | ' . $output;
		}
	}

	if ( $time_enabled ) {
		$time_output = eox_get_event_time_range( $event_id, $timezone_enabled );
		if ( '' !== $time_output ) {
			$output .= ' @ ' . $time_output;
		}
	}

	return esc_html( $output );
}

/**
 * Returns a short "coming up" message for the event.
 *
 * For recurring events this uses the next occurrence. For non-recurring events
 * it uses the scheduled start date. Past or already-started events return an
 * empty string because "coming up" would no longer be accurate.
 *
 * @param int $event_id Event post ID.
 * @return string
 */
function eox_get_event_countdown_text( $event_id ) {
	$event_id = (int) $event_id;

	if ( ! $event_id ) {
		return '';
	}

	if ( eo_recurs( $event_id ) ) {
		$next_occurrence = eo_get_next_occurrence_of( $event_id );

		if ( empty( $next_occurrence['start'] ) || ! ( $next_occurrence['start'] instanceof DateTime ) ) {
			return '';
		}

		$target = clone $next_occurrence['start'];
	} else {
		$schedule = eo_get_event_schedule( $event_id );
		if ( empty( $schedule['start'] ) || ! ( $schedule['start'] instanceof DateTime ) ) {
			return '';
		}

		$target = clone $schedule['start'];
	}

	$today = new DateTime( 'now', eo_get_blog_timezone() );
	$today->setTime( 0, 0, 0 );
	$target->setTime( 0, 0, 0 );

	$day_diff = (int) $today->diff( $target )->format( '%r%a' );

	if ( $day_diff < 0 ) {
		return '';
	}

	if ( 0 === $day_diff ) {
		return esc_html__( 'Coming up today', 'event-organiser-extras' );
	}

	if ( 1 === $day_diff ) {
		return esc_html__( 'Coming up tomorrow', 'event-organiser-extras' );
	}

	return sprintf(
		/* translators: %d: number of days until the event. */
		esc_html__( 'Coming up in %d days', 'event-organiser-extras' ),
		$day_diff
	);
}

// Create the [eox_event_times] shortcode.
function eox_event_times_shortcode( $atts = array() ) {
	$atts = shortcode_atts(
		array(
			'timezone' => 'true',
		),
		$atts,
		'eox_event_times'
	);

	$event_id = eox_get_current_event_id();

	if ( ! $event_id ) {
		return '';
	}

	$timezone_enabled = eox_shortcode_attribute_is_enabled( $atts['timezone'] );

	return eox_get_event_time_range( $event_id, $timezone_enabled );
}
add_shortcode( 'eox_event_times', 'eox_event_times_shortcode' );

// Create the [eox_event_date] shortcode.
function eox_event_date_shortcode( $atts = array() ) {
	// Allow one shortcode to handle plain dates or more detailed combined output.
	$atts = shortcode_atts(
		array(
			'time'       => 'false',
			'timezone'   => 'false',
			'occurrence' => 'false',
		),
		$atts,
		'eox_event_date'
	);

	$time_enabled       = eox_shortcode_attribute_is_enabled( $atts['time'] );
	$timezone_enabled   = eox_shortcode_attribute_is_enabled( $atts['timezone'] );
	$occurrence_enabled = eox_shortcode_attribute_is_enabled( $atts['occurrence'] );
	$event_id           = eox_get_current_event_id();

	if ( ! $event_id ) {
		return '';
	}

	$output = eox_get_event_date_text( $event_id, $time_enabled, $timezone_enabled, $occurrence_enabled );

	return '<div class="eox-event-meta ' . esc_attr( eo_recurs( $event_id ) ? 'eox-event-meta--recurring' : 'eox-event-meta--single' ) . '">' . $output . '</div>';
}
add_shortcode( 'eox_event_date', 'eox_event_date_shortcode' );
