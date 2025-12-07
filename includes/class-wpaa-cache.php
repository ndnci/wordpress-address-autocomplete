<?php
/**
 * Cache handler for API responses
 *
 * @package WordPress_Address_Autocomplete
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Cache handler class
 */
class NDNCI_WPAA_Cache {
    
    /**
     * Cache group name
     */
    const CACHE_GROUP = 'ndnci_wpaa_geocoding';
    
    /**
     * Get cached data
     *
     * @param string $key Cache key
     * @return mixed|false Cached data or false if not found
     */
    public static function get( $key ) {
        if ( ! self::is_enabled() ) {
            return false;
        }
        
        $cache_key = self::generate_key( $key );
        $cached_data = get_transient( $cache_key );
        
        /**
         * Filter cached data before returning
         *
         * @since 1.0.0
         * @param mixed $cached_data The cached data
         * @param string $key The original cache key
         */
        return apply_filters( 'ndnci_wpaa_cache_get', $cached_data, $key );
    }
    
    /**
     * Set cached data
     *
     * @param string $key Cache key
     * @param mixed $data Data to cache
     * @param int|null $duration Cache duration in seconds (null = use default)
     * @return bool True on success, false on failure
     */
    public static function set( $key, $data, $duration = null ) {
        if ( ! self::is_enabled() ) {
            return false;
        }
        
        if ( null === $duration ) {
            $duration = self::get_duration();
        }
        
        /**
         * Filter data before caching
         *
         * @since 1.0.0
         * @param mixed $data The data to cache
         * @param string $key The cache key
         */
        $data = apply_filters( 'ndnci_wpaa_cache_set_data', $data, $key );
        
        $cache_key = self::generate_key( $key );
        return set_transient( $cache_key, $data, $duration );
    }
    
    /**
     * Delete cached data
     *
     * @param string $key Cache key
     * @return bool True on success, false on failure
     */
    public static function delete( $key ) {
        $cache_key = self::generate_key( $key );
        return delete_transient( $cache_key );
    }
    
    /**
     * Clear all plugin cache
     *
     * @return int Number of deleted entries
     */
    public static function clear_all() {
        global $wpdb;
        
        $count = $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                $wpdb->esc_like( '_transient_ndnci_wpaa_' ) . '%'
            )
        );
        
        /**
         * Fires after clearing all cache
         *
         * @since 1.0.0
         * @param int $count Number of deleted entries
         */
        do_action( 'ndnci_wpaa_cache_cleared', $count );
        
        return $count;
    }
    
    /**
     * Generate cache key
     *
     * @param string $key Original key
     * @return string Formatted cache key
     */
    private static function generate_key( $key ) {
        return 'ndnci_wpaa_' . md5( $key );
    }
    
    /**
     * Check if cache is enabled
     *
     * @return bool
     */
    private static function is_enabled() {
        $enabled = get_option( 'ndnci_wpaa_cache_enabled', '1' );
        
        /**
         * Filter whether cache is enabled
         *
         * @since 1.0.0
         * @param bool $enabled Whether cache is enabled
         */
        return apply_filters( 'ndnci_wpaa_cache_enabled', '1' === $enabled );
    }
    
    /**
     * Get cache duration
     *
     * @return int Duration in seconds
     */
    private static function get_duration() {
        $duration = absint( get_option( 'ndnci_wpaa_cache_duration', 86400 ) );
        
        /**
         * Filter cache duration
         *
         * @since 1.0.0
         * @param int $duration Cache duration in seconds
         */
        return apply_filters( 'ndnci_wpaa_cache_duration', $duration );
    }
}
