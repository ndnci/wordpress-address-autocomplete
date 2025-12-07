<?php
/**
 * Contact Form 7 integration
 *
 * @package WordPress_Address_Autocomplete
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Contact Form 7 integration class
 */
class NDNCI_WPAA_Contact_Form_7 extends NDNCI_WPAA_Form_Integration_Abstract {
    
    /**
     * Single instance
     *
     * @var NDNCI_WPAA_Contact_Form_7
     */
    private static $instance = null;
    
    /**
     * Get instance
     *
     * @return NDNCI_WPAA_Contact_Form_7
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->id = 'contact-form-7';
        $this->name = 'Contact Form 7';
        $this->plugin_file = 'contact-form-7/wp-contact-form-7.php';
        
        $this->init();
    }
    
    /**
     * Initialize integration
     */
    public function init() {
        if ( ! $this->is_plugin_active() ) {
            return;
        }
        
        add_action( 'wpcf7_init', array( $this, 'register_field_type' ) );
        add_action( 'wpcf7_admin_init', array( $this, 'add_tag_generator' ), 20 );
        add_filter( 'wpcf7_validate_address_autocomplete', array( $this, 'validate_field' ), 10, 2 );
        add_filter( 'wpcf7_validate_address_autocomplete*', array( $this, 'validate_field' ), 10, 2 );
        
        $this->log( 'Contact Form 7 integration initialized' );
    }
    
    /**
     * Register field type
     */
    public function register_field_type() {
        wpcf7_add_form_tag(
            array( 'address_autocomplete', 'address_autocomplete*' ),
            array( $this, 'render_field' ),
            array(
                'name-attr' => true,
            )
        );
        
        // Register map field
        wpcf7_add_form_tag(
            'address_map',
            array( $this, 'render_map_field' ),
            array(
                'name-attr' => false,
            )
        );
    }
    
    /**
     * Render field on frontend
     *
     * @param WPCF7_FormTag $tag Form tag object
     * @return string Field HTML
     */
    public function render_field( $tag, $args = array() ) {
        if ( empty( $tag->name ) ) {
            return '';
        }
        
        $validation_error = wpcf7_get_validation_error( $tag->name );
        
        $class = wpcf7_form_controls_class( $tag->type );
        
        if ( $validation_error ) {
            $class .= ' wpcf7-not-valid';
        }
        
        $atts = array();
        $atts['class'] = $tag->get_class_option( $class . ' ndnci-wpaa-address-field' );
        $atts['id'] = $tag->get_id_option();
        $atts['tabindex'] = $tag->get_option( 'tabindex', 'signed_int', true );
        $atts['autocomplete'] = 'off';
        $atts['aria-required'] = $tag->is_required() ? 'true' : 'false';
        $atts['aria-invalid'] = $validation_error ? 'true' : 'false';
        $atts['type'] = 'text';
        $atts['name'] = $tag->name;
        
        $value = (string) reset( $tag->values );
        
        if ( $tag->has_option( 'placeholder' ) || $value !== '' ) {
            $atts['placeholder'] = $value;
        }
        
        $value = isset( $_POST[ $tag->name ] ) ? wp_unslash( $_POST[ $tag->name ] ) : '';
        $atts['value'] = $value;
        
        $atts = wpcf7_format_atts( $atts );
        
        $html = sprintf(
            '<span class="ndnci-wpaa-field-wrapper"><span class="wpcf7-form-control-wrap %1$s"><input %2$s />%3$s</span><div class="ndnci-wpaa-suggestions"></div><input type="hidden" name="%1$s_place_id" class="ndnci-wpaa-place-id" /></span>',
            sanitize_html_class( $tag->name ),
            $atts,
            $validation_error
        );
        
        return $html;
    }
    
