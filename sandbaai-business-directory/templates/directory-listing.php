<?php
require_once SB_DIR_PATH . 'includes/sidebar.php';

function sb_render_directory_listing() {
    ob_start();

    // Begin Layout Wrapper
    echo '<div class="sb-directory-layout">';

    // Sidebar Navigation
    echo '<div class="sb-sidebar-container">';
    sb_render_sidebar();
    echo '</div>';

    // Main Content
    echo '<div class="sb-main-content">';

    // Query Sandbaai Businesses
    $sandbaai_query = new WP_Query(array(
        'post_type' => 'business_listing',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'orderby' => 'title',
        'order' => 'ASC',
        'tax_query' => array(
            array(
                'taxonomy' => 'business_category',
                'field' => 'slug',
                'terms' => 'sb_business', // Sandbaai Business category
            ),
        ),
    ));

    // Display Sandbaai Businesses
    if ($sandbaai_query->have_posts()) {
        echo '<h2>Sandbaai Businesses</h2>';
        echo '<ul class="business-listing">';
        while ($sandbaai_query->have_posts()) {
            $sandbaai_query->the_post();
            echo '<li>';
            echo '<a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a>';
            echo '</li>';
        }
        echo '</ul>';
    }

    // Reset post data
    wp_reset_postdata();

    echo '</div>'; // End Main Content
    echo '</div>'; // End Layout Wrapper

    return ob_get_clean();
}

add_shortcode('sb_directory_listing', 'sb_render_directory_listing');
