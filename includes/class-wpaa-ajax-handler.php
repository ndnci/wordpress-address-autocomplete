<?php
/**
 * AJAX handler
 *
 * @package WordPress_Address_Autocomplete
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * AJAX handler class
 */
class NDNCI_WPAA_Ajax_Handler {
    
    /**
     * Single instance
     *
     * @var NDNCI_WPAA_Ajax_Handler
     */
    private static $instance = null;
    
    /**
     * Get instance
     *
     * @return NDNCI_WPAA_Ajax_Handler
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        add_action( 'wp_ajax_wpaa_search', array( $this, 'search' ) );
        add_action( 'wp_ajax_nopriv_wpaa_search', array( $this, 'search' ) );
        
        add_action( 'wp_ajax_wpaa_get_place_details', array( $this, 'get_place_details' ) );
        add_action( 'wp_ajax_nopriv_wpaa_get_place_details', array( $this, 'get_place_details' ) );
    }
    
    /**
     * Search for addresses
     */
    public function search() {
        check_ajax_referer( 'ndnci_wpaa_nonce', 'nonce' );
        
        $query = isset( $_POST['query'] ) ? sanitize_text_field( wp_unslash( $_POST['query'] ) ) : '';
        
        if ( empty( $query ) ) {
            wp_send_json_error(
                array( 'message' => __( 'Search query is required', 'wp-address-autocomplete' ) )
            );
        }
        
        /**
         * Filter search query before processing
         *
         * @since 1.0.0
         * @param string $query Search query
         */
        $query = apply_filters( 'ndnci_wpaa_search_query', $query );
        
        $provider = WPAA_Provider_Factory::get_provider();
        
        if ( ! $provider ) {
            wp_send_json_error(
                array( 'message' => __( 'Provider not available', 'wp-address-autocomplete' ) )
            );
        }
        
        $results = $provider->search( $query );
        
        if ( is_wp_error( $results ) ) {
            wp_send_json_error(
                array( 'message' => $results->get_error_message() )
            );
        }
        
        /**
         * Filter search results before sending response
         *
         * @since 1.0.0
         * @param array $results Search results
         * @param string $query Search query
         */
        $results = apply_filters( 'ndnci_wpaa_search_results', $results, $query );
        
        wp_send_json_success( array( 'results' => $results ) );
    }
    
    /**
     * Get place details
     */
    public function get_place_details() {
        check_ajax_referer( 'ndnci_wpaa_nonce', 'nonce' );
        
        $place_id = isset( $_POST['place_id'] ) ? sanitize_text_field( wp_unslash( $_POST['place_id'] ) ) : '';
        
        if ( empty( $place_id ) ) {
            wp_send_json_error(
                array( 'message' => __( 'Place ID is required', 'wp-address-autocomplete' ) )
            );
        }
        
        $provider = WPAA_Provider_Factory::get_provider();
        
        if ( ! $provider ) {
            wp_send_json_error(
                array( 'message' => __( 'Provider not available', 'wp-address-autocomplete' ) )
            );
        }
        
        $details = $provider->get_place_details( $place_id );
        
        if ( is_wp_error( $details ) ) {
            wp_send_json_error(
                array( 'message' => $details->get_error_message() )
            );
        }
        
        /**
         * Filter place details before sending response
         *
         * @since 1.0.0
         * @param array $details Place details
         * @param string $place_id Place ID
         */
        $details = apply_filters( 'ndnci_wpaa_place_details', $details, $place_id );
        
        wp_send_json_success( array( 'details' => $details ) );
    }
}
