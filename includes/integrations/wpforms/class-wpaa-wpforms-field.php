<?php
/**
 * WPForms address autocomplete field
 *
 * @package WordPress_Address_Autocomplete
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * WPForms address autocomplete field class
 */
class NDNCI_WPAA_WPForms_Field extends WPForms_Field {
    
    /**
     * Constructor
     */
    public function init() {
        $this->name = __( 'Address Autocomplete', 'wp-address-autocomplete' );
        $this->type = 'address_autocomplete';
        $this->icon = 'fa-map-marker';
        $this->order = 50;
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
        $this->field_option( 'required', $field );
        $this->field_option( 'basic-options', $field, array( 'markup' => 'close' ) );
        
        // Advanced options
        $this->field_option( 'advanced-options', $field, array( 'markup' => 'open' ) );
        $this->field_option( 'placeholder', $field );
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
        $placeholder = ! empty( $field['placeholder'] ) ? esc_attr( $field['placeholder'] ) : '';
        $required = ! empty( $field['required'] ) ? ' <span class="required">*</span>' : '';
        
        $this->field_preview_option( 'label', $field );
        
        printf(
            '<input type="text" placeholder="%s" class="primary-input" disabled />',
            $placeholder
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
        $primary = $field['properties']['inputs']['primary'];
        $field_placeholder = ! empty( $field['placeholder'] ) ? esc_attr( $field['placeholder'] ) : '';
        $field_required = ! empty( $field['required'] ) ? ' required' : '';
        $field_class = implode( ' ', array_merge( $primary['class'], array( 'ndnci-wpaa-address-field' ) ) );
        
        printf(
            '<input type="text" %s %s autocomplete="off">',
            wpforms_html_attributes( $primary['id'], $field_class, $primary['data'], $primary['attr'] ),
            $field_placeholder ? 'placeholder="' . $field_placeholder . '"' : ''
        );
        
        printf(
            '<div class="ndnci-wpaa-suggestions"></div>'
        );
        
        printf(
            '<input type="hidden" name="wpforms[fields][%d][place_id]" class="ndnci-wpaa-place-id" />',
            absint( $field['id'] )
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
        $form_id = $form_data['id'];
        $field = $form_data['fields'][ $field_id ];
        $required = wpforms_get_required_label();
        
        // Basic required check
        if ( ! empty( $field['required'] ) && empty( $field_submit ) ) {
            wpforms()->process->errors[ $form_id ][ $field_id ] = $required;
        }
    }
    
    /**
     * Format field value
     *
     * @param int $field_id Field ID
     * @param array $field_submit Submitted field value
     * @param array $form_data Form data and settings
     */
    public function format( $field_id, $field_submit, $form_data ) {
        $field = $form_data['fields'][ $field_id ];
        $name = ! empty( $field['label'] ) ? $field['label'] : __( 'Address', 'wp-address-autocomplete' );
        
        wpforms()->process->fields[ $field_id ] = array(
            'name' => sanitize_text_field( $name ),
            'value' => sanitize_text_field( $field_submit ),
            'id' => absint( $field_id ),
            'type' => $this->type,
        );
    }
}

new NDNCI_WPAA_WPForms_Field();
