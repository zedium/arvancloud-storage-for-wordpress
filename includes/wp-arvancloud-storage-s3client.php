<?php

use Aws\S3\S3Client;

if( $acs_settings_option = get_option( 'arvan-cloud-storage-settings', true ) ) {
    $acs_settings_option = unserialize( $acs_settings_option );

    if( $acs_settings_option['config-type'] == 'db' ) {
        $credentials = $acs_settings_option;
    } else {
        $credentials =  unserialize( ARVANCLOUD_STORAGE_SETTINGS );
    }
}

// require the sdk from your composer vendor dir
require ACS_PLUGIN_ROOT . '/vendor/autoload.php';

// Instantiate the S3 class and point it at the desired host
$client = new S3Client([
    'region' => '',
    'version' => '2006-03-01',
    'endpoint' => $credentials['endpoint-url'],
    'credentials' => [
        'key' => $credentials['access-key'],
        'secret' => $credentials['secret-key']
    ],
    // Set the S3 class to use objects. arvanstorage.com/bucket
    // instead of bucket.objects. arvanstorage.com
    'use_path_style_endpoint' => true
]);