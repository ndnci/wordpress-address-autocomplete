<?php
/**
 * Gravity Forms address autocomplete field
 *
 * @package WordPress_Address_Autocomplete
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'GF_Field' ) ) {
    return;
}

/**
 * Gravity Forms address autocomplete field class
 */
class NDNCI_WPAA_GF_Field_Address_Autocomplete extends GF_Field {
    
    /**
     * Field type
     *
     * @var string
     */
    public $type = 'address_autocomplete';
    
    /**
     * Get form editor field title
     *
     * @return string
     */
    public function get_form_editor_field_title() {
        return esc_attr__( 'Address Autocomplete', 'wp-address-autocomplete' );
    }
    
    /**
     * Get form editor button
     *
     * @return array
     */
    public function get_form_editor_button() {
        return array(
            'group' => 'advanced_fields',
            'text' => $this->get_form_editor_field_title(),
        );
    }
    
    /**
     * Get field settings in form editor
     *
     * @return array
     */
    function get_form_editor_field_settings() {
        return array(
            'label_setting',
            'description_setting',
            'placeholder_setting',
            'css_class_setting',
            'required_setting',
            'visibility_setting',
            'prepopulate_field_setting',
            'conditional_logic_field_setting',
        );
    }
    
    /**
     * Whether this field expects an array value during submission
     *
     * @return bool
     */
    public function is_value_submission_array() {
        return false;
    }
    
    /**
     * Get field input
     *
     * @param array $form Current form
     * @param string $value Current field value
     * @param array $entry Current entry
     * @return string
     */
    public function get_field_input( $form, $value = '', $entry = null ) {
        $form_id = absint( $form['id'] );
        $is_entry_detail = $this->is_entry_detail();
        $is_form_editor = $this->is_form_editor();
        
        $id = $this->id;
        $field_id = $is_entry_detail || $is_form_editor || $form_id == 0 ? "input_$id" : 'input_' . $form_id . "_$id";
        
        $value = esc_attr( $value );
        
        $disabled_text = $is_form_editor ? 'disabled="disabled"' : '';
        $class_suffix = $is_entry_detail ? '_admin' : '';
        
        $tabindex = $this->get_tabindex();
        $placeholder = $this->get_input_placeholder_attribute();
        
        $required_attribute = $this->isRequired ? 'aria-required="true"' : '';
        $invalid_attribute = $this->failed_validation ? 'aria-invalid="true"' : 'aria-invalid="false"';
        
        $input = sprintf(
            '<div class="ginput_container ginput_container_text">
                <input name="input_%d" id="%s" type="text" value="%s" class="large wpaa-address-field%s" %s %s %s %s %s autocomplete="off" />
                <div class="wpaa-suggestions"></div>
                <input type="hidden" name="input_%d_place_id" class="wpaa-place-id" />
            </div>',
            $id,
            $field_id,
            $value,
            $class_suffix,
            $tabindex,
            $placeholder,
            $required_attribute,
            $invalid_attribute,
            $disabled_text,
            $id
        );
        
        return $input;
    }
    
    /**
     * Validate field value
     *
     * @param string $value Field value
     * @param array $form Current form
     */
    public function validate( $value, $form ) {
        if ( $this->isRequired && empty( $value ) ) {
            $this->failed_validation = true;
            if ( empty( $this->errorMessage ) ) {
                $this->errorMessage = esc_html__( 'This field is required.', 'wp-address-autocomplete' );
            }
        }
    }
    
    /**
     * Sanitize entry value before saving
     *
     * @param string $value Field value
     * @param array $form Current form
     * @param string $input_name Input name
     * @param int $lead_id Entry ID
     * @param array $lead Entry object
     * @return string
     */
    public function get_value_save_entry( $value, $form, $input_name, $lead_id, $lead ) {
        return sanitize_text_field( $value );
    }
}
