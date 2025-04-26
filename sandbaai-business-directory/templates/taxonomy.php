<?php
/**
 * Generic template for displaying taxonomy pages
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
            echo '<ul class="business-listing">';
            while (have_posts()) {
                the_post();
                echo '<li><a href="' . get_permalink() . '">' . get_the_title() . '</a></li>';
            }
            echo '</ul>';
        } else {
            echo '<p>No businesses found for this taxonomy.</p>';
        }
        ?>
    </div>
</div>

<?php get_footer(); ?>