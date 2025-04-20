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
        $website = !empty($_POST['website']) ? esc_url($_POST['website']) : '';
        $whatsapp = sanitize_text_field($_POST['whatsapp']);
        $facebook = !empty($_POST['facebook']) ? esc_url($_POST['facebook']) : '';
        $tags = !empty($_POST['tags']) ? array_map('intval', $_POST['tags']) : array(); // Tags as array
        $address_privacy = sanitize_text_field($_POST['address_privacy']);
        $suggestions = sanitize_textarea_field($_POST['suggestions']);

        // Validate required fields
        if (empty($business_name) || empty($address) || empty($phone) || empty($email) || empty($description)) {
            echo '<p style="color: red;">Error: Please fill in all required fields.</p>';
            return;
        }

        if (!is_email($email)) {
            echo '<p style="color: red;">Error: Invalid email format.</p>';
            return;
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
                'post_tag' => $tags, // Assign tags
            ),
            'meta_input' => array(
                'address' => $address,
                'suburb' => $suburb,
                'phone' => $phone,
                'email' => $email,
                'description' => $description,
                'website' => $website,
                'whatsapp' => $whatsapp,
                'facebook' => $facebook,
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

        <hr>

        <!-- Optional Fields -->
        <label for="website">Website:</label>
        <input type="url" id="website" name="website" placeholder="https://">

        <label for="whatsapp">WhatsApp Number:</label>
        <input type="text" id="whatsapp" name="whatsapp">

        <label for="facebook">Facebook Page URL:</label>
        <input type="url" id="facebook" name="facebook" placeholder="https://">

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

        <label for="address_privacy">Hide Address? (yes/no):</label>
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
