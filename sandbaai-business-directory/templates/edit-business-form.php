<?php
/**
 * Edit Business Form Template
 */

// Function to get the user's business listing
function sb_get_user_business_listing($user_id) {
    $args = array(
        'post_type' => 'business_listing',
        'author' => $user_id,
        'post_status' => 'any', // Include all statuses
        'posts_per_page' => 1, // Get only one post
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        $query->the_post();
        $post_id = get_the_ID();
        wp_reset_postdata(); // Always reset after a custom query
        return $post_id;
    }

    return false; // No listing found
}

// Function to render the edit business form dynamically
function sb_render_edit_business_form_dynamic() {
    // Ensure the user is logged in
    if (!is_user_logged_in()) {
        return '<p style="color: red;">You must be logged in to edit a business listing.</p>';
    }

    $current_user_id = get_current_user_id();

    // Dynamically fetch the user's business listing
    $post_id = sb_get_user_business_listing($current_user_id);

    // Check if the user has a business listing
    if (!$post_id) {
        return '<p>You do not have a business listing to edit. Please create one first.</p>';
    }

    // Fetch the post and metadata
    $post = get_post($post_id);
    $meta = get_post_meta($post_id);

    // Render the form
    ob_start(); ?>
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="post_id" value="<?php echo esc_attr($post_id); ?>">

        <label for="business_name">Business Name:</label>
        <input type="text" id="business_name" name="post_title" value="<?php echo esc_attr($post->post_title); ?>" required>

        <label for="business_description">Business Description:</label>
        <textarea id="business_description" name="business_description" required><?php echo esc_textarea($meta['business_description'][0] ?? ''); ?></textarea>

        <label for="business_phone">Phone:</label>
        <input type="text" id="business_phone" name="business_phone" value="<?php echo esc_attr($meta['business_phone'][0] ?? ''); ?>" required>

        <label for="business_email">Email:</label>
        <input type="email" id="business_email" name="business_email" value="<?php echo esc_attr($meta['business_email'][0] ?? ''); ?>" required>

        <label for="business_website">Website:</label>
        <input type="url" id="business_website" name="business_website" value="<?php echo esc_url($meta['business_website'][0] ?? ''); ?>">

        <input type="submit" name="sb_update_business" value="Update Business">
    </form>
    <?php
    return ob_get_clean();
}

// Function to handle form submission for editing
function sb_handle_edit_form_submission_dynamic() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sb_update_business'])) {
        // Ensure the user is logged in
        if (!is_user_logged_in()) {
            echo '<p style="color: red;">You must be logged in to edit a business listing.</p>';
            return;
        }

        $current_user_id = get_current_user_id();
        $post_id = intval($_POST['post_id']); // Retrieve post_id from the form submission

        // Fetch the post
        $post = get_post($post_id);

        // Verify if the post exists and the user is the author
        if (!$post || $post->post_type !== 'business_listing' || $post->post_author != $current_user_id) {
            echo '<p style="color: red;">You are not authorized to edit this business listing.</p>';
            return;
        }

        // Sanitize inputs
        $post_title = sanitize_text_field($_POST['post_title']);
        $business_description = sanitize_textarea_field($_POST['business_description']);
        $business_phone = sanitize_text_field($_POST['business_phone']);
        $business_email = sanitize_email($_POST['business_email']);
        $business_website = esc_url_raw($_POST['business_website']);

        // Update the post
        wp_update_post(array(
            'ID' => $post_id,
            'post_title' => $post_title,
        ));

        // Update metadata
        update_post_meta($post_id, 'business_description', $business_description);
        update_post_meta($post_id, 'business_phone', $business_phone);
        update_post_meta($post_id, 'business_email', $business_email);
        update_post_meta($post_id, 'business_website', $business_website);

        echo '<p style="color: green;">Your business listing has been updated successfully.</p>';
    }
}

// Shortcode to display the edit business form
add_shortcode('sb_edit_business_form', 'sb_render_edit_business_form_dynamic');

// Hook to handle form submission
add_action('init', 'sb_handle_edit_form_submission_dynamic');
