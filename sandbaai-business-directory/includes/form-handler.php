<?php
// Function to handle file uploads
function sb_handle_file_upload($file, $max_size) {
    error_log("File data received in sb_handle_file_upload: " . print_r($file, true));

    if ($file['error'] !== UPLOAD_ERR_OK) {
        error_log("File upload error: " . $file['error']);
        return new WP_Error('upload_error', 'File upload failed.');
    }

    if ($file['size'] > $max_size) {
        error_log("File size exceeds limit: " . $file['size']);
        return new WP_Error('file_size_error', 'File exceeds the maximum allowed size.');
    }

    $allowed_types = array('image/jpeg', 'image/png');
    if (!in_array($file['type'], $allowed_types)) {
        error_log("Invalid file type: " . $file['type']);
        return new WP_Error('file_type_error', 'Invalid file type. Only JPEG and PNG are allowed.');
    }

    $upload = wp_handle_upload($file, array('test_form' => false));
    error_log("Upload result inside sb_handle_file_upload: " . print_r($upload, true));
    //debugging
    $upload_dir = wp_upload_dir();
    error_log("Expected upload path: " . $upload_dir['basedir'] . '/' . basename($uploaded_logo));

    if (isset($upload['error'])) {
        error_log("Upload error: " . $upload['error']);
        return new WP_Error('upload_error', $upload['error']);
    }

    return $upload['url'];
}

// Function to sanitize business phone number
function sb_sanitize_phone_number($phone) {
    // Remove parentheses, hyphens, and spaces
    return preg_replace('/[\(\)\-\s]/', '', $phone);
}

// Register the add business form shortcode
function sb_register_add_business_form_shortcode() {
    add_shortcode('sb_add_business_form', 'sb_render_add_business_form');
}
add_action('init', 'sb_register_add_business_form_shortcode');

// Shortcode rendering function for adding business form
function sb_render_add_business_form() {
    ob_start(); // Start output buffering
    ?>
    <form method="post" enctype="multipart/form-data" action="">
        <label for="business_name">Business Name* (cannot be changed later):</label>
        <input type="text" id="business_name" name="business_name" required>

        <label for="business_address">Business Address (required):</label>
        <input type="text" id="business_address" name="business_address" required>

        <label for="address_privacy">Hide Address?:</label>
        <input type="radio" id="address_privacy_yes" name="address_privacy" value="1"> Yes
        <input type="radio" id="address_privacy_no" name="address_privacy" value="0" checked> No<br>

        <label for="business_suburb">Business Suburb (required):</label>
        <input type="text" id="business_suburb" name="business_suburb" value="Sandbaai" required>

        <label for="business_phone">Business Phone (required):</label>
        <input type="text" id="business_phone" name="business_phone" required>

        <label for="business_email">Business Email (required):</label>
        <input type="email" id="business_email" name="business_email" required>

        <label for="business_description">Business Description (required):</label>
        <textarea id="business_description" name="business_description" required></textarea>

        <hr>
        <label for="business_website">Business Website: (optional)</label>
        <input type="text" id="business_website" name="business_website">

        <label for="business_whatsapp">WhatsApp Number: (optional)</label>
        <input type="text" id="business_whatsapp" name="business_whatsapp">

        <label for="facebook">Business Facebook Page: (optional)</label>
        <input type="text" id="facebook" name="facebook">

        <label for="logo">Upload Business Logo:</label>
        <input type="file" id="logo" name="logo">

        <label for="gallery">Upload photos for your business:</label>
        <input type="file" id="gallery" name="gallery[]" multiple><br>

        <label>**<i>add more photos on the edit page once your business is approved</i></label><br><br>
<?php
// Display the tag selection dropdowns
echo '<label for="business_tag_1">Select Tag 1: (required to list your business)</label>';
echo '<select name="business_tag_1" id="business_tag_1" required>';
echo '<option value="" disabled selected>Select the first tag</option>';
$tags = get_terms(array(
    'taxonomy' => 'business_tag',
    'hide_empty' => false,
));
foreach ($tags as $tag) {
    echo '<option value="' . esc_attr($tag->term_id) . '">' . esc_html($tag->name) . '</option>';
}
echo '</select>';

echo '<label for="business_tag_2">Select Tag 2: (optional)</label>';
echo '<select name="business_tag_2" id="business_tag_2">';
echo '<option value="" disabled selected>Select the second tag (optional)</option>';
foreach ($tags as $tag) {
    echo '<option value="' . esc_attr($tag->term_id) . '">' . esc_html($tag->name) . '</option>';
}
echo '</select>';
?>
        <label for="suggestions">Suggestions & Feedback:</label>
        <textarea id="suggestions" name="suggestions"></textarea>
<?php echo '<script src="' . esc_url(plugin_dir_url(__FILE__) . 'js/tag-restriction.js') . '"></script>'; ?>
        <!-- Submit and Cancel Buttons -->
        <div style="display: flex; gap: 10px; align-items: center;">
            <input type="submit" name="sb_submit_business" value="Submit" style="width: 150px;">
            <button type="button" onclick="window.location.href='<?php echo esc_url(home_url('/business-directory/')); ?>';" style="width: 150px;">Cancel</button>
        </div>
    </form><br>
    <?php
    return ob_get_clean(); // Return the buffered content
}

