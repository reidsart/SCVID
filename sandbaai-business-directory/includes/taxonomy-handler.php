<?php
// Add sidebar to taxonomy pages dynamically
function sb_handle_taxonomy_pages() {
    if (is_tax('business_tag')) {
        // Enqueue CSS for styling
        wp_enqueue_style('directory-styles', plugin_dir_url(__FILE__) . '../assets/css/directory.css');

        // Hook into the content filter to add sidebar
        add_filter('the_content', 'sb_render_taxonomy_with_sidebar');
    }
}
add_action('template_redirect', 'sb_handle_taxonomy_pages');

// Render taxonomy page with sidebar
function sb_render_taxonomy_with_sidebar($content) {
    // Only modify content for taxonomy pages
    if (!is_tax('business_tag')) {
        return $content;
    }

    $sidebar = sb_render_sidebar(); // Call the sidebar rendering function

    ob_start();
    echo '<div class="directory-layout">';
    echo $sidebar; // Render sidebar

    // Main Content
    echo '<div class="main-content">';
    $current_tag = get_queried_object();
    echo '<h2>Businesses Tagged: ' . esc_html($current_tag->name) . '</h2>';

    // Custom query to fetch posts
    add_filter('posts_orderby', 'sb_case_insensitive_orderby'); // Apply case-insensitive sorting
    $args = array(
        'post_type' => 'business_listing',
        'tax_query' => array(
            array(
                'taxonomy' => 'business_tag',
                'field' => 'slug',
                'terms' => $current_tag->slug,
            ),
        ),
        'orderby' => 'title', // Order by title
        'order' => 'ASC', // Alphabetical order
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        echo '<ul class="business-listing">';
        while ($query->have_posts()) {
            $query->the_post();
            echo '<li>';
            echo '<a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a>';
            echo '</li>';
        }
        echo '</ul>';
        wp_reset_postdata(); // Reset the query
    } else {
        echo '<p>No businesses found with this tag.</p>';
    }
    remove_filter('posts_orderby', 'sb_case_insensitive_orderby'); // Remove the filter after usage

    echo '</div>'; // End Main Content
    echo '</div>'; // End Layout Wrapper

    return ob_get_clean();
}

// Case-insensitive orderby filter
function sb_case_insensitive_orderby($orderby) {
    global $wpdb;
    return "LOWER({$wpdb->posts}.post_title) ASC";
}

// Add a custom template for taxonomy pages
function sb_load_custom_taxonomy_template($template) {
    if (is_tax('business_tag')) {
        $custom_template = plugin_dir_path(dirname(__FILE__)) . 'includes/taxonomy-business_tag.php';
        if (file_exists($custom_template)) {
            return $custom_template;
        }
    }
    return $template;
}
add_filter('template_include', 'sb_load_custom_taxonomy_template');