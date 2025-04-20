<?php
function sb_render_directory_listing() {
    ob_start();

    // Display Tags Table
    $tags = get_tags(array('orderby' => 'name', 'order' => 'ASC', 'hide_empty' => false));

    if (!empty($tags)) {
        echo '<h2>Tags</h2>';
        echo '<table class="tags-table">';
        echo '<thead><tr><th>Tag Name</th></tr></thead>';
        echo '<tbody>';
        foreach ($tags as $tag) {
            if ($tag->count > 0) { // Only show tags used 1 or more times
                echo '<tr>';
                echo '<td><a href="' . esc_url(get_tag_link($tag->term_id)) . '">' . esc_html($tag->name) . '</a></td>';
                echo '</tr>';
            }
        }
        echo '</tbody>';
        echo '</table>';
    }

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
