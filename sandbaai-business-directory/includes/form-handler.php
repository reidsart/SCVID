<?php
// Handle form submission
function sb_handle_form_submission() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sb_submit_business'])) {
        // Sanitize and validate input fields
        $business_name = sanitize_text_field($_POST['business_name']);
        $address = sanitize_text_field($_POST['address']);
        $suburb = sanitize_text_field($_POST['suburb']);
        $phone = sanitize_text_field($_POST['phone']);
        $email = sanitize_email($_POST['email']);
        $description = sanitize_textarea_field($_POST['description']);
        $location = sanitize_text_field($_POST['location']);
        $website = !empty($_POST['website']) ? esc_url($_POST['website']) : '';
        $whatsapp = sanitize_text_field($_POST['whatsapp']);
        $facebook = !empty($_POST['facebook']) ? esc_url($_POST['facebook']) : '';
        $tags = !empty($_POST['tags']) ? sanitize_text_field($_POST['tags']) : '';
        $address_privacy = sanitize_text_field($_POST['address_privacy']);
        $suggestions = sanitize_textarea_field($_POST['suggestions']);

        // Validate required fields
        if (empty($business_name) || empty($address) || empty($phone) || empty($email) || empty($description) || empty($location)) {
            echo '<p style="color: red;">Error: Please fill in all required fields.</p>';
            return;
        }

        if (!is_email($email)) {
            echo '<p style="color: red;">Error: Invalid email format.</p>';
            return;
        }

        // Process file uploads (logo and gallery)
        $logo = '';
        if (!empty($_FILES['logo']['name'])) {
            $logo = sb_handle_file_upload($_FILES['logo'], 500 * 1024); // 500KB limit
            if (is_wp_error($logo)) {
                echo '<p style="color: red;">' . $logo->get_error_message() . '</p>';
                return;
            }
        }

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
                    echo '<p style="color: red;">' . $upload->get_error_message() . '</p>';
                    return;
                }

                $gallery[] = $upload;
            }
        }

        // Determine category based on suburb
        $category_slug = strtolower($suburb) === 'sandbaai' ? 'sb_business' : 'ob_business';

        // Create a new business listing post
        $post_id = wp_insert_post(array(
            'post_type' => 'business_listing',
            'post_title' => $business_name,
            'post_status' => 'pending', // Set to Pending Review
            'tax_input' => array(
                'business_category' => array($category_slug), // Assign category
            ),
            'meta_input' => array(
                'address' => $address,
                'suburb' => $suburb,
                'phone' => $phone,
                'email' => $email,
                'description' => $description,
                'location' => $location,
                'website' => $website,
                'whatsapp' => $whatsapp,
                'facebook' => $facebook,
                'logo' => $logo,
                'gallery' => $gallery,
                'tags' => $tags,
                'address_privacy' => $address_privacy,
                'suggestions' => $suggestions,
            ),
        ));

        if ($post_id) {
            echo '<p style="color: green;">Success: Your business listing has been submitted for review.</p>';
        } else {
            echo '<p style="color: red;">Error: Unable to save your business listing. Please try again later.</p>';
        }
    }
}
add_action('init', 'sb_handle_form_submission');

// Handle file uploads
function sb_handle_file_upload($file, $max_size) {
    if ($file['size'] > $max_size) {
        return new WP_Error('upload_error', 'File size exceeds the limit.');
    }

    $upload = wp_handle_upload($file, array('test_form' => false));
    if (isset($upload['error'])) {
        return new WP_Error('upload_error', $upload['error']);
    }

    return $upload['url'];
}

// Render the Add Business Form (Shortcode Function)
function sb_render_add_business_form() {
    ob_start();
    ?>
    <form method="post" enctype="multipart/form-data">
        <h2>Add Your Business</h2>

        <!-- Required Fields -->
        <label for="business_name">Business Name (required):</label>
        <input type="text" id="business_name" name="business_name" required>

        <label for="address">Address (required):</label>
        <input type="text" id="address" name="address" required>

        <label for="suburb">Suburb (default: Sandbaai):</label>
        <input type="text" id="suburb" name="suburb" value="Sandbaai">

        <label for="phone">Phone (required):</label>
        <input type="text" id="phone" name="phone" required>

        <label for="email">Email (required):</label>
        <input type="email" id="email" name="email" required>

        <label for="description">Description (required):</label>
        <textarea id="description" name="description" required></textarea>

        <label for="location">Location in Sandbaai? (yes/no):</label>
        <input type="text" id="location" name="location" required>

        <hr>

        <!-- Optional Fields -->
        <label for="website">Website:</label>
        <input type="url" id="website" name="website" placeholder="https://">

        <label for="whatsapp">WhatsApp Number:</label>
        <input type="text" id="whatsapp" name="whatsapp">

        <label for="facebook">Facebook Page URL:</label>
        <input type="url" id="facebook" name="facebook" placeholder="https://">

        <label for="logo">Logo (JPEG/PNG, max 500KB):</label>
        <input type="file" id="logo" name="logo" accept="image/jpeg, image/png">

        <label for="gallery">Photo Gallery (up to 5, max 2MB each):</label>
        <input type="file" id="gallery" name="gallery[]" accept="image/jpeg, image/png" multiple>

        <label for="tags">Tags (separate with commas, add up to 2):</label>
        <input type="text" id="tags" name="tags">

        <label for="address_privacy">Hide Address? (yes/no):</label>
        <input type="radio" id="address_privacy_yes" name="address_privacy" value="yes"> Yes
        <input type="radio" id="address_privacy_no" name="address_privacy" value="no" checked> No

        <label for="suggestions">Suggestions:</label>
        <textarea id="suggestions" name="suggestions"></textarea>

        <br>
        <input type="submit" name="sb_submit_business" value="Submit Business">
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode('sb_add_business_form', 'sb_render_add_business_form');