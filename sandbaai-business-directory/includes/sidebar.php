<?php
// Global variable to track if the sidebar has already been rendered
global $sb_sidebar_rendered;
$sb_sidebar_rendered = false;

/**
 * Render the sidebar.
 * This will only render once per page load.
 */
function sb_render_sidebar() {
    global $sb_sidebar_rendered;

    // Prevent multiple renders
    if ($sb_sidebar_rendered) {
        return;
    }

    // Mark the sidebar as rendered
    $sb_sidebar_rendered = true;

    echo '<div class="sb-sidebar-navigation">';


    // Link to "All Listings"
    echo '<div class="sb-all-listings">';
    echo '<a href="' . esc_url(home_url('/business-directory/')) . '">All Listings</a>';
    echo '</div><hr>';

    // Tags Section
    $tags = get_terms(array(
        'taxonomy' => 'business_tag',
        'orderby' => 'name',
        'order' => 'ASC',
        'hide_empty' => false,
    ));

    echo '<div class="sb-tags-section">';
    echo '<h3>Categories</h3>';
    echo '<ul>';
    foreach ($tags as $tag) {
        if ($tag->count > 0) {
            // Link to taxonomy archive page
            echo '<li><a href="' . esc_url(get_term_link($tag->term_id, 'business_tag')) . '">• ' . esc_html($tag->name) . '</a></li>';
        } else {
            // Display as grey non-clickable text if the tag is not used
            echo '<li style="color: grey; font-size: 12px;">• ' . esc_html($tag->name) . '</li>';
        }
    }
    echo '</ul>';
    echo '</div>';
    
    // Search Bar
    echo '<div class="sb-search-bar">';
    echo '<form method="get" action="' . home_url('/') . '">';
    echo '<input type="hidden" name="post_type" value="business_listing">';
    echo '<input type="text" name="s" placeholder="Search Business Listings">';
    echo '<button type="submit">Search</button>';
    echo '</form>';
    echo '</div>';

    // Link to "Add Your Business"
    echo '<div class="sb-add-business">';
    echo '<hr><a href="' . esc_url(home_url('/add-business/')) . '">Add Your Business</a>';
    echo '</div>';
    
    echo '</div>';
}
