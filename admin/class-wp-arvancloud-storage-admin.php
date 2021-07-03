<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       khorshidlab.com
 * @since      1.0.0
 *
 * @package    Wp_Arvancloud_Storage
 * @subpackage Wp_Arvancloud_Storage/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wp_Arvancloud_Storage
 * @subpackage Wp_Arvancloud_Storage/admin
 * @author     Khorshid <info@khorshidlab.com>
 */
class Wp_Arvancloud_Storage_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wp_Arvancloud_Storage_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wp_Arvancloud_Storage_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wp-arvancloud-storage-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wp_Arvancloud_Storage_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wp_Arvancloud_Storage_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wp-arvancloud-storage-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
     * Register submenu
     * @return void
     */
    public function setup_admin_menu() {

        add_menu_page( 
			__( ACS_NAME, ACS_TEXTDOMAIN ), 
			__( ACS_NAME, ACS_TEXTDOMAIN), 
			'manage_options', 
			ACS_SLUG, 
			__CLASS__ . '::settings_page',
            'dashicons-cloud'
        );

		add_submenu_page(
			'wp-arvancloud-storage',
			__( 'About Us', ACS_TEXTDOMAIN ),
			__( 'About Us', ACS_TEXTDOMAIN ),
			'manage_options',
			ACS_SLUG . '-about-us',
			__CLASS__ . '::about_us_page'
		);

    }

	public static function settings_page() {

		require_once( 'partials/wp-arvancloud-storage-settings-display.php' );

    }

	public static function about_us_page() {

		require_once( 'partials/wp-arvancloud-storage-about-us-display.php' );

    }

	public function store_access_keys_in_db() {

		if( isset( $_POST['save_access_keys'] ) ) {
			update_option( 'arvan-cloud-storage-settings', serialize( [
				'access-key'   => sanitize_text_field( $_POST[ 'access-key' ] ),
				'secret-key'   => sanitize_text_field( $_POST[ 'secret-key' ] ),
				'endpoint-url' => sanitize_text_field( $_POST[ 'endpoint-url' ] ),
			] ) );
		}

	}

}
