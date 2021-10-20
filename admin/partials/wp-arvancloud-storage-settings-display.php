<div class="wrap">
    <?php
    if( $acs_settings_option = get_storage_settings() ) {
        $config_type         = $acs_settings_option['config-type'];
        $snippet_defined     = defined( 'ARVANCLOUD_STORAGE_SETTINGS' );
        $db_defined          = $config_type == 'db' && ! empty( $acs_settings_option['access-key'] ) && ! empty( $acs_settings_option['secret-key'] ) && ! empty( $acs_settings_option['endpoint-url'] ) ? true : false;
        $bucket_selected     = get_bucket_name();
        $acs_settings	     = get_option( 'acs_settings', true );
    }
    ?>

    <H1><?php echo __( 'Settings', 'wp-arvancloud-storage' ) ?></H1>
    <hr>

    <?php
    if( ( ! $db_defined && ! $snippet_defined ) || ( isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] == 'change-access-option' ) ) {
        if( isset( $_GET['error_message'] ) ) {
            echo '<div class="notice notice-error is-dismissible"><p>'. $_GET['error_message'] .'</p></div>';
        }
        ?>
        <h3><?php echo __( 'Configure Cloud Storage', 'wp-arvancloud-storage' ) ?></h3>

        <form class="arvancloud-storage-config-form" method="post" action="<?php echo admin_url( '/admin.php?page=wp-arvancloud-storage' ) ?>">
            <section class="accordion-container">
                <div class="accordion-box">
                    <?php
                    if ( $snippet_defined ) {
                        echo '<span class="acs-defined-in-config">' . __( 'defined in wp-config.php', 'wp-arvancloud-storage' ) . '</span>';
                    }
                    ?>
                    <input id="config-type-snippet" name="config-type" value="snippet" type="radio" <?php echo $config_type == 'snippet' ? 'checked' : '' ?> />
                    <label for="config-type-snippet"><?php echo __( "Define access keys in wp-config.php", 'wp-arvancloud-storage' ) ?></label>
                    <section class="accordion">
                        <?php 
                        if ( $snippet_defined ) {
                            _e( "You've defined your access keys in your wp-config.php. To select a different option here, simply comment out or remove the Access Keys defined in your wp-config.php.", 'wp-arvancloud-storage' );
                            
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
                            _e( "Copy the following snippet <strong>near the top</strong> of your wp-config.php and replace the stars with the keys.", 'wp-arvancloud-storage' ) 
                        
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
                    <label for="config-type-db"><?php echo __( "I understand the risks but I'd like to store access keys in the database anyway (not recommended)", 'wp-arvancloud-storage' ) ?></label>
                    <section class="accordion">
                        <?php echo __( "Storing your access keys in the database is less secure than the options above, but if you're ok with that, go ahead and enter your keys in the form below.", 'wp-arvancloud-storage' ) ; ?>
                        <table class="table-form">
                            <tbody><tr valign="top">
                                <th scope="row"><?php echo __( "Access Key", 'wp-arvancloud-storage' ) ?></th>
                                <td>
                                    <div class="accordion-field-wrap">
                                        <input type="text" name="access-key" value="<?php echo $config_type == 'db' ? $acs_settings_option['access-key'] : '' ?>" autocomplete="off">
                                    </div>
                                </td>
                            </tr>

                            <tr valign="top">
                                <th scope="row"><?php echo __( "Secret Key", 'wp-arvancloud-storage' ) ?></th>
                                <td>
                                    <div class="accordion-field-wrap">
                                        <input type="password" id="secret-key" name="secret-key" value="<?php echo $config_type == 'db' && $acs_settings_option['secret-key'] != null ? __( "-- not shown --", 'wp-arvancloud-storage' ) : '' ?>" autocomplete="off">
                                        <?php if( $acs_settings_option['secret-key'] == null ): ?>
                                            <span toggle="#secret-key" class="dashicons dashicons-visibility field-icon toggle-password"></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>

                            <tr valign="top">
                                <th scope="row"><?php echo __( "Endpoint URL", 'wp-arvancloud-storage' ) ?></th>
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
                <p><button type="submit" class="button button-primary" name="config-cloud-storage" value="1"><?php echo __( "Next", 'wp-arvancloud-storage' ) ?></button></p>
            </section>
    </form>
    <?php
    } elseif( ! $bucket_selected || ( isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] == 'change-bucket' ) ) {
        ?>
        <a class="acs-back-btn" href="<?php echo admin_url( '/admin.php?page=wp-arvancloud-storage&action=change-access-option' ) ?>"><?php echo __( "«&nbsp;Back", 'wp-arvancloud-storage' ) ?></a>
        <h3><?php echo __( "Select bucket", 'wp-arvancloud-storage' ) ?></h3>

        <form class="arvancloud-storage-select-bucket-form" method="post">
            <ul class="acs-bucket-list">
                <?php
                try {
                    require_once ACS_PLUGIN_ROOT . 'includes/wp-arvancloud-storage-s3client.php';

                    $list_response = $client->listBuckets();
                    $buckets       = $list_response[ 'Buckets' ];  

                    if( count($buckets) == 0 ) {
                        echo __( "You have not any bucket in ArvanCloud, please create a bucket in ArvanCloud storage panel then refresh this page!", 'wp-arvancloud-storage' );
                    } else {
                        $selected_bucket = get_option( 'arvan-cloud-storage-bucket-name', false );
    
                        foreach ( $buckets as $bucket ) {
                            $selected = $selected_bucket == $bucket['Name'] ? 'checked="checked"' : '';
                            echo '<label for="' . $bucket['Name'] . '"><input id="' . $bucket['Name'] . '" name="acs-bucket-select-name" type="radio" class="no-compare" value="' . $bucket['Name'] . '"' . $selected . '>'. $bucket['Name'] .'</label>';
                        }
                    }
                } catch ( Exception $e ) {
                    $error = $e->getMessage();
                    $url   = admin_url( "?page=wp-arvancloud-storage&action=change-access-option&error_message=" . urlencode( $error ) );

                    echo '<div class="notice notice-error is-dismissible"><p>'. $error .'</p></div>';
                    echo "<script>window.location='$url';</script>";
                }
                ?>
            </ul>
            <p class="bucket-actions actions select">
                <button id="acs-bucket-select-save" type="submit" class="bucket-action-save button button-primary" <?php echo isset( $e ) ? 'disabled' : '' ?>><?php _e( 'Save Selected Bucket', 'wp-arvancloud-storage' ); ?></button>
            </p>
        </form>

        <?php
    } else {
        if( isset( $_GET['notice'] ) && $_GET['notice'] == 'selected-bucket-saved' ) {
            echo '<div class="notice notice-success is-dismissible">
                        <p>'. esc_html__( "Selected bucket saved.", 'wp-arvancloud-storage' ) .'</p>
                    </div>';
        }
        ?>
        <div class="acs-bucket-list">
            <h4> <?php echo __( 'URL PREVIEW', 'wp-arvancloud-storage' ) ?> </h4>
            <span><?php echo get_storage_url() ?></span>
        </div>
        
        <form method="post">
            <table class="form-table">
                <tbody>
                    <tr>
                        <th><span><?php echo __( 'Bucket: ', 'wp-arvancloud-storage' ) ?></span></th>
                        <td><span><?php echo get_bucket_name() ?></span> <a class="acs-change-btn" href="<?php echo admin_url( '/admin.php?page=wp-arvancloud-storage&action=change-bucket' ) ?>"><?php echo __( "Change Bucket", 'wp-arvancloud-storage' ) ?></a></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo __( "Keep local files", 'wp-arvancloud-storage' ) ?></th>
                        <td>
                            <input id="keep-local-files" type="checkbox" name="keep-local-files" value="1" <?php echo $acs_settings['keep-local-files'] ? 'checked' : '' ?> class="regular-text">
                            <label for="keep-local-files"><?php echo __( 'Keep local files after uploading them to storage.', 'wp-arvancloud-storage' ) ?></label>
                        </td>
                    </tr>
                </tbody>
            </table>

            <p class="submit"><input type="submit" name="acs-settings" id="submit" class="button button-primary" value="<?php echo __( 'Save Changes', 'wp-arvancloud-storage' ) ?>"></p>
        </form>
        <?php
    }

    require_once( ACS_PLUGIN_ROOT . 'admin/partials/wp-arvancloud-storage-footer.php' );
    ?>
</div>