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
        $listing_suburb = get_post_meta($listing_id, 'business_suburb', true); // Retrieve the suburb
        $listing_website = get_post_meta($listing_id, 'business_website', true);
        $listing_whatsapp = get_post_meta($listing_id, 'business_whatsapp', true);
        $facebook = get_post_meta($listing_id, 'facebook', true);
        $address_privacy = get_post_meta($listing_id, 'address_privacy', true);
        $suggestions = get_post_meta($listing_id, 'suggestions', true); // Retrieve suggestions field
echo '</div>';
        echo '<div class="listing">';
        echo '<h3>' . esc_html($listing_title) . '</h3>';

        // Display the edit form for the listing
        echo '<form method="post" enctype="multipart/form-data">';
        echo '<input type="hidden" name="listing_id" value="' . esc_attr($listing_id) . '">';
        echo '<input type="hidden" name="post_title" value="' . esc_attr($listing_title) . '">';

        // Business Address
        echo '<label for="listing_address_' . esc_attr($listing_id) . '">Business Address:</label>';
        echo '<input type="text" id="listing_address_' . esc_attr($listing_id) . '" name="business_address" value="' . esc_attr($listing_address) . '" required>';

        // Suburb Field
        echo '<label for="listing_suburb_' . esc_attr($listing_id) . '">Business Suburb:</label>';
        echo '<input type="text" id="listing_suburb_' . esc_attr($listing_id) . '" name="business_suburb" value="' . esc_attr($listing_suburb) . '" required>';

        // Address Privacy
        echo '<label for="address_privacy_' . esc_attr($listing_id) . '">Hide Address?&nbsp;&nbsp;</label>';
        echo '<input type="radio" name="address_privacy" value="1" ' . checked($address_privacy, '1', false) . '> Yes';
        echo '<input type="radio" name="address_privacy" value="0" ' . checked($address_privacy, '0', false) . '> No';
        echo '<br>';

