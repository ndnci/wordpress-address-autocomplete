<?php
/**
 * Plugin updater for GitHub releases
 *
 * @package WordPress_Address_Autocomplete
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Plugin updater class for GitHub integration
 */
class NDNCI_WPAA_Updater {
    
    /**
     * GitHub username
     *
     * @var string
     */
    private $username = 'ndnci';
    
    /**
     * GitHub repository name
     *
     * @var string
     */
    private $repository = 'wordpress-address-autocomplete';
    
    /**
     * Plugin basename
     *
     * @var string
     */
    private $plugin_basename;
    
    /**
     * Current plugin version
     *
     * @var string
     */
    private $current_version;
    
    /**
     * GitHub API result
     *
     * @var object|null
     */
    private $github_response;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->plugin_basename = NDNCI_WPAA_PLUGIN_BASENAME;
        $this->current_version = NDNCI_WPAA_VERSION;
        
        add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_for_update' ) );
        add_filter( 'plugins_api', array( $this, 'plugin_info' ), 10, 3 );
        add_filter( 'upgrader_post_install', array( $this, 'after_install' ), 10, 3 );
    }
    
    /**
     * Get information from GitHub API
     *
     * @return object|null
     */
    private function get_github_release() {
        if ( ! empty( $this->github_response ) ) {
            return $this->github_response;
        }
        
        $url = "https://api.github.com/repos/{$this->username}/{$this->repository}/releases/latest";
        
        $response = wp_remote_get( $url, array(
            'timeout' => 10,
            'headers' => array(
                'Accept' => 'application/vnd.github.v3+json',
            ),
        ) );
        
        if ( is_wp_error( $response ) ) {
            return null;
        }
        
        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body );
        
        if ( ! empty( $data ) && isset( $data->tag_name ) ) {
            $this->github_response = $data;
            return $data;
        }
        
        return null;
    }
    
    /**
     * Check for plugin updates
     *
     * @param object $transient Update transient
     * @return object
     */
    public function check_for_update( $transient ) {
        if ( empty( $transient->checked ) ) {
            return $transient;
        }
        
        $release = $this->get_github_release();
        
        if ( ! $release ) {
            return $transient;
        }
        
        // Remove 'v' prefix from tag if present
        $latest_version = ltrim( $release->tag_name, 'v' );
        
        if ( version_compare( $this->current_version, $latest_version, '<' ) ) {
            $plugin_data = array(
                'slug'        => dirname( $this->plugin_basename ),
                'new_version' => $latest_version,
                'url'         => $release->html_url,
                'package'     => $release->zipball_url,
                'tested'      => get_bloginfo( 'version' ),
            );
            
            $transient->response[ $this->plugin_basename ] = (object) $plugin_data;
        }
        
        return $transient;
    }
    
    /**
     * Provide plugin information for update screen
     *
     * @param false|object|array $result The result object or array
     * @param string $action The type of information being requested
     * @param object $args Plugin API arguments
     * @return false|object
     */
    public function plugin_info( $result, $action, $args ) {
        if ( 'plugin_information' !== $action ) {
            return $result;
        }
        
        if ( ! isset( $args->slug ) || $args->slug !== dirname( $this->plugin_basename ) ) {
            return $result;
        }
        
        $release = $this->get_github_release();
        
        if ( ! $release ) {
            return $result;
        }
        
        $latest_version = ltrim( $release->tag_name, 'v' );
        
        $plugin_info = array(
            'name'          => 'NDNCI - WordPress Address Autocomplete',
            'slug'          => dirname( $this->plugin_basename ),
            'version'       => $latest_version,
            'author'        => '<a href="https://www.ndnci.com">NDNCI</a>',
            'homepage'      => 'https://github.com/' . $this->username . '/' . $this->repository,
            'requires'      => '5.8',
            'tested'        => get_bloginfo( 'version' ),
            'downloaded'    => 0,
            'last_updated'  => $release->published_at,
            'sections'      => array(
                'description'  => $release->body,
                'installation' => 'Upload the plugin files to WordPress and activate.',
                'changelog'    => $release->body,
            ),
            'download_link' => $release->zipball_url,
        );
        
        return (object) $plugin_info;
    }
    
    /**
     * Rename plugin folder after installation
     *
     * @param bool $response Installation response
     * @param array $hook_extra Extra arguments passed to hook
     * @param array $result Installation result
     * @return array
     */
    public function after_install( $response, $hook_extra, $result ) {
        global $wp_filesystem;
        
        $plugin_folder = WP_PLUGIN_DIR . '/' . dirname( $this->plugin_basename );
        $wp_filesystem->move( $result['destination'], $plugin_folder );
        $result['destination'] = $plugin_folder;
        
        if ( isset( $hook_extra['plugin'] ) && $hook_extra['plugin'] === $this->plugin_basename ) {
            activate_plugin( $this->plugin_basename );
        }
        
        return $result;
    }
}
