<?php
/**
 * Process comment submissions for the Business Listing.
 */

function sb_process_comment_submission() {
    // Ensure the user is logged in
    if (!is_user_logged_in()) {
        wp_die(__('You must be logged in to leave a review.', 'textdomain'));
    }

    // Check if the form is submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_comment'])) {
        // Validate required fields
        if (empty($_POST['post_id']) || empty($_POST['comment_content'])) {
            wp_die(__('Missing required fields.', 'textdomain'));
        }

        $post_id = intval($_POST['post_id']);
        $comment_content = sanitize_textarea_field($_POST['comment_content']);
        $current_user = wp_get_current_user();

        // Verify the post exists and is of type 'business_listing'
        $post = get_post($post_id);
        if (!$post || $post->post_type !== 'business_listing') {
            wp_die(__('Invalid post.', 'textdomain'));
        }

        // Prepare comment data
        $comment_data = array(
            'comment_post_ID' => $post_id,
            'comment_content' => $comment_content,
            'comment_author' => $current_user->display_name,
            'comment_author_email' => $current_user->user_email,
            'user_id' => $current_user->ID,
            'comment_approved' => 0, // Default to pending approval
        );

        // Insert the comment
        $comment_id = wp_insert_comment($comment_data);

        // Redirect back to the post with a success message
        if ($comment_id) {
            wp_safe_redirect(get_permalink($post_id) . '?review_submitted=pending');
            exit;
        } else {
            wp_die(__('Failed to submit your review. Please try again.', 'textdomain'));
        }
    } else {
        wp_die(__('Invalid request.', 'textdomain'));
    }
}