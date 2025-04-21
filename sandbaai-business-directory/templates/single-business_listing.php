<?php
/**
 * Template for displaying a single Business Listing
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
            <div class="business-details">
                <h1 class="business-title"><?php the_title(); ?></h1>

                <?php if (!empty($logo)) : ?>
                    <div class="business-logo">
                        <img src="<?php echo esc_url($logo); ?>" alt="<?php the_title(); ?>">
                    </div>
                <?php endif; ?>

                <div class="business-meta">
                    <?php if ($address_privacy !== '1' && !empty($business_address)) : ?>
                        <p><strong>Address:</strong> <?php echo esc_html($business_address); ?>, <?php echo esc_html($business_suburb); ?></p>
                    <?php endif; ?>

                    <?php if (!empty($business_phone)) : ?>
                        <p><strong>Phone:</strong> <?php echo esc_html($business_phone); ?></p>
                    <?php endif; ?>

                    <?php if (!empty($business_email)) : ?>
                        <p><strong>Email:</strong> <a href="mailto:<?php echo esc_attr($business_email); ?>"><?php echo esc_html($business_email); ?></a></p>
                    <?php endif; ?>

                    <?php if (!empty($business_website)) : ?>
                        <p><strong>Website:</strong> <a href="<?php echo esc_url($business_website); ?>" target="_blank"><?php echo esc_html($business_website); ?></a></p>
                    <?php endif; ?>

                    <?php if (!empty($business_whatsapp)) : ?>
                        <p><strong>WhatsApp:</strong> <?php echo esc_html($business_whatsapp); ?></p>
                    <?php endif; ?>

                    <?php if (!empty($facebook)) : ?>
                        <p><strong>Facebook:</strong> <a href="<?php echo esc_url($facebook); ?>" target="_blank"><?php echo esc_html($facebook); ?></a></p>
                    <?php endif; ?>

                    <?php if (!empty($business_description)) : ?>
                        <p><strong>Description:</strong> <?php echo esc_html($business_description); ?></p>
                    <?php endif; ?>
                </div>

                <?php if (!empty($gallery)) : ?>
                    <div class="business-gallery">
                        <h2>Photo Gallery</h2>
                        <?php foreach ($gallery as $photo_url) : ?>
                            <img src="<?php echo esc_url($photo_url); ?>" alt="Gallery Image">
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($tags)) : ?>
                    <p><strong>Tags:</strong> <?php echo esc_html($tags); ?></p>
                <?php endif; ?>
            </div>

            <!-- Add Edit Listing Button -->
            <?php if ($current_user_id === $post_author_id) : ?>
                <div class="edit-listing-button">
                    <a href="<?php echo esc_url(home_url('/edit-listing/?listing_id=' . get_the_ID())); ?>" class="button" style="background-color: #0073aa; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;"
                    >Edit This Listing</a>
    
                </div>
            <?php endif; ?>

        <?php endwhile;
    else : ?>
        <p>No business details found.</p>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
