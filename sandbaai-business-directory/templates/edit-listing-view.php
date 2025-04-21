<?php
// Check if user is logged in
if (!is_user_logged_in()) {
    echo '<p>You must be logged in to view or edit a listing.</p>';
    echo '<a href="' . wp_login_url(get_permalink()) . '">Log in</a>';
    return;
}

// Get the current user
$current_user = wp_get_current_user();
$current_user_id = $current_user->ID;

// Handle displaying all listings authored by the current user
$args = array(
    'post_type' => 'business_listing',
    'post_status' => 'any', // Include all statuses
    'author' => $current_user_id, // Filter by the current user's ID
    'orderby' => 'date',
    'order' => 'DESC',
);

$query = new WP_Query($args);

if ($query->have_posts()) {
    echo '<div class="listings">';

    while ($query->have_posts()) {
        $query->the_post();
        $listing_id = get_the_ID();
        $listing_title = get_the_title(); // Retrieve the post title
        $listing_description = get_post_meta($listing_id, 'business_description', true);
        $listing_phone = get_post_meta($listing_id, 'business_phone', true);
        $listing_email = get_post_meta($listing_id, 'business_email', true);
        $listing_address = get_post_meta($listing_id, 'business_address', true);
        $listing_website = get_post_meta($listing_id, 'business_website', true);
        $listing_whatsapp = get_post_meta($listing_id, 'business_whatsapp', true);
        $facebook = get_post_meta($listing_id, 'facebook', true);
        $address_privacy = get_post_meta($listing_id, 'address_privacy', true);
        $tags = get_the_terms($listing_id, 'post_tag');
        $selected_tags = !empty($tags) ? wp_list_pluck($tags, 'term_id') : array();

        echo '<div class="listing">';
        echo '<h3>' . esc_html($listing_title) . '</h3>';

        // Display the edit form for the listing
        echo '<form method="post" enctype="multipart/form-data">';
        echo '<input type="hidden" name="listing_id" value="' . esc_attr($listing_id) . '">';

        // IMPORTANT: Add a hidden field for the post title so it gets submitted with the form
        echo '<input type="hidden" name="post_title" value="' . esc_attr($listing_title) . '">';

        // Business Address
        echo '<label for="listing_address_' . esc_attr($listing_id) . '">Business Address:</label>';
        echo '<input type="text" id="listing_address_' . esc_attr($listing_id) . '" name="business_address" value="' . esc_attr($listing_address) . '" required>';

        // Address Privacy
        echo '<label for="address_privacy_' . esc_attr($listing_id) . '">Hide Address?&nbsp&nbsp</label>';
        echo '<input type="radio" name="address_privacy" value="1" ' . checked($address_privacy, 'yes', false) . '> Yes';
        echo '<input type="radio" name="address_privacy" value="0" ' . checked($address_privacy, 'no', false) . '> No';
        echo '<br>';

        // Business Phone
        echo '<label for="listing_phone_' . esc_attr($listing_id) . '">Business Phone:</label>';
        echo '<input type="text" id="listing_phone_' . esc_attr($listing_id) . '" name="business_phone" value="' . esc_attr($listing_phone) . '" required>';

        // Business Email
        echo '<label for="listing_email_' . esc_attr($listing_id) . '">Business Email:</label>';
        echo '<input type="email" id="listing_email_' . esc_attr($listing_id) . '" name="business_email" value="' . esc_attr($listing_email) . '" required>';

        // Business Description
        echo '<label for="listing_description_' . esc_attr($listing_id) . '">Business Description:</label>';
        echo '<textarea id="listing_description_' . esc_attr($listing_id) . '" name="business_description" required>' . esc_textarea($listing_description) . '</textarea>';

        // Business Website
        echo '<label for="listing_website_' . esc_attr($listing_id) . '">Business Website:</label>';
        echo '<input type="url" id="listing_website_' . esc_attr($listing_id) . '" name="business_website" value="' . esc_url($listing_website) . '">';

        // WhatsApp Number
        echo '<label for="listing_whatsapp_' . esc_attr($listing_id) . '">Business WhatsApp Number:</label>';
        echo '<input type="text" id="listing_whatsapp_' . esc_attr($listing_id) . '" name="business_whatsapp" value="' . esc_attr($listing_whatsapp) . '">';

        // Facebook Page
        echo '<label for="facebook_' . esc_attr($listing_id) . '">Business Facebook Page:</label>';
        echo '<input type="url" id="facebook_' . esc_attr($listing_id) . '" name="facebook" value="' . esc_url($facebook) . '">';

        // Tags as Dropdowns
        $tags = get_tags(array('hide_empty' => false));
        $selected_tags = array_values($selected_tags); // Ensure selected tags are indexed numerically

        // Tag 1 Dropdown
        echo '<label for="tag_1">Tag 1:</label>';
        echo '<select id="tag_1" name="tags[]" required>';
        echo '<option value="">Select Tag 1</option>';
        foreach ($tags as $tag) {
            $selected = (isset($selected_tags[0]) && $tag->term_id == $selected_tags[0]) ? 'selected' : '';
            echo '<option value="' . esc_attr($tag->term_id) . '" ' . $selected . '>' . esc_html($tag->name) . '</option>';
        }
        echo '</select>';

        // Tag 2 Dropdown
        echo '<label for="tag_2">Tag 2:</label>';
        echo '<select id="tag_2" name="tags[]">';
        echo '<option value="">Select Tag 2</option>';
        foreach ($tags as $tag) {
            $selected = (isset($selected_tags[1]) && $tag->term_id == $selected_tags[1]) ? 'selected' : '';
            echo '<option value="' . esc_attr($tag->term_id) . '" ' . $selected . '>' . esc_html($tag->name) . '</option>';
        }
        echo '</select><br><br>';

        // Gallery and Logo Handling
        $gallery = get_post_meta($listing_id, 'gallery', true);
        $logo = get_post_meta($listing_id, 'logo', true);

        // Debug: Log the gallery meta
        error_log('Gallery meta for listing ' . $listing_id . ': ' . print_r($gallery, true));

        // Display Logo
        if (!empty($logo)) {
            echo '<div>';
            echo '<label>Current Logo:</label><br>';
            echo '<img src="' . esc_url($logo) . '" alt="Logo" style="max-width: 150px;"><br>';
            echo '<input type="checkbox" name="remove_logo" value="1"> Remove Logo';
            echo '</div>';
        }

        // Display Gallery
        if (!empty($gallery) && is_array($gallery)) {
            echo '<div>';
            echo '<label>Current Gallery:</label><br>';
            foreach ($gallery as $key => $photo_url) {
                echo '<div style="margin-bottom: 10px;">';
                echo '<img src="' . esc_url($photo_url) . '" alt="Gallery Photo" style="max-width: 150px;"><br>';
                echo '<input type="checkbox" name="remove_gallery[]" value="' . esc_attr($key) . '"> Remove Photo';
                echo '</div>';
            }
            echo '</div>';
        }

        // File upload for new photos
        echo '<label for="gallery">Upload New Photos:</label>';
        echo '<input type="file" id="gallery" name="gallery[]" multiple><br>';

        echo '<label for="logo">Upload New Logo:</label>';
        echo '<input type="file" id="logo" name="logo"><br>';

        // Submit button
        echo '<input type="submit" name="update_listing" value="Update Listing">';
        echo '</form>';
        echo '</div><br>';
    }

    echo '</div>';
} else {
    echo '<p>You have not created any listings yet.</p>';
}

wp_reset_postdata();
?>
