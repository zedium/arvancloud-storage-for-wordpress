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

if( isset( $_GET['error_message'] ) ) {
    echo '<div class="notice notice-error is-dismissible"><p>'. esc_html( $_GET['error_message'] ) .'</p></div>';
}
?>
<h3><?php echo __( 'Configure Cloud Storage', 'arvancloud-object-storage' ) ?></h3>

<form class="arvancloud-storage-config-form" method="post" action="<?php echo admin_url( '/admin.php?page=wp-arvancloud-storage' ) ?>">
    <section class="accordion-container">
        <div class="accordion-box">
            <?php
            if ( $snippet_defined ) {
                echo '<span class="acs-defined-in-config">' . __( 'defined in wp-config.php', 'arvancloud-object-storage' ) . '</span>';
            }
            ?>
            <input id="config-type-snippet" name="config-type" value="snippet" type="radio" <?php echo $config_type == 'snippet' ? 'checked' : '' ?> />
            <label for="config-type-snippet"><?php echo __( "Define access keys in wp-config.php", 'arvancloud-object-storage' ) ?></label>
            <section class="accordion">
                <?php 
                if ( $snippet_defined ) {
                    _e( "You've defined your access keys in your wp-config.php. To select a different option here, simply comment out or remove the Access Keys defined in your wp-config.php.", 'arvancloud-object-storage' );
                    
                    if ( $config_type == 'snippet' && ! $snippet_defined ) {
                        ?>
                        <div class="notice-error notice">
                            <p>
                                <?php _e( 'Please check your wp-config.php file as it looks like one of your access key defines is missing or incorrect.', 'amazon-s3-and-cloudfront' ) ?>
                            </p>
                        </div>
                        <?php
                    }
                } else {
                    _e( "Copy the following snippet <strong>near the top</strong> of your wp-config.php and replace the stars with the keys.", 'arvancloud-object-storage' ) 
                
                ?>
                <textarea rows="5" class="as3cf-define-snippet code clear" readonly="">
define( 'ARVANCLOUD_STORAGE_SETTINGS', json_encode( array(
'access-key' =&gt; '********************',
'secret-key' =&gt; '**************************************',
'endpoint-url' =&gt; '*********************',
) ) );
                </textarea>
                <?php
                }
                ?>
            </section>
        </div>
        <div class="accordion-box">
            <input id="config-type-db" name="config-type" value="db" type="radio" <?php echo $config_type == 'db' ? 'checked' : '' ?> <?php echo $snippet_defined ? 'disabled="disabled"' : '' ?> />
            <label for="config-type-db"><?php echo __( "I understand the risks but I'd like to store access keys in the database anyway (not recommended)", 'arvancloud-object-storage' ) ?></label>
            <section class="accordion">
                <?php echo __( "Storing your access keys in the database is less secure than the options above, but if you're ok with that, go ahead and enter your keys in the form below.", 'arvancloud-object-storage' ) ; ?>
                <table class="table-form">
                    <tbody><tr valign="top">
                        <th scope="row"><?php echo __( "Access Key", 'arvancloud-object-storage' ) ?></th>
                        <td>
                            <div class="accordion-field-wrap">
                                <input type="text" name="access-key" value="<?php echo $config_type == 'db' ? $acs_settings_option['access-key'] : '' ?>" autocomplete="off">
                            </div>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php echo __( "Secret Key", 'arvancloud-object-storage' ) ?></th>
                        <td>
                            <div class="accordion-field-wrap">
                                <input type="password" id="secret-key" name="secret-key" value="<?php echo $config_type == 'db' && $acs_settings_option['secret-key'] != null ? __( "-- not shown --", 'arvancloud-object-storage' ) : '' ?>" autocomplete="off">
                                <?php if( $config_type == 'db' && $acs_settings_option['secret-key'] == null ): ?>
                                    <span toggle="#secret-key" class="dashicons dashicons-visibility field-icon toggle-password"></span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php echo __( "Endpoint URL", 'arvancloud-object-storage' ) ?></th>
                        <td>
                            <div class="accordion-field-wrap">
                                <input type="text" name="endpoint-url" value="<?php echo $config_type == 'db' ? $acs_settings_option['endpoint-url'] : '' ?>" autocomplete="off">
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </section>
        </div>
        <p><button type="submit" class="button button-primary" name="config-cloud-storage" value="1"><?php echo __( "Next", 'arvancloud-object-storage' ) ?></button></p>
    </section>
</form>