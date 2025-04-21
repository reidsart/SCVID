<?php
// Check if user is logged in
if (!is_user_logged_in()) {
    echo '<p>You must be logged in to view your listings.</p>';
    echo '<a href="' . wp_login_url(get_permalink()) . '">Log in</a>';
    return;
}

// Check if the user is logged in for debugging
if (is_user_logged_in()) {
    $current_user_id = get_current_user_id();
    echo '<p>Current User ID: ' . esc_html($current_user_id) . '</p>';
} else {
    echo '<p>No user is logged in.</p>';
}

// Get the current user
$current_user = wp_get_current_user();

// Query all listings authored by the current user
$args = array(
    'post_type' => 'listing', // Ensure you use the correct post type
    'post_status' => 'any', // Include all statuses (published, pending, etc.)
    'author' => $current_user->ID, // Filter by the current user's ID
    'orderby' => 'date',
    'order' => 'DESC',
);

$query = new WP_Query($args);

if (!$query->have_posts()) {
    echo '<p>You have not created any listings yet.</p>';
    return;
}

// Display the listings
echo '<div class="user-listings">';
echo '<h2>Your Listings</h2>';

while ($query->have_posts()) {
    $query->the_post();
    $listing_id = get_the_ID();
    $listing_title = get_the_title();
    $listing_description = get_the_excerpt(); // Short description
    $edit_url = add_query_arg('listing_id', $listing_id, home_url('/edit-listing/'));
    
    echo '<div class="listing-item">';
    echo '<h3>' . esc_html($listing_title) . '</h3>';
    echo '<p>' . esc_html($listing_description) . '</p>';
    echo '<a href="' . esc_url($edit_url) . '">Edit Listing</a>';
    echo '</div>';
}

echo '</div>';

// Reset post data
wp_reset_postdata();
?>
