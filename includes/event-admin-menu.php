<?php
/**
 * Event admin menu additions.
 *
 * @package Event_Organiser_Extras
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Adds a Future Events submenu item under the Events admin menu.
 */
function eox_add_future_events_menu_link() {
	global $submenu;

	$parent_slug   = 'edit.php?post_type=event';
	$link_url      = 'edit.php?post_type=event&eo_interval=future';
	$menu_position = 6;

	if ( ! isset( $submenu[ $parent_slug ] ) || isset( $submenu[ $parent_slug ][ $menu_position ] ) ) {
		return;
	}

	$submenu[ $parent_slug ][ $menu_position ] = array(
		__( 'Future Events', 'event-organiser-extras' ),
		'edit_events',
		$link_url,
	);

	ksort( $submenu[ $parent_slug ] );
}
add_action( 'admin_menu', 'eox_add_future_events_menu_link', 100 );
