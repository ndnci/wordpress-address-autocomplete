<?php
/**
 * OpenStreetMap provider
 *
 * @package WordPress_Address_Autocomplete
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * OpenStreetMap provider class
 */
class NDNCI_WPAA_Provider_OpenStreetMap extends NDNCI_WPAA_Provider_Abstract {
    
    /**
     * Nominatim API base URL
     */
    const API_BASE_URL = 'https://nominatim.openstreetmap.org';
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->id = 'openstreetmap';
        $this->name = __( 'OpenStreetMap', 'wp-address-autocomplete' );
        $this->requires_api_key = false;
    }
    
    /**
     * Search for addresses
     *
     * @param string $query Search query
     * @return array|WP_Error
     */
    public function search( $query ) {
        if ( empty( $query ) ) {
            return new WP_Error( 'empty_query', __( 'Search query cannot be empty', 'wp-address-autocomplete' ) );
        }
        
        // Check cache first
        $cache_key = 'osm_search_' . md5( $query );
        $cached = WPAA_Cache::get( $cache_key );
        
        if ( false !== $cached ) {
            return $cached;
        }
        
        $url = add_query_arg(
            array(
                'q' => $query,
                'format' => 'json',
                'addressdetails' => 1,
                'limit' => 10,
            ),
            self::API_BASE_URL . '/search'
        );
        
        $response = $this->make_request( $url );
        
        if ( is_wp_error( $response ) ) {
            $this->log_error( 'Search request failed', array( 'query' => $query, 'error' => $response->get_error_message() ) );
            return $response;
        }
        
        $formatted = $this->format_results( $response );
        
        // Cache the results
        WPAA_Cache::set( $cache_key, $formatted );
        
        return $formatted;
    }
    
    /**
     * Get place details by ID
     *
     * @param string $place_id Place ID (OSM ID)
     * @return array|WP_Error
     */
    public function get_place_details( $place_id ) {
        if ( empty( $place_id ) ) {
            return new WP_Error( 'empty_place_id', __( 'Place ID cannot be empty', 'wp-address-autocomplete' ) );
        }
        
        // Check cache first
        $cache_key = 'osm_details_' . $place_id;
        $cached = WPAA_Cache::get( $cache_key );
        
        if ( false !== $cached ) {
            return $cached;
        }
        
        // Parse OSM type and ID (format: N123456 or W123456 or R123456)
        $osm_type_map = array(
            'N' => 'node',
            'W' => 'way',
            'R' => 'relation',
        );
        
        $type_letter = substr( $place_id, 0, 1 );
        $osm_id = substr( $place_id, 1 );
        
        if ( ! isset( $osm_type_map[ $type_letter ] ) ) {
            return new WP_Error( 'invalid_place_id', __( 'Invalid place ID format', 'wp-address-autocomplete' ) );
        }
        
        $url = add_query_arg(
            array(
                'osm_type' => $osm_type_map[ $type_letter ],
                'osm_id' => $osm_id,
                'format' => 'json',
                'addressdetails' => 1,
            ),
            self::API_BASE_URL . '/lookup'
        );
        
        $response = $this->make_request( $url );
        
        if ( is_wp_error( $response ) ) {
            $this->log_error( 'Place details request failed', array( 'place_id' => $place_id, 'error' => $response->get_error_message() ) );
            return $response;
        }
        
        if ( empty( $response[0] ) ) {
            return new WP_Error( 'place_not_found', __( 'Place not found', 'wp-address-autocomplete' ) );
        }
        
        $formatted = $this->format_place_details( $response[0] );
        
        // Cache the results
        WPAA_Cache::set( $cache_key, $formatted );
        
        return $formatted;
    }
    
    /**
     * Validate provider configuration
     *
     * @return bool|WP_Error
     */
    public function validate() {
        // Test API connection
        $test_url = self::API_BASE_URL . '/search?q=test&format=json&limit=1';
        $response = $this->make_request( $test_url );
        
        if ( is_wp_error( $response ) ) {
            return new WP_Error(
                'validation_failed',
                sprintf(
                    __( 'Could not connect to OpenStreetMap API: %s', 'wp-address-autocomplete' ),
                    $response->get_error_message()
                )
            );
        }
        
        return true;
    }
    
    /**
     * Get map script URL
     *
     * @return string
     */
    public function get_map_script_url() {
        return 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
    }
    
    /**
     * Format search results
     *
     * @param array $results Raw API results
     * @return array
     */
    protected function format_results( $results ) {
        if ( empty( $results ) || ! is_array( $results ) ) {
            return array();
        }
        
        $formatted = array();
        
        foreach ( $results as $result ) {
            $formatted[] = array(
                'place_id' => $result['osm_type'][0] . $result['osm_id'],
                'description' => $result['display_name'],
                'address' => $this->parse_address( $result ),
                'location' => array(
                    'lat' => floatval( $result['lat'] ),
                    'lng' => floatval( $result['lon'] ),
                ),
            );
        }
        
        return parent::format_results( $formatted );
    }
    
    /**
     * Format place details
     *
     * @param array $place Raw place data
     * @return array
     */
    private function format_place_details( $place ) {
        return array(
            'place_id' => $place['osm_type'][0] . $place['osm_id'],
            'description' => $place['display_name'],
            'address' => $this->parse_address( $place ),
            'location' => array(
                'lat' => floatval( $place['lat'] ),
                'lng' => floatval( $place['lon'] ),
            ),
        );
    }
    
    /**
     * Parse address components
     *
     * @param array $result API result
     * @return array
     */
    private function parse_address( $result ) {
        $address = isset( $result['address'] ) ? $result['address'] : array();
        
        return array(
            'street' => $address['road'] ?? '',
            'city' => $address['city'] ?? $address['town'] ?? $address['village'] ?? '',
            'state' => $address['state'] ?? '',
            'postal_code' => $address['postcode'] ?? '',
            'country' => $address['country'] ?? '',
            'country_code' => $address['country_code'] ?? '',
        );
    }
}
