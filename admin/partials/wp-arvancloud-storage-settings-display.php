<div class="wrap">
    <?php
    if( $acs_settings_option = get_storage_settings() ) {
        $config_type         = $acs_settings_option['config-type'];
        $snippet_defined     = defined( 'ARVANCLOUD_STORAGE_SETTINGS' );
        $db_defined          = $config_type == 'db' && isset( $acs_settings_option['access-key'] ) && isset( $acs_settings_option['secret-key'] ) && isset( $acs_settings_option['endpoint-url'] ) ? true : false;
        $bucket_selected     = get_bucket_name();
    }
    ?>

    <H1><?php echo __( ACS_NAME . ' Settings', 'wp-arvancloud-storage' ) ?></H1>
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
        <a href="<?php echo admin_url( '/admin.php?page=wp-arvancloud-storage&action=change-access-option' ) ?>"><?php echo __( "«&nbsp;Back", 'wp-arvancloud-storage' ) ?></a>
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
        <div class="acs-bucket-list" style="text-align: center; margin-bottom: 20px">
            <h4> <?php echo __( 'URL PREVIEW:', 'wp-arvancloud-storage' ) ?> </h4>
            <span><?php echo get_storage_url() ?></span>
        </div>
        <span style="font-weight: bold"><?php echo __( 'Bucket: ', 'wp-arvancloud-storage' ) ?></span> <span><?php echo get_bucket_name() ?></span>
        <a href="<?php echo admin_url( '/admin.php?page=wp-arvancloud-storage&action=change-bucket' ) ?>"><?php echo __( "Change", 'wp-arvancloud-storage' ) ?></a>
        
        <form method="post">
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row"><?php echo __( "Path", 'wp-arvancloud-storage' ) ?></th>
                        <td>
                            <input id="bucket-path" type="text" name="bucket-path" value="" class="regular-text">
                            <p class="description" id="tagline-description"><?php echo __( 'By default the path is the same as your local WordPress files.', 'wp-arvancloud-storage' ) ?></p>
                        </td>
                    </tr>

                </tbody>
            </table>

            <p class="submit"><input type="submit" name="acs-settings" id="submit" class="button button-primary" value="<?php echo __( 'Save Changes', 'wp-arvancloud-storage' ) ?>"></p>
        </form>
        <?php
    }
    ?>
    <hr>
    <div class="acs-footer">
        <div class="acs-footer-links">        
            <ul>
                <li><a href="https://npanel.arvancloud.com/storage/plans" target="_blank"><?php echo __( 'Storage Plans', 'wp-arvancloud-storage' ) ?> <span class="dashicons dashicons-external"></span></a></li>
                <li><a href="https://forum.arvancloud.com/" target="_blank"><?php echo __( 'Support', 'wp-arvancloud-storage' ) ?> <span class="dashicons dashicons-external"></span></a></li>
            </ul>
        </div>
        <div class="acs-footer-social">
            <div class="footer__social-icons">
                <a class="footer__social-icons__link footer__social-icons--telegram" href="https://t.me/arvancloud" target="_blank" rel="noopener noreferrer">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 34.59 29.03"><g><g><path d="M34.05,2.17,26.35,27.4a1.4,1.4,0,0,1-2.21.85L12.93,19.87,12.32,26a.56.56,0,0,1-1.08.12L8.3,17.57,1.47,15.35a1.4,1.4,0,0,1-.08-2.63L32.17.6A1.4,1.4,0,0,1,34.05,2.17Z" style="fill:none;stroke:#7ce8dd;stroke-linecap:round;stroke-linejoin:round;stroke-width:1.00622773842599px"></path><polyline points="8.3 17.57 27.96 5.6 12.93 19.87" style="fill:none;stroke:#7ce8dd;stroke-linecap:round;stroke-linejoin:round;stroke-width:1.00622773842599px"></polyline><line x1="16.49" y1="22.53" x2="12.07" y2="26.37" style="fill:none;stroke:#7ce8dd;stroke-linecap:round;stroke-linejoin:round;stroke-width:1.00622773842599px"></line></g></g></svg>                    
                </a>
                <a class="footer__social-icons__link footer__social-icons--linkedin" href="https://www.linkedin.com/company/arvancloud-fa" target="_blank" rel="noopener noreferrer">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 29.41 29.85"><g><g><rect x="0.97" y="10.18" width="6.53" height="19.16" style="fill:none;stroke:#7ce8dd;stroke-linecap:round;stroke-linejoin:round;stroke-width:1.00622773842599px"></rect><path d="M28.9,16.17V29.34H22.38V17.53a2.43,2.43,0,0,0-1.2-2.13A3.09,3.09,0,0,0,19.6,15a2.79,2.79,0,0,0-1.29.34,3.62,3.62,0,0,0-1.91,3.26V29.34H9.87V10.18H16.4v2.05A8,8,0,0,1,21.09,10a11.27,11.27,0,0,1,3,.28h0l.24.07a8,8,0,0,1,.78.25l.23.1.22.09.24.13a5.64,5.64,0,0,1,.7.46l0,0A6,6,0,0,1,28.9,16.17Z" style="fill:none;stroke:#7ce8dd;stroke-linecap:round;stroke-linejoin:round;stroke-width:1.00622773842599px"></path><circle cx="4.42" cy="4.42" r="3.92" style="fill:none;stroke:#7ce8dd;stroke-linecap:round;stroke-linejoin:round;stroke-width:1.00622773842599px"></circle></g></g></svg>                    
                </a>
                <a class="footer__social-icons__link footer__social-icons--twitter" href="https://twitter.com/ArvanCloud_fa" target="_blank" rel="noopener noreferrer">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 34.72 28.86"><g><g><path d="M8.81,23.82a7.45,7.45,0,0,1-2.72,1.94,10.49,10.49,0,0,1-2.5.83A5.9,5.9,0,0,1,1,26.78c11.19,4.6,28.58-.61,28.66-19.15,0,0,3.68-2.07,4.59-4.68L31.68,4,33.45.66a8.5,8.5,0,0,1-5.59,2.22C26.17-.95,15-1,16.67,9.31,8.32,10.31,1.35,2.5,1.35,2.5S-.49,7.78,3.87,11c0,0-.42,1.13-3.37,0,0,0,.92,5.9,6,5.9,0,0,.41,2.74-3.3,1.91A7.67,7.67,0,0,0,9.54,22a7.23,7.23,0,0,1-.17.8A3.68,3.68,0,0,1,8.81,23.82Z" style="fill:none;stroke:#7ce8dd;stroke-linecap:round;stroke-linejoin:round;stroke-width:1.00622773842599px"></path></g></g></svg>                    
                </a>
                <a class="footer__social-icons__link footer__social-icons--instagram" href="https://instagram.com/arvancloud_fa" target="_blank" rel="noopener noreferrer">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 29.2 29.34"><g><g><rect x="0.5" y="0.5" width="28.19" height="28.34" rx="8.18" ry="8.18" style="fill:none;stroke:#7ce8dd;stroke-linecap:round;stroke-linejoin:round;stroke-width:1.00622773842599px"></rect><circle cx="14.6" cy="14.67" r="6.8" style="fill:none;stroke:#7ce8dd;stroke-linecap:round;stroke-linejoin:round;stroke-width:1.00622773842599px"></circle><circle cx="22.61" cy="6.65" r="1.22" style="fill:none;stroke:#7ce8dd;stroke-linecap:round;stroke-linejoin:round;stroke-width:1.00622773842599px"></circle></g></g></svg>                    
                </a>
                <a class="footer__social-icons__link footer__social-icons--github" href="https://github.com/arvancloud" target="_blank" rel="noopener noreferrer">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 139.04 160.41"><path d="M93.75,102.76c29.9-.11,57.07-38.71,33.1-72.61,2-5.25,2.25-19-1.75-26.75-8.25-2.75-28.25,9.75-28.25,9.75-13.75-7.5-38.24-4.42-47,.25,0,0-20-12.5-28.25-9.75-4,7.75-3.75,21.5-1.75,26.75C-4.11,64.3,23.06,102.9,53,103a1.35,1.35,0,0,1,1.18,2c-1.85,3-3.51,8-4.76,13.82a54.86,54.86,0,0,1-8,1,17.75,17.75,0,0,1-15.18-7.11c-3.17-4.24-6-9.14-9.91-11.37C10.8,98.22,6.22,98.2,3.64,100a1.55,1.55,0,0,0,.21,2.63c13.51,7.09,14.17,19.06,22.5,25.25,5.14,3.81,16.93,4.56,21,4.71-.71,8.84-.41,17.94,1.48,24.79l49-.25c4.32-15.7.36-43.19-5.28-52.35A1.35,1.35,0,0,1,93.75,102.76Z" style="fill:none;stroke:#8ccece;stroke-miterlimit:10;stroke-width:6px"></path></svg>                    
                </a>
            </div>
        </div>
    </div>
</div>