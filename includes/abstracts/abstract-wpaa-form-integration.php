<?php
/**
 * Abstract form integration
 *
 * @package WordPress_Address_Autocomplete
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Abstract form integration class
 */
abstract class NDNCI_WPAA_Form_Integration_Abstract {
    
    /**
     * Integration ID
     *
     * @var string
     */
    protected $id = '';
    
    /**
     * Integration name
     *
     * @var string
     */
    protected $name = '';
    
    /**
     * Parent plugin file to check
     *
     * @var string
     */
    protected $plugin_file = '';
    
    /**
     * Check if parent plugin is active
     *
     * @return bool
     */
    public function is_plugin_active() {
        if ( empty( $this->plugin_file ) ) {
            return false;
        }
        
        return is_plugin_active( $this->plugin_file );
    }
    
    /**
     * Get integration ID
     *
     * @return string
     */
    public function get_id() {
        return $this->id;
    }
    
    /**
     * Get integration name
     *
     * @return string
     */
    public function get_name() {
        return $this->name;
    }
    
    /**
     * Initialize integration
     */
    abstract public function init();
    
    /**
     * Register field type
     */
    abstract protected function register_field_type();
    
    /**
     * Render field on frontend
     *
     * @param array $field Field configuration
     * @param array $args Additional arguments
     */
    abstract public function render_field( $field, $args = array() );
    
    /**
     * Log message
     *
     * @param string $message Log message
     * @param string $level Log level (info, warning, error)
     */
    protected function log( $message, $level = 'info' ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( sprintf( '[NDNCI WPAA Integration: %s] [%s] %s', $this->id, $level, $message ) );
        }
        
        /**
         * Fires when an integration message is logged
         *
         * @since 1.0.0
         * @param string $message Log message
         * @param string $level Log level
         * @param string $integration_id Integration ID
         */
        do_action( 'ndnci_wpaa_integration_log', $message, $level, $this->id );
    }
}
