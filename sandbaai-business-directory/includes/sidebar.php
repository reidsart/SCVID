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

    // Add a custom class to the sidebar container for styling
    echo '<div class="sb-sidebar-container custom-sidebar-style">';

    // Link to "All Listings"
    echo '<div class="sb-all-listings">';
    echo '<a href="' . esc_url(home_url('/business-directory/')) . '"><strong>All Listings</strong></a>';
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
            echo '<li><a href="' . esc_url(get_term_link($tag->term_id, 'business_tag')) . '" style="color: white;">• ' . esc_html($tag->name) . '</a></li>';
        } else {
            // Display as grey non-clickable text if the tag is not used
            echo '<li style="color: grey; font-size: 12px;">• ' . esc_html($tag->name) . '</li>';
        }
    }
    echo '</ul>';
    echo '</div>';

    // Link to "Add Your Business" or alternative display for non-logged-in users
    echo '<div class="sb-add-business">';
    echo '<hr>';

    if (is_user_logged_in()) {
        $current_user_id = get_current_user_id();

        // Query for the user's business listing
        $business_listing = get_posts(array(
            'post_type' => 'business_listing',
            'author' => $current_user_id,
            'posts_per_page' => 1, // We only need one post
            'post_status' => array('publish', 'pending'), // Check both approved and pending listings
        ));

        if (!empty($business_listing)) {
            $listing = $business_listing[0]; // Get the first (and only) listing

            if ($listing->post_status === 'publish') {
                // Approved listing: Show "Edit Your Listing" link
                echo '<a href="' . esc_url(home_url('/edit-listing/?listing_id=' . $listing->ID)) . '" style="color: white;">Edit Your Listing</a>';
            } elseif ($listing->post_status === 'pending') {
                // Pending listing: Show "Listing Submitted" text
                echo '<span style="font-weight: bold; color: grey;">Listing Submitted</span>';
            }
        } else {
            // No listing: Show "Add Your Business" link
            echo '<a href="' . esc_url(home_url('/add-business/')) . '" style="color: white;">Add Your Business</a>';
        }
    } else {
        // Non-logged-in users
        echo '<span style="font-weight: bold;">Add Your Business</span><br>';
        echo '<span style="font-size: 12px; color: grey;">Members Only</span><br>';
        echo '<a href="https://sandbaaicommunity.co.za/register/" style="font-size: 16px; color: white; font-weight: bold;">Join</a>';
        echo ' | ';
        echo '<a href="https://sandbaaicommunity.co.za/login/" style="font-size: 16px; color: white; font-weight: bold;">Login</a>';
    }

    echo '</div>';
    echo '</div>';
}