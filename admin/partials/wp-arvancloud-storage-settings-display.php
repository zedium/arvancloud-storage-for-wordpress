<div class="wrap">
    <?php
    if( $acs_settings_option = get_storage_settings() ) {
        $config_type         = $acs_settings_option['config-type'];
        $snippet_defined     = defined( 'ARVANCLOUD_STORAGE_SETTINGS' );
        $db_defined          = $config_type == 'db' && isset( $acs_settings_option['access-key'] ) && isset( $acs_settings_option['secret-key'] ) && isset( $acs_settings_option['endpoint-url'] ) ? true : false;
        $bucket_selected     = get_bucket_name();
    }
    ?>

    <H1><?php echo __( 'Settings', 'wp-arvancloud-storage' ) ?></H1>
    <hr>

    <?php
    if( ( ! $db_defined && ! $snippet_defined ) || ( isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] == 'change-access-option' ) ) {
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
                                        <input type="text" name="secret-key" value="<?php echo $config_type == 'db' ? __( "-- not shown --", 'wp-arvancloud-storage' ) : '' ?>" autocomplete="off">
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
                        $selected_bucket = get_option( 'arvan-cloud-storage-bucket-name', true );
    
                        foreach ( $buckets as $bucket ) {
                            $selected      = $selected_bucket == $bucket['Name'] ? 'selected' : '';
                            $selected_icon = $selected_bucket == $bucket['Name'] ? '<span class="dashicons dashicons-yes-alt"></span>' : '';
                            echo '<li class="'. $selected .'">' . $selected_icon . ' ' . $bucket['Name'] . '</li>';
                        }
                    }
                } catch ( Exception $e ) {
                    echo '<div class="notice notice-error is-dismissible"><p>'. $e->getMessage() .'</p></div>'; 
                }
                ?>
            </ul>
            <?php if( ! isset( $e ) ) {
               echo '<input id="acs-bucket-select-name" name="acs-bucket-select-name" type="hidden" class="no-compare" name="bucket_name" value="">';
            }
            ?>
            <p class="bucket-actions actions select">
                <button id="acs-bucket-select-save" type="submit" class="bucket-action-save button button-primary" <?php echo isset( $e ) ? 'disabled' : '' ?>><?php _e( 'Save Selected Bucket', 'wp-arvancloud-storage' ); ?></button>
                <span><a href="#" class="acs-bucket-action-refresh"><i class="dashicons dashicons-update"></i> <?php _e( 'Refresh', 'wp-arvancloud-storage' ); ?></a></span>
            </p>
        </form>

        <?php
    } else {
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
                        <th scope="row"><?php echo __( "Path", 'wp-arvancloud-storage' ) ?></th>
                        <td>
                            <input id="bucket-path" type="text" name="bucket-path" value="" class="regular-text">
                            <p class="description" id="tagline-description"><?php echo __( 'By default the path is the same as your local WordPress files.', 'wp-arvancloud-storage' ) ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo __( "Keep local files", 'wp-arvancloud-storage' ) ?></th>
                        <td>
                            <input id="keep-local-files" type="checkbox" name="keep-local-files" value="1" class="regular-text">
                            <p class="description" id="tagline-description"><?php echo __( 'Keep local files after uploading them to storage.', 'wp-arvancloud-storage' ) ?></p>
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