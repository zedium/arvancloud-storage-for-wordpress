<?php
if( $acs_settings_option = get_option( 'arvan-cloud-storage-settings', true ) ) {
    $acs_settings_option = unserialize( $acs_settings_option );
    $config_type         = $acs_settings_option['config-type'];
    $snippet_defined     = defined( 'ARVANCLOUD_STORAGE_SETTINGS' );
}
?>

<H1><?php echo __( ACS_NAME . ' Settings', ACS_TEXTDOMAIN ) ?></H1>
<hr>
<h3><?php echo __( 'Configure Cloud Storage', ACS_TEXTDOMAIN ) ?></h3>

<form class="arvancloud-storage-config-form" method="post">
    <section class="accordion-container">
        <div class="accordion-box">
            <?php
            if ( $snippet_defined ) {
                echo '<span class="acs-defined-in-config">' . __( 'defined in wp-config.php', ACS_TEXTDOMAIN ) . '</span>';
            }
            ?>
            <input id="config-type-snippet" name="config-type" value="snippet" type="radio" <?php echo $config_type == 'snippet' ? 'checked' : '' ?> />
            <label for="config-type-snippet"><?php echo __( "Define access keys in wp-config.php", ACS_TEXTDOMAIN ) ?></label>
            <section class="accordion">
                <?php 
                if ( $snippet_defined ) {
                    _e( "You've defined your access keys in your wp-config.php. To select a different option here, simply comment out or remove the Access Keys defined in your wp-config.php.", ACS_TEXTDOMAIN );
                    
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
                    _e( "Copy the following snippet <strong>near the top</strong> of your wp-config.php and replace the stars with the keys.", ACS_TEXTDOMAIN ) 
                
                ?>
                <textarea rows="5" class="as3cf-define-snippet code clear" readonly="">
define( 'ARVANCLOUD_STORAGE_SETTINGS', serialize( array(
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
            <label for="config-type-db"><?php echo __( "I understand the risks but I'd like to store access keys in the database anyway (not recommended)", ACS_TEXTDOMAIN ) ?></label>
            <section class="accordion">
                <?php echo __( "Storing your access keys in the database is less secure than the options above, but if you're ok with that, go ahead and enter your keys in the form below.", ACS_TEXTDOMAIN ) ; ?>
                <table class="table-form">
                    <tbody><tr valign="top">
                        <th scope="row"><?php echo __( "Access Key", ACS_TEXTDOMAIN ) ?></th>
                        <td>
                            <div class="accordion-field-wrap">
                                <input type="text" name="access-key" value="<?php echo $config_type == 'db' ? $acs_settings_option['access-key'] : '' ?>" autocomplete="off">
                            </div>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php echo __( "Secret Key", ACS_TEXTDOMAIN ) ?></th>
                        <td>
                            <div class="accordion-field-wrap">
                                <input type="text" name="secret-key" value="<?php echo $config_type == 'db' ? __( "-- not shown --", ACS_TEXTDOMAIN ) : '' ?>" autocomplete="off">
                            </div>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php echo __( "Endpoint URL", ACS_TEXTDOMAIN ) ?></th>
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
        <p><button type="submit" class="button button-primary" name="config-cloud-storage" value="1"><?php echo __( "Next", ACS_TEXTDOMAIN ) ?></button></p>
    </section>
</form>