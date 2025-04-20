<?php
// This is the view-only part of edit-listing.php
// No processing logic, just the form display

// Check if user is logged in
if (!is_user_logged_in()) {
    echo '<p>You must be logged in to edit a listing.</p>';
    echo '<a href="' . wp_login_url(get_permalink()) . '">Log in</a>';
    return;
}

// Get the listing ID from URL parameter
$listing_id = isset($_GET['listing_id']) ? intval($_GET['listing_id']) : 0;

if ($listing_id <= 0) {
    echo '<p>No listing specified. Please provide a valid listing ID.</p>';
    return;
}

// Check if current user is the author or an admin
$current_user = wp_get_current_user();
$listing = get_post($listing_id);

if (!$listing || $listing->post_type !== 'listing') {
    echo '<p>Invalid listing ID.</p>';
    return;
}

if ($listing->post_author != $current_user->ID && !current_user_can('administrator')) {
    echo '<p>You do not have permission to edit this listing.</p>';
    return;
}

// Get listing data
$listing_title = $listing->post_title;
$listing_description = $listing->post_content;
$listing_phone = get_post_meta($listing_id, '_listing_phone', true);
$listing_email = get_post_meta($listing_id, '_listing_email', true);
$listing_website = get_post_meta($listing_id, '_listing_website', true);
$listing_address = get_post_meta($listing_id, '_listing_address', true);

// Get listing category
$listing_categories = wp_get_object_terms($listing_id, 'listing_category');
$listing_category_id = !empty($listing_categories) ? $listing_categories[0]->term_id : 0;

// Display the edit form
?>
<div class="edit-listing-form">
    <h2>Edit Listing</h2>
    
    <form method="post" action="">
        <input type="hidden" name="listing_id" value="<?php echo $listing_id; ?>">
        
        <div class="form-field">
            <label for="listing_title">Title</label>
            <input type="text" id="listing_title" name="listing_title" value="<?php echo esc_attr($listing_title); ?>" required>
        </div>
        
        <div class="form-field">
            <label for="listing_description">Description</label>
            <textarea id="listing_description" name="listing_description" rows="5" required><?php echo esc_textarea($listing_description); ?></textarea>
        </div>
        
        <div class="form-field">
            <label for="listing_phone">Phone</label>
            <input type="text" id="listing_phone" name="listing_phone" value="<?php echo esc_attr($listing_phone); ?>">
        </div>
        
        <div class="form-field">
            <label for="listing_email">Email</label>
            <input type="email" id="listing_email" name="listing_email" value="<?php echo esc_attr($listing_email); ?>">
        </div>
        
        <div class="form-field">
            <label for="listing_website">Website</label>
            <input type="url" id="listing_website" name="listing_website" value="<?php echo esc_url($listing_website); ?>">
        </div>
        
        <div class="form-field">
            <label for="listing_address">Address</label>
            <input type="text" id="listing_address" name="listing_address" value="<?php echo esc_attr($listing_address); ?>">
        </div>
        
        <div class="form-field">
            <label for="listing_category">Category</label>
            <?php
            // Display category dropdown
            $categories = get_terms(array(
                'taxonomy' => 'listing_category',
                'hide_empty' => false,
            ));
            
            if (!empty($categories) && !is_wp_error($categories)) {
                echo '<select id="listing_category" name="listing_category">';
                echo '<option value="">Select Category</option>';
                
                foreach ($categories as $category) {
                    $selected = ($category->term_id == $listing_category_id) ? 'selected' : '';
                    echo '<option value="' . $category->term_id . '" ' . $selected . '>' . $category->name . '</option>';
                }
                
                echo '</select>';
            } else {
                echo '<p>No categories available.</p>';
            }
            ?>
        </div>
        
        <div class="form-field">
            <button type="submit" class="submit-button">Update Listing</button>
        </div>
    </form>
</div>
