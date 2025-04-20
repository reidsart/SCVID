<?php
/*
Plugin Name: Sandbaai Business Directory
Plugin URI: https://sandbaaicommunity.co.za
Description: A WordPress plugin for managing a business directory focused on Sandbaai and Overberg businesses.
Version: 1.0
Author: reidsart
Author URI: https://github.com/reidsart
License: GPL2
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define constants for plugin paths
define('SB_DIR_PATH', plugin_dir_path(__FILE__));
define('SB_DIR_URL', plugin_dir_url(__FILE__));

// Include necessary files
require_once SB_DIR_PATH . 'includes/custom-post-type.php';
require_once SB_DIR_PATH . 'includes/form-handler.php';
require_once SB_DIR_PATH . 'includes/directory-handler.php';
require_once plugin_dir_path(__FILE__) . 'includes/database.php';

// Enqueue assets
function sb_enqueue_assets() {
    wp_enqueue_style('sb-styles', SB_DIR_URL . 'assets/css/css_styles.css', array(), '1.0');
}
add_action('wp_enqueue_scripts', 'sb_enqueue_assets');

register_activation_hook(__FILE__, 'sandbaai_business_directory_activate');

function sandbaai_business_directory_activate() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'businesses';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        business_name varchar(255) NOT NULL,
        category_id mediumint(9) NOT NULL,
        business_description text NOT NULL,
        business_address text NOT NULL,
        business_phone varchar(50) NOT NULL,
        business_email varchar(100) NOT NULL,
        business_website varchar(255) DEFAULT '' NOT NULL,
        business_facebook varchar(255) DEFAULT '' NOT NULL,
        business_instagram varchar(255) DEFAULT '' NOT NULL,
        business_twitter varchar(255) DEFAULT '' NOT NULL,
        approved tinyint(1) DEFAULT 0 NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Activation hook
function sb_activate_plugin() {
    // Register custom post type and taxonomies on activation
    sb_register_custom_post_type();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'sb_activate_plugin');

// Deactivation hook
function sb_deactivate_plugin() {
    // Cleanup on deactivation
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'sb_deactivate_plugin');

// Load custom template for single business listings
function sb_load_custom_template($template) {
    if (is_singular('business_listing')) {
        $custom_template = plugin_dir_path(__FILE__) . 'templates/single-business_listing.php';
        if (file_exists($custom_template)) {
            return $custom_template;
        }
    }
    return $template;
}
add_filter('single_template', 'sb_load_custom_template');

// Load edit listing page
function render_edit_listing_page() {
    // Don't execute during any admin requests
    if (is_admin()) {
        return '[edit_listing]'; // Just return the placeholder during admin operations
    }
    
    // Start output buffering
    ob_start();
    
    // Get necessary data for the template
    global $post;
    $current_user = wp_get_current_user();
    
    // Only process form submission when POST data exists and we're not in admin
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['listing_id'])) {
        // Form processing logic would go here
        // We'll move this from edit-listing.php
        $listing_id = intval($_POST['listing_id']);
        
        // Check if the current user is allowed to edit this listing
        $listing_author = get_post_field('post_author', $listing_id);
        if ($listing_author == $current_user->ID || current_user_can('administrator')) {
            // Process the form submission
            // Update post data
            $listing_data = array(
                'ID' => $listing_id,
                'post_title' => sanitize_text_field($_POST['listing_title']),
                'post_content' => wp_kses_post($_POST['listing_description']),
            );
            
            $update_success = wp_update_post($listing_data);
            
            // Update meta fields (copied from edit-listing.php)
            if ($update_success) {
                update_post_meta($listing_id, '_listing_phone', sanitize_text_field($_POST['listing_phone']));
                update_post_meta($listing_id, '_listing_email', sanitize_email($_POST['listing_email']));
                update_post_meta($listing_id, '_listing_website', esc_url_raw($_POST['listing_website']));
                update_post_meta($listing_id, '_listing_address', sanitize_text_field($_POST['listing_address']));
                
                // Handle category if needed
                if (!empty($_POST['listing_category'])) {
                    wp_set_object_terms($listing_id, intval($_POST['listing_category']), 'listing_category');
                }
                
                // Redirect after update
                $listing_url = get_permalink($listing_id);
                wp_redirect($listing_url);
                exit;
            }
        }
    }
    
    // Now include the template view part only (no processing logic)
    include plugin_dir_path(__FILE__) . 'templates/edit-listing-view.php';
    
    return ob_get_clean();
}
add_shortcode('edit_listing', 'render_edit_listing_page');
