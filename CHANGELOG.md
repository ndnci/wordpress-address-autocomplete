# Changelog

All notable changes to WordPress Address Autocomplete will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.1] - 2025-12-07

### Fixed

-   Contact Form 7 integration: Fixed shortcodes not being rendered on front-end. Changed plugin initialization from `init` hook to `plugins_loaded` (priority 5) to ensure form tags are registered before Contact Form 7 processes them.
-   Fixed fatal error "cannot access protected method register_field_type()". Changed visibility of `register_field_type()` from `protected` to `public` in all form integrations (CF7, WPForms, Gravity Forms) since these methods are used as WordPress hook callbacks.
-   Fixed JavaScript error "ndnciWpaaData is not defined". Corrected the localized script variable name from `wpaaData` to `ndnciWpaaData` to match frontend.js expectations.
-   Fixed Contact Form 7 `address_map` shortcode not rendering. Corrected the parsing of CF7 options (fields, mode, height) and set `name-attr` to `false` for map field registration.
-   Fixed autocomplete suggestions not displaying. Restructured HTML with wrapper element and adjusted JavaScript selectors to properly locate suggestion containers across different form builders.
-   Fixed map not displaying. Changed map CSS class from `wpaa-map` to `ndnci-wpaa-map` to match JavaScript selectors.
-   Fixed deprecated Contact Form 7 tag generator warning. Updated to use version 2 API with proper options parameter.

### Improved

-   **Performance optimization**: Removed unnecessary AJAX call when selecting autocomplete suggestion. Address data (location, address components) is now stored directly in suggestion data attributes and retrieved instantly on selection.
-   **Contact Form 7 map shortcode**: Fixed `width` and `height` parameters not being applied. The shortcode now correctly respects both parameters (e.g., `[address_map map-1 fields:address-1 height:500px width:600px]`).

### Added

-   Added comprehensive unit tests for Contact Form 7 integration to verify shortcode rendering and parameter handling.

## [1.0.0] - 2025-12-07

### Added

-   Initial release of WordPress Address Autocomplete
-   Support for OpenStreetMap (Nominatim) provider - free, no API key required
-   Support for Google Maps provider with API key
-   Integration with Contact Form 7
-   Integration with WPForms (Lite and Pro)
-   Integration with Gravity Forms
-   Address autocomplete field with dropdown suggestions
-   Interactive map display with markers mode
-   Interactive map display with route mode
-   Smart caching system to reduce API calls
-   Cache management tools in admin settings
-   Provider connection testing tool
-   Comprehensive settings page
-   English translation (default)
-   French translation
-   Extensive developer hooks and filters
-   PHPUnit test suite
-   Comprehensive documentation

### Features

-   Debounced search input (500ms) for better performance
-   AJAX-powered autocomplete with loading states
-   Error handling for API failures
-   Responsive design for all screen sizes
-   Accessibility improvements
-   WordPress coding standards compliance
-   Secure nonce verification on all AJAX requests
-   Input sanitization and validation
-   Extensible architecture for adding providers
-   Extensible architecture for adding form integrations
-   Auto-fit map bounds to show all markers
-   Custom map heights
-   Multiple address fields support on single form
-   Field linking for route display

### Developer

-   Custom action hooks for plugin lifecycle
-   Custom filter hooks for data manipulation
-   Well-documented code with DocBlocks
-   Object-oriented architecture
-   Singleton pattern for core classes
-   Factory pattern for providers
-   Abstract classes for easy extension
-   Comprehensive inline documentation

## [Unreleased]

### Planned

-   Support for Elementor Forms
-   Support for Formidable Forms
-   Support for Mapbox provider
-   Support for HERE Maps provider
-   Geolocation support
-   Address validation
-   Custom map styling options
-   Bulk geocoding functionality
-   Enhanced caching strategies
-   Performance optimizations
-   Additional translations

---

[1.0.0]: https://github.com/ndnci/wordpress-address-autocomplete/releases/tag/v1.0.0
