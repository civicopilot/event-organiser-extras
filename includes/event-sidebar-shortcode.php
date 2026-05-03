<?php
/**
 * Event sidebar shortcode and template loading.
 *
 * @package Event_Organiser_Extras
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Locates the sidebar template, preferring child-theme overrides.
 *
 * @return string
 */
function eox_get_event_sidebar_template_path() {
	$template_names = array(
		'event-organiser-extras/event-sidebar-meta.php',
	);

	$template = locate_template( $template_names, false, false );

	if ( ! empty( $template ) ) {
		return $template;
	}

	return plugin_dir_path( dirname( __FILE__ ) ) . 'templates/event-sidebar-meta.php';
}

/**
 * Returns whether the plugin fallback sidebar template is being used.
 *
 * @param string $template_path Absolute template path.
 * @return bool
 */
function eox_is_plugin_sidebar_template( $template_path ) {
	return plugin_dir_path( dirname( __FILE__ ) ) . 'templates/event-sidebar-meta.php' === $template_path;
}

/**
 * Create the [eox_sidebar_meta] shortcode.
 *
 * Loads a default sidebar meta template from the plugin, but allows a child
 * theme override so client-specific sidebar presentation can live in the theme.
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function eox_event_sidebar_meta_shortcode( $atts = array() ) {
	unset( $atts );

	$event_id = eox_get_current_event_id();

	if ( ! $event_id ) {
		return '';
	}

	$template_path = eox_get_event_sidebar_template_path();

	if ( ! file_exists( $template_path ) ) {
		return '';
	}

	if ( eox_is_plugin_sidebar_template( $template_path ) ) {
		wp_enqueue_style( 'event-organiser-extras' );
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
add_shortcode( 'eox_sidebar_meta', 'eox_event_sidebar_meta_shortcode' );
