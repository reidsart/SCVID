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

        <label for="business_address">Business Address:</label>
        <input type="text" id="business_address" name="business_address" required>

        <label for="address_privacy">Keep Address Private:</label>
        <input type="checkbox" id="address_privacy" name="address_privacy" value="1">

        <label for="business_suburb">Business Suburb:</label>
        <input type="text" id="business_suburb" name="business_suburb" value="Sandbaai" required>

        <label for="business_phone">Business Phone:</label>
        <input type="text" id="business_phone" name="business_phone" required>

        <label for="business_email">Business Email:</label>
        <input type="email" id="business_email" name="business_email" required>

        <label for="business_description">Business Description:</label>
        <textarea id="business_description" name="business_description" required></textarea>

        <label for="business_website">Business Website:</label>
        <input type="url" id="business_website" name="business_website">

        <label for="business_whatsapp">WhatsApp Number:</label>
        <input type="text" id="business_whatsapp" name="business_whatsapp">

        <label for="facebook">Business Facebook Page:</label>
        <input type="url" id="facebook" name="facebook">

        <!-- Dropdowns for Tags -->
        <label for="tag_1">Tag 1:</label>
        <select id="tag_1" name="tags[]" required>
            <option value="">Select Tag 1</option>
            <?php
            $tags = get_tags(array('hide_empty' => false)); // Fetch all tags
            if ($tags) {
                foreach ($tags as $tag) {
                    echo '<option value="' . esc_attr($tag->term_id) . '">' . esc_html($tag->name) . '</option>';
                }
            }
            ?>
        </select>

        <label for="tag_2">Tag 2:</label>
        <select id="tag_2" name="tags[]" required>
            <option value="">Select Tag 2</option>
            <?php
            if ($tags) { // Reuse the fetched tags
                foreach ($tags as $tag) {
                    echo '<option value="' . esc_attr($tag->term_id) . '">' . esc_html($tag->name) . '</option>';
                }
            }
            ?>
        </select>

        <label for="suggestions">Suggestions or Feedback:</label>
        <textarea id="suggestions" name="suggestions"></textarea>

        <label for="logo">Upload Business Logo:</label>
        <input type="file" id="logo" name="logo">

        <label for="gallery">Upload photos for your business:</label>
        <input type="file" id="gallery" name="gallery[]" multiple>

        <br>

        <input type="submit" name="sb_submit_business" value="Submit">

        <br>
    </form>
    <?php
    return ob_get_clean(); // Return the buffered content
}

// Handle form submission
function sb_handle_form_submission() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sb_submit_business'])) {
        // Sanitize and validate input fields
        $business_name = sanitize_text_field($_POST['business_name']);
        $business_address = sanitize_text_field($_POST['business_address']);
        $business_suburb = sanitize_text_field($_POST['business_suburb']);
        $business_phone = sanitize_text_field($_POST['business_phone']);
        $business_email = sanitize_email($_POST['business_email']);
        $business_description = sanitize_textarea_field($_POST['business_description']);
        $business_website = !empty($_POST['business_website']) ? esc_url($_POST['business_website']) : '';
        $business_whatsapp = sanitize_text_field($_POST['business_whatsapp']);
        $facebook = !empty($_POST['facebook']) ? esc_url($_POST['facebook']) : '';
        $tags = !empty($_POST['tags']) ? array_map('intval', $_POST['tags']) : array();
        $address_privacy = isset($_POST['address_privacy']) ? '1' : '0';
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

        if ($post_id) {
            echo '<p style="color: green;">Success: Your business listing has been submitted for review.</p>';
        } else {
            echo '<p style="color: red;">Error: Unable to save your business listing. Please try again later.</p>';
        }
    }
}
add_action('init', 'sb_handle_form_submission');
?>
