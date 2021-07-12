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

	public function config_access_keys() {

		if( isset( $_POST['config-cloud-storage'] ) ) {
			$options = ['config-type'  => sanitize_text_field( $_POST[ 'config-type' ] ) ];

			if( $_POST['config-type'] == 'db' ) {
				$options[ 'access-key' ]   = sanitize_text_field( $_POST[ 'access-key' ] );
				$options[ 'secret-key' ]   = sanitize_text_field( $_POST[ 'secret-key' ] );
				$options[ 'endpoint-url' ] = sanitize_text_field( $_POST[ 'endpoint-url' ] );
			} else {
				delete_option( 'arvan-cloud-storage-settings' );
			}

			$save_settings = update_option( 'arvan-cloud-storage-settings', serialize( $options ) );

			if( $save_settings ) {
				add_action( 'admin_notices', function () {
					echo '<div class="notice notice-success is-dismissible">
							<p>'. __( "Settings saved.", ACS_TEXTDOMAIN ) .'</p>
						</div>';
				} );
			}
		}

	}

	public function store_selected_bucket_in_db() {

		if( isset( $_POST['acs-bucket-select-name'] ) ) {
			
			$save_bucket = update_option( 'arvan-cloud-storage-bucket-name', sanitize_text_field( $_POST[ 'acs-bucket-select-name' ] ) );

			if( $save_bucket ) {
				add_action( 'admin_notices', function () {
					echo '<div class="notice notice-success is-dismissible">
							<p>'. __( "Selected bucket saved.", ACS_TEXTDOMAIN ) .'</p>
						</div>';
				} );
			} else {
				add_action( 'admin_notices', function () {
					echo '<div class="notice notice-error is-dismissible">
							<p>'. __( "Saving selected bucket failed. Please try again or contact with admin.", ACS_TEXTDOMAIN ) .'</p>
						</div>';
				} );
			}
		}

	}
	
	public function upload_media_to_storage( $upload ) {

		if( $bucket_name = get_option( 'arvan-cloud-storage-bucket-name', true ) ) {
			require_once ACS_PLUGIN_ROOT . 'includes/wp-arvancloud-storage-s3client.php';

			$file_size = number_format( filesize( $upload['file'] ) / 1048576, 2 ); // Get file size in MB

			if( $file_size > 400 ) {
				$source = $upload['file'];
				$uploader = new MultipartUploader( $client, $source, [
					'bucket' => $bucket_name,
					'key' => basename( $upload['file'] ),
				]);

				try {
					$result = $uploader->upload();

					add_action( 'admin_notices', function () use( $result ) {
						echo '<div class="notice notice-success is-dismissible">
								<p>'. __( "Upload complete:" . $result['ObjectURL'], ACS_TEXTDOMAIN ) .'</p>
							</div>';
					} );
				} catch ( Exception $e ) {
					add_action( 'admin_notices', function () use( $e ) {
						echo '<div class="notice notice-error is-dismissible">
								<p>'. $e->getMessage() .'</p>
							</div>';
					} );
				}
			} else {
				try {
					$client->putObject([
						'Bucket' 	 => $bucket_name,
						'Key' 		 => basename( $upload['file'] ),
						'SourceFile' => $upload['file'],
					]);
				} catch ( Exception $e ) {
					add_action( 'admin_notices', function () use( $e ) {
						echo '<div class="notice notice-error is-dismissible">
								<p>'. $e->getMessage() .'</p>
							</div>';
					} );
				}	
			}
		}

		return $upload;

	}

	public function delete_media_from_storage( $id ) {
		
		if( $bucket_name = get_option( 'arvan-cloud-storage-bucket-name', true ) ) {
			require_once ACS_PLUGIN_ROOT . 'includes/wp-arvancloud-storage-s3client.php';

			$filename = basename ( get_attached_file( $id ) );

			$client->deleteObject ([
				'Bucket' => $bucket_name, 
				'Key' => $filename
			]);
		}
	}

}