// Handle form submission for adding business
function sb_handle_form_submission() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sb_submit_business'])) {
        // Debugging: Log the POST data and uploaded files
        error_log("Submitted POST data: " . print_r($_POST, true));
        error_log("Uploaded FILES data: " . print_r($_FILES, true));

        // Debugging: Log the remove_logo checkbox value
        if (isset($_POST['remove_logo'])) {
            error_log("Remove logo value: " . print_r($_POST['remove_logo'], true));
        } else {
            error_log("Remove logo checkbox not set.");
        }
        global $wp_filter;

        // Remove Paystack's save_post_meta hook temporarily
        $paystack_instance = null;
        if (isset($wp_filter['save_post'])) {
            foreach ($wp_filter['save_post']->callbacks as $priority => $callbacks) {
                foreach ($callbacks as $callback_key => $callback_data) {
                    if (
                        is_array($callback_data['function']) &&
                        is_object($callback_data['function'][0]) &&
                        get_class($callback_data['function'][0]) === 'paystack\payment_forms\Forms_Update' &&
                        $callback_data['function'][1] === 'save_post_meta'
                    ) {
                        $paystack_instance = $callback_data['function'][0];
                        remove_action('save_post', [$paystack_instance, 'save_post_meta'], $priority);
                        error_log('Paystack save_post_meta removed for frontend submission.');
                        break 2;
                    }
                }
            }
        }

        // Sanitize and validate input fields
        $business_name = sanitize_text_field($_POST['business_name']);
        $business_address = sanitize_text_field($_POST['business_address']);
        $business_suburb = sanitize_text_field($_POST['business_suburb']);
        $business_phone = sb_sanitize_phone_number(sanitize_text_field($_POST['business_phone']));
        $business_email = sanitize_email($_POST['business_email']);
        $business_description = sanitize_textarea_field($_POST['business_description']);
        $business_website = sanitize_text_field($_POST['business_website'] ?? '');
        $business_whatsapp = sanitize_text_field($_POST['business_whatsapp']);
        $facebook = sanitize_text_field($_POST['facebook'] ?? '');
        $address_privacy = isset($_POST['address_privacy']) ? '1' : '0';
        $suggestions = sanitize_textarea_field($_POST['suggestions']);

        // Validate required fields
        if (empty($business_name) || empty($business_address) || empty($business_phone) || empty($business_email) || empty($business_description)) {
            add_session_message('Error: Please fill in all required fields.', 'error');
            return;
        }

        if (!is_email($business_email)) {
            add_session_message('Error: Invalid email format.', 'error');
            return;
        }

// Save tags to the listing
if (!empty($selected_tags)) {
    wp_set_object_terms($post_id, $selected_tags, 'business_tag');
    error_log('Business tags saved: ' . implode(', ', $selected_tags)); // Debugging
}

        // Determine category based on suburb
        $category_slug = strtolower($business_suburb) === 'sandbaai' ? 'sb_business' : 'ob_business';

        // Ensure the category term exists
        if (!term_exists($category_slug, 'business_category')) {
            wp_insert_term(
                $category_slug === 'sb_business' ? 'Sandbaai Businesses' : 'Overberg Businesses',
                'business_category',
                array('slug' => $category_slug)
            );
        }

        // Create a new business listing post
        $post_id = wp_insert_post(array(
            'post_type' => 'business_listing',
            'post_title' => $business_name,
            'post_status' => 'pending', // Set to Pending Review
            'post_author' => get_current_user_id(),
            'meta_input' => array(
                'business_address' => $business_address,
                'business_suburb' => $business_suburb,
                'business_phone' => $business_phone,
                'business_email' => $business_email,
                'business_description' => $business_description,
                'business_website' => $business_website,
                'business_whatsapp' => $business_whatsapp,
                'facebook' => $facebook,
                'address_privacy' => $address_privacy,
                'suggestions' => $suggestions,
            ),
        ));
