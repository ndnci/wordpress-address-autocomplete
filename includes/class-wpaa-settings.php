<?php
/**
 * Settings page
 *
 * @package WordPress_Address_Autocomplete
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Settings class
 */
class NDNCI_WPAA_Settings {
    
    /**
     * Single instance
     *
     * @var NDNCI_WPAA_Settings
     */
    private static $instance = null;
    
    /**
     * Get instance
     *
     * @return NDNCI_WPAA_Settings
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
        add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'wp_ajax_ndnci_wpaa_test_connection', array( $this, 'test_connection' ) );
        add_action( 'wp_ajax_ndnci_wpaa_clear_cache', array( $this, 'clear_cache' ) );
    }
    
    /**
     * Add settings menu page
     */
    public function add_menu_page() {
        add_options_page(
            __( 'Address Autocomplete Settings', 'wp-address-autocomplete' ),
            __( 'Address Autocomplete', 'wp-address-autocomplete' ),
            'manage_options',
            'ndnci-wpaa-settings',
            array( $this, 'render_settings_page' )
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        // Provider settings section
        add_settings_section(
            'ndnci_wpaa_provider_section',
            __( 'Provider Settings', 'wp-address-autocomplete' ),
            array( $this, 'render_provider_section' ),
            'ndnci-wpaa-settings'
        );
        
        register_setting( 'ndnci_wpaa_settings', 'ndnci_wpaa_provider', array(
            'type' => 'string',
            'default' => 'openstreetmap',
            'sanitize_callback' => 'sanitize_text_field',
        ) );
        
        add_settings_field(
            'ndnci_wpaa_provider',
            __( 'Map Provider', 'wp-address-autocomplete' ),
            array( $this, 'render_provider_field' ),
            'ndnci-wpaa-settings',
            'ndnci_wpaa_provider_section'
        );
        
        register_setting( 'ndnci_wpaa_settings', 'ndnci_wpaa_google_maps_api_key', array(
            'type' => 'string',
            'default' => '',
            'sanitize_callback' => 'sanitize_text_field',
        ) );
        
        add_settings_field(
            'ndnci_wpaa_google_maps_api_key',
            __( 'Google Maps API Key', 'wp-address-autocomplete' ),
            array( $this, 'render_google_api_key_field' ),
            'ndnci-wpaa-settings',
            'ndnci_wpaa_provider_section'
        );
        
        // Cache settings section
        add_settings_section(
            'ndnci_wpaa_cache_section',
            __( 'Cache Settings', 'wp-address-autocomplete' ),
            array( $this, 'render_cache_section' ),
            'ndnci-wpaa-settings'
        );
        
        register_setting( 'ndnci_wpaa_settings', 'ndnci_wpaa_cache_enabled', array(
            'type' => 'string',
            'default' => '1',
            'sanitize_callback' => 'sanitize_text_field',
        ) );
        
        add_settings_field(
            'ndnci_wpaa_cache_enabled',
            __( 'Enable Cache', 'wp-address-autocomplete' ),
            array( $this, 'render_cache_enabled_field' ),
            'ndnci-wpaa-settings',
            'ndnci_wpaa_cache_section'
        );
        
        register_setting( 'ndnci_wpaa_settings', 'ndnci_wpaa_cache_duration', array(
            'type' => 'integer',
            'default' => 86400,
            'sanitize_callback' => 'absint',
        ) );
        
        add_settings_field(
            'ndnci_wpaa_cache_duration',
            __( 'Cache Duration', 'wp-address-autocomplete' ),
            array( $this, 'render_cache_duration_field' ),
            'ndnci-wpaa-settings',
            'ndnci_wpaa_cache_section'
        );
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        
        // Handle settings update
        if ( isset( $_GET['settings-updated'] ) ) {
            add_settings_error(
                'ndnci_wpaa_messages',
                'ndnci_wpaa_message',
                __( 'Settings saved successfully.', 'wp-address-autocomplete' ),
                'success'
            );
        }
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            
            <div class="ndnci-wpaa-settings-header">
                <p><?php esc_html_e( 'Configure address autocomplete settings for your forms.', 'wp-address-autocomplete' ); ?></p>
                <p>
                    <strong><?php esc_html_e( 'Developed by:', 'wp-address-autocomplete' ); ?></strong>
                    <a href="https://www.ndnci.com" target="_blank" rel="noopener noreferrer">NDNCI</a>
                </p>
            </div>
            
            <?php settings_errors( 'ndnci_wpaa_messages' ); ?>
            
            <form action="options.php" method="post">
                <?php
                settings_fields( 'ndnci_wpaa_settings' );
                do_settings_sections( 'ndnci-wpaa-settings' );
                submit_button( __( 'Save Settings', 'wp-address-autocomplete' ) );
                ?>
            </form>
            
            <div class="ndnci-wpaa-tools">
                <h2><?php esc_html_e( 'Tools', 'wp-address-autocomplete' ); ?></h2>
                
                <p>
                    <button type="button" class="button" id="ndnci-wpaa-test-connection">
                        <?php esc_html_e( 'Test Provider Connection', 'wp-address-autocomplete' ); ?>
                    </button>
                    <span class="ndnci-wpaa-test-result"></span>
                </p>
                
                <p>
                    <button type="button" class="button" id="ndnci-wpaa-clear-cache">
                        <?php esc_html_e( 'Clear Cache', 'wp-address-autocomplete' ); ?>
                    </button>
                    <span class="ndnci-wpaa-cache-result"></span>
                </p>
            </div>
            
            <div class="ndnci-wpaa-info">
                <h2><?php esc_html_e( 'Supported Form Plugins', 'wp-address-autocomplete' ); ?></h2>
                <ul>
                    <li><?php esc_html_e( 'Contact Form 7', 'wp-address-autocomplete' ); ?></li>
                    <li><?php esc_html_e( 'WPForms', 'wp-address-autocomplete' ); ?></li>
                    <li><?php esc_html_e( 'Gravity Forms', 'wp-address-autocomplete' ); ?></li>
                </ul>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render provider section description
     */
    public function render_provider_section() {
        echo '<p>' . esc_html__( 'Choose your preferred geocoding provider and configure API keys if needed.', 'wp-address-autocomplete' ) . '</p>';
    }
    
    /**
     * Render provider field
     */
    public function render_provider_field() {
        $value = get_option( 'ndnci_wpaa_provider', 'openstreetmap' );
        $providers = NDNCI_WPAA_Provider_Factory::get_all_providers();
        ?>
        <select name="ndnci_wpaa_provider" id="ndnci_wpaa_provider">
            <?php foreach ( $providers as $provider_id => $provider ) : ?>
                <option value="<?php echo esc_attr( $provider_id ); ?>" <?php selected( $value, $provider_id ); ?>>
                    <?php echo esc_html( $provider->get_name() ); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <p class="description">
            <?php esc_html_e( 'OpenStreetMap is free and does not require an API key.', 'wp-address-autocomplete' ); ?>
        </p>
        <?php
    }
    
    /**
     * Render Google API key field
     */
    public function render_google_api_key_field() {
        $value = get_option( 'ndnci_wpaa_google_maps_api_key', '' );
        ?>
        <input type="text" name="ndnci_wpaa_google_maps_api_key" id="ndnci_wpaa_google_maps_api_key" 
               value="<?php echo esc_attr( $value ); ?>" class="regular-text">
        <p class="description">
            <?php
            printf(
                /* translators: %s: Google Cloud Console URL */
                esc_html__( 'Required only if using Google Maps. Get your API key from %s', 'wp-address-autocomplete' ),
                '<a href="https://console.cloud.google.com/" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Google Cloud Console', 'wp-address-autocomplete' ) . '</a>'
            );
            ?>
        </p>
        <?php
    }
    
    /**
     * Render cache section description
     */
    public function render_cache_section() {
        echo '<p>' . esc_html__( 'Caching helps reduce API calls and improve performance.', 'wp-address-autocomplete' ) . '</p>';
    }
    
    /**
     * Render cache enabled field
     */
    public function render_cache_enabled_field() {
        $value = get_option( 'ndnci_wpaa_cache_enabled', '1' );
        ?>
        <label>
            <input type="checkbox" name="ndnci_wpaa_cache_enabled" value="1" <?php checked( $value, '1' ); ?>>
            <?php esc_html_e( 'Enable caching of API responses', 'wp-address-autocomplete' ); ?>
        </label>
        <?php
    }
    
    /**
     * Render cache duration field
     */
    public function render_cache_duration_field() {
        $value = get_option( 'ndnci_wpaa_cache_duration', 86400 );
        ?>
        <input type="number" name="ndnci_wpaa_cache_duration" value="<?php echo esc_attr( $value ); ?>" 
               min="3600" step="3600" class="small-text">
        <span><?php esc_html_e( 'seconds', 'wp-address-autocomplete' ); ?></span>
        <p class="description">
            <?php esc_html_e( 'How long to cache API responses (3600 = 1 hour, 86400 = 24 hours)', 'wp-address-autocomplete' ); ?>
        </p>
        <?php
    }
    
    /**
     * Test provider connection (AJAX)
     */
    public function test_connection() {
        check_ajax_referer( 'ndnci_wpaa_admin_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied', 'wp-address-autocomplete' ) ) );
        }
        
        $provider = NDNCI_WPAA_Provider_Factory::get_provider();
        
        if ( ! $provider ) {
            wp_send_json_error( array( 'message' => __( 'Provider not found', 'wp-address-autocomplete' ) ) );
        }
        
        $result = $provider->validate();
        
        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }
        
        wp_send_json_success( array( 'message' => __( 'Connection successful!', 'wp-address-autocomplete' ) ) );
    }
    
    /**
     * Clear cache (AJAX)
     */
    public function clear_cache() {
        check_ajax_referer( 'ndnci_wpaa_admin_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied', 'wp-address-autocomplete' ) ) );
        }
        
        $count = NDNCI_WPAA_Cache::clear_all();
        
        wp_send_json_success(
            array(
                'message' => sprintf(
                    /* translators: %d: number of cache entries cleared */
                    _n( 'Cleared %d cache entry', 'Cleared %d cache entries', $count, 'wp-address-autocomplete' ),
                    $count
                ),
            )
        );
    }
}
