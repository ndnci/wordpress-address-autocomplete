<?php
/**
 * Plugin Name: NDNCI - WordPress Address Autocomplete
 * Plugin URI: https://www.ndnci.com
 * Description: Add address autocomplete fields with map support to popular form plugins (Contact Form 7, WPForms, Gravity Forms). Supports Google Maps and OpenStreetMap.
 * Version: 1.0.0
 * Author: NDNCI
 * Author URI: https://www.ndnci.com
 * Text Domain: wp-address-autocomplete
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants
define( 'NDNCI_WPAA_VERSION', '1.0.0' );
define( 'NDNCI_WPAA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'NDNCI_WPAA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'NDNCI_WPAA_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Main plugin class
 */
class WordPress_Address_Autocomplete {
    
    /**
     * Single instance of the class
     *
     * @var WordPress_Address_Autocomplete
     */
    private static $instance = null;
    
    /**
     * Get single instance
     *
     * @return WordPress_Address_Autocomplete
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
        $this->load_dependencies();
        $this->init_hooks();
    }
    
    /**
     * Load required files
     */
    private function load_dependencies() {
        // Core classes
        require_once NDNCI_WPAA_PLUGIN_DIR . 'includes/class-wpaa-cache.php';
        require_once NDNCI_WPAA_PLUGIN_DIR . 'includes/class-wpaa-provider-factory.php';
        require_once NDNCI_WPAA_PLUGIN_DIR . 'includes/abstracts/abstract-wpaa-provider.php';
        require_once NDNCI_WPAA_PLUGIN_DIR . 'includes/providers/class-wpaa-provider-openstreetmap.php';
        require_once NDNCI_WPAA_PLUGIN_DIR . 'includes/providers/class-wpaa-provider-google-maps.php';
        require_once NDNCI_WPAA_PLUGIN_DIR . 'includes/class-wpaa-ajax-handler.php';
        require_once NDNCI_WPAA_PLUGIN_DIR . 'includes/class-wpaa-settings.php';
        require_once NDNCI_WPAA_PLUGIN_DIR . 'includes/class-wpaa-assets.php';
        
        // Form integrations
        require_once NDNCI_WPAA_PLUGIN_DIR . 'includes/abstracts/abstract-wpaa-form-integration.php';
        require_once NDNCI_WPAA_PLUGIN_DIR . 'includes/integrations/class-wpaa-contact-form-7.php';
        require_once NDNCI_WPAA_PLUGIN_DIR . 'includes/integrations/class-wpaa-wpforms.php';
        require_once NDNCI_WPAA_PLUGIN_DIR . 'includes/integrations/class-wpaa-gravity-forms.php';
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
        add_action( 'init', array( $this, 'init' ) );
        add_action( 'admin_notices', array( $this, 'admin_notices' ) );
        add_action( 'admin_notices', array( $this, 'check_dependencies' ) );
        
        // Activation/Deactivation hooks
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
    }
    
    /**
     * Load plugin text domain
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'wp-address-autocomplete',
            false,
            dirname( NDNCI_WPAA_PLUGIN_BASENAME ) . '/languages'
        );
    }
    
    /**
     * Initialize plugin components
     */
    public function init() {
        // Initialize settings
        NDNCI_WPAA_Settings::get_instance();
        
        // Initialize assets
        NDNCI_WPAA_Assets::get_instance();
        
        // Initialize AJAX handler
        NDNCI_WPAA_Ajax_Handler::get_instance();
        
        // Initialize form integrations
        NDNCI_WPAA_Contact_Form_7::get_instance();
        NDNCI_WPAA_WPForms::get_instance();
        NDNCI_WPAA_Gravity_Forms::get_instance();
        
        /**
         * Fires after the plugin is fully initialized
         *
         * @since 1.0.0
         */
        do_action( 'ndnci_wpaa_initialized' );
    }
    
    /**
     * Show admin notices
     */
    public function admin_notices() {
        $provider = get_option( 'ndnci_wpaa_provider', 'openstreetmap' );
        
        if ( 'google-maps' === $provider ) {
            $api_key = get_option( 'ndnci_wpaa_google_maps_api_key', '' );
            if ( empty( $api_key ) ) {
                ?>
                <div class="notice notice-warning">
                    <p>
                        <?php 
                        echo wp_kses_post(
                            sprintf(
                                __( '<strong>NDNCI - WordPress Address Autocomplete:</strong> Google Maps is selected but no API key is configured. Please add your API key in the <a href="%s">settings page</a>.', 'wp-address-autocomplete' ),
                                admin_url( 'options-general.php?page=ndnci-wpaa-settings' )
                            )
                        );
                        ?>
                    </p>
                </div>
                <?php
            }
        }
    }
    
    /**
     * Check for required form plugin dependencies
     */
    public function check_dependencies() {
        // Get list of supported form plugins
        $form_plugins = array(
            'contact-form-7/wp-contact-form-7.php' => 'Contact Form 7',
            'wpforms-lite/wpforms.php' => 'WPForms Lite',
            'wpforms/wpforms.php' => 'WPForms',
            'gravityforms/gravityforms.php' => 'Gravity Forms',
        );
        
        // Check if any supported form plugin is active
        $has_form_plugin = false;
        foreach ( $form_plugins as $plugin_file => $plugin_name ) {
            if ( is_plugin_active( $plugin_file ) ) {
                $has_form_plugin = true;
                break;
            }
        }
        
        // Show warning if no form plugins are active
        if ( ! $has_form_plugin ) {
            ?>
            <div class="notice notice-warning is-dismissible">
                <p>
                    <strong><?php esc_html_e( 'NDNCI - WordPress Address Autocomplete:', 'wp-address-autocomplete' ); ?></strong>
                    <?php esc_html_e( 'No compatible form plugins detected. This plugin requires at least one of the following:', 'wp-address-autocomplete' ); ?>
                </p>
                <ul style="list-style: disc; margin-left: 25px;">
                    <?php foreach ( $form_plugins as $plugin_file => $plugin_name ) : ?>
                        <li><?php echo esc_html( $plugin_name ); ?></li>
                    <?php endforeach; ?>
                </ul>
                <p>
                    <?php 
                    echo wp_kses_post(
                        sprintf(
                            __( 'Please install and activate one of these plugins to use address autocomplete functionality. <a href="%s" target="_blank">Learn more</a>', 'wp-address-autocomplete' ),
                            'https://www.ndnci.com/wordpress-address-autocomplete'
                        )
                    );
                    ?>
                </p>
            </div>
            <?php
        }
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Set default options
        if ( false === get_option( 'ndnci_wpaa_provider' ) ) {
            update_option( 'ndnci_wpaa_provider', 'openstreetmap' );
        }
        
        if ( false === get_option( 'ndnci_wpaa_cache_enabled' ) ) {
            update_option( 'ndnci_wpaa_cache_enabled', '1' );
        }
        
        if ( false === get_option( 'ndnci_wpaa_cache_duration' ) ) {
            update_option( 'ndnci_wpaa_cache_duration', '86400' ); // 24 hours
        }
        
        /**
         * Fires when the plugin is activated
         *
         * @since 1.0.0
         */
        do_action( 'ndnci_wpaa_activated' );
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        /**
         * Fires when the plugin is deactivated
         *
         * @since 1.0.0
         */
        do_action( 'ndnci_wpaa_deactivated' );
    }
}

/**
 * Get the main plugin instance
 *
 * @return WordPress_Address_Autocomplete
 */
function ndnci_wpaa() {
    return WordPress_Address_Autocomplete::get_instance();
}

// Initialize the plugin
ndnci_wpaa();
