<?php

/**
 * The file that defines the plugin helper functions
 *
 * A class definition that includes functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       khorshidlab.com
 * @since      1.0.0
 *
 * @package    Wp_Arvancloud_Storage
 * @subpackage Wp_Arvancloud_Storage/includes
 */

function get_storage_settings() {

    $credentials = false;

    if( $acs_settings_option = get_option( 'arvan-cloud-storage-settings', true ) ) {    
        $acs_settings_option = unserialize( acs_decrypt( $acs_settings_option ) );
    
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

    $bucket_name = esc_attr( get_option( 'arvan-cloud-storage-bucket-name', false ) );

    return $bucket_name;

}

function get_storage_url() {

    $credentials  = get_storage_settings();
    $bucket_name  = get_bucket_name();
    $endpoint_url = $credentials['endpoint-url'] . "/";

    return esc_url( substr_replace( $endpoint_url, $bucket_name . ".", 8, 0 ) );
    
}

/**
 * Encrypts a value.
 *
 * If a user-based key is set, that key is used. Otherwise the default key is used.
 *
 * @since 1.0.0
 *
 * @param string $value Value to encrypt.
 * @return string|bool Encrypted value, or false on failure.
 */
function acs_encrypt( $value ) {

    if ( ! extension_loaded( 'openssl' ) ) {
        return $value;
    }

    $key      = acs_get_default_key();
    $method   = 'aes-256-cbc';
    $iv       = chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0); // IV must be exact 16 chars (128 bit)
    
    $encrypted = base64_encode( openssl_encrypt( $value, $method, $key, OPENSSL_RAW_DATA, $iv ) );

    return $encrypted;

}

/**
 * Decrypts a value.
 *
 * If a user-based key is set, that key is used. Otherwise the default key is used.
 *
 * @since 1.0.0
 *
 * @param string $raw_value Value to decrypt.
 * @return string|bool Decrypted value, or false on failure.
 */
function acs_decrypt( $raw_value ) {

    if ( ! extension_loaded( 'openssl' ) ) {
        return $raw_value;
    }

    $key      = acs_get_default_key();
    $method   = 'aes-256-cbc';
    $iv       = chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0); // IV must be exact 16 chars (128 bit)
    
    $decrypted = openssl_decrypt(base64_decode($raw_value), $method, $key, OPENSSL_RAW_DATA, $iv);
    
    return $decrypted;

}

/**
 * Gets the default encryption key to use.
 *
 * @since 1.0.0
 *
 * @return string Default (not user-based) encryption key.
 */
function acs_get_default_key() {

    if ( defined( 'LOGGED_IN_KEY' ) && '' !== LOGGED_IN_KEY ) {
        return LOGGED_IN_KEY;
    }

    // If this is reached, you're either not on a live site or have a serious security issue.
    return 'There is not a secret key!';

}