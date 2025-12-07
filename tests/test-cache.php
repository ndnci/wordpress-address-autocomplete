<?php
/**
 * Tests for cache functionality
 *
 * @package WordPress_Address_Autocomplete
 */

class Test_NDNCI_WPAA_Cache extends WP_UnitTestCase {
    
    /**
     * Test cache set and get
     */
    public function test_cache_set_and_get() {
        $key = 'test_key';
        $data = array( 'test' => 'data' );
        
        // Set cache
        $result = NDNCI_WPAA_Cache::set( $key, $data );
        $this->assertTrue( $result );
        
        // Get cache
        $cached = NDNCI_WPAA_Cache::get( $key );
        $this->assertEquals( $data, $cached );
    }
    
    /**
     * Test cache delete
     */
    public function test_cache_delete() {
        $key = 'test_key_delete';
        $data = array( 'test' => 'data' );
        
        // Set cache
        NDNCI_WPAA_Cache::set( $key, $data );
        
        // Delete cache
        $result = NDNCI_WPAA_Cache::delete( $key );
        $this->assertTrue( $result );
        
        // Verify deleted
        $cached = NDNCI_WPAA_Cache::get( $key );
        $this->assertFalse( $cached );
    }
    
    /**
     * Test cache clear all
     */
    public function test_cache_clear_all() {
        // Set multiple cache entries
        NDNCI_WPAA_Cache::set( 'key1', 'data1' );
        NDNCI_WPAA_Cache::set( 'key2', 'data2' );
        
        // Clear all
        $count = NDNCI_WPAA_Cache::clear_all();
        $this->assertGreaterThanOrEqual( 2, $count );
        
        // Verify all cleared
        $this->assertFalse( NDNCI_WPAA_Cache::get( 'key1' ) );
        $this->assertFalse( NDNCI_WPAA_Cache::get( 'key2' ) );
    }
    
    /**
     * Test cache when disabled
     */
    public function test_cache_when_disabled() {
        update_option( 'ndnci_wpaa_cache_enabled', '0' );
        
        $key = 'test_key_disabled';
        $data = array( 'test' => 'data' );
        
        // Try to set cache
        $result = NDNCI_WPAA_Cache::set( $key, $data );
        $this->assertFalse( $result );
        
        // Try to get cache
        $cached = NDNCI_WPAA_Cache::get( $key );
        $this->assertFalse( $cached );
        
        // Re-enable cache
        update_option( 'ndnci_wpaa_cache_enabled', '1' );
    }
}
