<?php
/**
 * Utility functions for the Sandbaai Business Directory plugin.
 */

/**
// Sanitize input data
function sanitize_input($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}
*/

// Add a message to the session
function add_session_message($message, $type = 'info') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
}

// Display session messages
function display_session_message() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $type = $_SESSION['message_type'] ?? 'info';
        echo "<div class='alert alert-{$type}'>{$message}</div>";
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    }
} 
/**
 * Modify the search query to include business descriptions.
 */
function sb_extend_business_search($query) {
    if (!is_admin() && $query->is_main_query() && $query->is_search() && $query->get('post_type') === 'business_listing') {
        // Include meta query for business_description
        $meta_query = array(
            array(
                'key'     => 'business_description',
                'value'   => $query->get('s'),
                'compare' => 'LIKE',
            ),
        );

        // Set the meta query
        $query->set('meta_query', $meta_query);

        // Ensure it also searches post titles and content
        $query->set('s', $query->get('s'));
    }
}
add_action('pre_get_posts', 'sb_extend_business_search');

// Add the business_tag taxonomy column to the Business Listing admin table
function add_business_tag_column($columns) {
    $columns['business_tag'] = 'Tags';
    return $columns;
}
add_filter('manage_business_listing_posts_columns', 'add_business_tag_column');

// Populate the business_tag column with terms
function populate_business_tag_column($column, $post_id) {
    if ($column === 'business_tag') {
        $terms = get_the_terms($post_id, 'business_tag');
        if (!empty($terms) && !is_wp_error($terms)) {
            $tags = array_map(function($term) {
                return $term->name;
            }, $terms);
            echo implode(', ', $tags);
        } else {
            echo 'No Tags';
        }
    }
}
add_action('manage_business_listing_posts_custom_column', 'populate_business_tag_column', 10, 2);

function add_business_tag_metabox() {
    register_taxonomy_for_object_type('business_tag', 'business_listing');
}
add_action('init', 'add_business_tag_metabox');

?>
