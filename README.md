# WordPress Address Autocomplete

A professional WordPress plugin that adds address autocomplete functionality with map support to popular form builders.

## 🌟 Features

-   **Multiple Provider Support**: Choose between OpenStreetMap (free, no API key) or Google Maps
-   **Form Builder Integration**: Works seamlessly with Contact Form 7, WPForms, and Gravity Forms
-   **Map Display**: Show selected addresses on interactive maps with markers or route display
-   **Smart Caching**: Reduces API calls and improves performance
-   **Fully Translatable**: Includes English and French translations out of the box
-   **Extensible Architecture**: Easy to add new providers and form integrations
-   **Custom Hooks**: Extensive hooks for developers to customize functionality

## 📋 Requirements

-   WordPress 5.8 or higher
-   PHP 7.4 or higher
-   At least one of the supported form plugins:
    -   Contact Form 7
    -   WPForms (Lite or Pro)
    -   Gravity Forms

## 🚀 Installation

1. Upload the `wordpress-address-autocomplete` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings > Address Autocomplete to configure your provider
4. If using Google Maps, add your API key in the settings

## ⚙️ Configuration

### Provider Settings

1. Navigate to **Settings > Address Autocomplete**
2. Choose your preferred map provider:
    - **OpenStreetMap** (recommended for most users - free, no API key required)
    - **Google Maps** (requires API key)
