<?php
/**
 * Provider factory
 *
 * @package WordPress_Address_Autocomplete
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Provider factory class
 */
class NDNCI_WPAA_Provider_Factory {
    
    /**
     * Available providers
     *
     * @var array
     */
    private static $providers = array();
    
    /**
     * Get provider instance
     *
     * @param string|null $provider_id Provider ID (null = get active provider)
     * @return NDNCI_WPAA_Provider_Abstract|null
     */
    public static function get_provider( $provider_id = null ) {
        if ( null === $provider_id ) {
            $provider_id = get_option( 'ndnci_wpaa_provider', 'openstreetmap' );
        }
        
        if ( isset( self::$providers[ $provider_id ] ) ) {
            return self::$providers[ $provider_id ];
        }
        
        $provider = self::create_provider( $provider_id );
        
        if ( $provider ) {
            self::$providers[ $provider_id ] = $provider;
        }
        
        return $provider;
    }
    
    /**
     * Get all available providers
     *
     * @return array Array of provider instances
     */
    public static function get_all_providers() {
        $provider_ids = self::get_available_provider_ids();
        $providers = array();
        
        foreach ( $provider_ids as $provider_id ) {
            $provider = self::get_provider( $provider_id );
            if ( $provider ) {
                $providers[ $provider_id ] = $provider;
            }
        }
        
        return $providers;
    }
    
    /**
     * Get available provider IDs
     *
     * @return array
     */
    public static function get_available_provider_ids() {
        $default_providers = array(
            'openstreetmap',
            'google-maps',
        );
        
        /**
         * Filter available provider IDs
         *
         * @since 1.0.0
         * @param array $provider_ids Array of provider IDs
         */
        return apply_filters( 'ndnci_wpaa_available_providers', $default_providers );
    }
    
    /**
     * Create provider instance
     *
     * @param string $provider_id Provider ID
     * @return NDNCI_WPAA_Provider_Abstract|null
     */
    private static function create_provider( $provider_id ) {
        $class_map = array(
            'openstreetmap' => 'NDNCI_WPAA_Provider_OpenStreetMap',
            'google-maps' => 'NDNCI_WPAA_Provider_Google_Maps',
        );
        
        /**
         * Filter provider class map
         *
         * @since 1.0.0
         * @param array $class_map Map of provider IDs to class names
         */
        $class_map = apply_filters( 'ndnci_wpaa_provider_class_map', $class_map );
        
        if ( ! isset( $class_map[ $provider_id ] ) ) {
            return null;
        }
        
        $class_name = $class_map[ $provider_id ];
        
        if ( ! class_exists( $class_name ) ) {
            return null;
        }
        
        $provider = new $class_name();
        
        /**
         * Filter provider instance after creation
         *
         * @since 1.0.0
         * @param NDNCI_WPAA_Provider_Abstract $provider Provider instance
         * @param string $provider_id Provider ID
         */
        return apply_filters( 'ndnci_wpaa_provider_instance', $provider, $provider_id );
    }
}
