<?php
// Register the shortcode
function sb_register_add_business_form_shortcode() {
    add_shortcode('sb_add_business_form', 'sb_render_add_business_form');
}
add_action('init', 'sb_register_add_business_form_shortcode');

// Shortcode rendering function
function sb_render_add_business_form() {
    ob_start(); // Start output buffering
    ?>
    <form method="post" enctype="multipart/form-data" action="">
        <label for="business_name">Business Name:</label>
        <input type="text" id="business_name" name="business_name" required>

        <label for="address">Address:</label>
        <input type="text" id="address" name="address" required>

        <label for="suburb">Suburb:</label>
        <input type="text" id="suburb" name="suburb" required>

        <label for="phone">Phone:</label>
        <input type="text" id="phone" name="phone" required>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>

        <label for="description">Description:</label>
        <textarea id="description" name="description" required></textarea>

        <label for="website">Website:</label>
        <input type="url" id="website" name="website">

        <label for="whatsapp">WhatsApp:</label>
        <input type="text" id="whatsapp" name="whatsapp">

        <label for="facebook">Facebook:</label>
        <input type="url" id="facebook" name="facebook">

        <label for="tags">Tags:</label>
        <input type="text" id="tags" name="tags" placeholder="Enter comma-separated tags">

        <label for="address_privacy">Address Privacy:</label>
        <input type="checkbox" id="address_privacy" name="address_privacy" value="1"> Keep Address Private

        <label for="suggestions">Suggestions:</label>
        <textarea id="suggestions" name="suggestions"></textarea>

        <label for="logo">Logo:</label>
        <input type="file" id="logo" name="logo">

        <label for="gallery">Gallery:</label>
        <input type="file" id="gallery" name="gallery[]" multiple>

        <input type="submit" name="sb_submit_business" value="Submit">
    </form>
    <?php
    return ob_get_clean(); // Return the buffered content
}

// Handle form submission
function sb_handle_form_submission() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sb_submit_business'])) {
        // Sanitize and validate input fields
        $business_name = sanitize_text_field($_POST['business_name']);
        $business_address = sanitize_text_field($_POST['address']); // Updated to match 'business_address'
        $business_suburb = sanitize_text_field($_POST['suburb']); // Updated to match 'business_suburb'
        $business_phone = sanitize_text_field($_POST['phone']); // Updated to match 'business_phone'
        $business_email = sanitize_email($_POST['email']); // Updated to match 'business_email'
        $business_description = sanitize_textarea_field($_POST['description']); // Updated to match 'business_description'
        $business_website = !empty($_POST['website']) ? esc_url($_POST['website']) : ''; // Updated to match 'business_website'
        $business_whatsapp = sanitize_text_field($_POST['whatsapp']); // Updated to match 'business_whatsapp'
        $facebook = !empty($_POST['facebook']) ? esc_url($_POST['facebook']) : '';
        $tags = !empty($_POST['tags']) ? array_map('intval', $_POST['tags']) : array(); // Tags as array
        $address_privacy = sanitize_text_field($_POST['address_privacy']);
        $suggestions = sanitize_textarea_field($_POST['suggestions']);

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
            'tax_input' => array(
                'business_category' => array($category_slug), // Assign category
                'post_tag' => $tags, // Assign tags
            ),
            'meta_input' => array(
                'business_address' => $business_address, // Changed key
                'business_suburb' => $business_suburb, // Changed key
                'business_phone' => $business_phone, // Changed key
                'business_email' => $business_email, // Changed key
                'business_description' => $business_description, // Changed key
                'business_website' => $business_website, // Changed key
                'business_whatsapp' => $business_whatsapp, // Changed key
                'facebook' => $facebook,
                'logo' => $logo,
                'gallery' => $gallery,
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
?>
