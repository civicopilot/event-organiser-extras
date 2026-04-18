<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that loads the plugin's shared functionality.
 *
 * @link       https://civicopilot.com
 * @since      1.0.0
 *
 * @package    Event_Organiser_Extras
 * @subpackage Event_Organiser_Extras/includes
 */

/**
 * The core plugin class.
 *
 * This keeps the plugin name and version available and registers shared hooks
 * such as translation loading.
 *
 * @since      1.0.0
 * @package    Event_Organiser_Extras
 * @subpackage Event_Organiser_Extras/includes
 * @author     Andy Burns <andy@andyburns.co>
 */
class Event_Organiser_Extras {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Event_Organiser_Extras_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and version used throughout the plugin, load the
	 * shared dependencies, and register shared hooks.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'EVENT_ORGANISER_EXTRAS_VERSION' ) ) {
			$this->version = EVENT_ORGANISER_EXTRAS_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'event-organiser-extras';

		$this->load_dependencies();
		$this->set_locale();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Event_Organiser_Extras_Loader. Orchestrates the hooks of the plugin.
	 * - Event_Organiser_Extras_i18n. Defines internationalization functionality.
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-event-organiser-extras-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-event-organiser-extras-i18n.php';

		$this->loader = new Event_Organiser_Extras_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Event_Organiser_Extras_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Event_Organiser_Extras_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Event_Organiser_Extras_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
