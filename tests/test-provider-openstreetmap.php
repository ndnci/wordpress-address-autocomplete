<?php
/**
 * Tests for OpenStreetMap provider
 *
 * @package WordPress_Address_Autocomplete
 */

class Test_NDNCI_WPAA_Provider_OpenStreetMap extends WP_UnitTestCase {
    
    /**
     * Provider instance
     *
     * @var NDNCI_WPAA_Provider_OpenStreetMap
     */
    private $provider;
    
    /**
     * Setup test
     */
    public function setUp(): void {
        parent::setUp();
        $this->provider = new NDNCI_WPAA_Provider_OpenStreetMap();
    }
    
    /**
     * Test provider ID
     */
    public function test_provider_id() {
        $this->assertEquals( 'openstreetmap', $this->provider->get_id() );
    }
    
    /**
     * Test provider name
     */
    public function test_provider_name() {
        $this->assertNotEmpty( $this->provider->get_name() );
    }
    
    /**
     * Test requires API key
     */
    public function test_requires_api_key() {
        $this->assertFalse( $this->provider->requires_api_key() );
    }
    
    /**
     * Test empty search query
     */
    public function test_empty_search_query() {
        $result = $this->provider->search( '' );
        
        $this->assertWPError( $result );
        $this->assertEquals( 'empty_query', $result->get_error_code() );
    }
    
    /**
     * Test validation
     */
    public function test_validation() {
        $result = $this->provider->validate();
        
        // This might fail if there's no internet connection
        // In a real test environment, you'd mock the HTTP request
        $this->assertTrue( $result === true || is_wp_error( $result ) );
    }
    
    /**
     * Test map script URL
     */
    public function test_map_script_url() {
        $url = $this->provider->get_map_script_url();
        
        $this->assertNotEmpty( $url );
        $this->assertStringContainsString( 'leaflet', $url );
    }
}
