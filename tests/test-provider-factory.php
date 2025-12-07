<?php
/**
 * Tests for provider factory
 *
 * @package WordPress_Address_Autocomplete
 */

class Test_NDNCI_WPAA_Provider_Factory extends WP_UnitTestCase {
    
    /**
     * Test get provider
     */
    public function test_get_provider() {
        $provider = NDNCI_WPAA_Provider_Factory::get_provider( 'openstreetmap' );
        
        $this->assertInstanceOf( 'NDNCI_WPAA_Provider_Abstract', $provider );
        $this->assertEquals( 'openstreetmap', $provider->get_id() );
    }
    
    /**
     * Test get default provider
     */
    public function test_get_default_provider() {
        update_option( 'ndnci_wpaa_provider', 'openstreetmap' );
        
        $provider = NDNCI_WPAA_Provider_Factory::get_provider();
        
        $this->assertInstanceOf( 'NDNCI_WPAA_Provider_Abstract', $provider );
        $this->assertEquals( 'openstreetmap', $provider->get_id() );
    }
    
    /**
     * Test get all providers
     */
    public function test_get_all_providers() {
        $providers = NDNCI_WPAA_Provider_Factory::get_all_providers();
        
        $this->assertIsArray( $providers );
        $this->assertArrayHasKey( 'openstreetmap', $providers );
        $this->assertArrayHasKey( 'google-maps', $providers );
    }
    
    /**
     * Test get available provider IDs
     */
    public function test_get_available_provider_ids() {
        $ids = NDNCI_WPAA_Provider_Factory::get_available_provider_ids();
        
        $this->assertIsArray( $ids );
        $this->assertContains( 'openstreetmap', $ids );
        $this->assertContains( 'google-maps', $ids );
    }
    
    /**
     * Test invalid provider
     */
    public function test_invalid_provider() {
        $provider = NDNCI_WPAA_Provider_Factory::get_provider( 'invalid-provider' );
        
        $this->assertNull( $provider );
    }
}
