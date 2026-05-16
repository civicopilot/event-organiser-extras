<?php
/**
 * Default sidebar meta template for the [eox_event_sidebar_meta] shortcode.
 *
 * Child themes can override this by creating:
 * - event-organiser-extras/event-sidebar-meta.php
 *
 * @package Event_Organiser_Extras
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$venue_id              = eo_get_venue();
$venue_name            = $venue_id ? eo_get_venue_name( $venue_id ) : '';
$address_details       = $venue_id ? array_filter( eo_get_venue_address( $venue_id ) ) : array();
$location_parts        = array();
$event_id              = get_the_ID();
$is_recurring          = eo_recurs( $event_id );
$recurrence_text       = eox_get_event_recurrence_text( $event_id );
$next_occurrence       = $is_recurring ? eo_get_next_occurrence_of( $event_id ) : false;
$has_future_occurrence = ! empty( $next_occurrence );
$countdown_text        = eox_get_event_countdown_text( $event_id );

if ( $is_recurring && $has_future_occurrence ) {
	$occurrence_id = ! empty( $next_occurrence['occurrence_id'] ) ? (int) $next_occurrence['occurrence_id'] : 0;
	$date_value    = $occurrence_id ? eo_get_the_start( 'l, F j, Y', $event_id, $occurrence_id ) : '';

	if ( $occurrence_id && eo_is_all_day( $event_id ) ) {
		$time_value = esc_html__( 'All day', 'eventorganiser' );
	} elseif ( $occurrence_id ) {
		$time_value = sprintf(
			'%1$s - %2$s %3$s',
			eo_get_the_start( 'g:i A', $event_id, $occurrence_id ),
			eo_get_the_end( 'g:i A', $event_id, $occurrence_id ),
			eox_shortcode_timezone_abbreviation()
		);
	} else {
		$time_value = '';
	}
} else {
	$occurrences          = eo_get_the_occurrences_of( $event_id );
	$occurrence_ids       = is_array( $occurrences ) ? array_keys( $occurrences ) : array();
	$single_occurrence_id = ! empty( $occurrence_ids ) ? (int) $occurrence_ids[0] : 0;
	$date_value           = $single_occurrence_id ? eo_get_the_start( 'l, F j, Y', $event_id, $single_occurrence_id ) : eo_get_schedule_start( 'l, F j, Y', $event_id );

	$time_value = eo_is_all_day( $event_id )
		? esc_html__( 'All day', 'eventorganiser' )
		: sprintf(
			'%1$s - %2$s %3$s',
			$single_occurrence_id ? eo_get_the_start( 'g:i A', $event_id, $single_occurrence_id ) : eo_get_schedule_start( 'g:i A', $event_id ),
			$single_occurrence_id ? eo_get_the_end( 'g:i A', $event_id, $single_occurrence_id ) : '',
			eox_shortcode_timezone_abbreviation()
		);
}

if ( ! empty( $address_details['address'] ) ) {
	$location_parts[] = $address_details['address'];
}

$city_state_postcode = '';

if ( ! empty( $address_details['city'] ) ) {
	$city_state_postcode .= rtrim( $address_details['city'], ',' );
}

if ( ! empty( $address_details['state'] ) ) {
	$city_state_postcode .= $city_state_postcode ? ', ' : '';
	$city_state_postcode .= $address_details['state'];
}

if ( ! empty( $address_details['postcode'] ) ) {
	$city_state_postcode .= $city_state_postcode ? ' ' : '';
	$city_state_postcode .= $address_details['postcode'];
}

if ( '' !== $city_state_postcode ) {
	$location_parts[] = $city_state_postcode;
}

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

$google_maps_url = $google_maps_address
	? 'https://www.google.com/maps/dir/?api=1&destination=' . rawurlencode( $google_maps_address )
	: '';
?>

<aside class="eox-event-meta eox-sidebar-meta">
	<h4><?php esc_html_e( 'Event Details', 'event-organiser-extras' ); ?></h4>

	<div class="eox-event-meta__list">
		<div class="eox-event-meta__item">
			<div class="eox-event-meta__label"><?php esc_html_e( 'When', 'event-organiser-extras' ); ?></div>
			<div class="eox-event-meta__value">
				<div class="eox-event-meta__date"><?php echo esc_html( $date_value ); ?></div>
				<?php if ( '' !== $time_value ) : ?>
					<div class="eox-event-meta__time"><?php echo esc_html( $time_value ); ?></div>
				<?php endif; ?>
				<?php if ( $has_future_occurrence && '' !== $recurrence_text ) : ?>
					<div class="eox-event-meta__recurrence"><?php echo esc_html( ucfirst( $recurrence_text ) ); ?></div>
				<?php elseif ( ! $is_recurring && '' !== $countdown_text ) : ?>
					<div class="eox-event-meta__countdown"><?php echo esc_html( $countdown_text ); ?></div>
				<?php endif; ?>
			</div>
		</div>

		<?php if ( $venue_id ) : ?>
			<div class="eox-event-meta__item">
				<div class="eox-event-meta__label"><?php esc_html_e( 'Where', 'event-organiser-extras' ); ?></div>
				<div class="eox-event-meta__value">
					<?php if ( $venue_name ) : ?>
						<div class="eox-event-meta__venue"><?php echo esc_html( $venue_name ); ?></div>
					<?php endif; ?>
						<?php foreach ( $location_parts as $location_part ) : ?>
							<div class="eox-event-meta__address-line"><?php echo esc_html( $location_part ); ?></div>
						<?php endforeach; ?>
					<?php if ( $google_maps_url ) : ?>
						<div class="eox-event-meta__map-link">
							<a href="<?php echo esc_url( $google_maps_url ); ?>" target="_blank" rel="noopener noreferrer">
								<?php esc_html_e( 'Google Maps & Directions', 'event-organiser-extras' ); ?>
							</a>
						</div>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>
	</div>

	<div class="eox-register-link">
		<?php
		if ( $is_recurring ) {
			$register_link = eox_get_recurring_register_links_markup( $event_id );
		} else {
			$register_link = do_shortcode( '[ceo_register_link]' );
		}

		if ( ! empty( $register_link ) ) {
			echo wp_kses_post( $register_link );
		}
		?>
	</div>
</aside>
