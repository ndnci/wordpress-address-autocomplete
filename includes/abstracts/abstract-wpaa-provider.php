<?php
/**
 * Abstract provider class
 *
 * @package WordPress_Address_Autocomplete
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Abstract provider class
 */
abstract class NDNCI_WPAA_Provider_Abstract {
    
    /**
     * Provider ID
     *
     * @var string
     */
    protected $id = '';
    
    /**
     * Provider name
     *
     * @var string
     */
    protected $name = '';
    
    /**
     * Whether the provider requires an API key
     *
     * @var bool
     */
    protected $requires_api_key = false;
    
    /**
     * Get provider ID
     *
     * @return string
     */
    public function get_id() {
        return $this->id;
    }
    
    /**
     * Get provider name
     *
     * @return string
     */
    public function get_name() {
        return $this->name;
    }
    
    /**
     * Check if provider requires API key
     *
     * @return bool
     */
    public function requires_api_key() {
        return $this->requires_api_key;
    }
    
    /**
     * Search for addresses
     *
     * @param string $query Search query
     * @return array|WP_Error Array of results or WP_Error on failure
     */
    abstract public function search( $query );
    
    /**
     * Get place details by ID
     *
     * @param string $place_id Place ID
     * @return array|WP_Error Place details or WP_Error on failure
     */
    abstract public function get_place_details( $place_id );
    
    /**
     * Validate provider configuration
     *
     * @return bool|WP_Error True if valid, WP_Error on failure
     */
    abstract public function validate();
    
    /**
     * Get map script URL
     *
     * @return string
     */
    abstract public function get_map_script_url();
    
    /**
     * Format search results to a common structure
     *
     * @param array $results Raw API results
     * @return array Formatted results
     */
    protected function format_results( $results ) {
        /**
         * Filter formatted results
         *
         * @since 1.0.0
         * @param array $results Formatted results
         * @param string $provider_id Provider ID
         */
        return apply_filters( 'ndnci_wpaa_provider_format_results', $results, $this->id );
    }
    
    /**
     * Make HTTP request
     *
     * @param string $url Request URL
     * @param array $args Request arguments
     * @return array|WP_Error Response data or WP_Error on failure
     */
    protected function make_request( $url, $args = array() ) {
        $defaults = array(
            'timeout' => 15,
            'user-agent' => 'WordPress Address Autocomplete/' . NDNCI_WPAA_VERSION,
        );
        
        $args = wp_parse_args( $args, $defaults );
        
        /**
         * Filter request arguments before making the request
         *
         * @since 1.0.0
         * @param array $args Request arguments
         * @param string $url Request URL
         * @param string $provider_id Provider ID
         */
        $args = apply_filters( 'ndnci_wpaa_provider_request_args', $args, $url, $this->id );
        
        $response = wp_remote_get( $url, $args );
        
        if ( is_wp_error( $response ) ) {
            return $response;
        }
        
        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );
        
        if ( null === $data ) {
            return new WP_Error(
                'invalid_response',
                __( 'Invalid JSON response from API', 'wp-address-autocomplete' )
            );
        }
        
        return $data;
    }
    
    /**
     * Log error
     *
     * @param string $message Error message
     * @param array $context Additional context
     */
    protected function log_error( $message, $context = array() ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log(
                sprintf(
                    '[WPAA Provider: %s] %s %s',
                    $this->id,
                    $message,
                    ! empty( $context ) ? wp_json_encode( $context ) : ''
                )
            );
        }
        
        /**
         * Fires when a provider error is logged
         *
         * @since 1.0.0
         * @param string $message Error message
         * @param array $context Error context
         * @param string $provider_id Provider ID
         */
        do_action( 'ndnci_wpaa_provider_error', $message, $context, $this->id );
    }
}
