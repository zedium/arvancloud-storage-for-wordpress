<?php
function get_storage_settings() {

    $credentials = false;

    if( $acs_settings_option = get_option( 'arvan-cloud-storage-settings', true ) ) {
        $acs_settings_option = unserialize( $acs_settings_option );
    
        if( $acs_settings_option['config-type'] == 'db' ) {
            $credentials = $acs_settings_option;
        } else {
            if( defined( 'ARVANCLOUD_STORAGE_SETTINGS' ) ) {
                $settings = unserialize( ARVANCLOUD_STORAGE_SETTINGS );
                $settings['config-type'] = $acs_settings_option['config-type'];
                
                $credentials = $settings;
            }
        }
    }

    return $credentials;

}

function get_bucket_name() {

    $bucket_name = get_option( 'arvan-cloud-storage-bucket-name', false );

    return $bucket_name;

}

function get_storage_url() {

    $credentials  = get_storage_settings();
    $bucket_name  = get_bucket_name();
    $endpoint_url = $credentials['endpoint-url'] . "/";

    return substr_replace( $endpoint_url, $bucket_name . ".", 8, 0 );
    
}