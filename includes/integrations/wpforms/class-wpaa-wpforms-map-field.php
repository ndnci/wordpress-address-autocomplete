<?php
/**
 * WPForms map field
 *
 * @package WordPress_Address_Autocomplete
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * WPForms map field class
 */
class NDNCI_WPAA_WPForms_Map_Field extends WPForms_Field {
    
    /**
     * Constructor
     */
    public function init() {
        $this->name = __( 'Address Map', 'wp-address-autocomplete' );
        $this->type = 'address_map';
        $this->icon = 'fa-map';
        $this->order = 51;
        $this->group = 'fancy';
    }
    
    /**
     * Field options
     *
     * @param array $field Field settings
     */
    public function field_options( $field ) {
        // Basic options
        $this->field_option( 'basic-options', $field, array( 'markup' => 'open' ) );
        $this->field_option( 'label', $field );
        $this->field_option( 'description', $field );
        
        // Address fields option
        $option_label = $this->field_element(
            'label',
            $field,
            array(
                'slug' => 'address_fields',
                'value' => __( 'Address Fields to Display', 'wp-address-autocomplete' ),
                'tooltip' => __( 'Comma-separated field IDs of address autocomplete fields', 'wp-address-autocomplete' ),
            ),
            false
        );
        
        $option_input = $this->field_element(
            'text',
            $field,
            array(
                'slug' => 'address_fields',
                'value' => ! empty( $field['address_fields'] ) ? esc_attr( $field['address_fields'] ) : '',
            ),
            false
        );
        
        $this->field_element(
            'row',
            $field,
            array(
                'slug' => 'address_fields',
                'content' => $option_label . $option_input,
            )
        );
        
        // Display mode option
        $option_label = $this->field_element(
            'label',
            $field,
            array(
                'slug' => 'display_mode',
                'value' => __( 'Display Mode', 'wp-address-autocomplete' ),
            ),
            false
        );
        
        $option_select = $this->field_element(
            'select',
            $field,
            array(
                'slug' => 'display_mode',
                'value' => ! empty( $field['display_mode'] ) ? esc_attr( $field['display_mode'] ) : 'markers',
                'options' => array(
                    'markers' => __( 'Markers', 'wp-address-autocomplete' ),
                    'route' => __( 'Route', 'wp-address-autocomplete' ),
                ),
            ),
            false
        );
        
        $this->field_element(
            'row',
            $field,
            array(
                'slug' => 'display_mode',
                'content' => $option_label . $option_select,
            )
        );
        
        // Map height option
        $option_label = $this->field_element(
            'label',
            $field,
            array(
                'slug' => 'map_height',
                'value' => __( 'Map Height', 'wp-address-autocomplete' ),
            ),
            false
        );
        
        $option_input = $this->field_element(
            'text',
            $field,
            array(
                'slug' => 'map_height',
                'value' => ! empty( $field['map_height'] ) ? esc_attr( $field['map_height'] ) : '400px',
            ),
            false
        );
        
        $this->field_element(
            'row',
            $field,
            array(
                'slug' => 'map_height',
                'content' => $option_label . $option_input,
            )
        );
        
        $this->field_option( 'basic-options', $field, array( 'markup' => 'close' ) );
        
        // Advanced options
        $this->field_option( 'advanced-options', $field, array( 'markup' => 'open' ) );
        $this->field_option( 'css', $field );
        $this->field_option( 'custom_css', $field );
        $this->field_option( 'advanced-options', $field, array( 'markup' => 'close' ) );
    }
    
    /**
     * Field preview
     *
     * @param array $field Field settings
     */
    public function field_preview( $field ) {
        $this->field_preview_option( 'label', $field );
        
        $height = ! empty( $field['map_height'] ) ? esc_attr( $field['map_height'] ) : '400px';
        
        printf(
            '<div style="height: %s; background: #f0f0f0; display: flex; align-items: center; justify-content: center; border: 1px solid #ddd;">%s</div>',
            $height,
            esc_html__( 'Map will be displayed here', 'wp-address-autocomplete' )
        );
        
        $this->field_preview_option( 'description', $field );
    }
    
    /**
     * Field display on the form front-end
     *
     * @param array $field Field settings
     * @param array $deprecated Deprecated parameter
     * @param array $form_data Form data and settings
     */
    public function field_display( $field, $deprecated, $form_data ) {
        $address_fields = ! empty( $field['address_fields'] ) ? esc_attr( $field['address_fields'] ) : '';
        $display_mode = ! empty( $field['display_mode'] ) ? esc_attr( $field['display_mode'] ) : 'markers';
        $height = ! empty( $field['map_height'] ) ? esc_attr( $field['map_height'] ) : '400px';
        
        printf(
            '<div class="ndnci-wpaa-map" data-fields="%s" data-mode="%s" style="height: %s;"></div>',
            $address_fields,
            $display_mode,
            $height
        );
    }
    
    /**
     * Validate field on form submit
     *
     * @param int $field_id Field ID
     * @param array $field_submit Submitted field value
     * @param array $form_data Form data and settings
     */
    public function validate( $field_id, $field_submit, $form_data ) {
        // Maps don't need validation
    }
    
    /**
     * Format field value
     *
     * @param int $field_id Field ID
     * @param array $field_submit Submitted field value
     * @param array $form_data Form data and settings
     */
    public function format( $field_id, $field_submit, $form_data ) {
        // Maps don't submit data
    }
}

new NDNCI_WPAA_WPForms_Map_Field();
