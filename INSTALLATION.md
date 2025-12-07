# Installation Guide

## Requirements

Before installing WordPress Address Autocomplete, ensure your environment meets these requirements:

-   **WordPress**: 5.8 or higher
-   **PHP**: 7.4 or higher
-   **Form Plugin**: At least one of:
    -   Contact Form 7
    -   WPForms (Lite or Pro)
    -   Gravity Forms

## Installation Methods

### Method 1: WordPress Admin (Recommended)

1. Download the plugin ZIP file
2. Log in to your WordPress admin panel
3. Navigate to **Plugins > Add New**
4. Click **Upload Plugin** button
5. Choose the downloaded ZIP file
6. Click **Install Now**
7. After installation, click **Activate Plugin**

### Method 2: FTP Upload

1. Download and extract the plugin ZIP file
2. Connect to your server via FTP
3. Navigate to `/wp-content/plugins/`
4. Upload the `wordpress-address-autocomplete` folder
5. Log in to WordPress admin
6. Navigate to **Plugins**
7. Find "WordPress Address Autocomplete" and click **Activate**

### Method 3: WP-CLI

```bash
wp plugin install wordpress-address-autocomplete.zip --activate
```

## Post-Installation Configuration

### Step 1: Choose Your Provider

1. Navigate to **Settings > Address Autocomplete**
2. Select your preferred map provider:
    - **OpenStreetMap** (Recommended for most users)
        - Free
        - No API key required
        - No usage limits
    - **Google Maps**
        - Requires API key
        - May have costs depending on usage
        - More familiar to some users

### Step 2: Configure Google Maps (If Selected)

If you chose Google Maps as your provider:

1. Visit [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project (or select existing one)
3. Enable the following APIs:
    - Places API
    - Maps JavaScript API
    - Geocoding API
4. Create credentials (API key)
5. Copy the API key
6. Paste it in **Settings > Address Autocomplete > Google Maps API Key**
7. Click **Save Settings**
8. Click **Test Provider Connection** to verify

### Step 3: Configure Cache Settings (Optional)

1. **Enable Cache**: Check to enable caching (recommended)
2. **Cache Duration**: Set how long to cache responses
    - 3600 = 1 hour
    - 86400 = 24 hours (default)
    - 604800 = 1 week
3. Click **Save Settings**

### Step 4: Test the Connection

1. Click **Test Provider Connection** button
2. Wait for the result
3. If successful, you'll see "Connection successful!"
4. If failed, check your settings and try again

## Adding Fields to Forms

### Contact Form 7

1. Edit a Contact Form 7 form
2. Click the **Address Autocomplete** button
3. Configure the field:
    - Name: `address-1`
    - Check "Required" if needed
    - Add placeholder text
4. Click **Insert Tag**
5. Add to form: `[address_autocomplete* address-1 placeholder "Enter your address"]`

To add a map:

```
[address_map map-1 fields:address-1,address-2 mode:markers height:400px]
```

### WPForms

1. Edit a WPForms form
2. Find **Address Autocomplete** in Fancy Fields
3. Drag it to your form
4. Click the field to configure:
    - Label
    - Description
    - Placeholder
    - Required
5. Save the form

To add a map:

1. Drag **Address Map** to your form
2. Configure:
    - Address Fields: `1,2,3` (field IDs)
    - Display Mode: Markers or Route
    - Map Height: `400px`

### Gravity Forms

1. Edit a Gravity Forms form
2. Click **Add Fields**
3. Find **Address Autocomplete** in Advanced Fields
4. Drag it to your form
5. Configure in the right panel:
    - Field Label
    - Description
    - Placeholder
    - Required
6. Update the form

To add a map:

1. Add **Address Map** from Advanced Fields
2. Configure:
    - Address Fields to Display: `1,2,3`
    - Display Mode: Markers or Route
    - Map Height: `400px` or `50vh`

## Troubleshooting

### Autocomplete Not Working

**Check JavaScript Console**

1. Open browser developer tools (F12)
2. Check Console tab for errors
3. Common issues:
    - Script not loading
    - AJAX errors
    - Nonce verification failures

**Verify Settings**

1. Go to Settings > Address Autocomplete
2. Verify provider is selected
3. If Google Maps, verify API key is entered
4. Test provider connection

**Check Plugin Conflicts**

1. Deactivate other plugins temporarily
2. Test if autocomplete works
3. Reactivate plugins one by one to find conflict

### Map Not Displaying

**Verify Field IDs**

1. Check that field IDs in map settings match actual field IDs
2. For CF7: Use field names (e.g., `address-1`)
3. For WPForms/Gravity Forms: Use field IDs (e.g., `1,2,3`)

**Check Map Container**

1. Inspect the map element in browser dev tools
2. Verify it has content and correct dimensions
3. Check for CSS conflicts

**Provider Script Loading**

1. Verify map provider script is loading in page source
2. Check browser console for script errors
3. For Google Maps, verify API key is correct

### Google Maps API Errors

**"API key not found"**

-   Verify API key is entered correctly in settings
-   No extra spaces before or after the key

**"This API project is not authorized"**

-   Check that required APIs are enabled in Google Cloud Console
-   Verify billing is set up (required even for free tier)

**"Request denied"**

-   Check API key restrictions in Google Cloud Console
-   Verify referrer restrictions allow your domain

### Cache Issues

**Stale Results**

1. Go to Settings > Address Autocomplete
2. Click **Clear Cache**
3. Try searching again

**Cache Not Working**

1. Check that caching is enabled in settings
2. Verify PHP can write to database
3. Check WordPress transient functionality

## Uninstallation

### Complete Removal

1. Deactivate the plugin
2. Delete the plugin
3. (Optional) Clean up options:

```sql
DELETE FROM wp_options WHERE option_name LIKE 'wpaa_%';
DELETE FROM wp_options WHERE option_name LIKE '%_transient_wpaa_%';
```

## Getting Help

If you encounter issues:

1. Check this installation guide
2. Review the main README.md
3. Check the FAQ section
4. Visit [NDNCI Support](https://www.ndnci.com)
5. Open an issue on GitHub

## Next Steps

After successful installation:

1. Add address autocomplete fields to your forms
2. Test on frontend
3. Add maps if needed
4. Monitor cache performance
5. Review developer documentation for customization

## Updates

The plugin will notify you when updates are available through the WordPress admin panel. Always backup your site before updating.

## Support

For support and questions:

-   Website: [www.ndnci.com](https://www.ndnci.com)
-   Email: support@ndnci.com
