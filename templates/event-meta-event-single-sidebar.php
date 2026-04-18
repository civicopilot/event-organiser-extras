<?php
/**
 * Default sidebar meta template for the [eo_extras_sidebar_meta] shortcode.
 *
 * Child themes can override this by creating either:
 * - event-meta-event-single-sidebar.php
 * - event-organiser-extras/event-meta-event-single-sidebar.php
 *
 * @package Event_Organiser_Extras
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="eventorganiser-event-meta eventorganiser-event-meta-sidebar">
	<h4><?php esc_html_e( 'Event Details', 'eventorganiser' ); ?></h4>

	<div class="eo-event-meta">
		<?php if ( eo_recurs() ) : ?>
			<?php echo wp_kses_post( eo_extras_event_date_shortcode( array(
				'time'       => 'true',
				'timezone'   => 'true',
				'occurrence' => 'true',
			) ) ); ?>
			<div>
				<strong><?php esc_html_e( 'Recurrence', 'eventorganiser' ); ?>:</strong>
				<?php echo esc_html( eo_extras_get_event_recurrence_text( get_the_ID() ) ); ?>
			</div>
		<?php else : ?>
			<?php echo wp_kses_post( eo_extras_event_date_shortcode( array(
				'time'     => 'true',
				'timezone' => 'true',
			) ) ); ?>
		<?php endif; ?>

		<?php if ( eo_get_venue() ) : ?>
			<?php $address = array_filter( eo_get_venue_address() ); ?>
			<div>
				<strong><?php esc_html_e( 'Venue', 'eventorganiser' ); ?>:</strong>
				<?php eo_venue_name(); ?>
				<?php if ( ! empty( $address ) ) : ?>
					(<?php echo esc_html( implode( ', ', $address ) ); ?>)
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php $categories = get_the_terms( get_the_ID(), 'event-category' ); ?>
		<?php if ( $categories && ! is_wp_error( $categories ) ) : ?>
			<div>
				<strong><?php esc_html_e( 'Category', 'eventorganiser' ); ?>:</strong>
				<?php echo wp_kses_post( get_the_term_list( get_the_ID(), 'event-category', '', ', ', '' ) ); ?>
			</div>
		<?php endif; ?>

		<?php $tags = get_the_terms( get_the_ID(), 'event-tag' ); ?>
		<?php if ( $tags && ! is_wp_error( $tags ) ) : ?>
			<div>
				<strong><?php esc_html_e( 'Tags', 'eventorganiser' ); ?>:</strong>
				<?php echo wp_kses_post( get_the_term_list( get_the_ID(), 'event-tag', '', ', ', '' ) ); ?>
			</div>
		<?php endif; ?>
	</div>
</div>
