<?php
/**
 * Template for displaying taxonomy pages for business tags
 */

require_once SB_DIR_PATH . 'includes/sidebar.php';

get_header();
?>

<div class="sb-directory-layout">
    <!-- Sidebar -->
    <div class="sb-sidebar-container">
        <?php sb_render_sidebar(); ?>
    </div>

    <!-- Main Content -->
    <div class="sb-main-content">
        <?php
        if (have_posts()) {
            echo '<h2>' . single_term_title('', false) . '</h2>';
            echo '<ul class="taxonomy-business-listing" style="list-style-type: none;">'; // Updated class name
            while (have_posts()) {
                the_post();
                $logo = get_post_meta(get_the_ID(), 'logo', true); // Fetch logo meta
                $description = get_post_meta(get_the_ID(), 'business_description', true); // Fetch custom description meta field

                echo '<li class="taxonomy-business-item">'; // Updated class name
                if (!empty($logo)) {
                    // Wrap the logo in a link to the business page
                    echo '<div class="taxonomy-business-logo"><a href="' . get_permalink() . '"><img src="' . esc_url($logo) . '" alt="Logo"></a></div>';
                }
                echo '<div class="taxonomy-business-details">';
                echo '<h3 class="taxonomy-business-title"><a href="' . get_permalink() . '">' . get_the_title() . '</a></h3>';
                if (!empty($description)) {
                    echo '<p class="taxonomy-business-description">' . wp_trim_words(esc_html($description), 20, ' <a href="' . get_permalink() . '" class="read-more">Read More</a>') . '</p>';
                }
                echo '</div>';
                echo '</li>';
            }
            echo '</ul>';
        } else {
            echo '<p>No businesses found for this tag.</p>';
        }
        ?>
    </div>
</div>

<?php get_footer(); ?>