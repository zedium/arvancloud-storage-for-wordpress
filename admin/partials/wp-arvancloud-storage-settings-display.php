<div class="wrap">
    <?php
    if (isset( $_GET['system-info'] ) && $_GET['system-info'] == true) {
        require_once( ACS_PLUGIN_ROOT . 'admin/partials/partial-system-info.php' );
        return;
    } else {


        $config_type     = false;
        $snippet_defined = false;
        $db_defined      = false;
        $bucket_selected = false;
        $acs_settings    = false;
    
        if( $acs_settings_option = get_storage_settings() ) {
            $config_type         = $acs_settings_option['config-type'];
            $snippet_defined     = defined( 'ARVANCLOUD_STORAGE_SETTINGS' );
            $db_defined          = $config_type == 'db' && ! empty( $acs_settings_option['access-key'] ) && ! empty( $acs_settings_option['secret-key'] ) && ! empty( $acs_settings_option['endpoint-url'] ) ? true : false;
            $bucket_selected     = get_bucket_name();
            $acs_settings	     = get_option( 'acs_settings' );
    
        }

        if ( isset($_GET['notice']) && sanitize_text_field( $_GET['notice'] ) == 'bucket-created' ) {
            echo '<div class="notice notice-success is-dismissible"><p>' . __( 'Bucket created successfully', 'arvancloud-object-storage' ) . '</p></div>';
        }

        ?>
            <div class="ar-heading without-btn">
                <h1><?php echo __( 'Settings', 'arvancloud-object-storage' ) ?></h1>
            </div>
            <hr>
        <?php
    }

    if( ( ! $db_defined && ! $snippet_defined ) || ( isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] == 'change-access-option' ) ) {

        // change access option
        require_once( ACS_PLUGIN_ROOT . 'admin/partials/partial-set-api-key.php' );

    } elseif( ! $bucket_selected || ( isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] == 'change-bucket' ) ) {

        // change bucket
        require_once( ACS_PLUGIN_ROOT . 'admin/partials/partial-change-bucket.php' );

    } else if (isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] == 'create-bucket') {

        // create bucket
        require_once( ACS_PLUGIN_ROOT . 'admin/partials/partial-create-bucket.php' );

    } else {
        // Bucket List
        if( isset( $_GET['notice'] ) && $_GET['notice'] == 'selected-bucket-saved' ) {
            echo '<div class="notice notice-success is-dismissible">
                <p>'. esc_html__( "Selected bucket saved.", 'arvancloud-object-storage' ) .'</p>
            </div>';
        }
        require_once( ACS_PLUGIN_ROOT . 'admin/partials/partial-bucket-list.php' );
    }

    require_once( ACS_PLUGIN_ROOT . 'admin/partials/wp-arvancloud-storage-footer.php' );
    ?>
</div>