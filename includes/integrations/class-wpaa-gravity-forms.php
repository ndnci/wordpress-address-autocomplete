<?php
/**
 * Gravity Forms integration
 *
 * @package WordPress_Address_Autocomplete
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Gravity Forms integration class
 */
class NDNCI_WPAA_Gravity_Forms extends NDNCI_WPAA_Form_Integration_Abstract {
    
    /**
     * Single instance
     *
     * @var NDNCI_WPAA_Gravity_Forms
     */
    private static $instance = null;
    
    /**
     * Get instance
     *
     * @return NDNCI_WPAA_Gravity_Forms
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
        $this->id = 'gravity-forms';
        $this->name = 'Gravity Forms';
        $this->plugin_file = 'gravityforms/gravityforms.php';
        
        $this->init();
    }
    
    /**
     * Initialize integration
     */
    public function init() {
        if ( ! $this->is_plugin_active() ) {
            return;
        }
        
        add_action( 'gform_loaded', array( $this, 'register_field_type' ), 5 );
        
        $this->log( 'Gravity Forms integration initialized' );
    }
    
    /**
     * Register field type
     */
    protected function register_field_type() {
        if ( ! method_exists( 'GFForms', 'include_addon_framework' ) ) {
            return;
        }
        
        require_once NDNCI_WPAA_PLUGIN_DIR . 'includes/integrations/gravityforms/class-wpaa-gf-field-address-autocomplete.php';
        require_once NDNCI_WPAA_PLUGIN_DIR . 'includes/integrations/gravityforms/class-wpaa-gf-field-address-map.php';
        
        GF_Fields::register( new NDNCI_WPAA_GF_Field_Address_Autocomplete() );
        GF_Fields::register( new NDNCI_WPAA_GF_Field_Address_Map() );
    }
    
    /**
     * Render field (not used for Gravity Forms, handled by field class)
     *
     * @param array $field Field configuration
     * @param array $args Additional arguments
     */
    public function render_field( $field, $args = array() ) {
        // Handled by Gravity Forms field class
    }
}
