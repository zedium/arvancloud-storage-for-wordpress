<?php
use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use Aws\S3\MultipartUploader;
use Aws\Exception\MultipartUploadException;

$credentials = get_storage_settings();

if( $credentials ) {
    
    // require the sdk from your composer vendor dir
    require ACS_PLUGIN_ROOT . '/vendor/autoload.php';

    // Instantiate the S3 class and point it at the desired host
    $client = new S3Client([
        'region'   => '',
        'version'  => '2006-03-01',
        'endpoint' => $credentials['endpoint-url'],
        'credentials' => [
            'key'     => $credentials['access-key'],
            'secret'  => $credentials['secret-key']
        ],
        // Set the S3 class to use objects. arvanstorage.com/bucket
        // instead of bucket.objects. arvanstorage.com
        'use_path_style_endpoint' => true
    ]);
}