// Debug: Log post creation
if (!$post_id || is_wp_error($post_id)) {
    error_log("Error creating post: " . print_r($post_id, true));
    add_session_message('Error: Unable to save your business listing. Please contact us.', 'error');
    return;
} else {
    // Assign the category to the post
    wp_set_object_terms($post_id, $category_slug, 'business_category');
    error_log("Category '$category_slug' assigned to post ID $post_id");
}
// Process selected tags from two dropdowns
$selected_tags = array();

if (!empty($_POST['business_tag_1'])) {
    $selected_tags[] = intval($_POST['business_tag_1']);
}
if (!empty($_POST['business_tag_2'])) { // Only add the second tag if it is explicitly selected
    $selected_tags[] = intval($_POST['business_tag_2']);
}

// Ensure no more than 2 tags are selected
if (count($selected_tags) > 2) {
    error_log('Error: More than 2 tags were selected.');
    add_session_message('Error: You can only select up to 2 tags.', 'error');
    return;
}

// Save the tags to the listing
wp_set_object_terms($post_id, $selected_tags, 'business_tag');
error_log('Tags saved for post ID ' . $post_id . ': ' . implode(', ', $selected_tags));

// Redirect to the main directory page with a success message
wp_redirect(home_url('/business-directory/?submission=pending'));
exit; // Prevent further execution
    }
}
add_action('init', 'sb_handle_form_submission');

// Edit business listings
// Temporarily disable Paystack save_post_meta during frontend submissions
add_action('init', function () {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_listing'])) {
        global $wp_filter;

        // Find the Paystack instance and remove the save_post_meta hook
        $paystack_instance = null;

        if (isset($wp_filter['save_post'])) {
            foreach ($wp_filter['save_post']->callbacks as $priority => $callbacks) {
                foreach ($callbacks as $callback_key => $callback_data) {
                    if (
                        is_array($callback_data['function']) &&
                        is_object($callback_data['function'][0]) &&
                        get_class($callback_data['function'][0]) === 'paystack\payment_forms\Forms_Update' &&
                        $callback_data['function'][1] === 'save_post_meta'
                    ) {
                        $paystack_instance = $callback_data['function'][0];
                        remove_action('save_post', [$paystack_instance, 'save_post_meta'], $priority);
                        error_log('Paystack save_post_meta removed for frontend submission.');
                        break 2;
                    }
                }
            }
        }

        // Call the form handler logic here
        sb_handle_edit_form_submission();

        // Do NOT re-enable the Paystack save_post_meta hook for frontend submissions
        // It will only be added back by WordPress when needed in the backend.
        if ($paystack_instance) {
            error_log('Paystack save_post_meta was NOT re-added after frontend submission.');
        }
    }
});

