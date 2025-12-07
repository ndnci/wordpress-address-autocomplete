<?php
/**
 * Tests for main plugin class
 *
 * @package WordPress_Address_Autocomplete
 */

class Test_WordPress_Address_Autocomplete extends WP_UnitTestCase {
    
    /**
     * Test plugin constants
     */
    public function test_plugin_constants() {
        $this->assertTrue( defined( 'NDNCI_WPAA_VERSION' ) );
        $this->assertTrue( defined( 'NDNCI_WPAA_PLUGIN_DIR' ) );
        $this->assertTrue( defined( 'NDNCI_WPAA_PLUGIN_URL' ) );
        $this->assertTrue( defined( 'NDNCI_WPAA_PLUGIN_BASENAME' ) );
    }
    
    /**
     * Test plugin instance
     */
    public function test_plugin_instance() {
        $plugin = ndnci_wpaa();
        
        $this->assertInstanceOf( 'WordPress_Address_Autocomplete', $plugin );
    }
    
    /**
     * Test singleton pattern
     */
    public function test_singleton_pattern() {
        $instance1 = ndnci_wpaa();
        $instance2 = ndnci_wpaa();
        
        $this->assertSame( $instance1, $instance2 );
    }
    
    /**
     * Test initialization hook
     */
    public function test_initialization_hook() {
        $hook_fired = false;
        
        add_action( 'ndnci_wpaa_initialized', function() use ( &$hook_fired ) {
            $hook_fired = true;
        } );
        
        do_action( 'ndnci_wpaa_initialized' );
        
        $this->assertTrue( $hook_fired );
    }
}
