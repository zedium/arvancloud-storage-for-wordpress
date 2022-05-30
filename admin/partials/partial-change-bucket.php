<?php 
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if( $acs_settings_option = get_storage_settings() ) {
    $config_type         = $acs_settings_option['config-type'];
    $snippet_defined     = defined( 'ARVANCLOUD_STORAGE_SETTINGS' );
    $db_defined          = $config_type == 'db' && ! empty( $acs_settings_option['access-key'] ) && ! empty( $acs_settings_option['secret-key'] ) && ! empty( $acs_settings_option['endpoint-url'] ) ? true : false;
    $bucket_selected     = get_bucket_name();
    $acs_settings	     = get_option( 'acs_settings' );

}

?>
<a class="acs-back-btn" href="<?php echo admin_url( '/admin.php?page=wp-arvancloud-storage&action=change-access-option' ) ?>"><?php echo __( "«&nbsp;Back", 'arvancloud-object-storage' ) ?></a>
<h3><?php echo __( "Select bucket", 'arvancloud-object-storage' ) ?></h3>

<form class="arvancloud-storage-select-bucket-form" method="post">
    <ul class="acs-bucket-list">
        <?php
        try {
            require_once ACS_PLUGIN_ROOT . 'includes/wp-arvancloud-storage-s3client.php';

            $list_response = $client->listBuckets();
            $buckets       = $list_response[ 'Buckets' ];  

            if( count($buckets) == 0 ) {
                echo __( "You have not any bucket in ArvanCloud, please create a bucket in ArvanCloud storage panel then refresh this page!", 'arvancloud-object-storage' );
            } else {
                $selected_bucket = get_option( 'arvan-cloud-storage-bucket-name', false );

                foreach ( $buckets as $bucket ) {
                    $selected = $selected_bucket == $bucket['Name'] ? 'checked="checked"' : '';
                    echo '<label for="' . esc_attr( $bucket['Name'] ) . '"><input id="' . esc_attr( $bucket['Name'] ) . '" name="acs-bucket-select-name" type="radio" class="no-compare" value="' . esc_attr( $bucket['Name'] ) . '"' . esc_attr( $selected ) . '>'. esc_html( $bucket['Name'] ) .'</label>';
                }
            }
        } catch ( Exception $e ) {
            $error = $e->getMessage();
            $url   = admin_url( "?page=wp-arvancloud-storage&action=change-access-option&error_message=" . urlencode( $error ) );

            echo '<div class="notice notice-error is-dismissible"><p>'. esc_html( $error ) .'</p></div>';
            echo '<script>window.location="' . esc_attr( $url ) . '"</script>';
        }
        ?>
        <label for="create_new_bucket"><input id="create_new_bucket" name="acs-bucket-select-name" type="radio" class="no-compare" value="create_new_bucket"><?php _e('Create New Bucket', 'arvancloud-object-storage') ?></label>
    </ul>
    <p class="bucket-actions actions select">
        <button id="acs-bucket-select-save" type="submit" class="bucket-action-save button button-primary" <?php echo isset( $e ) ? 'disabled' : '' ?>><?php _e( 'Save Selected Bucket', 'arvancloud-object-storage' ); ?></button>
    </p>
</form>
