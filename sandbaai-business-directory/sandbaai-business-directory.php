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

// Global flag to prevent recursive post updates
global $sb_is_updating_post;
$sb_is_updating_post = false;

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

// Add a custom class to the body for single business listing pages.
function sb_add_body_class($classes) {
    if (is_singular('business_listing')) {
        $classes[] = 'single-business-listing';
    }
    return $classes;
}
add_filter('body_class', 'sb_add_body_class');

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
            
            // Get form values with validation - FIX for the undefined array key errors
            $listing_title = isset($_POST['listing_title']) ? sanitize_text_field($_POST['listing_title']) : '';
            $listing_description = isset($_POST['listing_description']) ? wp_kses_post($_POST['listing_description']) : '';
            
            $listing_data = array(
                'ID' => $listing_id,
                'post_title' => $listing_title,
                'post_content' => $listing_description,
            );
            
            error_log('Current title before update: ' . get_the_title($listing_id));
            $update_success = wp_update_post($listing_data);
            error_log('Title after update: ' . get_the_title($listing_id));
            
            // Update meta fields (with fixes for undefined array keys)
            if ($update_success) {
                // Safely get and update phone
                $listing_phone = isset($_POST['listing_phone']) ? sanitize_text_field($_POST['listing_phone']) : '';
                update_post_meta($listing_id, '_listing_phone', $listing_phone);
                
                // Safely get and update email
                $listing_email = isset($_POST['listing_email']) ? sanitize_email($_POST['listing_email']) : '';
                update_post_meta($listing_id, '_listing_email', $listing_email);
                
                // Safely get and update website - fix for the ltrim deprecated warning
                $listing_website = isset($_POST['listing_website']) ? esc_url_raw($_POST['listing_website']) : '';
                update_post_meta($listing_id, '_listing_website', $listing_website);
                
                // Safely get and update address
                $listing_address = isset($_POST['listing_address']) ? sanitize_text_field($_POST['listing_address']) : '';
                update_post_meta($listing_id, '_listing_address', $listing_address);
                
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

// Fix for the Paystack save_post_meta issue
function paystack_handle_save_post($post_id) {
    // Skip if we're in a context where get_current_screen isn't available
    if (!function_exists('get_current_screen') || !is_admin() || defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        error_log('Paystack save_post_meta skipped: get_current_screen unavailable.');
        return;
    }
    
    // Continue with your Paystack processing code
    // ...
}
add_action('save_post', 'paystack_handle_save_post', 10, 1);

// Fix for the preg_replace deprecated warning
function safe_kses_replace($pattern, $replacement, $subject) {
    if ($subject === null) {
        return '';
    }
    return preg_replace($pattern, $replacement, $subject);
}

// Add a filter to prevent null values being passed to kses functions
add_filter('wp_kses_normalize_entities', function($string) {
    if ($string === null) {
        return '';
    }
    return $string;
}, 5, 1);

add_filter('wp_insert_post_data', 'sb_ensure_post_title', 999, 2);
function sb_ensure_post_title($data, $postarr) {
    // Only apply to existing posts that are being updated
    if (!empty($postarr['ID']) && empty($data['post_title'])) {
        // Get the existing post title
        $existing_post = get_post($postarr['ID']);
        if ($existing_post && !empty($existing_post->post_title)) {
            $data['post_title'] = $existing_post->post_title;
            error_log("Force-preserved post title: " . $existing_post->post_title);
        }
    }
    return $data;
}

// Enqueue styles for the Single Business Listing page.

function enqueue_single_business_css() {
    if (is_singular('business_listing')) {
        wp_enqueue_style(
            'single-business-css', 
            plugin_dir_url(__FILE__) . 'assets/css/single-business.css', 
            array('astra-theme-css'), // Ensure it loads after Astra's CSS
            '1.0.0'
        );
    }
}
add_action('wp_enqueue_scripts', 'enqueue_single_business_css');

// Enqueue lightbox javascript
function enqueue_lightbox_scripts() {
    wp_enqueue_style('lightbox-css', 'https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css');
    wp_enqueue_script('lightbox-js', 'https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js', array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'enqueue_lightbox_scripts');

function sb_enqueue_styles() {
    wp_enqueue_style('directory-styles', plugin_dir_url(__FILE__) . '/assets/css/directory.css');
}
add_action('wp_enqueue_scripts', 'sb_enqueue_styles');
