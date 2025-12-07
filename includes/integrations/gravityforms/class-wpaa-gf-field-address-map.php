<?php
/**
 * Gravity Forms map field
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
 * Gravity Forms map field class
 */
class NDNCI_WPAA_GF_Field_Address_Map extends GF_Field {
    
    /**
     * Field type
     *
     * @var string
     */
    public $type = 'address_map';
    
    /**
     * Get form editor field title
     *
     * @return string
     */
    public function get_form_editor_field_title() {
        return esc_attr__( 'Address Map', 'wp-address-autocomplete' );
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
            'css_class_setting',
            'visibility_setting',
            'conditional_logic_field_setting',
            'ndnci_wpaa_address_fields_setting',
            'ndnci_wpaa_display_mode_setting',
            'ndnci_wpaa_map_height_setting',
        );
    }
    
    /**
     * Add custom field settings
     */
    public static function add_custom_settings() {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                // Add settings to fields
                fieldSettings.address_map = '.label_setting, .description_setting, .css_class_setting, .visibility_setting, .conditional_logic_field_setting, .wpaa_address_fields_setting, .wpaa_display_mode_setting, .wpaa_map_height_setting';
                
                // Bind to the load field settings event
                $(document).on('gform_load_field_settings', function(event, field, form) {
                    if (field.type === 'address_map') {
                        $('#wpaa_address_fields').val(field.wpaaAddressFields || '');
                        $('#wpaa_display_mode').val(field.wpaaDisplayMode || 'markers');
                        $('#wpaa_map_height').val(field.wpaaMapHeight || '400px');
                    }
                });
            });
        </script>
        
        <li class="ndnci_wpaa_address_fields_setting field_setting">
            <label for="ndnci_wpaa_address_fields" class="section_label">
                <?php esc_html_e( 'Address Fields to Display', 'wp-address-autocomplete' ); ?>
                <?php gform_tooltip( 'ndnci_wpaa_address_fields' ); ?>
            </label>
            <input type="text" id="ndnci_wpaa_address_fields" class="fieldwidth-3" 
                   onchange="SetFieldProperty('wpaaAddressFields', this.value);" />
            <p class="description"><?php esc_html_e( 'Comma-separated field IDs (e.g., 1,2,3)', 'wp-address-autocomplete' ); ?></p>
        </li>
        
        <li class="ndnci_wpaa_display_mode_setting field_setting">
            <label for="ndnci_wpaa_display_mode" class="section_label">
                <?php esc_html_e( 'Display Mode', 'wp-address-autocomplete' ); ?>
                <?php gform_tooltip( 'ndnci_wpaa_display_mode' ); ?>
            </label>
            <select id="ndnci_wpaa_display_mode" onchange="SetFieldProperty('wpaaDisplayMode', this.value);">
                <option value="markers"><?php esc_html_e( 'Markers', 'wp-address-autocomplete' ); ?></option>
                <option value="route"><?php esc_html_e( 'Route', 'wp-address-autocomplete' ); ?></option>
            </select>
        </li>
        
        <li class="ndnci_wpaa_map_height_setting field_setting">
            <label for="ndnci_wpaa_map_height" class="section_label">
                <?php esc_html_e( 'Map Height', 'wp-address-autocomplete' ); ?>
                <?php gform_tooltip( 'ndnci_wpaa_map_height' ); ?>
            </label>
            <input type="text" id="ndnci_wpaa_map_height" class="fieldwidth-2" 
                   onchange="SetFieldProperty('wpaaMapHeight', this.value);" />
            <p class="description"><?php esc_html_e( 'E.g., 400px or 50vh', 'wp-address-autocomplete' ); ?></p>
        </li>
        <?php
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
        
        $address_fields = isset( $this->wpaaAddressFields ) ? esc_attr( $this->wpaaAddressFields ) : '';
        $display_mode = isset( $this->wpaaDisplayMode ) ? esc_attr( $this->wpaaDisplayMode ) : 'markers';
        $map_height = isset( $this->wpaaMapHeight ) ? esc_attr( $this->wpaaMapHeight ) : '400px';
        
        if ( $is_form_editor ) {
            return sprintf(
                '<div class="ginput_container">
                    <div style="height: %s; background: #f0f0f0; display: flex; align-items: center; justify-content: center; border: 1px solid #ddd;">
                        %s
                    </div>
                </div>',
                $map_height,
                esc_html__( 'Map will be displayed here', 'wp-address-autocomplete' )
            );
        }
        
        return sprintf(
            '<div class="ginput_container">
                <div class="ndnci-wpaa-map" data-fields="%s" data-mode="%s" style="height: %s;"></div>
            </div>',
            $address_fields,
            $display_mode,
            $map_height
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
     * Skip field on entry detail page
     *
     * @return bool
     */
    public function get_value_entry_detail( $value, $currency = '', $use_text = false, $format = 'html', $media = 'screen' ) {
        return '';
    }
}

// Add custom settings to form editor
add_action( 'gform_field_standard_settings', array( 'NDNCI_WPAA_GF_Field_Address_Map', 'add_custom_settings' ), 10, 2 );
