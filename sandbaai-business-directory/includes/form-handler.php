<?php
// Function to handle file uploads
function sb_handle_file_upload($file, $max_size) {
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return new WP_Error('upload_error', 'File upload failed.');
    }

    // Validate file size
    if ($file['size'] > $max_size) {
        return new WP_Error('file_size_error', 'File exceeds the maximum allowed size.');
    }

    // Validate file type (allow only JPEG and PNG)
    $allowed_types = array('image/jpeg', 'image/png');
    if (!in_array($file['type'], $allowed_types)) {
        return new WP_Error('file_type_error', 'Invalid file type. Only JPEG and PNG are allowed.');
    }

    // Upload the file to WordPress uploads directory
    $upload = wp_handle_upload($file, array('test_form' => false));
    if (isset($upload['error'])) {
        return new WP_Error('upload_error', $upload['error']);
    }

    return $upload['url']; // Return the file URL on success
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

        <label for="business_address">Business Address*:</label>
        <input type="text" id="business_address" name="business_address" required>

        <label for="address_privacy">Hide Address?:</label>
        <input type="radio" id="address_privacy_yes" name="address_privacy" value="1"> Yes
        <input type="radio" id="address_privacy_no" name="address_privacy" value="0" checked> No<br>

        <label for="business_suburb">Business Suburb*:</label>
        <input type="text" id="business_suburb" name="business_suburb" value="Sandbaai" required>

        <label for="business_phone">Business Phone*:</label>
        <input type="text" id="business_phone" name="business_phone" required>

        <label for="business_email">Business Email*:</label>
        <input type="email" id="business_email" name="business_email" required>

        <label for="business_description">Business Description*:</label>
        <textarea id="business_description" name="business_description" required></textarea>

        <hr>
        <label for="business_website">Business Website: (optional)</label>
        <input type="text" id="business_website" name="business_website">

        <label for="business_whatsapp">WhatsApp Number:</label>
        <input type="text" id="business_whatsapp" name="business_whatsapp">

        <label for="facebook">Business Facebook Page:</label>
        <input type="text" id="facebook" name="facebook">

        <label for="tags">Add up to 2 categories for your business:</label>
        <div style="display: flex; align-items: center; gap: 10px;">
            <div>
                <label for="tag_1">1st Category:</label>
                <select id="tag_1" name="tags[]" required>
                    <option value="">Select 1st Category</option>
                    <?php
                    $tags = get_tags(array('hide_empty' => false)); // Fetch all tags
                    if ($tags) {
                        foreach ($tags as $tag) {
                            echo '<option value="' . esc_attr($tag->term_id) . '">' . esc_html($tag->name) . '</option>';
                        }
                    }
                    ?>
                </select>
            </div>

            <div>
                <label for="tag_2">2nd Category:</label>
                <select id="tag_2" name="tags[]">
                    <option value="">Select 2nd Category</option>
                    <?php
                    if ($tags) { // Reuse the fetched tags
                        foreach ($tags as $tag) {
                            echo '<option value="' . esc_attr($tag->term_id) . '">' . esc_html($tag->name) . '</option>';
                        }
                    }
                    ?>
                </select>
            </div>
        </div><br>

        <label for="logo">Upload Business Logo:</label>
        <input type="file" id="logo" name="logo">

        <label for="gallery">Upload photos for your business:</label>
        <input type="file" id="gallery" name="gallery[]" multiple><br>

        <label>**<i>add more photos on the edit page once your business is approved</i></label><br><br>

        <label for="suggestions">Suggestions & Feedback:</label>
        <textarea id="suggestions" name="suggestions"></textarea>

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
        // Temporarily disable Paystack save_post_meta during frontend submissions
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
        // Sanitize and validate input fields
        $business_name = sanitize_text_field($_POST['business_name']);
        $business_address = sanitize_text_field($_POST['business_address']);
        $business_suburb = sanitize_text_field($_POST['business_suburb']);
        $business_phone = sanitize_text_field($_POST['business_phone']);
        $business_email = sanitize_email($_POST['business_email']);
        $business_description = sanitize_textarea_field($_POST['business_description']);
        $business_website = sanitize_text_field($_POST['business_website'] ?? '');
        $business_whatsapp = sanitize_text_field($_POST['business_whatsapp']);
        $facebook = sanitize_text_field($_POST['facebook'] ?? '');
        $tags = !empty($_POST['tags']) ? array_map('intval', $_POST['tags']) : array();
        $address_privacy = isset($_POST['address_privacy']) ? '1' : '0';
        $suggestions = sanitize_textarea_field($_POST['suggestions']);

        // Process logo upload
        $logo = '';
        if (!empty($_FILES['logo']['name'])) {
            $logo = sb_handle_file_upload($_FILES['logo'], 500 * 1024); // 500KB limit
            if (is_wp_error($logo)) {
                echo '<p style="color: red;">Logo Upload Error: ' . $logo->get_error_message() . '</p>';
                return;
            }
        }

        // Process gallery uploads
        $gallery = array();
        if (!empty($_FILES['gallery']['name'][0])) {
            foreach ($_FILES['gallery']['name'] as $key => $value) {
                $file = array(
                    'name' => $_FILES['gallery']['name'][$key],
                    'type' => $_FILES['gallery']['type'][$key],
                    'tmp_name' => $_FILES['gallery']['tmp_name'][$key],
                    'error' => $_FILES['gallery']['error'][$key],
                    'size' => $_FILES['gallery']['size'][$key],
                );

                $upload = sb_handle_file_upload($file, 2 * 1024 * 1024); // 2MB limit
                if (is_wp_error($upload)) {
                    echo '<p style="color: red;">Gallery Upload Error: ' . $upload->get_error_message() . '</p>';
                    return;
                }

                $gallery[] = $upload;
            }
        }

        // Validate required fields
        if (empty($business_name) || empty($business_address) || empty($business_phone) || empty($business_email) || empty($business_description)) {
            echo '<p style="color: red;">Error: Please fill in all required fields.</p>';
            return;
        }

        if (!is_email($business_email)) {
            echo '<p style="color: red;">Error: Invalid email format.</p>';
            return;
        }

