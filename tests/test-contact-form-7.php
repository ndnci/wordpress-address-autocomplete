<?php
/**
 * Tests for Contact Form 7 integration
 *
 * @package WordPress_Address_Autocomplete
 */

class Test_Contact_Form_7_Integration extends WP_UnitTestCase {
    
    /**
     * Instance of Contact Form 7 integration
     *
     * @var NDNCI_WPAA_Contact_Form_7
     */
    private $cf7_integration;
    
    /**
     * Set up
     */
    public function setUp(): void {
        parent::setUp();
        
        // Only run tests if Contact Form 7 is active
        if ( ! class_exists( 'WPCF7_ContactForm' ) ) {
            $this->markTestSkipped( 'Contact Form 7 is not active' );
        }
        
        $this->cf7_integration = new NDNCI_WPAA_Contact_Form_7();
    }
    
    /**
     * Test address autocomplete field rendering
     */
    public function test_address_autocomplete_field_rendering() {
        $tag = new WPCF7_FormTag( array(
            'type' => 'address_autocomplete',
            'name' => 'test-address',
            'options' => array(),
            'values' => array(),
        ) );
        
        $output = $this->cf7_integration->render_field( $tag );
        
        // Check that output contains expected elements
        $this->assertStringContainsString( 'ndnci-wpaa-address-field', $output );
        $this->assertStringContainsString( 'ndnci-wpaa-suggestions', $output );
        $this->assertStringContainsString( 'ndnci-wpaa-place-id', $output );
        $this->assertStringContainsString( 'name="test-address"', $output );
    }
    
    /**
     * Test address map field rendering with default options
     */
    public function test_address_map_field_rendering_default() {
        $tag = new WPCF7_FormTag( array(
            'type' => 'address_map',
            'name' => 'test-map',
            'options' => array(),
            'values' => array(),
        ) );
        
        $output = $this->cf7_integration->render_map_field( $tag );
        
        // Check that output contains expected elements
        $this->assertStringContainsString( 'ndnci-wpaa-map', $output );
        $this->assertStringContainsString( 'data-mode="markers"', $output );
        $this->assertStringContainsString( 'height: 400px', $output );
        $this->assertStringContainsString( 'width: 100%', $output );
    }
    
    /**
     * Test address map field rendering with custom options
     */
    public function test_address_map_field_rendering_custom_options() {
        $tag = new WPCF7_FormTag( array(
            'type' => 'address_map',
            'name' => 'test-map',
            'options' => array(
                'fields:address-1,address-2',
                'mode:route',
                'height:500px',
                'width:600px',
            ),
            'values' => array(),
        ) );
        
        $output = $this->cf7_integration->render_map_field( $tag );
        
        // Check fields option
        $this->assertStringContainsString( 'data-fields="address-1,address-2"', $output );
        
        // Check mode option
        $this->assertStringContainsString( 'data-mode="route"', $output );
        
        // Check height option
        $this->assertStringContainsString( 'height: 500px', $output );
        
        // Check width option
        $this->assertStringContainsString( 'width: 600px', $output );
    }
    
    /**
     * Test address map field with various height formats
     */
    public function test_address_map_field_height_formats() {
        // Test with px
        $tag = new WPCF7_FormTag( array(
            'type' => 'address_map',
            'name' => 'test-map',
            'options' => array( 'height:350px' ),
            'values' => array(),
        ) );
        
        $output = $this->cf7_integration->render_map_field( $tag );
        $this->assertStringContainsString( 'height: 350px', $output );
        
        // Test with vh
        $tag = new WPCF7_FormTag( array(
            'type' => 'address_map',
            'name' => 'test-map',
            'options' => array( 'height:50vh' ),
            'values' => array(),
        ) );
        
        $output = $this->cf7_integration->render_map_field( $tag );
        $this->assertStringContainsString( 'height: 50vh', $output );
        
        // Test with percentage
        $tag = new WPCF7_FormTag( array(
            'type' => 'address_map',
            'name' => 'test-map',
            'options' => array( 'height:100%' ),
            'values' => array(),
        ) );
        
        $output = $this->cf7_integration->render_map_field( $tag );
        $this->assertStringContainsString( 'height: 100%', $output );
    }
    
