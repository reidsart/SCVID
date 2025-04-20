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
            $address = get_post_meta(get_the_ID(), 'address', true);
            $suburb = get_post_meta(get_the_ID(), 'suburb', true);
            $phone = get_post_meta(get_the_ID(), 'phone', true);
            $email = get_post_meta(get_the_ID(), 'email', true);
            $description = get_post_meta(get_the_ID(), 'description', true);
            $location = get_post_meta(get_the_ID(), 'location', true);
            $website = get_post_meta(get_the_ID(), 'website', true);
            $whatsapp = get_post_meta(get_the_ID(), 'whatsapp', true);
            $facebook = get_post_meta(get_the_ID(), 'facebook', true);
            $logo = get_post_meta(get_the_ID(), 'logo', true);
            $gallery = get_post_meta(get_the_ID(), 'gallery', true);
            $tags = get_post_meta(get_the_ID(), 'tags', true);
            $address_privacy = get_post_meta(get_the_ID(), 'address_privacy', true);
            $suggestions = get_post_meta(get_the_ID(), 'suggestions', true);
            ?>

            <div class="business-details">
                <h1 class="business-title"><?php the_title(); ?></h1>

                <?php if (!empty($logo)) : ?>
                    <div class="business-logo">
                        <img src="<?php echo esc_url($logo); ?>" alt="<?php the_title(); ?>">
                    </div>
                <?php endif; ?>

                <div class="business-meta">
                    <?php if ($address_privacy !== 'yes' && !empty($address)) : ?>
                        <p><strong>Address:</strong> <?php echo esc_html($address); ?>, <?php echo esc_html($suburb); ?></p>
                    <?php endif; ?>

                    <?php if (!empty($phone)) : ?>
                        <p><strong>Phone:</strong> <?php echo esc_html($phone); ?></p>
                    <?php endif; ?>

                    <?php if (!empty($email)) : ?>
                        <p><strong>Email:</strong> <a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a></p>
                    <?php endif; ?>

                    <?php if (!empty($website)) : ?>
                        <p><strong>Website:</strong> <a href="<?php echo esc_url($website); ?>" target="_blank"><?php echo esc_html($website); ?></a></p>
                    <?php endif; ?>

                    <?php if (!empty($whatsapp)) : ?>
                        <p><strong>WhatsApp:</strong> <?php echo esc_html($whatsapp); ?></p>
                    <?php endif; ?>

                    <?php if (!empty($facebook)) : ?>
                        <p><strong>Facebook:</strong> <a href="<?php echo esc_url($facebook); ?>" target="_blank"><?php echo esc_html($facebook); ?></a></p>
                    <?php endif; ?>

                    <?php if (!empty($location)) : ?>
                        <p><strong>Location in Sandbaai:</strong> <?php echo esc_html($location); ?></p>
                    <?php endif; ?>

                    <?php if (!empty($description)) : ?>
                        <p><strong>Description:</strong> <?php echo esc_html($description); ?></p>
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

                <?php if (!empty($suggestions)) : ?>
                    <p><strong>Suggestions:</strong> <?php echo esc_html($suggestions); ?></p>
                <?php endif; ?>
            </div>

        <?php endwhile;
    else : ?>
        <p>No business details found.</p>
    <?php endif; ?>
</div>

<?php get_footer(); ?>