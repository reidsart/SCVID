<?php
function sb_render_edit_business_form($post_id) {
    // Ensure the user is logged in
    if (!is_user_logged_in()) {
        echo '<p style="color: red;">You need to log in to edit your business listing.</p>';
        return;
    }

    $current_user = wp_get_current_user();

    // Fetch the post to edit
    $business_listing = get_post($post_id);

    // Verify if the post exists and is of the correct type
    if (!$business_listing || $business_listing->post_type !== 'business_listing') {
        echo '<p style="color: red;">Invalid business listing.</p>';
        return;
    }

    // Verify if the current user is the owner of the post
    if ($business_listing->post_author != $current_user->ID) {
        echo '<p style="color: red;">You are not authorized to edit this business listing.</p>';
        return;
    }

    // Pre-fill form fields with existing data
    $meta = get_post_meta($post_id);
    $tags = wp_get_post_terms($post_id, 'post_tag', array('fields' => 'ids'));

    ob_start();
    ?>
    <form method="post" enctype="multipart/form-data">
        <h2>Edit Your Business</h2>

        <!-- Required Fields -->
        <label for="business_name">Business Name (required):</label>
        <input type="text" id="business_name" name="post_title" value="<?php echo esc_attr($business_listing->post_title); ?>" required>

        <label for="business_address">Business Address (required):</label>
        <input type="text" id="business_address" name="business_address" value="<?php echo esc_attr($meta['business_address'][0]); ?>" required>

        <label for="address_privacy">Hide Address? (yes/no):</label>
        <input type="radio" id="address_privacy_yes" name="address_privacy" value="yes" <?php checked($meta['address_privacy'][0], 'yes'); ?>> Yes
        <input type="radio" id="address_privacy_no" name="address_privacy" value="no" <?php checked($meta['address_privacy'][0], 'no'); ?>> No

        <label for="business_suburb">Business Suburb (default: Sandbaai):</label>
        <input type="text" id="business_suburb" name="business_suburb" value="<?php echo esc_attr($meta['business_suburb'][0]); ?>">

        <label for="business_phone">Business Phone (required):</label>
        <input type="text" id="business_phone" name="business_phone" value="<?php echo esc_attr($meta['business_phone'][0]); ?>" required>

        <label for="business_email">Business Email (required):</label>
        <input type="email" id="business_email" name="business_email" value="<?php echo esc_attr($meta['business_email'][0]); ?>" required>

        <label for="business_description">Business Description (required):</label>
        <textarea id="business_description" name="business_description" required><?php echo esc_textarea($meta['business_description'][0]); ?></textarea>

        <hr>

        <!-- Optional Fields -->
        <label for="business_website">Business Website:</label>
        <input type="url" id="business_website" name="business_website" value="<?php echo esc_url($meta['business_website'][0]); ?>">

        <label for="business_whatsapp">Business WhatsApp Number:</label>
        <input type="text" id="business_whatsapp" name="business_whatsapp" value="<?php echo esc_attr($meta['business_whatsapp'][0]); ?>">

        <label for="facebook">Facebook Page URL:</label>
        <input type="url" id="facebook" name="facebook" value="<?php echo esc_url($meta['facebook'][0]); ?>">

        <label for="tags">Select Tags:</label>
        <?php
        $all_tags = get_tags();
        foreach ($all_tags as $tag) {
            $checked = in_array($tag->term_id, $tags) ? 'checked' : '';
            echo '<label><input type="checkbox" name="tags[]" value="' . esc_attr($tag->term_id) . '" ' . $checked . '> ' . esc_html($tag->name) . '</label><br>';
        }
        ?>

        <hr>

        <label for="logo">Business Logo (JPEG/PNG, max 500KB):</label>
        <input type="file" id="logo" name="logo" accept="image/jpeg, image/png">

        <label for="gallery">Photo Gallery (up to 5, max 2MB each):</label>
        <input type="file" id="gallery" name="gallery[]" accept="image/jpeg, image/png" multiple>

        <br>
        <input type="submit" name="sb_update_business" value="Update Business">
    </form>
    <?php
    return ob_get_clean();
}

function sb_handle_edit_form_submission() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sb_update_business'])) {
        // Ensure the user is logged in
        if (!is_user_logged_in()) {
            echo '<p style="color: red;">You need to log in to edit your business listing.</p>';
            return;
        }

        $current_user = wp_get_current_user();
        $post_id = intval($_POST['post_id']);

        // Fetch the post to edit
        $business_listing = get_post($post_id);

        // Verify if the current user is the owner of the post
        if ($business_listing->post_author != $current_user->ID) {
            echo '<p style="color: red;">You are not authorized to edit this business listing.</p>';
            return;
        }

        // Sanitize and validate inputs
        $post_title = sanitize_text_field($_POST['post_title']);
        $business_address = sanitize_text_field($_POST['business_address']);
        $address_privacy = sanitize_text_field($_POST['address_privacy']);
        $business_suburb = sanitize_text_field($_POST['business_suburb']);
        $business_phone = sanitize_text_field($_POST['business_phone']);
        $business_email = sanitize_email($_POST['business_email']);
        $business_description = sanitize_textarea_field($_POST['business_description']);
        $business_website = !empty($_POST['business_website']) ? esc_url_raw($_POST['business_website']) : '';
        $business_whatsapp = sanitize_text_field($_POST['business_whatsapp']);
        $facebook = !empty($_POST['facebook']) ? esc_url_raw($_POST['facebook']) : '';
        $tags = !empty($_POST['tags']) ? array_map('intval', $_POST['tags']) : array();

        // Update the post
        wp_update_post(array(
            'ID' => $post_id,
            'post_title' => $post_title,
        ));

        // Update metadata
        update_post_meta($post_id, 'business_address', $business_address);
        update_post_meta($post_id, 'address_privacy', $address_privacy);
        update_post_meta($post_id, 'business_suburb', $business_suburb);
        update_post_meta($post_id, 'business_phone', $business_phone);
        update_post_meta($post_id, 'business_email', $business_email);
        update_post_meta($post_id, 'business_description', $business_description);
        update_post_meta($post_id, 'business_website', $business_website);
        update_post_meta($post_id, 'business_whatsapp', $business_whatsapp);
        update_post_meta($post_id, 'facebook', $facebook);

        // Update tags
        wp_set_post_terms($post_id, $tags, 'post_tag');

        echo '<p style="color: green;">Your business listing has been updated successfully.</p>';
    }
}
add_shortcode('sb_edit_business_form', 'sb_render_edit_business_form');
add_action('init', 'sb_handle_edit_form_submission');
