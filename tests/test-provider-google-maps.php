<?php
/**
 * Tests for Google Maps provider
 *
 * @package WordPress_Address_Autocomplete
 */

class Test_NDNCI_WPAA_Provider_Google_Maps extends WP_UnitTestCase {
    
    /**
     * Provider instance
     *
     * @var NDNCI_WPAA_Provider_Google_Maps
     */
    private $provider;
    
    /**
     * Setup test
     */
    public function setUp(): void {
        parent::setUp();
        $this->provider = new NDNCI_WPAA_Provider_Google_Maps();
    }
    
    /**
     * Test provider ID
     */
    public function test_provider_id() {
        $this->assertEquals( 'google-maps', $this->provider->get_id() );
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
        $this->assertTrue( $this->provider->requires_api_key() );
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
     * Test search without API key
     */
    public function test_search_without_api_key() {
        delete_option( 'ndnci_wpaa_google_maps_api_key' );
        
        $result = $this->provider->search( 'test address' );
        
        $this->assertWPError( $result );
        $this->assertEquals( 'missing_api_key', $result->get_error_code() );
    }
    
    /**
     * Test validation without API key
     */
    public function test_validation_without_api_key() {
        delete_option( 'ndnci_wpaa_google_maps_api_key' );
        
        $result = $this->provider->validate();
        
        $this->assertWPError( $result );
        $this->assertEquals( 'missing_api_key', $result->get_error_code() );
    }
    
    /**
     * Test map script URL
     */
    public function test_map_script_url_without_key() {
        delete_option( 'ndnci_wpaa_google_maps_api_key' );
        
        $url = $this->provider->get_map_script_url();
        
        $this->assertEmpty( $url );
    }
    
    /**
     * Test map script URL with key
     */
    public function test_map_script_url_with_key() {
        update_option( 'ndnci_wpaa_google_maps_api_key', 'test_key' );
        
        $url = $this->provider->get_map_script_url();
        
        $this->assertNotEmpty( $url );
        $this->assertStringContainsString( 'maps.googleapis.com', $url );
        $this->assertStringContainsString( 'test_key', $url );
        
        delete_option( 'ndnci_wpaa_google_maps_api_key' );
    }
}