// Determine category based on suburb
$category_slug = strtolower($business_suburb) === 'sandbaai' ? 'sb_business' : 'ob_business';

// Create a new business listing post
$post_id = wp_insert_post(array(
    'post_type' => 'business_listing',
    'post_title' => $business_name,
    'post_status' => 'pending', // Set to Pending Review
    'post_author' => get_current_user_id(), // Explicitly set the post author
    'tax_input' => array(
        'post_tag' => $tags, // Assign tags
    ),
    'meta_input' => array(
        'business_address' => $business_address,
        'business_suburb' => $business_suburb,
        'business_phone' => $business_phone,
        'business_email' => $business_email,
        'business_description' => $business_description,
        'business_website' => $business_website,
        'business_whatsapp' => $business_whatsapp,
        'facebook' => $facebook,
        'logo' => $logo,
        'gallery' => $gallery,
        'address_privacy' => $address_privacy,
        'suggestions' => $suggestions,
    ),
));

// Debug: Log the post ID and category slug
error_log("Post ID: $post_id, Category Slug: $category_slug");

// Check if the post was created successfully
if ($post_id && !is_wp_error($post_id)) {
    // Ensure the category term exists
    if (!term_exists($category_slug, 'business_category')) {
        // Create the term if it doesn't exist
        wp_insert_term(
            $category_slug === 'sb_business' ? 'Sandbaai Businesses' : 'Overberg Businesses',
            'business_category',
            array('slug' => $category_slug)
        );
    }

    // Assign the category taxonomy to the post
    wp_set_post_terms($post_id, $category_slug, 'business_category');

    // Debug: Log success
    error_log("Successfully set category $category_slug for post $post_id");
} else {
    // Debug: Log error
    error_log("Error creating post: " . print_r($post_id, true));
}
// Automatically add https:// to website or Facebook URL if missing
function sb_sanitize_urls($post_data) {
    // Automatically prepend "https://" to URLs missing a valid prefix
    if (!empty($post_data['business_website']) && !preg_match('/^https?:\/\//', $post_data['business_website'])) {
        $post_data['business_website'] = 'https://' . ltrim($post_data['business_website'], '/');
    }
    if (!empty($post_data['facebook']) && !preg_match('/^https?:\/\//', $post_data['facebook'])) {
        $post_data['facebook'] = 'https://' . ltrim($post_data['facebook'], '/');
    }
    return $post_data;
}

