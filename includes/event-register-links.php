<?php
/**
 * Event registration link helpers.
 *
 * @package Event_Organiser_Extras
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Returns whether the paid_event ACF field exists for the given event.
 *
 * @param int $event_id Event post ID.
 * @return bool
 */
function eo_extras_has_paid_event_field( $event_id ) {
	if ( ! function_exists( 'get_field_object' ) ) {
		return false;
	}

	$field_object = get_field_object( 'paid_event', $event_id, false, false );

	if ( empty( $field_object ) || empty( $field_object['type'] ) ) {
		return false;
	}

	return 'true_false' === $field_object['type'];
}

/**
 * Returns register link markup for an event.
 *
 * Single events defer to the existing CiviCRM Event Organiser shortcode.
 * Recurring events are reduced to one registration link for the next active occurrence.
 *
 * @param int   $event_id Event post ID.
 * @param array $args Optional arguments.
 * @return string
 */
function eo_extras_get_event_register_link_markup( $event_id, $args = array() ) {
	$event_id = (int) $event_id;

	if ( ! $event_id ) {
		return '';
	}

	$args = wp_parse_args(
		$args,
		array(
			'messages'     => 'no',
			'wrap'         => 'div',
			'anchor_class' => '',
			'paid_event'   => null,
		)
	);

	if ( function_exists( 'get_field' ) ) {
		$args['paid_event'] = get_field( 'paid_event', $event_id );
	}

	$title = false === $args['paid_event'] ? 'RSVP' : 'Register';

	if ( ! eo_recurs( $event_id ) ) {
		$shortcode = sprintf(
			'[ceo_register_link event_id="%1$d" messages="%2$s" wrap="%3$s" title="%4$s" anchor_class="%5$s"]',
			$event_id,
			esc_attr( $args['messages'] ),
			esc_attr( $args['wrap'] ),
			esc_attr( $title ),
			esc_attr( $args['anchor_class'] )
		);

		return do_shortcode( $shortcode );
	}

	if ( ! function_exists( 'get_field' ) || ! eo_extras_has_paid_event_field( $event_id ) ) {
		return '';
	}

	if ( ! function_exists( 'civicrm_event_organiser_get_register_links' ) || ! function_exists( 'civicrm_eo' ) ) {
		return '';
	}

	$links_data = civicrm_event_organiser_get_register_links( $event_id );
	$next_link  = '';
	$next_time  = null;

	foreach ( $links_data as $civi_event_id => $link_data ) {
		if ( empty( $link_data['link'] ) || empty( $link_data['meta'] ) || ! in_array( 'active', $link_data['meta'], true ) ) {
			continue;
		}

		$occurrence_id = civicrm_eo()->mapping->get_eo_occurrence_id_by_civi_event_id( (int) $civi_event_id );
		if ( empty( $occurrence_id ) ) {
			continue;
		}

		$occurrence_ts = eo_get_the_start( 'U', $event_id, $occurrence_id );
		if ( empty( $occurrence_ts ) ) {
			continue;
		}

		if ( null === $next_time || (int) $occurrence_ts < $next_time ) {
			$date_label = eo_get_the_start( 'F j, Y', $event_id, $occurrence_id );
			$link_text  = sprintf( '%1$s for %2$s', $title, $date_label );
			$href       = '';
			$classes    = 'civicrm-event-organiser-register-link';

			if ( preg_match( '/href=(["\'])(.*?)\1/', $link_data['link'], $href_match ) ) {
				$href = $href_match[2];
			}

			if ( preg_match( '/class=(["\'])(.*?)\1/', $link_data['link'], $class_match ) ) {
				$classes = $class_match[2];
			}

			if ( ! empty( $args['anchor_class'] ) ) {
				$classes = trim( $classes . ' ' . $args['anchor_class'] );
			}

			if ( ! empty( $href ) ) {
				$next_link = sprintf(
					'<a class="%1$s" href="%2$s">%3$s</a>',
					esc_attr( $classes ),
					esc_url( $href ),
					esc_html( $link_text )
				);
			}

			$next_time = (int) $occurrence_ts;
		}
	}

	if ( empty( $next_link ) ) {
		return '';
	}

	if ( 'div' === $args['wrap'] ) {
		return '<div>' . $next_link . '</div>';
	}

	return $next_link;
}
