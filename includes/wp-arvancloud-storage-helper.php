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
    $credentials         = false;
    $acs_settings_option = get_option( 'arvan-cloud-storage-settings' );

    if( !empty( $acs_settings_option ) ) {    
        $acs_settings_option = json_decode( acs_decrypt( $acs_settings_option ), true );

        if( $acs_settings_option['config-type'] == 'db' ) {
            $credentials = $acs_settings_option;
        } else {
            if( defined( 'ARVANCLOUD_STORAGE_SETTINGS' ) ) {
                $settings = json_decode( ARVANCLOUD_STORAGE_SETTINGS, true );
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

    $method = 'aes-256-ctr';
    $ivlen  = openssl_cipher_iv_length( $method );
    $iv     = openssl_random_pseudo_bytes( $ivlen );

    $raw_value = openssl_encrypt( $value . acs_get_default_salt(), $method, acs_get_default_key(), 0, $iv );

    if ( ! $raw_value ) {
        return false;
    }

    return base64_encode( $iv . $raw_value ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode

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

    $raw_value = base64_decode( $raw_value, true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
    
    $method = 'aes-256-ctr';
    $ivlen  = openssl_cipher_iv_length( $method );
    $iv     = substr( $raw_value, 0, $ivlen );
    $key    = acs_get_default_key();
    $salt   = acs_get_default_salt();

    $raw_value = substr( $raw_value, $ivlen );

    $value = openssl_decrypt( $raw_value, $method, $key, 0, $iv );

    if ( ! $value || substr( $value, - strlen( $salt ) ) !== $salt ) {
        return false;
    }

    return substr( $value, 0, - strlen( $salt ) );

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
    return 'There-is-not-a-secret-key';

}

/**
 * Gets the default encryption salt to use.
 *
 * @since 1.0.0
 *
 * @return string Encryption salt.
 */
function acs_get_default_salt() {

    if ( defined( 'LOGGED_IN_SALT' ) && '' !== LOGGED_IN_SALT ) {
        return LOGGED_IN_SALT;
    }

    // If this is reached, you're either not on a live site or have a serious security issue.
    return 'There-is-not-a-secret-salt-key';

}

/**
 * Recursive sanitation for an array
 * 
 * @param $array
 *
 * @return mixed
 */
function acs_recursive_sanitize( $array ) {
    foreach ( $array as $key => &$value ) {
        if ( is_array( $value ) ) {
            $value = acs_recursive_sanitize( $value );
        } else {
            $value = sanitize_text_field( $value );
        }
    }

    return $array;
}