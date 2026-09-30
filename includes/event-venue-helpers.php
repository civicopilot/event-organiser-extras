<?php
/**
 * Venue helpers for Event Organiser.
 *
 * @package Event_Organiser_Extras
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Returns a Google Maps directions URL for a venue's address.
 *
 * @param int $venue_id Event Organiser venue term ID.
 * @return string Unescaped URL, or an empty string when no address is available.
 *                Escape with esc_url() when rendering in HTML.
 */
function eox_get_venue_google_directions_url( $venue_id ) {
	$venue_id = (int) $venue_id;

	if ( $venue_id <= 0 ) {
		return '';
	}

	$address_details = eo_get_venue_address( $venue_id );

	$google_maps_address = implode(
		', ',
		array_filter(
			array(
				$address_details['address'] ?? '',
				$address_details['city'] ?? '',
				trim(
					implode(
						' ',
						array_filter(
							array(
								$address_details['state'] ?? '',
								$address_details['postcode'] ?? '',
							)
						)
					)
				),
				$address_details['country'] ?? '',
			)
		)
	);

	return $google_maps_address
		? 'https://www.google.com/maps/dir/?api=1&destination=' . rawurlencode( $google_maps_address )
		: '';
}
