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
<div class="acs-bucket-list">
    <h4> <?php echo __( 'URL PREVIEW', 'arvancloud-object-storage' ) ?> </h4>
    <span><?php echo get_storage_url() ?></span>
</div>

<form method="post">
    <table class="form-table">
        <tbody>
            <tr>
                <th><span><?php echo __( 'Bucket: ', 'arvancloud-object-storage' ) ?></span></th>
                <td><span><?php echo get_bucket_name() ?></span> <a class="acs-change-btn" href="<?php echo admin_url( '/admin.php?page=wp-arvancloud-storage&action=change-bucket' ) ?>"><?php echo __( "Change Bucket", 'arvancloud-object-storage' ) ?></a> <a class="acs-change-btn" href="<?php echo admin_url( '/admin.php?page=wp-arvancloud-storage&action=create-bucket' ) ?>"><?php echo __( "Create Bucket", 'arvancloud-object-storage' ) ?></a></td>
            </tr>
            <tr>
                <th scope="row"><?php echo __( "Keep local files", 'arvancloud-object-storage' ) ?></th>
                <td>
                    <input id="keep-local-files" type="checkbox" name="keep-local-files" value="1" <?php echo ( !isset($acs_settings['keep-local-files']) || $acs_settings['keep-local-files']) ? 'checked' : '' ?> class="regular-text">
                    <label for="keep-local-files"><?php echo __( 'Keep local files after uploading them to storage.', 'arvancloud-object-storage' ) ?></label>
                </td>
            </tr>
        </tbody>
    </table>

    <p class="submit"><input type="submit" name="acs-settings" id="submit" class="button button-primary" value="<?php echo __( 'Save Changes', 'arvancloud-object-storage' ) ?>"></p>
</form>
