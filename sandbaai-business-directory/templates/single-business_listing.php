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
                            <p class="business-item email-icon"><a href="mailto:<?php echo esc_attr($business_email); ?>"><?php echo esc_html($business_email); ?></a></p>
                        <?php endif; ?>

                        <?php if (!empty($business_whatsapp)) : ?>
                            <?php
                            $whatsapp_number = preg_replace('/^0/', '+27', esc_html($business_whatsapp));
                            ?>
                            <p class="business-item whatsapp-icon"><a href="https://wa.me/<?php echo $whatsapp_number; ?>" target="_blank"><?php echo esc_html($business_whatsapp); ?></a></p>
                        <?php endif; ?>

                        <?php if (!empty($facebook)) : ?>
                            <p class="business-item facebook-icon"><a href="<?php echo esc_url($facebook); ?>" target="_blank">Facebook</a></p>
                        <?php endif; ?>
                    </div>
                </div>
        <?php if (!empty($business_website)) : ?>
            <div class="business-website">
                <p class="business-item website-icon"><a href="<?php echo esc_url($business_website); ?>" target="_blank"><?php echo esc_html($business_website); ?></a></p>
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

            <?php if ($current_user_id === $post_author_id) : ?>
                <div class="edit-listing-button">
                    <a href="<?php echo get_edit_post_link(); ?>" class="button">Edit This Listing</a>
                    <br>
                    <a href="/business-directory/">Return to Business Directory</a>
                </div>
            <?php endif; ?>

    <?php
        endwhile;
    endif;
    ?>
</div>

<?php get_footer(); ?>