    /**
     * Test address map field with various width formats
     */
    public function test_address_map_field_width_formats() {
        // Test with px
        $tag = new WPCF7_FormTag( array(
            'type' => 'address_map',
            'name' => 'test-map',
            'options' => array( 'width:800px' ),
            'values' => array(),
        ) );
        
        $output = $this->cf7_integration->render_map_field( $tag );
        $this->assertStringContainsString( 'width: 800px', $output );
        
        // Test with percentage
        $tag = new WPCF7_FormTag( array(
            'type' => 'address_map',
            'name' => 'test-map',
            'options' => array( 'width:80%' ),
            'values' => array(),
        ) );
        
        $output = $this->cf7_integration->render_map_field( $tag );
        $this->assertStringContainsString( 'width: 80%', $output );
    }
    
    /**
     * Test address map field with multiple fields
     */
    public function test_address_map_field_multiple_fields() {
        $tag = new WPCF7_FormTag( array(
            'type' => 'address_map',
            'name' => 'test-map',
            'options' => array(
                'fields:address-1,address-2,address-3',
            ),
            'values' => array(),
        ) );
        
        $output = $this->cf7_integration->render_map_field( $tag );
        $this->assertStringContainsString( 'data-fields="address-1,address-2,address-3"', $output );
    }
    
    /**
     * Test that map field has unique ID
     */
    public function test_address_map_field_unique_id() {
        $tag1 = new WPCF7_FormTag( array(
            'type' => 'address_map',
            'name' => 'test-map-1',
            'options' => array(),
            'values' => array(),
        ) );
        
        $tag2 = new WPCF7_FormTag( array(
            'type' => 'address_map',
            'name' => 'test-map-2',
            'options' => array(),
            'values' => array(),
        ) );
        
        $output1 = $this->cf7_integration->render_map_field( $tag1 );
        $output2 = $this->cf7_integration->render_map_field( $tag2 );
        
        // Extract IDs from outputs
        preg_match( '/id="([^"]+)"/', $output1, $matches1 );
        preg_match( '/id="([^"]+)"/', $output2, $matches2 );
        
        // Check that IDs exist
        $this->assertNotEmpty( $matches1[1] );
        $this->assertNotEmpty( $matches2[1] );
        
        // Check that IDs are different
        $this->assertNotEquals( $matches1[1], $matches2[1] );
    }
    
    /**
     * Test real-world shortcode example from documentation
     */
    public function test_documentation_example_shortcode() {
        // This tests the exact example from the issue:
        // [address_map address_map-161 fields:address_autocomplete-1,address_autocomplete-2 mode:markers height:500px width:500px]
        
        $tag = new WPCF7_FormTag( array(
            'type' => 'address_map',
            'name' => 'address_map-161',
            'options' => array(
                'fields:address_autocomplete-1,address_autocomplete-2',
                'mode:markers',
                'height:500px',
                'width:500px',
            ),
            'values' => array(),
        ) );
        
        $output = $this->cf7_integration->render_map_field( $tag );
        
        // Check all expected attributes
        $this->assertStringContainsString( 'class="ndnci-wpaa-map', $output );
        $this->assertStringContainsString( 'data-fields="address_autocomplete-1,address_autocomplete-2"', $output );
        $this->assertStringContainsString( 'data-mode="markers"', $output );
        $this->assertStringContainsString( 'height: 500px', $output );
        $this->assertStringContainsString( 'width: 500px', $output );
        
        // Ensure no empty data-fields attribute
        $this->assertStringNotContainsString( 'data-fields=""', $output );
    }
}
