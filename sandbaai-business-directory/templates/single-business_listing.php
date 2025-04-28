<?php
/**
 * Template for displaying a single Business Listing with comments
 */

get_header(); ?>

<div class="business-listing-container">
    <?php
    if (have_posts()) :
        while (have_posts()) :
            the_post();

            // Get custom fields
            $business_address = get_post_meta(get_the_ID(), 'business_address', true);
            $business_suburb = get_post_meta(get_the_ID(), 'business_suburb', true);
            $business_phone = get_post_meta(get_the_ID(), 'business_phone', true);
            $business_email = get_post_meta(get_the_ID(), 'business_email', true);
            $business_description = get_post_meta(get_the_ID(), 'business_description', true);
            $business_website = get_post_meta(get_the_ID(), 'business_website', true);
            $business_whatsapp = get_post_meta(get_the_ID(), 'business_whatsapp', true);
            $facebook = get_post_meta(get_the_ID(), 'facebook', true);
            $logo = get_post_meta(get_the_ID(), 'logo', true);
            $gallery = get_post_meta(get_the_ID(), 'gallery', true);
            $tags = get_post_meta(get_the_ID(), 'tags', true);
            $address_privacy = get_post_meta(get_the_ID(), 'address_privacy', true);

            // Check if the logged-in user is the author of the post
            $current_user_id = get_current_user_id();
            $post_author_id = get_the_author_meta('ID');
            ?>
 <?php if ($current_user_id === $post_author_id) : ?>
                <div class="edit-listing-button">
                    <a href="/edit-listing/" class="button">Edit Your Business Listing</a>
                    <br>
                </div>
            <?php endif; ?>
            
            <!-- Business details -->
            <div class="business-details">
                <div class="business-header">
                    <h1 class="business-title"><?php the_title(); ?></h1>
                </div>

                <div class="business-content">
                    <div class="business-column business-logo">
                        <?php if (!empty($logo)) : ?>
                            <img src="<?php echo esc_url($logo); ?>" alt="<?php the_title(); ?>">
                        <?php endif; ?>
                    </div>
                    <div class="business-column business-meta">
                        <?php if ($address_privacy !== '1' && !empty($business_address)) : ?>
                            <p class="business-item address-icon">
                                <?php echo esc_html($business_address); ?>
                                <br>
                                <?php echo esc_html($business_suburb); ?>
                            </p>
                        <?php endif; ?>

                        <?php if (!empty($business_phone)) : ?>
                            <p class="business-item phone-icon"><a href="tel:<?php echo esc_html($business_phone); ?>"><?php echo esc_html($business_phone); ?></a></p>
                        <?php endif; ?>

                        <?php if (!empty($business_email)) : ?>
                            <p class="business-item email-icon">
                                <a href="mailto:<?php echo esc_attr($business_email); ?>?subject=SCVID%20member%20query">
                                    <?php echo esc_html($business_email); ?>
                                </a>
                            </p>
                        <?php endif; ?>

                        <?php if (!empty($business_whatsapp)) : ?>
                            <?php
                            $whatsapp_number = preg_replace('/^0/', '+27', esc_html($business_whatsapp));
                            ?>
                            <p class="business-item whatsapp-icon"><a href="https://wa.me/<?php echo $whatsapp_number; ?>" target="_blank"><?php echo esc_html($business_whatsapp); ?></a></p>
                        <?php endif; ?>

                        <?php if (!empty($facebook)) : ?>
                            <p class="business-item facebook-icon"><a href="<?php echo $facebook; ?>" target="_blank">Facebook</a></p>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!empty($business_website)) : ?>
                    <div class="business-website">
                        <p class="business-item website-icon"><a href="<?php echo $business_website; ?>" target="_blank"><?php echo esc_html($business_website); ?></a></p>
                    </div>
                <?php endif; ?>

                <div class="business-description">
                    <p><?php echo esc_html($business_description); ?></p>
                </div>
            </div>

            <?php if (!empty($gallery)) : ?>
                <div class="business-gallery">
                    <h2>Photo Gallery</h2>
                    <div class="gallery-thumbnails">
                        <?php foreach ($gallery as $image) : ?>
                            <a href="<?php echo esc_url($image); ?>" data-lightbox="business-gallery">
                                <img src="<?php echo esc_url($image); ?>" alt="Gallery image">
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Success Message -->
            <?php if (isset($_GET['review_submitted']) && $_GET['review_submitted'] === 'pending') : ?>
                <div class="notice notice-success">
                    <p>Your review has been submitted and is pending approval.</p>
                </div>
            <?php endif; ?>

            <!-- Comments Section -->
            <div class="comments-section">
                <h2>Reviews</h2>

                <?php
                // Fetch and display comments
                $comments = get_comments(array(
                    'post_id' => get_the_ID(),
                    'status' => 'approve',
                    'orderby' => 'comment_date',
                    'order' => 'DESC',
                ));

                if (!empty($comments)) {
                    echo '<div class="comments-box">';
                    foreach ($comments as $comment) {
                        $comment_author = get_userdata($comment->user_id);
                        $comment_author_name = $comment_author->first_name ? $comment_author->first_name : $comment_author->display_name;
                        $comment_content = $comment->comment_content;

                        echo '<div class="comment">';
                        echo '<p><strong>' . esc_html($comment_author_name) . ':</strong> ' . esc_html($comment_content) . '</p>';
                        echo '</div>';
                    }
                    echo '</div>';
                } else {
                    echo '<p>No reviews yet. Be the first to leave a review!</p>';
                }
                ?>

                <!-- Comment Form -->
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <input type="hidden" name="action" value="process_comment">
                    <input type="hidden" name="post_id" value="<?php echo esc_attr(get_the_ID()); ?>">
                    <textarea name="comment_content" placeholder="Write your review here..." required></textarea><br>
                    <button type="submit" name="submit_comment">Submit Review</button>
                </form>
            </div>

    <?php
        endwhile;
    endif;
    ?>
    <div style="text-align: center; margin-top: 20px;">
        <form action="/business-directory/" method="get" style="display: inline-block;">
            <button type="submit" style="
                background-color: #0056b3; 
                color: white; 
                border: none; 
                padding: 10px 20px; 
                border-radius: 4px; 
                font-weight: bold; 
                cursor: pointer; 
                text-transform: uppercase;">
                Back To Directory
            </button>
        </form>
    </div>
</div>
<?php get_footer(); ?>