// Function to handle editing listing updates
function sb_handle_edit_form_submission() {
    global $sb_is_updating_post;
//debugging
if (!empty($_FILES['logo']['name'])) {
    $file = $_FILES['logo'];

    error_log("Logo file data: " . print_r($file, true));

    $uploaded_logo = sb_handle_file_upload($file, 2 * 1024 * 1024); // 2MB limit

    if (is_wp_error($uploaded_logo)) {
        error_log("Logo upload error: " . $uploaded_logo->get_error_message());
    } else {
        error_log("Uploaded logo URL: " . $uploaded_logo);

        if (update_post_meta($listing_id, 'logo', $uploaded_logo)) {
            error_log("Logo meta updated successfully for listing ID: " . $listing_id);
        } else {
            error_log("Failed to update logo meta for listing ID: " . $listing_id);
        }
    }
}
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_listing'])) {
        // Set the flag to prevent recursive updates
        if ($sb_is_updating_post) {
            error_log("Preventing recursive post update");
            return;
        }
        $sb_is_updating_post = true;

        // Use the correct field name for the listing ID
        $listing_id = intval($_POST['listing_id']);

        // Debug the submitted data
        error_log("Submitted POST data: " . print_r($_POST, true));

        // Check if the current user is the author of the listing
        $current_user_id = get_current_user_id();
        $listing = get_post($listing_id);

        if (!$listing || $listing->post_author != $current_user_id) {
            echo '<p style="color: red;">You do not have permission to edit this listing.</p>';
            $sb_is_updating_post = false; // Reset flag before returning
            return;
        }

        // Get the post title from the form input
        $post_title = isset($_POST['post_title']) ? sanitize_text_field($_POST['post_title']) : $listing->post_title;

        // Sanitize and update post fields
        $updated_description = sanitize_textarea_field($_POST['business_description'] ?? '');
        $updated_phone = sb_sanitize_phone_number(sanitize_text_field($_POST['business_phone'] ?? ''));
        $updated_email = sanitize_email($_POST['business_email'] ?? '');
        $updated_address = sanitize_text_field($_POST['business_address'] ?? '');
        $updated_address_privacy = sanitize_text_field($_POST['address_privacy'] ?? '');
        $updated_whatsapp = sanitize_text_field($_POST['business_whatsapp'] ?? '');
        $updated_website = sanitize_text_field($_POST['business_website'] ?? '');
        $updated_facebook = sanitize_text_field($_POST['facebook'] ?? '');

         // Handle gallery updates
         if (!empty($_POST['remove_gallery']) || !empty($_FILES['gallery']['name'][0])) {
             $existing_gallery = get_post_meta($listing_id, 'gallery', true);
             if (!is_array($existing_gallery)) {
                 $existing_gallery = [];
             }
// Handle logo upload
if (!empty($_FILES['logo']['name'])) {
    $file = [
        'name' => $_FILES['logo']['name'],
        'type' => $_FILES['logo']['type'],
        'tmp_name' => $_FILES['logo']['tmp_name'],
        'error' => $_FILES['logo']['error'],
        'size' => $_FILES['logo']['size'],
    ];

    // Debugging: Log file data
    error_log("Logo file data: " . print_r($file, true));

    // Call the upload function
    $uploaded_logo = sb_handle_file_upload($file, 2 * 1024 * 1024); // 2MB limit

    // Debugging: Log upload result
    if (is_wp_error($uploaded_logo)) {
        error_log("Logo upload error: " . $uploaded_logo->get_error_message());
    } else {
        error_log("Uploaded logo URL: " . $uploaded_logo);
    }

    // Update the meta field
    if (!is_wp_error($uploaded_logo) && update_post_meta($listing_id, 'logo', $uploaded_logo)) {
        error_log("Logo meta updated successfully for listing ID: " . $listing_id);
    } else {
        error_log("Failed to update logo meta for listing ID: " . $listing_id);
    }
}

// Handle logo removal
if (!empty($_POST['remove_logo']) && intval($_POST['remove_logo']) === 1) {
    error_log("Attempting to remove logo for listing ID: " . $listing_id); // Debug log
    $result = delete_post_meta($listing_id, 'logo'); // Remove logo meta
    if ($result) {
        error_log("Logo removed successfully for listing ID: " . $listing_id);
    } else {
        error_log("Failed to remove logo for listing ID: " . $listing_id);
    }
}

// Handle logo upload
if (!empty($_FILES['logo']['name'])) {
    $file = [
        'name' => $_FILES['logo']['name'],
        'type' => $_FILES['logo']['type'],
        'tmp_name' => $_FILES['logo']['tmp_name'],
        'error' => $_FILES['logo']['error'],
        'size' => $_FILES['logo']['size'],
    ];

    // Debugging: Log file data
    error_log("Logo file data: " . print_r($file, true));
error_log("Upload result: " . print_r($upload, true));
    $uploaded_logo = sb_handle_file_upload($file, 2 * 1024 * 1024); // 2MB limit

    // Debugging: Log upload result
    if (is_wp_error($uploaded_logo)) {
        error_log("Logo upload error: " . $uploaded_logo->get_error_message());
    } else {
        error_log("Uploaded logo URL: " . $uploaded_logo);
    }

    // Update the meta field
    if (!is_wp_error($uploaded_logo) && update_post_meta($listing_id, 'logo', $uploaded_logo)) {
        error_log("Logo meta updated successfully for listing ID: " . $listing_id);
    } else {
        error_log("Failed to update logo meta for listing ID: " . $listing_id);
    }
}
             // Remove selected gallery photos
             if (!empty($_POST['remove_gallery'])) {
                 foreach ($_POST['remove_gallery'] as $remove_index) {
                     if (isset($existing_gallery[$remove_index])) {
                         unset($existing_gallery[$remove_index]);
                     }
                 }
                 $existing_gallery = array_values($existing_gallery); // Re-index
             }
 
             // Handle new uploads
             if (!empty($_FILES['gallery']['name'][0])) {
                 foreach ($_FILES['gallery']['name'] as $key => $value) {
                     if (!empty($value) && count($existing_gallery) < 5) {
                         $file = [
                             'name' => $_FILES['gallery']['name'][$key],
                             'type' => $_FILES['gallery']['type'][$key],
                             'tmp_name' => $_FILES['gallery']['tmp_name'][$key],
                             'error' => $_FILES['gallery']['error'][$key],
                             'size' => $_FILES['gallery']['size'][$key],
                         ];
                         $uploaded_file = sb_handle_file_upload($file, 2 * 1024 * 1024); // 2MB limit
                         if (!is_wp_error($uploaded_file)) {
                             $existing_gallery[] = $uploaded_file;
                         } else {
                             echo '<p style="color: red;">Error uploading photo: ' . $uploaded_file->get_error_message() . '</p>';
                         }
                     }
                 }
             }
 
             // Update gallery meta
             update_post_meta($listing_id, 'gallery', $existing_gallery);
             // Debug after update_post_meta
if (update_post_meta($listing_id, 'logo', $uploaded_logo)) {
    error_log("Logo meta updated successfully for listing ID: " . $listing_id);
} else {
    error_log("Failed to update logo meta for listing ID: " . $listing_id);
}
         }

        // Create a complete post data array with all post content
        $post_data = array(
            'ID' => $listing_id,
            'post_title' => $post_title,
            'post_content' => $updated_description // Ensure this is correct if description is stored as post content
        );

        // Update the post
        $update_result = wp_update_post($post_data, true);

        // Log any errors or success from wp_update_post
        if (is_wp_error($update_result)) {
            error_log("Post update error: " . $update_result->get_error_message());
        } else {
            error_log("Post updated successfully with ID: " . $update_result);
        }

        // Save meta fields explicitly
        update_post_meta($listing_id, 'business_description', $updated_description);
        update_post_meta($listing_id, 'business_phone', $updated_phone);
        update_post_meta($listing_id, 'business_email', $updated_email);
        update_post_meta($listing_id, 'business_address', $updated_address);
        update_post_meta($listing_id, 'business_website', $updated_website);
        update_post_meta($listing_id, 'address_privacy', $updated_address_privacy);
        update_post_meta($listing_id, 'business_whatsapp', $updated_whatsapp);
        update_post_meta($listing_id, 'facebook', $updated_facebook);

        // Log meta updates
        error_log("Meta fields updated for listing ID: $listing_id");

        echo '<p style="color: green;">Listing updated successfully.</p>';

        // Redirect to the single listing page for the updated listing
        $listing_permalink = get_permalink($listing_id);

        if (!is_wp_error($listing_permalink)) {
            wp_redirect($listing_permalink); // Redirect to the single listing page
            exit; // Stop further execution
        } else {
            // Handle error in getting permalink
            wp_die("Error: Unable to retrieve the permalink for the updated listing.");
        }

        // Reset the flag at the end of the function
        $sb_is_updating_post = false;
    }
}

// Shortcode to render the edit business form
function sb_render_edit_business_form_shortcode($atts) {
    // Parse shortcode attributes
    $atts = shortcode_atts(
        array(
            'post_id' => 0, // Default to 0 if no post_id is provided
        ),
        $atts
    );

    // Ensure post_id is valid
    $post_id = intval($atts['post_id']);
    if ($post_id <= 0) {
        return '<p style="color: red;">Invalid or missing business listing ID.</p>';
    }

    // Include the template file from the templates directory
    $template_path = plugin_dir_path(__FILE__) . '../templates/edit-business-form.php';
    if (file_exists($template_path)) {
        ob_start();
        include $template_path;
        return ob_get_clean();
    } else {
        return '<p style="color: red;">Form template not found.</p>';
    }
}
add_shortcode('sb_edit_business_form', 'sb_render_edit_business_form_shortcode');
