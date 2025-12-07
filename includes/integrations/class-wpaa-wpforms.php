<?php
/**
 * WPForms integration
 *
 * @package WordPress_Address_Autocomplete
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * WPForms integration class
 */
class NDNCI_WPAA_WPForms extends NDNCI_WPAA_Form_Integration_Abstract {
    
    /**
     * Single instance
     *
     * @var NDNCI_WPAA_WPForms
     */
    private static $instance = null;
    
    /**
     * Get instance
     *
     * @return NDNCI_WPAA_WPForms
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
        $this->id = 'wpforms';
        $this->name = 'WPForms';
        $this->plugin_file = 'wpforms-lite/wpforms.php';
        
        // Check for pro version too
        if ( ! $this->is_plugin_active() ) {
            $this->plugin_file = 'wpforms/wpforms.php';
        }
        
        $this->init();
    }
    
    /**
     * Initialize integration
     */
    public function init() {
        if ( ! $this->is_plugin_active() ) {
            return;
        }
        
        add_action( 'wpforms_loaded', array( $this, 'register_field_type' ) );
        
        $this->log( 'WPForms integration initialized' );
    }
    
    /**
     * Register field type
     */
    protected function register_field_type() {
        require_once NDNCI_WPAA_PLUGIN_DIR . 'includes/integrations/wpforms/class-wpaa-wpforms-field.php';
        require_once NDNCI_WPAA_PLUGIN_DIR . 'includes/integrations/wpforms/class-wpaa-wpforms-map-field.php';
    }
    
    /**
     * Render field (not used for WPForms, handled by field class)
     *
     * @param array $field Field configuration
     * @param array $args Additional arguments
     */
    public function render_field( $field, $args = array() ) {
        // Handled by WPForms field class
    }
}
