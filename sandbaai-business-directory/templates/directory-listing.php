<?php
function sb_render_directory_listing() {
    ob_start();

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

    // Query Overberg Businesses
    $overberg_query = new WP_Query(array(
        'post_type' => 'business_listing',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'orderby' => 'title',
        'order' => 'ASC',
        'tax_query' => array(
            array(
                'taxonomy' => 'business_category',
                'field' => 'slug',
                'terms' => 'ob_business', // Overberg Business category
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

    // Display Overberg Businesses
    if ($overberg_query->have_posts()) {
        echo '<h2>Overberg Businesses</h2>';
        echo '<ul class="business-listing">';
        while ($overberg_query->have_posts()) {
            $overberg_query->the_post();
            echo '<li>';
            echo '<a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a>';
            echo '</li>';
        }
        echo '</ul>';
    }

    // Reset post data
    wp_reset_postdata();

    return ob_get_clean();
}
add_shortcode('sb_directory_listing', 'sb_render_directory_listing');