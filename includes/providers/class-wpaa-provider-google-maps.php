<?php
/**
 * Google Maps provider
 *
 * @package WordPress_Address_Autocomplete
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Google Maps provider class
 */
class NDNCI_WPAA_Provider_Google_Maps extends NDNCI_WPAA_Provider_Abstract {
    
    /**
     * Google Places API base URL
     */
    const API_BASE_URL = 'https://maps.googleapis.com/maps/api';
    
    /**
     * API key
     *
     * @var string
     */
    private $api_key = '';
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->id = 'google-maps';
        $this->name = __( 'Google Maps', 'wp-address-autocomplete' );
        $this->requires_api_key = true;
        $this->api_key = get_option( 'ndnci_wpaa_google_maps_api_key', '' );
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
        
        if ( empty( $this->api_key ) ) {
            return new WP_Error( 'missing_api_key', __( 'Google Maps API key is not configured', 'wp-address-autocomplete' ) );
        }
        
        // Check cache first
        $cache_key = 'google_search_' . md5( $query . $this->api_key );
        $cached = WPAA_Cache::get( $cache_key );
        
        if ( false !== $cached ) {
            return $cached;
        }
        
        $url = add_query_arg(
            array(
                'input' => $query,
                'key' => $this->api_key,
                'language' => $this->get_language_code(),
            ),
            self::API_BASE_URL . '/place/autocomplete/json'
        );
        
        $response = $this->make_request( $url );
        
        if ( is_wp_error( $response ) ) {
            $this->log_error( 'Search request failed', array( 'query' => $query, 'error' => $response->get_error_message() ) );
            return $response;
        }
        
        // Check for API errors
        if ( isset( $response['status'] ) && 'OK' !== $response['status'] && 'ZERO_RESULTS' !== $response['status'] ) {
            $error_message = $response['error_message'] ?? $response['status'];
            $this->log_error( 'API error', array( 'status' => $response['status'], 'message' => $error_message ) );
            return new WP_Error( 'api_error', $error_message );
        }
        
        $formatted = $this->format_results( $response['predictions'] ?? array() );
        
        // Cache the results
        WPAA_Cache::set( $cache_key, $formatted );
        
        return $formatted;
    }
    
    /**
     * Get place details by ID
     *
     * @param string $place_id Place ID
     * @return array|WP_Error
     */
    public function get_place_details( $place_id ) {
        if ( empty( $place_id ) ) {
            return new WP_Error( 'empty_place_id', __( 'Place ID cannot be empty', 'wp-address-autocomplete' ) );
        }
        
        if ( empty( $this->api_key ) ) {
            return new WP_Error( 'missing_api_key', __( 'Google Maps API key is not configured', 'wp-address-autocomplete' ) );
        }
        
        // Check cache first
        $cache_key = 'google_details_' . $place_id;
        $cached = WPAA_Cache::get( $cache_key );
        
        if ( false !== $cached ) {
            return $cached;
        }
        
        $url = add_query_arg(
            array(
                'place_id' => $place_id,
                'key' => $this->api_key,
                'fields' => 'address_components,formatted_address,geometry',
                'language' => $this->get_language_code(),
            ),
            self::API_BASE_URL . '/place/details/json'
        );
        
        $response = $this->make_request( $url );
        
        if ( is_wp_error( $response ) ) {
            $this->log_error( 'Place details request failed', array( 'place_id' => $place_id, 'error' => $response->get_error_message() ) );
            return $response;
        }
        
        // Check for API errors
        if ( isset( $response['status'] ) && 'OK' !== $response['status'] ) {
            $error_message = $response['error_message'] ?? $response['status'];
            $this->log_error( 'API error', array( 'status' => $response['status'], 'message' => $error_message ) );
            return new WP_Error( 'api_error', $error_message );
        }
        
        if ( empty( $response['result'] ) ) {
            return new WP_Error( 'place_not_found', __( 'Place not found', 'wp-address-autocomplete' ) );
        }
        
        $formatted = $this->format_place_details( $response['result'] );
        
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
        if ( empty( $this->api_key ) ) {
            return new WP_Error( 'missing_api_key', __( 'Google Maps API key is required', 'wp-address-autocomplete' ) );
        }
        
        // Test API with a simple request
        $test_url = add_query_arg(
            array(
                'input' => 'test',
                'key' => $this->api_key,
            ),
            self::API_BASE_URL . '/place/autocomplete/json'
        );
        
        $response = $this->make_request( $test_url );
        
        if ( is_wp_error( $response ) ) {
            return new WP_Error(
                'validation_failed',
                sprintf(
                    __( 'Could not connect to Google Maps API: %s', 'wp-address-autocomplete' ),
                    $response->get_error_message()
                )
            );
        }
        
        // Check for authentication errors
        if ( isset( $response['status'] ) && in_array( $response['status'], array( 'REQUEST_DENIED', 'INVALID_REQUEST' ), true ) ) {
            return new WP_Error(
                'invalid_api_key',
                $response['error_message'] ?? __( 'Invalid API key or API not enabled', 'wp-address-autocomplete' )
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
        if ( empty( $this->api_key ) ) {
            return '';
        }
        
        return add_query_arg(
            array(
                'key' => $this->api_key,
                'libraries' => 'places',
                'language' => $this->get_language_code(),
            ),
            'https://maps.googleapis.com/maps/api/js'
        );
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
                'place_id' => $result['place_id'],
                'description' => $result['description'],
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
            'place_id' => $place['place_id'],
            'description' => $place['formatted_address'],
            'address' => $this->parse_address_components( $place['address_components'] ?? array() ),
            'location' => array(
                'lat' => $place['geometry']['location']['lat'],
                'lng' => $place['geometry']['location']['lng'],
            ),
        );
    }
    
    /**
     * Parse address components
     *
     * @param array $components Address components from API
     * @return array
     */
    private function parse_address_components( $components ) {
        $address = array(
            'street' => '',
            'city' => '',
            'state' => '',
            'postal_code' => '',
            'country' => '',
            'country_code' => '',
        );
        
        foreach ( $components as $component ) {
            $types = $component['types'];
            
            if ( in_array( 'street_number', $types, true ) ) {
                $address['street'] = $component['long_name'] . ' ' . $address['street'];
            } elseif ( in_array( 'route', $types, true ) ) {
                $address['street'] .= $component['long_name'];
            } elseif ( in_array( 'locality', $types, true ) ) {
                $address['city'] = $component['long_name'];
            } elseif ( in_array( 'administrative_area_level_1', $types, true ) ) {
                $address['state'] = $component['long_name'];
            } elseif ( in_array( 'postal_code', $types, true ) ) {
                $address['postal_code'] = $component['long_name'];
            } elseif ( in_array( 'country', $types, true ) ) {
                $address['country'] = $component['long_name'];
                $address['country_code'] = $component['short_name'];
            }
        }
        
        $address['street'] = trim( $address['street'] );
        
        return $address;
    }
    
    /**
     * Get language code for API requests
     *
     * @return string
     */
    private function get_language_code() {
        $locale = get_locale();
        return substr( $locale, 0, 2 );
    }
}
