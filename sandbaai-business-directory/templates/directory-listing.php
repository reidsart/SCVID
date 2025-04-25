<?php
function sb_render_directory_listing() {
    ob_start();

    // Begin Layout Wrapper
    echo '<div class="directory-layout">';

    // Sidebar Navigation
    echo '<div class="sidebar-navigation">';
    
    // Search Bar
    echo '<div class="search-bar">';
    echo '<form method="get" action="' . home_url('/') . '">';
    echo '<input type="hidden" name="post_type" value="business_listing">';
    echo '<input type="text" name="s" placeholder="Search Business Listings">';
    echo '<button type="submit">Search</button>';
    echo '</form>';
    echo '</div>';

    // Tags Section
    $tags = get_terms(array(
        'taxonomy' => 'business_tag',
        'orderby' => 'name',
        'order' => 'ASC',
        'hide_empty' => false,
    ));

    echo '<div class="tags-section">';
    echo '<h3>Tags</h3>';
    echo '<ul>';
    foreach ($tags as $tag) {
        if ($tag->count > 0) {
            echo '<li><a href="' . esc_url(get_term_link($tag->term_id, 'business_tag')) . '">' . esc_html($tag->name) . '</a></li>';
        } else {
            echo '<li style="color: grey;">' . esc_html($tag->name) . '</li>';
        }
    }
    echo '</ul>';
    echo '</div>';
    echo '</div>'; // End Sidebar Navigation

    // Main Content
    echo '<div class="main-content">';

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
