<?php
/**
 * Assets handler
 *
 * @package WordPress_Address_Autocomplete
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Assets handler class
 */
class NDNCI_WPAA_Assets {
    
    /**
     * Single instance
     *
     * @var NDNCI_WPAA_Assets
     */
    private static $instance = null;
    
    /**
     * Get instance
     *
     * @return NDNCI_WPAA_Assets
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
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
    }
    
    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets() {
        // Get current provider
        $provider = NDNCI_WPAA_Provider_Factory::get_provider();
        
        if ( ! $provider ) {
            return;
        }
        
        // Enqueue provider-specific map scripts
        $map_script_url = $provider->get_map_script_url();
        
        if ( ! empty( $map_script_url ) ) {
            wp_enqueue_script(
                'wpaa-map-provider',
                $map_script_url,
                array(),
                null,
                true
            );
        }
        
        // Enqueue Leaflet for OpenStreetMap
        if ( 'openstreetmap' === $provider->get_id() ) {
            wp_enqueue_style(
                'leaflet',
                'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
                array(),
                '1.9.4'
            );
        }
        
        // Main plugin CSS
        wp_enqueue_style(
            'wpaa-frontend',
            NDNCI_WPAA_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            NDNCI_WPAA_VERSION
        );
        
        // Main plugin JS
        wp_enqueue_script(
            'wpaa-frontend',
            NDNCI_WPAA_PLUGIN_URL . 'assets/js/frontend.js',
            array( 'jquery' ),
            NDNCI_WPAA_VERSION,
            true
        );
        
        // Localize script
        wp_localize_script(
            'wpaa-frontend',
            'ndnciWpaaData',
            array(
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce( 'ndnci_wpaa_nonce' ),
                'provider' => $provider->get_id(),
                'i18n' => array(
                    'searching' => __( 'Searching...', 'wp-address-autocomplete' ),
                    'noResults' => __( 'No results found', 'wp-address-autocomplete' ),
                    'error' => __( 'An error occurred. Please try again.', 'wp-address-autocomplete' ),
                    'selectAddress' => __( 'Select an address', 'wp-address-autocomplete' ),
                ),
            )
        );
        
        /**
         * Fires after frontend assets are enqueued
         *
         * @since 1.0.0
         */
        do_action( 'ndnci_wpaa_frontend_assets_enqueued' );
    }
    
    /**
     * Enqueue admin assets
     *
     * @param string $hook Current admin page hook
     */
    public function enqueue_admin_assets( $hook ) {
        // Only load on settings page
        if ( 'settings_page_ndnci-wpaa-settings' !== $hook ) {
            return;
        }
        
        wp_enqueue_style(
            'wpaa-admin',
            NDNCI_WPAA_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            NDNCI_WPAA_VERSION
        );
        
        wp_enqueue_script(
            'wpaa-admin',
            NDNCI_WPAA_PLUGIN_URL . 'assets/js/admin.js',
            array( 'jquery' ),
            NDNCI_WPAA_VERSION,
            true
        );
        
        wp_localize_script(
            'wpaa-admin',
            'wpaaAdminData',
            array(
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce( 'ndnci_wpaa_admin_nonce' ),
                'i18n' => array(
                    'testing' => __( 'Testing...', 'wp-address-autocomplete' ),
                    'testSuccess' => __( 'Connection successful!', 'wp-address-autocomplete' ),
                    'testFailed' => __( 'Connection failed. Please check your settings.', 'wp-address-autocomplete' ),
                ),
            )
        );
        
        /**
         * Fires after admin assets are enqueued
         *
         * @since 1.0.0
         */
        do_action( 'ndnci_wpaa_admin_assets_enqueued' );
    }
}
