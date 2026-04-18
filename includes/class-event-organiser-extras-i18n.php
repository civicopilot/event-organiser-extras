<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://civicopilot.com
 * @since      1.0.0
 *
 * @package    Event_Organiser_Extras
 * @subpackage Event_Organiser_Extras/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Event_Organiser_Extras
 * @subpackage Event_Organiser_Extras/includes
 * @author     Andy Burns <andy@andyburns.co>
 */
class Event_Organiser_Extras_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'event-organiser-extras',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