3. If using Google Maps:
    - Get an API key from [Google Cloud Console](https://console.cloud.google.com/)
    - Enable the following APIs:
        - Places API
        - Maps JavaScript API
        - Geocoding API
    - Enter the API key in the settings

### Cache Settings

-   **Enable Cache**: Toggle to enable/disable response caching
-   **Cache Duration**: Set how long to cache API responses (default: 24 hours)

## 📝 Usage

### Contact Form 7

1. Edit a Contact Form 7 form
2. Click the **Address Autocomplete** tag button
3. Configure the field (name, required, placeholder, etc.)
4. Click **Insert Tag**

To add a map:

1. Click the **Address Map** tag button
2. Specify which address fields to display (comma-separated field names)
3. Choose display mode (Markers or Route)
4. Set map height
5. Click **Insert Tag**

Example form code:

```
[address_autocomplete* address-1 placeholder "Enter your address"]

[address_autocomplete address-2 placeholder "Enter destination"]

[address_map map-1 fields:address-1,address-2 mode:route height:500px]
```

### WPForms

1. Edit a WPForms form
2. In the form builder, look for **Address Autocomplete** in the Fancy Fields section
3. Drag and drop the field into your form
4. Configure field settings (label, description, required, placeholder)

To add a map:

1. Drag the **Address Map** field into your form
2. In field settings:
    - **Address Fields to Display**: Enter comma-separated field IDs (e.g., 1,2,3)
    - **Display Mode**: Choose Markers or Route
    - **Map Height**: Set desired height (e.g., 400px)

### Gravity Forms

1. Edit a Gravity Forms form
2. Click **Add Fields** and look for **Address Autocomplete** in Advanced Fields
3. Drag the field into your form
4. Configure field settings in the right panel

To add a map:

1. Add the **Address Map** field from Advanced Fields
2. Configure settings:
    - **Address Fields to Display**: Enter comma-separated field IDs (e.g., 1,2,3)
    - **Display Mode**: Choose Markers or Route
    - **Map Height**: Set desired height

## 🎨 Display Modes

### Markers Mode

Shows individual markers for each address on the map. Users can click markers to see address details.

### Route Mode

Displays all addresses connected by a route line, perfect for showing directions or travel paths.

## 🔧 Developer Documentation

### Custom Hooks

#### Actions

**`wpaa_initialized`**
Fires after the plugin is fully initialized.

```php
add_action( 'wpaa_initialized', function() {
    // Your code here
});
```

**`wpaa_activated`**
Fires when the plugin is activated.

**`wpaa_deactivated`**
Fires when the plugin is deactivated.

**`wpaa_frontend_assets_enqueued`**
Fires after frontend assets are enqueued.

**`wpaa_admin_assets_enqueued`**
Fires after admin assets are enqueued.

**`wpaa_cache_cleared`**
Fires after cache is cleared.

```php
add_action( 'wpaa_cache_cleared', function( $count ) {
    // $count = number of deleted cache entries
}, 10, 1 );
```

**`wpaa_provider_error`**
Fires when a provider error is logged.

```php
add_action( 'wpaa_provider_error', function( $message, $context, $provider_id ) {
    // Log or handle errors
}, 10, 3 );
```

#### Filters

**`wpaa_cache_enabled`**
Filter whether cache is enabled.

```php
add_filter( 'wpaa_cache_enabled', function( $enabled ) {
    return true; // or false
});
```

**`wpaa_cache_duration`**
Filter cache duration in seconds.

```php
add_filter( 'wpaa_cache_duration', function( $duration ) {
    return 3600; // 1 hour
});
```

**`wpaa_search_query`**
Filter search query before processing.

```php
add_filter( 'wpaa_search_query', function( $query ) {
    return trim( $query );
});
```

**`wpaa_search_results`**
Filter search results before sending to frontend.

```php
add_filter( 'wpaa_search_results', function( $results, $query ) {
    // Modify results
    return $results;
}, 10, 2 );
```

**`wpaa_place_details`**
Filter place details before sending to frontend.

```php
add_filter( 'wpaa_place_details', function( $details, $place_id ) {
    // Modify details
    return $details;
}, 10, 2 );
```

### Adding a Custom Provider

1. Create a new provider class extending `WPAA_Provider_Abstract`:

```php
class WPAA_Provider_Custom extends WPAA_Provider_Abstract {

    public function __construct() {
        $this->id = 'custom-provider';
        $this->name = __( 'Custom Provider', 'your-textdomain' );
        $this->requires_api_key = true; // or false
    }

    public function search( $query ) {
        // Implement search logic
    }

    public function get_place_details( $place_id ) {
        // Implement place details logic
    }

    public function validate() {
        // Implement validation logic
    }

    public function get_map_script_url() {
        // Return map script URL
    }
}
```

2. Register your provider:

```php
add_filter( 'wpaa_available_providers', function( $providers ) {
    $providers[] = 'custom-provider';
    return $providers;
});

add_filter( 'wpaa_provider_class_map', function( $class_map ) {
    $class_map['custom-provider'] = 'WPAA_Provider_Custom';
    return $class_map;
});
```

### Adding a Custom Form Integration

1. Create a new integration class extending `WPAA_Form_Integration_Abstract`:

```php
class WPAA_Custom_Form extends WPAA_Form_Integration_Abstract {

    private function __construct() {
        $this->id = 'custom-form';
        $this->name = 'Custom Form';
        $this->plugin_file = 'custom-form/custom-form.php';
        $this->init();
    }

    public function init() {
        if ( ! $this->is_plugin_active() ) {
            return;
        }
        // Add hooks
    }

    protected function register_field_type() {
        // Register your field type
    }

    public function render_field( $field, $args = array() ) {
        // Render field HTML
    }
}
```

2. Initialize your integration in the main plugin file.

## 🌍 Internationalization

The plugin is fully translatable and includes:

-   English (default)
-   French

To add a new language:

1. Copy `languages/wp-address-autocomplete.pot`
2. Translate using Poedit or similar tool
3. Save as `wp-address-autocomplete-{locale}.po` and generate `.mo` file
4. Place in the `languages/` folder

## 🧪 Testing

Run PHPUnit tests:

```bash
cd /path/to/plugin
composer install
./vendor/bin/phpunit
```

## 📄 License

This plugin is licensed under the GPL v2 or later.

## 👨‍💻 Author

**NDNCI**

-   Website: [www.ndnci.com](https://www.ndnci.com)

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📞 Support

For support, please visit [www.ndnci.com](https://www.ndnci.com) or open an issue on GitHub.

## 📝 Changelog

### 1.0.0

-   Initial release
-   Support for OpenStreetMap and Google Maps
-   Integration with Contact Form 7, WPForms, and Gravity Forms
-   Map display with markers and route modes
-   Caching system
-   English and French translations
-   Extensive developer hooks

## 🎯 Roadmap

-   [ ] Support for additional form builders (Elementor Forms, Formidable Forms)
-   [ ] More map providers (Mapbox, HERE Maps)
-   [ ] Geolocation support
-   [ ] Address validation
-   [ ] Custom styling options for maps
-   [ ] Bulk geocoding for existing data

## ⚡ Performance

-   Smart caching reduces API calls
-   Debounced search input (500ms delay)
-   Lazy loading of map libraries
-   Minified assets for production

## 🔒 Security

-   Nonce verification on all AJAX requests
-   Input sanitization and validation
-   Secure API key storage
-   No sensitive data logged in production

## 🌟 Features in Detail

### Caching System

-   Reduces API costs and improves performance
-   Configurable cache duration
-   Automatic cache invalidation
-   One-click cache clearing from admin

### Map Features

-   Interactive markers with info windows
-   Route display between multiple addresses
-   Auto-fit bounds to show all markers
-   Responsive design
-   Support for custom map heights

### Form Integration

-   Respects each form builder's UI/UX
-   Follows WordPress coding standards
-   Compatible with form validation
-   Works with conditional logic
-   AJAX-powered for smooth UX

## 💡 Tips

1. **Use OpenStreetMap for development** - No API key required, perfect for testing
2. **Enable caching in production** - Significantly reduces API costs
3. **Set appropriate cache duration** - Balance between freshness and performance
4. **Test API connections** - Use the built-in connection tester in settings
5. **Monitor API usage** - Keep track of your API quotas if using Google Maps

## 🐛 Troubleshooting

**Autocomplete not working?**

-   Check that JavaScript is enabled
-   Verify provider settings are correct
-   Test the provider connection in settings
-   Check browser console for errors

**Map not displaying?**

-   Ensure map script is loading correctly
-   Verify field IDs are correct in map settings
-   Check that addresses are selected before map loads
-   Try clearing cache

**Google Maps API errors?**

-   Verify API key is correct
-   Check that required APIs are enabled in Google Cloud Console
-   Ensure billing is set up (Google requires it even for free tier)
-   Check API key restrictions

## 📚 Additional Resources

-   [WordPress Plugin Development Handbook](https://developer.wordpress.org/plugins/)
-   [OpenStreetMap Nominatim Documentation](https://nominatim.org/release-docs/latest/)
-   [Google Maps Places API Documentation](https://developers.google.com/maps/documentation/places/web-service)
