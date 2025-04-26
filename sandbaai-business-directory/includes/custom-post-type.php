<?php
// Register the custom post type and taxonomy
function sb_register_custom_post_type() {
    // Register 'business_listing' custom post type
    register_post_type('business_listing', array(
        'labels' => array(
            'name' => 'Business Listings',
            'singular_name' => 'Business Listing',
            'add_new' => 'Add New Business',
            'add_new_item' => 'Add New Business Listing',
            'edit_item' => 'Edit Business Listing',
            'new_item' => 'New Business Listing',
            'view_item' => 'View Business Listing',
            'search_items' => 'Search Business Listings',
            'not_found' => 'No Business Listings Found',
            'not_found_in_trash' => 'No Business Listings Found in Trash',
        ),
        'public' => true,
        'has_archive' => true,
        'rewrite' => array('slug' => 'businesses'),
        'supports' => array('title', 'editor', 'thumbnail', 'custom-fields', 'revisions'),
    ));

    // Register taxonomies for categories and tags
    register_taxonomy('business_category', 'business_listing', array(
        'labels' => array(
            'name' => 'Categories',
            'singular_name' => 'Category',
        ),
        'hierarchical' => true,
        'public' => true,
        'rewrite' => array('slug' => 'business-category'),
    ));

    register_taxonomy('business_tag', 'business_listing', array(
        'labels' => array(
            'name' => 'Tags',
            'singular_name' => 'Tag',
        ),
        'hierarchical' => false,
        'public' => true,
        'rewrite' => array('slug' => 'business-tag'),
    ));
}
add_action('init', 'sb_register_custom_post_type');