// Business Phone
echo '<div style="display: flex; align-items: center; gap: 5px;">';
echo '<span style="
    display: inline-block;
    width: 20px;
    height: 20px;
    background-image: url(\'/wp-content/plugins/sandbaai-business-directory/assets/icons/phone.png\') !important;
    background-size: contain !important;
    background-repeat: no-repeat !important;
    background-position: center !important;">
</span>';
echo '<label for="listing_phone_' . esc_attr($listing_id) . '">Business Phone:</label>';
echo '</div>';
echo '<input type="text" id="listing_phone_' . esc_attr($listing_id) . '" name="business_phone" value="' . esc_attr($listing_phone) . '" required>';

// Business Email
echo '<div style="display: flex; align-items: center; gap: 5px;">';
echo '<span style="
    display: inline-block;
    width: 20px;
    height: 20px;
    background-image: url(\'/wp-content/plugins/sandbaai-business-directory/assets/icons/email.png\') !important;
    background-size: contain !important;
    background-repeat: no-repeat !important;
    background-position: center !important;">
</span>';
echo '<label for="listing_email_' . esc_attr($listing_id) . '">Business Email:</label>';
echo '</div>';
echo '<input type="email" id="listing_email_' . esc_attr($listing_id) . '" name="business_email" value="' . esc_attr($listing_email) . '" required>';

         // Business Description
        echo '<label for="listing_description_' . esc_attr($listing_id) . '">Business Description:</label>';
        echo '<textarea id="listing_description_' . esc_attr($listing_id) . '" name="business_description" required>' . esc_textarea($listing_description) . '</textarea>';

// Business Website
echo '<div style="display: flex; align-items: center; gap: 5px;">';
echo '<span style="
    display: inline-block;
    width: 20px;
    height: 20px;
    background-image: url(\'/wp-content/plugins/sandbaai-business-directory/assets/icons/website.png\') !important;
    background-size: contain !important;
    background-repeat: no-repeat !important;
    background-position: center !important;">
</span>';
echo '<label for="listing_website_' . esc_attr($listing_id) . '">Business Website:</label>';
echo '</div>';
echo '<input type="text" id="listing_website_' . esc_attr($listing_id) . '" name="business_website" value="' . esc_attr($listing_website) . '" placeholder="Enter your website (e.g., https://example.com)">';

// Facebook Page
echo '<div style="display: flex; align-items: center; gap: 5px;">';
echo '<span style="
    display: inline-block;
    width: 20px;
    height: 20px;
    background-image: url(\'/wp-content/plugins/sandbaai-business-directory/assets/icons/facebook.png\') !important;
    background-size: contain !important;
    background-repeat: no-repeat !important;
    background-position: center !important;">
</span>';
echo '<label for="facebook_' . esc_attr($listing_id) . '">Business Facebook Page:</label>';
echo '</div>';
echo '<input type="text" id="facebook_' . esc_attr($listing_id) . '" name="facebook" value="' . esc_attr($facebook) . '" placeholder="Enter your Facebook page (e.g., facebook.com/yourpage)">';

// WhatsApp Number
echo '<div style="display: flex; align-items: center; gap: 5px;">';
echo '<span style="
    display: inline-block;
    width: 20px;
    height: 20px;
    background-image: url(\'/wp-content/plugins/sandbaai-business-directory/assets/icons/whatsapp.png\') !important;
    background-size: contain !important;
    background-repeat: no-repeat !important;
    background-position: center !important;">
</span>';
echo '<label for="listing_whatsapp_' . esc_attr($listing_id) . '">Business WhatsApp Number:</label>';
echo '</div>';
echo '<input type="text" id="listing_whatsapp_' . esc_attr($listing_id) . '" name="business_whatsapp" value="' . esc_attr($listing_whatsapp) . '">';

         // Logo Handling
         $logo = get_post_meta($listing_id, 'logo', true);
         echo '<div class="logo-section">';
if (!empty($logo)) {
    echo '<div>';
    echo '<img src="' . esc_url($logo) . '" alt="Logo" style="max-width: 150px;">';
    echo '</div>';
}

// Inline styling for "Upload New Logo" and "Choose File"
echo '<div style="margin-top: 10px; display: flex; align-items: center; gap: 10px;">';
echo '<label for="logo"">Change Your Logo:</label>';
echo '<input type="file" id="logo" name="logo">';
echo '</div><hr>';
 
         // Gallery Handling
         $gallery = get_post_meta($listing_id, 'gallery', true);
         echo '<div class="gallery-section">';
         echo '<label for="gallery">Upload New Photos (Up to 5 photos/no larger than 4mb filesize each):</label>';
         if (!empty($gallery) && is_array($gallery)) {
             echo '<div style="display: flex; flex-wrap: wrap; gap: 10px;">';
             foreach ($gallery as $key => $photo_url) {
                echo '<div style="display: inline-block; text-align: center;">';
                echo '<img src="' . esc_url($photo_url) . '" alt="Gallery Photo" style="max-width: 100px;"><br>';
                echo '<input type="checkbox" name="remove_gallery[]" value="' . esc_attr($key) . '"> Remove';
                echo '</div>';
             }
             echo '</div>';
         }
        echo '<input type="file" id="gallery" name="gallery[]" multiple accept="image/*" onchange="validateGalleryUpload(this, 5)"><br>';
        echo '</div><hr>';

        // Add JavaScript for validation
        echo '<script>
             function validateGalleryUpload(input, maxFiles) {
                 if (input.files.length > maxFiles) {
                     alert("You can only upload up to " + maxFiles + " photos.");
                     input.value = ""; // Clear the input
               }
             }
     </script>';
     
echo '<i>Select 1 to 2 categories that best fit your business<BR>';     
// Retrieve all available tags
$tags = get_terms(array(
    'taxonomy' => 'business_tag',
    'hide_empty' => false,
));

// Get the selected tags for the listing
$selected_tags = wp_list_pluck(get_the_terms($listing_id, 'business_tag'), 'term_id');
$selected_tag_1 = isset($selected_tags[0]) ? $selected_tags[0] : '';
$selected_tag_2 = isset($selected_tags[1]) ? $selected_tags[1] : '';

// Dropdown for the first tag
echo '<label for="business_tag_1">Select Category 1:</label>';
echo '<select name="business_tag_1" id="business_tag_1" required>';
echo '<option value="" disabled>Select the first Category</option>';
foreach ($tags as $tag) {
    $selected = $tag->term_id == $selected_tag_1 ? 'selected' : '';
    echo '<option value="' . esc_attr($tag->term_id) . '" ' . $selected . '>' . esc_html($tag->name) . '</option>';
}
echo '</select>';

// Dropdown for the second tag
echo '<label for="business_tag_2">Select Category 2:</label>';
echo '<select name="business_tag_2" id="business_tag_2">';
echo '<option value="" selected>Select the second category (optional)</option>'; // Default blank option
foreach ($tags as $tag) {
    $selected = $tag->term_id == $selected_tag_2 ? 'selected' : '';
    echo '<option value="' . esc_attr($tag->term_id) . '" ' . $selected . '>' . esc_html($tag->name) . '</option>';
}
echo '</select>';

        // Suggestions and Feedback
        echo '<label for="listing_suggestions_' . esc_attr($listing_id) . '">Suggestions & Feedback:</label>';
        echo '<textarea id="listing_suggestions_' . esc_attr($listing_id) . '" name="suggestions">' . esc_textarea($suggestions) . '</textarea>';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_listing'])) {
    // Process form submission here (update meta fields, descriptions, etc.)
    
    // Perform necessary updates (e.g., wp_update_post, update_post_meta, etc.)

    // Redirect to the main directory with a success message
    wp_redirect(home_url('/business-directory/?success=1'));
    exit;
}

        // Submit and Cancel Buttons
        echo '<div style="display: flex; gap: 10px; align-items: center; margin-top: 15px;">';
        echo '<input type="submit" name="update_listing" value="Update Listing" style="width: 150px;">';
        echo '<button type="button" onclick="window.location.href=\'' . esc_url(home_url('/business-directory/')) . '\';" style="width: 150px;">Cancel</button>';
        echo '</div>';
    }

    echo '</div><br><i>If you have any problems with your listing or wish to remove your listing, email info@sandbaaicommunity.co.za for help.</i>';
} else {
    echo '<p>You have not created any listings yet.</p>';
}

wp_reset_postdata();
?>
