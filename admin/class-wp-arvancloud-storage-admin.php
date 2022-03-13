<?php
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

		$this->plugin_name 		= $plugin_name;
		$this->version 			= $version;
		$this->acs_settings 	= get_option( 'acs_settings', true );
		$this->bucket_name  	= get_bucket_name();
		$this->storage_settings	= get_storage_settings();

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

		wp_enqueue_media();

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

		wp_localize_script( $this->plugin_name, 'acs_media', array(
			'strings' => $this->get_media_action_strings(),
			'nonces'  => array(
				'get_attachment_provider_details' => wp_create_nonce( 'get-attachment-s3-details' ),
			),
		) );

		if (isset( $_GET['system-info'] ) && $_GET['system-info'] == true) {
			wp_enqueue_script(  'clipboard' );
		}

	}

	/**
     * Register submenu
	 * 
     * @return void
     */
    public function setup_admin_menu() {

        add_menu_page( 
			__( ACS_NAME, 'arvancloud-object-storage' ), 
			__( ACS_NAME, 'arvancloud-object-storage'), 
			'manage_options', 
			ACS_SLUG, 
			__CLASS__ . '::settings_page',
            ACS_PLUGIN_ROOT_URL . 'admin/img/arvancloud-logo.svg'
        );

		add_submenu_page(
			'wp-arvancloud-storage',
			$this->settings_page_title(),
			__( 'Settings', 'arvancloud-object-storage' ),
			'manage_options',
			ACS_SLUG,
			__CLASS__ . '::settings_page'
		);

		add_submenu_page(
			'wp-arvancloud-storage',
			__( 'About ArvanCloud', 'arvancloud-object-storage' ),
			__( 'About', 'arvancloud-object-storage' ),
			'manage_options',
			ACS_SLUG . '-about',
			__CLASS__ . '::about_us_page'
		);

    }
	
	/**
	 * settings_page
	 *
	 * @return void
	 */
	public static function settings_page() {

		require_once( 'partials/wp-arvancloud-storage-settings-display.php' );

    }

	public function settings_page_title() {

		if (isset( $_GET['system-info'] ) && $_GET['system-info'] == true) {
			return __( 'System info', 'arvancloud-object-storage' );
		}

		return __( 'Settings', 'arvancloud-object-storage' );
    }
	
	/**
	 * about_us_page
	 *
	 * @return void
	 */
	public static function about_us_page() {

		require_once( 'partials/wp-arvancloud-storage-about-us-display.php' );

    }

	/**
	 * Sets the access control system and saves it to an option after encryption
	 *
	 * @return void
	 */
	public function config_access_keys() {

		if( isset( $_POST[ 'config-cloud-storage' ] ) ) {
			$options = [ 'config-type'  => sanitize_text_field( $_POST[ 'config-type' ] ) ];

			if( $_POST[ 'config-type' ] == 'db' ) {
				$options[ 'access-key' ]   = sanitize_key( $_POST[ 'access-key' ] );
				$options[ 'secret-key' ]   = sanitize_key( $_POST[ 'secret-key' ] );
				$options[ 'endpoint-url' ] = esc_url_raw( $_POST[ 'endpoint-url' ], [ 'https' ] );

				if ( ! empty( $_POST[ 'secret-key' ] ) && __( "-- not shown --", 'arvancloud-object-storage' ) === $_POST[ 'secret-key' ] ) {
					$options[ 'secret-key' ] = $this->storage_settings[ 'secret-key' ];
				}

				// Validates that the access-key is UUID.
				if( !wp_is_uuid( $options[ 'access-key' ] ) ) {
					unset( $options[ 'access-key' ] );

					update_option( 'arvan-cloud-storage-settings', acs_encrypt( json_encode( $options ) ) );

					add_action( 'admin_notices', function () {
						echo '<div class="notice notice-error is-dismissible">
								<p>'. esc_html__( "Access Key is not valid!", 'arvancloud-object-storage' ) .'</p>
							</div>';
					} );

					return false;
				}
			} else if ( $_POST[ 'config-type' ] == 'snippet' ) {
				$snippet_defined     = defined( 'ARVANCLOUD_STORAGE_SETTINGS' );

				if ( !$snippet_defined ) {
					add_action( 'admin_notices', function () {
						echo '<div class="notice notice-error is-dismissible">
								<p>'. esc_html__( "You have not defined your access keys in wp-config.php. Please try again.", 'arvancloud-object-storage' ) .'</p>
							</div>';
					} );

					return false;
				}
			}

			$save_settings = update_option( 'arvan-cloud-storage-settings', acs_encrypt( json_encode( $options ) ) );

			if( $save_settings ) {
				delete_option( 'arvan-cloud-storage-bucket-name' );
				
				add_action( 'admin_notices', function () {
					echo '<div class="notice notice-success is-dismissible">
							<p>'. esc_html__( "Settings saved.", 'arvancloud-object-storage' ) .'</p>
						</div>';
				} );
			}
		}

	}

	/**
	 * Saves selected bucket into database options table
	 *
	 * @return void
	 */
	public function store_selected_bucket_in_db() {

		if( isset( $_POST['acs-bucket-select-name'] ) ) {
			$save_bucket = update_option( 'arvan-cloud-storage-bucket-name', sanitize_text_field( $_POST[ 'acs-bucket-select-name' ] ) );

			if( $save_bucket ) {
				wp_redirect( add_query_arg( array( 'notice' => 'selected-bucket-saved' ), wp_sanitize_redirect( admin_url( '?page=wp-arvancloud-storage' ) ) ) ); 
				exit;
			} else {
				add_action( 'admin_notices', function () {
					echo '<div class="notice notice-error is-dismissible">
							<p>'. esc_html__( "Saving selected bucket failed. Please try again or contact with admin.", 'arvancloud-object-storage' ) .'</p>
						</div>';
				} );
			}
		}

	}

	/**
	 * Saves plugin settings into database options table
	 *
	 * @return void
	 */
	public function save_plugin_settings() {
		if( isset( $_POST['acs-settings'] ) ) {
			$settings = [
				'keep-local-files' => isset( $_POST['keep-local-files'] ) ?: false
			];

			$save_settings = update_option( 'acs_settings', $settings );

			if( $save_settings ) {
				add_action( 'admin_notices', function () {
					echo '<div class="notice notice-success is-dismissible">
							<p>'. esc_html__( "Settings saved.", 'arvancloud-object-storage' ) .'</p>
						</div>';
				} );
			} else {
				add_action( 'admin_notices', function () {
					echo '<div class="notice notice-error is-dismissible">
							<p>'. esc_html__( "Saving plugin settings failed. Please try again or contact with admin.", 'arvancloud-object-storage' ) .'</p>
						</div>';
				} );
			}
			
		}

	}
	
	/**
	 * Uploads media file to the storage bucket
	 *
	 * @param mixed $post_id 
	 * @param bool $force_upload Skips upload images by default
	 * @return void
	 */
	public function upload_media_to_storage( $post_id, $force_upload = false ) {

		if( !$this->bucket_name ) {
			return;
		}

		if( $force_upload || ( is_numeric( $post_id ) && !wp_attachment_is_image( $post_id ) ) ) {
			if(  
				( isset( $_POST['action'] ) && $_POST['action'] == 'upload-attachment' || $_POST['action'] == 'image-editor' ) || 
				$_SERVER['REQUEST_URI'] == '/wp-admin/async-upload.php' ||
				strpos( $_SERVER['REQUEST_URI'], 'media' ) !== false ||
				strpos( $_SERVER['REQUEST_URI'], 'action=copy' ) !== false ||
				$_POST['html-upload'] == 'Upload'
			) {
				require( ACS_PLUGIN_ROOT . 'includes/wp-arvancloud-storage-s3client.php' );
				$file 	   	  = is_numeric( $post_id ) ? get_attached_file( $post_id ) : $post_id;
				$file_size 	  = number_format( filesize( $file ) / 1048576, 2 ); // File size in MB
	
				if( $file_size > 400 ) {
					$uploader = new MultipartUploader( $client, $file, [
						'bucket' => $this->bucket_name,
						'key'    => basename( $file ),
						'ACL' 	 => 'public-read', // or private
					]);
	
					try {
						$result = $uploader->upload();
	
						add_action( 'admin_notices', function () use( $result ) {
							echo '<div class="notice notice-success is-dismissible">
									<p>'. esc_html__( "Upload complete:" . $result['ObjectURL'], 'arvancloud-object-storage' ) .'</p>
								</div>';
						} );
					} catch ( Exception $e ) {
						add_action( 'admin_notices', function () use( $e ) {
							echo '<div class="notice notice-error is-dismissible">
									<p>'. esc_html( $e->getMessage() ) .'</p>
								</div>';
						} );
					}
				} else {
					try {
						$client->putObject([
							'Bucket' 	 => $this->bucket_name,
							'Key' 		 => basename( $file ),
							'SourceFile' => $file,
							'ACL' 		 => 'public-read', // or private
						]);
					} catch ( Exception $e ) {
						add_action( 'admin_notices', function () use( $e ) {
							echo '<div class="notice notice-error is-dismissible">
									<p>'. esc_html( $e->getMessage() ) .'</p>
								</div>';
						} );
					}
				}
	
				if( is_numeric( $post_id ) ) {
					update_post_meta( $post_id, 'acs_storage_file_url', get_storage_url() );
	
					if( !$this->acs_settings['keep-local-files'] && !wp_attachment_is_image( $post_id ) ) {
						unlink( $file );
					}
				}
			}
			
		}

	}

	/**
	 * Uploads images and its sub sizes to the storage bucket
	 * 
	 * @param mixed $args 
	 * @return void
	 */
	public function upload_image_to_storage( $args ) {
		$upload_dir = wp_upload_dir();
		$basename	= basename( $args['file'] );
		$path 		= str_replace( $basename, "", $args['file'] );
		$url	    = $upload_dir['baseurl'] . '/' . $args['file'];
		$post_id	= attachment_url_to_postid($url);

		$this->upload_media_to_storage( $upload_dir['basedir'] . '/' . $args['file'], true );

		update_post_meta( $post_id, 'acs_storage_file_url', get_storage_url() );

		// Check if image has extra sizes
		if( array_key_exists( "sizes", $args ) ) {
			foreach ( $args['sizes'] as $sub_size ) {
				if ( $sub_size['file'] != "" ) {
					$file = $upload_dir['basedir'] . '/' . $path . $sub_size['file'];

					$this->upload_media_to_storage( $file, true );

					if( !$this->acs_settings['keep-local-files'] ) {
						unlink( $file );
					}
				}
			}
		}

		if( !$this->acs_settings['keep-local-files'] ) {
			unlink( $upload_dir['basedir'] . '/' . $args['file'] );
		}

		return $args;

	}

	/**
	 * Deletes media from the storage bucket
	 *
	 * @param mixed $id 
	 * @return void
	 */
	public function delete_media_from_storage( $id ) {
		
		if( !$this->bucket_name ) {
			return;
		}

		if( ( isset( $_POST['action'] ) && $_POST['action'] == 'delete-post' || $_POST['action'] == 'image-editor' ) && $this->is_attachment_served_by_storage( $id ) ) {
			require( ACS_PLUGIN_ROOT . 'includes/wp-arvancloud-storage-s3client.php' );

			if ( wp_attachment_is_image( $id ) ) {
				$args = wp_get_attachment_metadata( $id );

				$client->deleteObject ([
					'Bucket' => $this->bucket_name, 
					'Key' 	 => basename( $args['file'] )
				]);

				// Check if image has extra sizes
				if ( $args && array_key_exists( "sizes", $args ) ) {
					foreach ( $args['sizes'] as $list_file ) {
						if ( $list_file['file'] != "" ) {
							$client->deleteObject ([
								'Bucket' => $this->bucket_name, 
								'Key' 	 => basename( $list_file['file'] )
							]);
						}
					}
				}
			} else {
				$file = get_attached_file( $id );

				$client->deleteObject ([
					'Bucket' => $this->bucket_name, 
					'Key' 	 => basename( $file )
				]);
			}
		}
	}

	/**
	 * Calculates image srcset
	 *
	 * @param mixed $sources 
	 * @param mixed $size_array 
	 * @param mixed $image_src 
	 * @param mixed $image_meta 
	 * @param mixed $attachment_id 
	 * @return void
	 */
	public function calculate_image_srcset( $sources, $size_array, $image_src, $image_meta, $attachment_id ) {

		$base_upload      = wp_upload_dir();
		$uploads          = $base_upload['baseurl'];
		$filtered_sources = array();

		foreach ( $sources as $key => $source ) {
			if ( wp_attachment_is_image( $attachment_id ) ) {
				$cdn = get_post_meta( $attachment_id, 'acs_storage_file_url', true );
				
				if ( !empty( $cdn ) ) {
					$source['url'] = str_replace( trailingslashit( $uploads ), trailingslashit( get_storage_url() ), $source['url'] );
				}
			}

			$filtered_sources[ $key ] = $source;
		}

		return $filtered_sources;

	}

	/**
	 * Handles the upload of the attachment to provider when an attachment is updated using
	 * the 'wp_update_attachment_metadata' filter
	 *
	 * @param array $data meta data for attachment
	 * @param int   $post_id
	 *
	 * @return array
	 * @throws Exception
	 */
	function wp_update_attachment_metadata( $data, $post_id ) {

		if ( ! $this->bucket_name || ( isset( $_POST['action'] ) && $_POST['action'] == 'upload-attachment' ) ) {
			return $data;
		}

		// Protect against updates of partially formed metadata since WordPress 5.3.
		// Checks whether new upload currently has no subsizes recorded but is expected to have subsizes during upload,
		// and if so, are any of its currently missing sizes part of the set.
		if ( ! empty( $data ) && function_exists( 'wp_get_registered_image_subsizes' ) && function_exists( 'wp_get_missing_image_subsizes' ) ) {
			if ( empty( $data['sizes'] ) && wp_attachment_is_image( $post_id ) ) {
				// There is no unified way of checking whether subsizes are expected, so we have to duplicate WordPress code here.
				$new_sizes     = wp_get_registered_image_subsizes();
				$new_sizes     = apply_filters( 'intermediate_image_sizes_advanced', $new_sizes, $data, $post_id );
				$missing_sizes = wp_get_missing_image_subsizes( $post_id );

				if ( ! empty( $new_sizes ) && ! empty( $missing_sizes ) && array_intersect_key( $missing_sizes, $new_sizes ) ) {
					return $data;
				}
			}
		}
		
		// upload attachment to bucket
		$attachment_metadata = $this->upload_image_to_storage( $data );

		if ( is_wp_error( $attachment_metadata ) || empty( $attachment_metadata ) || ! is_array( $attachment_metadata ) ) {
			return $data;
		}

		return $attachment_metadata;
	}

	/**
	 * Rewirtes media library url to the storage url
	 *
	 * @param mixed $url 
	 * @return void
	 */
	public function media_library_url_rewrite( $url ) {

		$post_id 		  = attachment_url_to_postid( $url );
		$storage_file_url = get_post_meta( $post_id, 'acs_storage_file_url', true );

		if( !empty( $storage_file_url ) ) {
			$file_name = basename( $url );
			$url 	   = esc_url( $storage_file_url.$file_name );
		}
		
		return $url;
		
	}

	/**
	 * Adds copy to bucket link to Bulk actions
	 *
	 * @param mixed $bulk_actions 
	 * @return void
	 */
	public function bulk_actions_upload( $bulk_actions ) {

		if( $this->bucket_name ) {
			$bulk_actions['bulk_acs_copy'] = __( 'Copy to Bucket', 'arvancloud-object-storage' );
		}

		return $bulk_actions;

	}

	/**
	 * Handles bulk actions upload
	 *
	 * @param mixed $redirect 
	 * @param mixed $do_action 
	 * @param mixed $object_ids 
	 * @return void
	 */
	public function handle_bulk_actions_upload( $redirect, $do_action, $object_ids ) {

		$redirect = remove_query_arg( 'bulk_acs_copy_done', $redirect );

		if ( $do_action == 'bulk_acs_copy' ) {
			foreach ( $object_ids as $post_id ) {
				sleep( 2 ); // Delay execution
				
				if( wp_attachment_is_image( $post_id ) ) {
					$file = wp_get_attachment_metadata($post_id);
					$this->upload_image_to_storage( $file );
				} else {
					$this->upload_media_to_storage( $post_id );
				}
			}
	
			// add query args to URL because we will show notices later
			$redirect = add_query_arg(
				'bulk_acs_copy_done', // just a parameter for URL ( we will use $_GET['acs_copy_done'] )
				count( $object_ids ), // parameter value - how much posts have been affected
			$redirect );
		}

		return $redirect;

	}

	/**
	 * ajax_get_attachment_provider_details
	 *
	 * @return void
	 */
	public function ajax_get_attachment_provider_details() {
		
		if ( ! isset( $_POST['id'] ) ) {
			return;
		}

		check_ajax_referer( 'get-attachment-s3-details', '_nonce' );

		$id = intval( sanitize_text_field( $_POST['id'] ) );

		// get the actions available for the attachment
		$data = array(
			'links' => $this->add_media_row_actions( array(), $id ),
		);

		wp_send_json_success( $data );

	}

	/**
	 * Conditionally adds media action links for an attachment on the Media library list view.
	 *
	 * @param array       $actions
	 * @param WP_Post|int $post
	 *
	 * @return array
	 */
	function add_media_row_actions( array $actions, $post ) {

		$available_actions = $this->get_available_media_actions( 'singular' );

		if ( ! $available_actions ) {
			return $actions;
		}

		$post_id     = ( is_object( $post ) ) ? $post->ID : $post;
		$file        = get_attached_file( $post_id, true );
		$file_exists = file_exists( $file );

		if ( in_array( 'copy', $available_actions ) && $file_exists && ! $this->is_attachment_served_by_storage( $post_id, true ) ) {
			$this->add_media_row_action( $actions, $post_id, 'copy' );
		}

		return $actions;

	}

	/**
	 * Add an action link to the media actions array
	 *
	 * @param array  $actions
	 * @param int    $post_id
	 * @param string $action
	 * @param string $text
	 * @param bool   $show_warning
	 */
	function add_media_row_action( &$actions, $post_id, $action, $text = '', $show_warning = false ) {

		$url   = $this->get_media_action_url( $action, $post_id );
		$text  = $text ?: $this->get_media_action_strings( $action );
		$class = $action;

		if ( $show_warning ) {
			$class .= ' local-warning';
		}

		$actions[ 'acs_' . $action ] = '<a href="' . $url . '" class="' . $class . '" title="' . esc_attr( $text ) . '">' . esc_html( $text ) . '</a>';

	}

	/**
	 * Generate the URL for performing S3 media actions
	 *
	 * @param string      $action
	 * @param int         $post_id
	 * @param null|string $sendback_path
	 *
	 * @return string
	 */
	function get_media_action_url( $action, $post_id, $sendback_path = null ) {

		$args = array(
			'action' => $action,
			'ids'    => $post_id,
		);

		if ( ! is_null( $sendback_path ) ) {
			$args['sendback'] = urlencode( admin_url( $sendback_path ) );
		}

		$url = add_query_arg( $args, admin_url( 'upload.php' ) );
		$url = wp_nonce_url( $url, 'acs-' . $action );

		return esc_url( $url );

	}

	/**
	 * Get all strings or a specific string used for the media actions
	 *
	 * @param null|string $string
	 *
	 * @return array|string
	 */
	public function get_media_action_strings( $string = null ) {

		$strings = apply_filters( 'acs_media_action_strings', array(
			'copy' => __( 'Copy to Bucket', 'arvancloud-object-storage' ),
		) );

		if ( ! is_null( $string ) ) {
			return isset( $strings[ $string ] ) ? $strings[ $string ] : '';
		}

		return $strings;

	}

	/**
	 * Get a list of available media actions which can be performed according to plugin and user capability requirements.
	 *
	 * @param string|null $scope
	 *
	 * @return array
	 */
	public function get_available_media_actions( $scope = null ) {

		$actions = array();

		$actions['copy'] = array( 'singular', 'bulk' );

		if ( $scope ) {
			$in_scope = array_filter( $actions, function ( $scopes ) use ( $scope ) {
				return in_array( $scope, $scopes );
			} );

			return array_keys( $in_scope );
		}

		return $actions;

	}

	/**
	 * Is attachment served by object storage.
	 *
	 * @param int                   $attachment_id
	 *
	 * @return bool|Media_Library_Item
	 */
	public function is_attachment_served_by_storage( $attachment_id ) {

		$acs_item = get_post_meta( $attachment_id, 'acs_storage_file_url', true );

		if ( empty( $acs_item ) ) {
			// File not uploaded to a provider
			return false;
		}

		return true;

	}

	/**
	 * Handler for single and bulk media actions
	 *
	 * @return void
	 */
	function process_media_actions() {

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}

		global $pagenow;

		if ( 'upload.php' != $pagenow ) {
			return;
		}

		if ( ! isset( $_GET['action'] ) ) { // input var okay
			return;
		}

		if ( ! empty( $_REQUEST['action2'] ) && '-1' != $_REQUEST['action2'] ) {
			// Handle bulk actions from the footer bulk action select
			$action = sanitize_key( $_REQUEST['action2'] ); // input var okay
		} else {
			$action = sanitize_key( $_REQUEST['action'] ); // input var okay
		}

		if ( false === strpos( $action, 'bulk_acs_' ) ) {
			$available_actions = $this->get_available_media_actions( 'singular' );
			$referrer          = 'acs-' . $action;
			$doing_bulk_action = false;

			if ( ! isset( $_GET['ids'] ) ) {
				return;
			}

			$ids = explode( ',', sanitize_text_field( $_GET['ids'] ) ); // input var okay
		} else {
			$available_actions = $this->get_available_media_actions( 'bulk' );
			$action            = str_replace( 'bulk_acs_', '', $action );
			$referrer          = 'bulk-media';
			$doing_bulk_action = true;

			if ( ! isset( $_REQUEST['media'] ) ) {
				return;
			}

			$ids = acs_recursive_sanitize( $_REQUEST['media'] ); // input var okay
		}

		if ( ! in_array( $action, $available_actions ) ) {
			return;
		}

		$ids      = array_map( 'intval', $ids );
		$id_count = count( $ids );

		check_admin_referer( $referrer );

		$sendback = isset( $_GET['sendback'] ) ? sanitize_text_field( $_GET['sendback'] ) : admin_url( 'upload.php' );

		$args = array(
			'acs-action' => $action,
		);

		$result = $this->maybe_do_provider_action( $action, $ids, $doing_bulk_action );

		if ( ! $result ) {
			unset( $args['acs-action'] );
			$result = array();
		}

		// If we're uploading a single file, add the id to the `$args` array.
		if ( 'copy' === $action && 1 === $id_count && ! empty( $result ) && 1 === ( $result['count'] + $result['errors'] ) ) {
			$args['acs_id'] = array_shift( $ids );
		}

		$args = array_merge( $args, $result );
		$url  = add_query_arg( $args, $sendback );

		wp_redirect( esc_url_raw( $url ) );
		exit();

	}

	/**
	 * Wrapper for media actions
	 *
	 * @param string $action             type of media action, copy, remove, download, remove_local
	 * @param array  $ids                attachment IDs
	 * @param bool   $doing_bulk_action  flag for multiple attachments, if true then we need to
	 *                                   perform a check for each attachment
	 *
	 * @return bool|array on success array with success count and error count
	 * @throws Exception
	 */
	function maybe_do_provider_action( $action, $ids, $doing_bulk_action ) {

		switch ( $action ) {
			case 'copy':
				$result = $this->maybe_upload_attachments( $ids, $doing_bulk_action );
				break;
		}

		return $result;
	}

	/**
	 * Display notices after processing media actions
	 *
	 * @return void
	 */
	function maybe_display_media_action_message() {

		global $pagenow;

		if ( ! in_array( $pagenow, array( 'upload.php', 'post.php' ) ) ) {
			return;
		}

		if ( isset( $_GET['acs-action'] ) && isset( $_GET['errors'] ) && isset( $_GET['count'] ) ) {
			$action 	  = sanitize_key( $_GET['acs-action'] ); // input var okay
			$error_count  = absint( $_GET['errors'] ); // input var okay
			$count        = absint( $_GET['count'] ); // input var okay
			
			echo $this->get_media_action_result_message( $action, $count, $error_count );
		}
	}

	/**
	 * Get the result message after an S3 action has been performed
	 *
	 * @param string $action      type of S3 action
	 * @param int    $count       count of successful processes
	 * @param int    $error_count count of errors
	 *
	 * @return bool|string
	 */
	function get_media_action_result_message( $action, $count = 0, $error_count = 0 ) {

		$class = 'updated';
		$type  = 'success';

		if ( 0 === $count && 0 === $error_count ) {
			// don't show any message if no attachments processed
			// i.e. they haven't met the checks for bulk actions
			return false;
		}

		if ( $error_count > 0 ) {
			$type = $class = 'error';

			// We have processed some successfully.
			if ( $count > 0 ) {
				$type = 'partial';
			}
		}

		$message = $this->get_message( $action, $type );

		// can't find a relevant message, abort
		if ( ! $message ) {
			return false;
		}

		$id = $this->filter_input( 'acs_id', INPUT_GET, FILTER_VALIDATE_INT );

		// If we're uploading a single item, add an edit link.
		if ( 1 === ( $count + $error_count ) && ! empty( $id ) ) {
			$url = esc_url( get_edit_post_link( $id ) );

			// Only add the link if we have a URL.
			if ( ! empty( $url ) ) {
				$text    = esc_html__( 'Edit attachment', 'arvancloud-object-storage' );
				$message .= sprintf( ' <a href="%1$s">%2$s</a>', $url, $text );
			}
		}

		$message = sprintf( '<div class="notice acs-notice %s is-dismissible"><p>%s</p></div>', $class, $message );

		return $message;

	}

	/**
	 * Retrieve all the media action related notice messages
	 *
	 * @return array
	 */
	function get_messages() {
		$messages = array(
			'copy'         => array(
				'success' => __( 'Media successfully copied to bucket.', 'arvancloud-object-storage' ),
				'partial' => __( 'Media copied to bucket with some errors.', 'arvancloud-object-storage' ),
				'error'   => __( 'There were errors when copying the media to bucket.', 'arvancloud-object-storage' ),
			)
		);

		return $messages;
	}

	/**
	 * Get a specific media action notice message
	 *
	 * @param string $action type of action, e.g. copy, remove, download
	 * @param string $type   if the action has resulted in success, error, partial (errors)
	 *
	 * @return string|bool
	 */
	function get_message( $action = 'copy', $type = 'success' ) {

		$messages = $this->get_messages();

		if ( isset( $messages[ $action ][ $type ] ) ) {
			return $messages[ $action ][ $type ];
		}

		return false;

	}

	/**
	 * Helper function for filtering super globals. Easily testable.
	 *
	 * @param string $variable
	 * @param int    $type
	 * @param int    $filter
	 * @param mixed  $options
	 *
	 * @return mixed
	 */
	public function filter_input( $variable, $type = INPUT_GET, $filter = FILTER_DEFAULT, $options = array() ) {
		return filter_input( $type, $variable, $filter, $options );
	}

	/**
	 * Wrapper for uploading multiple attachments to S3
	 *
	 * @param array $post_ids            attachment IDs
	 * @param bool  $doing_bulk_action   flag for multiple attachments, if true then we need to
	 *                                   perform a check for each attachment to make sure the
	 *                                   file exists locally before uploading to S3
	 *
	 * @return array|WP_Error
	 * @throws Exception
	 */
	function maybe_upload_attachments( $post_ids, $doing_bulk_action = false ) {

		$error_count    = 0;
		$uploaded_count = 0;

		foreach ( $post_ids as $post_id ) {
			$file = wp_get_attachment_metadata($post_id);

			if ( $doing_bulk_action ) {
				// if the file doesn't exist locally we can't copy
				if ( ! file_exists( get_attached_file($post_id) ) ) {
					continue;
				}
			}

			if( wp_attachment_is_image( $post_id ) ) {
				$result = $this->upload_image_to_storage( $file );
			} else {
				$result = $this->upload_media_to_storage( $post_id );
			}

			if ( is_wp_error( $result ) ) {
				$error_count++;
				continue;
			}

			$uploaded_count++;
		}

		$result = array(
			'errors' => $error_count,
			'count'  => $uploaded_count,
		);

		return $result;

	}

	/**
	 * add_edit_attachment_metabox
	 *
	 * @param mixed $post 
	 * @return void
	 */
	public function add_edit_attachment_metabox( $post ) {

		if( !$this->is_attachment_served_by_storage( $_GET['post'], true ) ) {
			add_meta_box(
				'arvancloud-storage-metabox',
				__( 'ArvanCloud Storage', 'arvancloud-object-storage' ),
				array( $this, 'render_edit_attachment_metabox' ),
				'attachment',
				'side',
				'default'
			);
		}

    }

	/**
	 * render_edit_attachment_metabox
	 *
	 * @return void
	 */
	public function render_edit_attachment_metabox() {

		global $post;
	
        $actions = $this->add_media_row_actions( array(), $post );

		foreach( $actions as $action ) {
			echo wp_kses_post( $action );
		}
		
    }

}
