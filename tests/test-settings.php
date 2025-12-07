<?php
/**
 * Tests for settings
 *
 * @package WordPress_Address_Autocomplete
 */

class Test_NDNCI_WPAA_Settings extends WP_UnitTestCase {
    
    /**
     * Test default settings on activation
     */
    public function test_default_settings() {
        // Simulate activation
        do_action( 'ndnci_wpaa_activated' );
        
        $provider = get_option( 'ndnci_wpaa_provider' );
        $cache_enabled = get_option( 'ndnci_wpaa_cache_enabled' );
        $cache_duration = get_option( 'ndnci_wpaa_cache_duration' );
        
        $this->assertEquals( 'openstreetmap', $provider );
        $this->assertEquals( '1', $cache_enabled );
        $this->assertEquals( '86400', $cache_duration );
    }
    
    /**
     * Test settings instance
     */
    public function test_settings_instance() {
        $settings = NDNCI_WPAA_Settings::get_instance();
        
        $this->assertInstanceOf( 'NDNCI_WPAA_Settings', $settings );
    }
    
    /**
     * Test provider option
     */
    public function test_provider_option() {
        update_option( 'ndnci_wpaa_provider', 'google-maps' );
        
        $provider = get_option( 'ndnci_wpaa_provider' );
        $this->assertEquals( 'google-maps', $provider );
        
        // Reset to default
        update_option( 'ndnci_wpaa_provider', 'openstreetmap' );
    }
    
    /**
     * Test cache options
     */
    public function test_cache_options() {
        update_option( 'ndnci_wpaa_cache_enabled', '0' );
        update_option( 'ndnci_wpaa_cache_duration', '3600' );
        
        $cache_enabled = get_option( 'ndnci_wpaa_cache_enabled' );
        $cache_duration = get_option( 'ndnci_wpaa_cache_duration' );
        
        $this->assertEquals( '0', $cache_enabled );
        $this->assertEquals( '3600', $cache_duration );
        
        // Reset to defaults
        update_option( 'ndnci_wpaa_cache_enabled', '1' );
        update_option( 'ndnci_wpaa_cache_duration', '86400' );
    }
}
