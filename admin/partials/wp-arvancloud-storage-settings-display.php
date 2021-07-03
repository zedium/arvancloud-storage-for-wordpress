<H1><?php echo __( ACS_NAME . ' Settings', ACS_TEXTDOMAIN ) ?></H1>
<hr>
<h3><?php echo __( 'Configure Cloud Storage', ACS_TEXTDOMAIN ) ?></h3>

<section class="accordion-container">
    <div class="accordion-box">
        <input id="accordion-1" name="accordion-1" type="radio" />
        <label for="accordion-1"><?php echo __( "Define access keys in wp-config.php", ACS_TEXTDOMAIN ) ?></label>
        <section class="accordion">
            <?php echo __( "Copy the following snippet near the top of your wp-config.php and replace the stars with the keys.", ACS_TEXTDOMAIN ) ?>
            <textarea rows="5" class="as3cf-define-snippet code clear" readonly="">
define( 'ARVANCLOUD_STORAGE_SETTINGS', serialize( array(
    'access-key' =&gt; '********************',
    'secret-key' =&gt; '**************************************',
    'endpoint-url' =&gt; '*********************',
) ) );
			</textarea>
        </section>
    </div>
    <div class="accordion-box">
        <input id="accordion-2" name="accordion-1" type="radio" checked />
        <label for="accordion-2"><?php echo __( "I understand the risks but I'd like to store access keys in the database anyway (not recommended)", ACS_TEXTDOMAIN ) ?></label>
        <section class="accordion">
            <?php 
            echo __( "Storing your access keys in the database is less secure than the options above, but if you're ok with that, go ahead and enter your keys in the form below.", ACS_TEXTDOMAIN ) ;

            if( $acs_settings_option = get_option( 'arvan-cloud-storage-settings', true ) ) {
                $acs_settings_option = unserialize( $acs_settings_option );
            }
            
            ?>
            <form class="arvancloud-storage-config-form" method="post">
                <table class="table-form">
                    <tbody><tr valign="top">
                        <th scope="row">Access Key</th>
                        <td>
                            <div class="accordion-field-wrap">
                                <input type="text" name="access-key" value="<?php echo $acs_settings_option['access-key'] ?>" autocomplete="off">
                            </div>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">Secret Key</th>
                        <td>
                            <div class="accordion-field-wrap">
                                <input type="text" name="secret-key" value="<?php echo $acs_settings_option['secret-key'] ?>" autocomplete="off">
                            </div>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">Endpoint URL</th>
                        <td>
                            <div class="accordion-field-wrap">
                                <input type="text" name="endpoint-url" value="<?php echo $acs_settings_option['endpoint-url'] ?>" autocomplete="off">
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <button type="submit" class="button button-primary" name="save_access_keys" value="1"><?php echo __( "Save", ACS_TEXTDOMAIN ) ?></button>
            </form>
        </section>
    </div>
</section>