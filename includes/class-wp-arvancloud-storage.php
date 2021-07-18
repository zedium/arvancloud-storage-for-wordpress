<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       khorshidlab.com
 * @since      1.0.0
 *
 * @package    Wp_Arvancloud_Storage
 * @subpackage Wp_Arvancloud_Storage/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Wp_Arvancloud_Storage
 * @subpackage Wp_Arvancloud_Storage/includes
 * @author     Khorshid <info@khorshidlab.com>
 */
class Wp_Arvancloud_Storage {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Wp_Arvancloud_Storage_Loader    $loader    Maintains and registers all hooks for the plugin.
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
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'ACS_VERSION' ) ) {
			$this->version = ACS_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		
		$this->plugin_name = ACS_NAME;

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Wp_Arvancloud_Storage_Loader. Orchestrates the hooks of the plugin.
	 * - Wp_Arvancloud_Storage_i18n. Defines internationalization functionality.
	 * - Wp_Arvancloud_Storage_Admin. Defines all hooks for the admin area.
	 * - Wp_Arvancloud_Storage_Public. Defines all hooks for the public side of the site.
	 *
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
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-arvancloud-storage-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-arvancloud-storage-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wp-arvancloud-storage-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wp-arvancloud-storage-public.php';

		$this->loader = new Wp_Arvancloud_Storage_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Wp_Arvancloud_Storage_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Wp_Arvancloud_Storage_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Wp_Arvancloud_Storage_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		// $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'setup_admin_menu' );
		$this->loader->add_action( 'init', $plugin_admin, 'config_access_keys' );
		$this->loader->add_action( 'init', $plugin_admin, 'store_selected_bucket_in_db' );
		$this->loader->add_action( 'delete_attachment', $plugin_admin, 'delete_media_from_storage', 10, 1 );
		$this->loader->add_action( 'wp_ajax_acs_get_attachment_provider_details', $plugin_admin, 'ajax_get_attachment_provider_details' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'process_media_actions' );
		$this->loader->add_action( 'admin_notices', $plugin_admin, 'maybe_display_media_action_message' );
		$this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'add_edit_attachment_metabox' );
		$this->loader->add_filter( 'add_attachment', $plugin_admin, 'upload_media_to_storage', 10, 1 );
		$this->loader->add_filter( 'wp_generate_attachment_metadata', $plugin_admin, 'upload_image_to_storage', 10, 1 );
		$this->loader->add_filter( 'wp_get_attachment_url', $plugin_admin, 'media_library_url_rewrite' );
		$this->loader->add_filter( 'bulk_actions-upload', $plugin_admin, 'bulk_actions_upload' );
		$this->loader->add_filter( 'handle_bulk_actions-upload', $plugin_admin, 'handle_bulk_actions_upload', 10, 3 );
		$this->loader->add_filter( 'media_row_actions', $plugin_admin, 'add_media_row_actions', 10, 3 );
		$this->loader->add_filter( 'wp_calculate_image_srcset', $plugin_admin, 'calculate_image_srcset', 10, 5 );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Wp_Arvancloud_Storage_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

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
	 * @return    Wp_Arvancloud_Storage_Loader    Orchestrates the hooks of the plugin.
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
