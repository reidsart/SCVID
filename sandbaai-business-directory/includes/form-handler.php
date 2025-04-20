<?php
// Handle form submission
function sb_handle_form_submission() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sb_submit_business'])) {
        // Sanitize and validate input fields
        $business_name = sanitize_text_field($_POST['business_name']);
        $business_address = sanitize_text_field($_POST['address']);
        $business_suburb = sanitize_text_field($_POST['suburb']);
        $business_phone = sanitize_text_field($_POST['phone']);
        $business_email = sanitize_email($_POST['email']);
        $business_description = sanitize_textarea_field($_POST['description']);
        $business_website = !empty($_POST['website']) ? esc_url($_POST['website']) : '';
        $business_whatsapp = sanitize_text_field($_POST['whatsapp']);
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
                'address' => $business_address,
                'suburb' => $business_suburb,
                'phone' => $business_phone,
                'email' => $business_email,
                'description' => $business_description,
                'website' => $business_website,
                'whatsapp' => $business_whatsapp,
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

// Render the Add Business Form (Shortcode Function)
function sb_render_add_business_form() {
    ob_start();
    ?>
    <form method="post" enctype="multipart/form-data">
        <h2>Add Your Business</h2>

        <!-- Required Fields -->
        <label for="business_name">Business Name (required):</label>
        <input type="text" id="business_name" name="business_name" required>

        <label for="address">Business Address (required):</label>
        <input type="text" id="address" name="address" required>

        <label for="suburb">Business Suburb (default: Sandbaai):</label>
        <input type="text" id="suburb" name="suburb" value="Sandbaai">

        <label for="phone">Business Phone (required):</label>
        <input type="text" id="phone" name="phone" required>

        <label for="email">Business Email (required):</label>
        <input type="email" id="email" name="email" required>

        <label for="description">Business Description (required):</label>
        <textarea id="description" name="description" required></textarea>

        <hr>

        <!-- Optional Fields -->
        <label for="website">Business Website:</label>
        <input type="url" id="website" name="website">

        <label for="whatsapp">Business WhatsApp Number:</label>
        <input type="text" id="whatsapp" name="whatsapp">

        <label for="facebook">Facebook Page URL:</label>
        <input type="url" id="facebook" name="facebook">

        <label for="logo">Business Logo (JPEG/PNG, max 500KB):</label>
        <input type="file" id="logo" name="logo" accept="image/jpeg, image/png">

        <label for="gallery">Photo Gallery (up to 5, max 2MB each):</label>
        <input type="file" id="gallery" name="gallery[]" accept="image/jpeg, image/png" multiple>

        <label for="tags">Tags (select up to 2):</label>
        <div id="tags-table">
            <?php
            $tags = get_tags(); // Fetch WordPress post tags
            if ($tags) {
                foreach ($tags as $tag) {
                    echo '<label>';
                    echo '<input type="checkbox" name="tags[]" value="' . esc_attr($tag->term_id) . '" onchange="limitTagSelection()"> ';
                    echo esc_html($tag->name);
                    echo '</label><br>';
                }
            } else {
                echo '<p>No tags available.</p>';
            }
            ?>
        </div>

        <label for="address_privacy">Hide Business Address? (yes/no):</label>
        <input type="radio" id="address_privacy_yes" name="address_privacy" value="yes"> Yes
        <input type="radio" id="address_privacy_no" name="address_privacy" value="no" checked> No

        <br>
        <label for="suggestions">Suggestions:</label>
        <textarea id="suggestions" name="suggestions"></textarea>

        <br>
        <input type="submit" name="sb_submit_business" value="Submit Business">
    </form>

    <script>
        // JavaScript to limit tag selection to 2
        function limitTagSelection() {
            const checkboxes = document.querySelectorAll('input[name="tags[]"]');
            const checked = Array.from(checkboxes).filter(cb => cb.checked);
            if (checked.length > 2) {
                alert('You can select up to 2 tags only.');
                this.checked = false;
            }
        }
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('sb_add_business_form', 'sb_render_add_business_form');