    /**
     * Render map field
     *
     * @param WPCF7_FormTag $tag Form tag object
     * @return string Map HTML
     */
    public function render_map_field( $tag ) {
        if ( empty( $tag->name ) ) {
            return '';
        }
        
        // Get fields option (format: fields:field1,field2)
        $fields_option = $tag->get_option( 'fields' );
        $fields_attr = '';
        if ( ! empty( $fields_option ) && is_array( $fields_option ) ) {
            // get_option returns array like ['fields:field1,field2']
            $fields_value = reset( $fields_option );
            if ( strpos( $fields_value, ':' ) !== false ) {
                $fields_attr = substr( $fields_value, strpos( $fields_value, ':' ) + 1 );
            }
        }
        
        // Get mode option (format: mode:markers or mode:route)
        $mode_option = $tag->get_option( 'mode' );
        $display_mode = 'markers';
        if ( ! empty( $mode_option ) && is_array( $mode_option ) ) {
            $mode_value = reset( $mode_option );
            if ( strpos( $mode_value, ':' ) !== false ) {
                $display_mode = substr( $mode_value, strpos( $mode_value, ':' ) + 1 );
            }
        }
        
        // Get height option (format: height:400px)
        $height_option = $tag->get_option( 'height' );
        $height = '400px';
        if ( ! empty( $height_option ) && is_array( $height_option ) ) {
            $height_value = reset( $height_option );
            if ( strpos( $height_value, ':' ) !== false ) {
                $height = substr( $height_value, strpos( $height_value, ':' ) + 1 );
            }
        }
        
        $atts = array(
            'class' => 'ndnci-wpaa-map',
            'id' => $tag->get_id_option(),
            'data-fields' => $fields_attr,
            'data-mode' => $display_mode,
            'style' => 'height: ' . esc_attr( $height ),
        );
        
        $atts = wpcf7_format_atts( $atts );
        
        return sprintf( '<div %s></div>', $atts );
    }
    
    /**
     * Add tag generator
     */
    public function add_tag_generator() {
        if ( ! class_exists( 'WPCF7_TagGenerator' ) ) {
            return;
        }
        
        $tag_generator = WPCF7_TagGenerator::get_instance();
        
        $tag_generator->add(
            'address_autocomplete',
            __( 'Address Autocomplete', 'wp-address-autocomplete' ),
            array( $this, 'tag_generator_dialog' ),
            array( 'version' => '2' )
        );
        
        $tag_generator->add(
            'address_map',
            __( 'Address Map', 'wp-address-autocomplete' ),
            array( $this, 'tag_generator_map_dialog' ),
            array( 'version' => '2' )
        );
    }
    
    /**
     * Tag generator dialog
     *
     * @param WPCF7_ContactForm $contact_form Contact form object
     * @param array $args Arguments
     */
    public function tag_generator_dialog( $contact_form, $args = '' ) {
        $args = wp_parse_args( $args, array() );
        ?>
        <div class="control-box">
            <fieldset>
                <legend><?php esc_html_e( 'Generate an address autocomplete field', 'wp-address-autocomplete' ); ?></legend>
                
                <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label for="<?php echo esc_attr( $args['content'] . '-name' ); ?>">
                                    <?php esc_html_e( 'Name', 'wp-address-autocomplete' ); ?>
                                </label>
                            </th>
                            <td>
                                <input type="text" name="name" class="tg-name oneline" 
                                       id="<?php echo esc_attr( $args['content'] . '-name' ); ?>" />
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <?php esc_html_e( 'Field type', 'wp-address-autocomplete' ); ?>
                            </th>
                            <td>
                                <fieldset>
                                    <legend class="screen-reader-text">
                                        <?php esc_html_e( 'Field type', 'wp-address-autocomplete' ); ?>
                                    </legend>
                                    <label>
                                        <input type="checkbox" name="required" /> 
                                        <?php esc_html_e( 'Required field', 'wp-address-autocomplete' ); ?>
                                    </label>
                                </fieldset>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="<?php echo esc_attr( $args['content'] . '-values' ); ?>">
                                    <?php esc_html_e( 'Placeholder text', 'wp-address-autocomplete' ); ?>
                                </label>
                            </th>
                            <td>
                                <input type="text" name="values" class="oneline" 
                                       id="<?php echo esc_attr( $args['content'] . '-values' ); ?>" />
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="<?php echo esc_attr( $args['content'] . '-id' ); ?>">
                                    <?php esc_html_e( 'Id attribute', 'wp-address-autocomplete' ); ?>
                                </label>
                            </th>
                            <td>
                                <input type="text" name="id" class="idvalue oneline option" 
                                       id="<?php echo esc_attr( $args['content'] . '-id' ); ?>" />
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="<?php echo esc_attr( $args['content'] . '-class' ); ?>">
                                    <?php esc_html_e( 'Class attribute', 'wp-address-autocomplete' ); ?>
                                </label>
                            </th>
                            <td>
                                <input type="text" name="class" class="classvalue oneline option" 
                                       id="<?php echo esc_attr( $args['content'] . '-class' ); ?>" />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </fieldset>
        </div>
        
        <div class="insert-box">
            <input type="text" name="address_autocomplete" class="tag code" readonly="readonly" onfocus="this.select()" />
            
            <div class="submitbox">
                <input type="button" class="button button-primary insert-tag" 
                       value="<?php esc_attr_e( 'Insert Tag', 'wp-address-autocomplete' ); ?>" />
            </div>
        </div>
        <?php
    }
    
