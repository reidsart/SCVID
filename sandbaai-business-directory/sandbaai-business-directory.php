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
    // Include the edit-listing.php file
    ob_start();
    include plugin_dir_path(__FILE__) . 'templates/edit-listing.php';
    return ob_get_clean();
}
add_shortcode('edit_listing', 'render_edit_listing_page');
