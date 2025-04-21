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
        echo '<input type="radio" name="address_privacy" value="yes" ' . checked($address_privacy, 'yes', false) . '> Yes';
        echo '<input type="radio" name="address_privacy" value="no" ' . checked($address_privacy, 'no', false) . '> No';
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

// Fetch the listing data and display logo and photos to edit
$listing_id = isset($_GET['listing_id']) ? intval($_GET['listing_id']) : 0;
$listing = get_post($listing_id);

if (!$listing) {
    echo '<p style="color: red;">Invalid listing ID.</p>';
    return;
}

// Get the existing gallery and logo
$gallery = get_post_meta($listing_id, 'gallery', true);
$logo = get_post_meta($listing_id, 'logo', true);

echo '<form method="post" action="" enctype="multipart/form-data">';
// Other form fields for editing the listing

// Display existing photos
if (!empty($logo)): ?>
    <div>
        <label>Current Logo:</label><br>
        <img src="<?php echo esc_url($logo); ?>" alt="Logo" style="max-width: 150px;"><br>
        <input type="checkbox" name="remove_logo" value="1"> Remove Logo
    </div>
<?php endif; ?>

<?php if (!empty($gallery) && is_array($gallery)): ?>
    <div>
        <label>Current Gallery:</label><br>
        <?php foreach ($gallery as $key => $photo_url): ?>
            <div style="margin-bottom: 10px;">
                <img src="<?php echo esc_url($photo_url); ?>" alt="Gallery Photo" style="max-width: 150px;"><br>
                <input type="checkbox" name="remove_gallery[]" value="<?php echo esc_attr($key); ?>"> Remove Photo
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- File upload for new photos -->
<label for="gallery">Upload New Photos:</label>
<input type="file" id="gallery" name="gallery[]" multiple><br>

<label for="logo">Upload New Logo:</label>
<input type="file" id="logo" name="logo"><br>

<!-- Submit button -->
<input type="submit" name="update_listing" value="Update Listing">

<?php
// Backend handling for removals and updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_listing'])) {
    // Handle logo removal
    if (isset($_POST['remove_logo']) && $_POST['remove_logo'] == '1') {
        delete_post_meta($listing_id, 'logo');
    }

    // Handle gallery photo removal
    if (isset($_POST['remove_gallery']) && is_array($_POST['remove_gallery'])) {
        $current_gallery = get_post_meta($listing_id, 'gallery', true);
        foreach ($_POST['remove_gallery'] as $key_to_remove) {
            if (isset($current_gallery[$key_to_remove])) {
                unset($current_gallery[$key_to_remove]);
            }
        }
        update_post_meta($listing_id, 'gallery', array_values($current_gallery)); // Reset keys to avoid gaps
    }

    // Handle new logo upload
    if (!empty($_FILES['logo']['name'])) {
        $uploaded_logo = sb_handle_file_upload($_FILES['logo'], 500 * 1024); // 500KB limit
        if (!is_wp_error($uploaded_logo)) {
            update_post_meta($listing_id, 'logo', $uploaded_logo);
        } else {
            echo '<p style="color: red;">Error uploading logo: ' . $uploaded_logo->get_error_message() . '</p>';
        }
    }

    // Handle new gallery photo uploads
    if (!empty($_FILES['gallery']['name'][0])) {
        $current_gallery = get_post_meta($listing_id, 'gallery', true);
        $current_gallery = is_array($current_gallery) ? $current_gallery : array(); // Ensure it's an array
        foreach ($_FILES['gallery']['name'] as $key => $value) {
            if (!empty($value)) {
                $file = array(
                    'name' => $_FILES['gallery']['name'][$key],
                    'type' => $_FILES['gallery']['type'][$key],
                    'tmp_name' => $_FILES['gallery']['tmp_name'][$key],
                    'error' => $_FILES['gallery']['error'][$key],
                    'size' => $_FILES['gallery']['size'][$key],
                );
                $uploaded_file = sb_handle_file_upload($file, 2 * 1024 * 1024); // 2MB limit
                if (!is_wp_error($uploaded_file)) {
                    $current_gallery[] = $uploaded_file;
                } else {
                    echo '<p style="color: red;">Error uploading gallery photo: ' . $uploaded_file->get_error_message() . '</p>';
                }
            }
        }
        update_post_meta($listing_id, 'gallery', $current_gallery);
    }

    echo '<p style="color: green;">Listing updated successfully.</p>';
}
?>
<?php
    // Submit Button
    echo '<button type="submit" class="submit-button" name="update_listing" value="' . esc_attr($listing_id) . '">Update Listing</button>';
    echo '</form>';
    echo '</div><br>';
}

echo '</div>';
} else {
    echo '<p>You have not created any listings yet.</p>';
}

wp_reset_postdata();
?>
