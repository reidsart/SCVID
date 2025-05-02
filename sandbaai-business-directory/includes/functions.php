<?php
/**
 * Utility functions for the Sandbaai Business Directory plugin.
 */

/**
 * Add a message to the session.
 *
 * @param string $message The message to display.
 * @param string $type The type of the message (e.g., 'info', 'success', 'error').
 */
function add_session_message($message, $type = 'info') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
}

/**
 * Display session messages.
 */
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

/** This function is likely redundant
  Register the custom business_category taxonomy for business_listing post type.

function register_business_category_taxonomy() {
    // Prevent duplicate registration of the taxonomy
    if (taxonomy_exists('business_category')) {
        return;
    }


    $labels = array(
        'name'              => _x('Business Categories', 'taxonomy general name'),
        'singular_name'     => _x('Business Category', 'taxonomy singular name'),
        'search_items'      => __('Search Business Categories'),
        'all_items'         => __('All Business Categories'),
        'parent_item'       => __('Parent Business Category'),
        'parent_item_colon' => __('Parent Business Category:'),
        'edit_item'         => __('Edit Business Category'),
        'update_item'       => __('Update Business Category'),
        'add_new_item'      => __('Add New Business Category'),
        'new_item_name'     => __('New Business Category Name'),
        'menu_name'         => __('Business Categories'),
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'business-category'),
    );

    register_taxonomy('business_category', 'business_listing', $args);
}
add_action('init', 'register_business_category_taxonomy');
**/

/**
 * Customize admin columns for the Business Listing post type.
 *
 * @param array $columns The existing columns.
 * @return array Modified columns with 'Tags' and 'Business Categories' added and 'Date' moved to the far right.
 */

function customize_business_listing_columns($columns) {
    // Remove the default 'Date' column so we can add it at the end
    unset($columns['date']);

    // Add custom columns
    $columns['business_tag'] = 'Tags';
    $columns['business_category'] = 'Business Categories';

    // Re-add 'Date' at the end
    $columns['date'] = 'Date';

    return $columns;
}
add_filter('manage_business_listing_posts_columns', 'customize_business_listing_columns');

/**
 * Populate custom columns with data.
 *
 * @param string $column The current column being populated.
 * @param int $post_id The ID of the current post.
 */
function populate_business_listing_columns($column, $post_id) {
    if ($column === 'business_tag') {
        $terms = get_the_terms($post_id, 'business_tag');
        error_log("Tags retrieved for post $post_id: " . print_r($terms, true)); // Debug log
        if (!empty($terms) && !is_wp_error($terms)) {
            $tags = array_map(function ($term) {
                return $term->name;
            }, $terms);
            echo implode(', ', $tags);
        } else {
            echo 'No Tags';
        }
    } elseif ($column === 'business_category') {
        $categories = get_the_terms($post_id, 'business_category');
        error_log("Categories retrieved for post $post_id: " . print_r($categories, true)); // Debug log
        if (!empty($categories) && !is_wp_error($categories)) {
            $category_names = array_map(function ($term) {
                return $term->name;
            }, $categories);
            echo implode(', ', $category_names);
        } else {
            echo 'No Categories';
        }
    }
}
add_action('manage_business_listing_posts_custom_column', 'populate_business_listing_columns', 10, 2);

/**
 * Save the selected business_category for a business_listing post.
 *
 * @param int $post_id The ID of the post being saved.
 */
function save_business_listing_categories($post_id) {
    // Check if the form submission includes a category
    if (isset($_POST['business_category'])) {
        $categories = array_unique(array_map('intval', $_POST['business_category'])); // Remove duplicates
        wp_set_post_terms($post_id, $categories, 'business_category'); // Assign categories

        }
}

/**
 * Debug registered taxonomies for business_listing.
 */
add_action('init', function () {
    $taxonomies = get_object_taxonomies('business_listing');
});

/**
 * Debug tag and category saving for form submissions.
 */
function debug_business_listing_taxonomy_saving($post_id) {
    $tags = wp_get_post_terms($post_id, 'business_tag', array('fields' => 'names'));
    $categories = wp_get_post_terms($post_id, 'business_category', array('fields' => 'names'));

    error_log('Tags for post ' . $post_id . ': ' . implode(', ', $tags));
    error_log('Categories for post ' . $post_id . ': ' . implode(', ', $categories));
}
add_action('save_post_business_listing', 'debug_business_listing_taxonomy_saving', 20, 1);
?>