    /**
     * Tag generator map dialog
     *
     * @param WPCF7_ContactForm $contact_form Contact form object
     * @param array $args Arguments
     */
    public function tag_generator_map_dialog( $contact_form, $args = '' ) {
        $args = wp_parse_args( $args, array() );
        ?>
        <div class="control-box">
            <fieldset>
                <legend><?php esc_html_e( 'Generate a map to display addresses', 'wp-address-autocomplete' ); ?></legend>
                
                <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label for="<?php echo esc_attr( $args['content'] . '-name' ); ?>">
                                    <?php esc_html_e( 'Name', 'wp-address-autocomplete' ); ?>
                                </label>
                            </th>
                            <td>
                                <input type="text" name="name" class="tg-name oneline" 
                                       id="<?php echo esc_attr( $args['content'] . '-name' ); ?>" />
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="<?php echo esc_attr( $args['content'] . '-fields' ); ?>">
                                    <?php esc_html_e( 'Address fields to display', 'wp-address-autocomplete' ); ?>
                                </label>
                            </th>
                            <td>
                                <input type="text" name="fields" class="oneline option" 
                                       id="<?php echo esc_attr( $args['content'] . '-fields' ); ?>" />
                                <p class="description">
                                    <?php esc_html_e( 'Comma-separated field names (e.g., address-1,address-2)', 'wp-address-autocomplete' ); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="<?php echo esc_attr( $args['content'] . '-mode' ); ?>">
                                    <?php esc_html_e( 'Display mode', 'wp-address-autocomplete' ); ?>
                                </label>
                            </th>
                            <td>
                                <select name="mode" class="option" id="<?php echo esc_attr( $args['content'] . '-mode' ); ?>">
                                    <option value="markers"><?php esc_html_e( 'Markers', 'wp-address-autocomplete' ); ?></option>
                                    <option value="route"><?php esc_html_e( 'Route', 'wp-address-autocomplete' ); ?></option>
                                </select>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="<?php echo esc_attr( $args['content'] . '-height' ); ?>">
                                    <?php esc_html_e( 'Map height', 'wp-address-autocomplete' ); ?>
                                </label>
                            </th>
                            <td>
                                <input type="text" name="height" class="oneline option" 
                                       id="<?php echo esc_attr( $args['content'] . '-height' ); ?>" 
                                       value="400px" />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </fieldset>
        </div>
        
        <div class="insert-box">
            <input type="text" name="address_map" class="tag code" readonly="readonly" onfocus="this.select()" />
            
            <div class="submitbox">
                <input type="button" class="button button-primary insert-tag" 
                       value="<?php esc_attr_e( 'Insert Tag', 'wp-address-autocomplete' ); ?>" />
            </div>
        </div>
        <?php
    }
    
    /**
     * Validate field
     *
     * @param WPCF7_Validation $result Validation result
     * @param WPCF7_FormTag $tag Form tag
     * @return WPCF7_Validation
     */
    public function validate_field( $result, $tag ) {
        $name = $tag->name;
        
        if ( isset( $_POST[ $name ] ) ) {
            $value = trim( wp_unslash( strtr( (string) $_POST[ $name ], "\n", ' ' ) ) );
        } else {
            $value = '';
        }
        
        if ( $tag->is_required() && '' === $value ) {
            $result->invalidate( $tag, wpcf7_get_message( 'invalid_required' ) );
        }
        
        return $result;
    }
}