// Hook the function to sanitize URLs during both add and edit form submissions
add_filter('pre_post_form_submission_data', 'sb_sanitize_urls');
add_filter('pre_post_edit_form_submission_data', 'sb_sanitize_urls');

// If the listing was created successfully
        if ($post_id) {
            echo '<p style="color: green;">Success: Your business listing has been submitted for review.</p>';
if ($post_id) {
    // Redirect to the edit page with the new listing ID
    $edit_page_url = home_url('/edit-listing/?listing_id=' . $post_id);
    wp_redirect($edit_page_url);
    exit;
}
        } else {
// If the listing was not created successfully
            echo '<p style="color: red;">Error: Unable to save your business listing. Please try again later.</p>';
        }
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
        $updated_phone = sanitize_text_field($_POST['business_phone'] ?? '');
        $updated_email = sanitize_email($_POST['business_email'] ?? '');
        $updated_address = sanitize_text_field($_POST['business_address'] ?? '');
        $updated_address_privacy = sanitize_text_field($_POST['address_privacy'] ?? '');
        $updated_whatsapp = sanitize_text_field($_POST['business_whatsapp'] ?? '');
        $updated_website = sanitize_text_field($_POST['business_website'] ?? '');
        $updated_facebook = sanitize_text_field($_POST['facebook'] ?? '');

        // Sanitize and validate tags
        $updated_tags = isset($_POST['tags']) ? array_map('intval', $_POST['tags']) : array();
        $updated_tags = array_filter($updated_tags); // Remove any invalid or empty values

        // Handle gallery updates
        if (isset($_POST['remove_gallery']) || !empty($_FILES['gallery']['name'][0])) {
            // Get the existing gallery meta
            $existing_gallery = get_post_meta($listing_id, 'gallery', true);
            if (!is_array($existing_gallery)) {
                $existing_gallery = [];
            }
            error_log("Gallery meta for listing $listing_id: " . print_r($existing_gallery, true));

            // Remove selected gallery photos
            if (isset($_POST['remove_gallery']) && is_array($_POST['remove_gallery'])) {
                foreach ($_POST['remove_gallery'] as $remove_index) {
                    if (isset($existing_gallery[$remove_index])) {
                        unset($existing_gallery[$remove_index]);
                    }
                }
                // Re-index the array after removing items
                $existing_gallery = array_values($existing_gallery);
            }

            // Handle new gallery uploads
            if (!empty($_FILES['gallery']['name'][0])) {
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
                            $existing_gallery[] = $uploaded_file;
                        } else {
                            echo '<p style="color: red;">Error uploading gallery photo: ' . $uploaded_file->get_error_message() . '</p>';
                        }
                    }
                }
            }

            // Update the gallery meta field
            update_post_meta($listing_id, 'gallery', $existing_gallery);
            error_log("Updated gallery meta for listing $listing_id: " . print_r($existing_gallery, true));
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

        // Save tags
        wp_set_post_terms($listing_id, $updated_tags, 'post_tag');
        error_log("Tags updated: " . implode(', ', $updated_tags));

        echo '<p style="color: green;">Listing updated successfully.</p>';

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