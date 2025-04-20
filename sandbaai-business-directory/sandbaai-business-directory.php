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

// Enqueue assets
function sb_enqueue_assets() {
    wp_enqueue_style('sb-styles', SB_DIR_URL . 'assets/css/css_styles.css', array(), '1.0');
}
add_action('wp_enqueue_scripts', 'sb_enqueue_assets');

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
