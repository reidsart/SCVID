<?php
require_once SB_DIR_PATH . 'includes/sidebar.php';

function sb_render_directory_listing() {
    ob_start();

    // Check for success message
    if (isset($_GET['submission']) && $_GET['submission'] === 'pending') {
        echo '<div class="notice notice-success" style="background-color: #d4edda; padding: 10px; border: 1px solid #c3e6cb; border-radius: 5px; color: #155724; margin-bottom: 20px;">';
        echo '<p>Your business listing has been submitted and is pending review. Once it is approved, you will be able to edit it and add more photos.</p>';
        echo '</div>';
    }

    // Begin Layout Wrapper
    echo '<div class="sb-directory-layout">';

    // Sidebar Navigation
    echo '<div class="sb-sidebar-container">';
    sb_render_sidebar();
    echo '</div>';

    // Main Content
    echo '<div class="sb-main-content">';

    // Query Sandbaai Businesses
    $sandbaai_query = new WP_Query(array(
        'post_type' => 'business_listing',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'orderby' => 'title',
        'order' => 'ASC',
        'tax_query' => array(
            array(
                'taxonomy' => 'business_category',
                'field' => 'slug',
                'terms' => 'sb_business', // Sandbaai Business category
            ),
        ),
    ));

    // Display Sandbaai Businesses
    if ($sandbaai_query->have_posts()) {
        echo '<h2>Sandbaai Businesses</h2>';
        echo '<ul class="business-listing" style="list-style-type: none;">';
        while ($sandbaai_query->have_posts()) {
            $sandbaai_query->the_post();
$logo = get_post_meta(get_the_ID(), 'logo', true);

if (is_wp_error($logo)) {
    // Log the error and set a default or empty value
    error_log("Error fetching logo URL for listing ID " . get_the_ID() . ": " . $logo->get_error_message());
    $logo = ''; // Fallback to an empty string or default image
}

// Use esc_url only on valid URLs
$logo = get_post_meta(get_the_ID(), 'logo', true);
$default_logo = SB_DIR_URL . 'assets/icons/generic-business-icon.png'; // Default logo path

echo '<li style="display: flex; align-items: center; gap: 10px;">'; // Flexbox for inline layout
if (!empty($logo)) {
    echo '<img src="' . esc_url($logo) . '" alt="Logo" style="width: 20px; height: 20px; vertical-align: middle;">';
} else {
    echo '<img src="' . esc_url($default_logo) . '" alt="Generic Business Icon" style="width: 20px; height: 20px; vertical-align: middle;">';
}
echo '<a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a>';
echo '</li>';
        }
        echo '</ul>';
    }

    // Reset post data
    wp_reset_postdata();

    echo '</div>'; // End Main Content
    echo '</div>'; // End Layout Wrapper

    return ob_get_clean();
}

add_shortcode('sb_directory_listing', 'sb_render_directory_listing');