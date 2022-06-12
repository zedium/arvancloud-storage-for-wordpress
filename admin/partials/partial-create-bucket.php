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
<a class="acs-back-btn" href="<?php echo admin_url( '/admin.php?page=wp-arvancloud-storage&action=change-bucket' ) ?>"><?php echo __( "«&nbsp;Back", 'arvancloud-object-storage' ) ?></a>
<h3><?php echo __( "Create bucket", 'arvancloud-object-storage' ) ?></h3>

<form class="arvancloud-storage-select-bucket-form" method="post">
    <div style="display: flex;flex-direction: column;max-width: 340px;">
        <label for="acs-new-bucket-name"><?php _e( 'Bucket name', 'arvancloud-object-storage' ) ?></label>
        <input type="text" name="acs-new-bucket-name" id="acs-new-bucket-name" placeholder="<?php _e( 'The name should be unique', 'arvancloud-object-storage' ) ?>" value=""/>
        <p class="bucket-name-error" style="display: none;"></p>
        <div style="margin-top: 16px;">
            <input type="checkbox" name="acs-new-bucket-public" id="acs-new-bucket-public" value="0" />
            <label for="acs-new-bucket-public"><?php _e( 'Public read access', 'arvancloud-object-storage' ) ?></label>
        </div>
        <input type="hidden" name="bucket_nonce" value="<?php echo wp_create_nonce( 'create-bucket' ) ?>" />

    </div>
    <p class="bucket-actions actions select">
        <button id="acs-bucket-select-save" type="submit" class="bucket-action-save button button-primary" onclick="validate_bucket_name_submit(); return false;" <?php echo isset( $e ) ? 'disabled' : '' ?>><?php _e( 'Create', 'arvancloud-object-storage' ); ?></button>
    </p>
</form>
