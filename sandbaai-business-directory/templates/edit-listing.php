<?php
/**
 * Edit Listing Page
 */

if (!defined('ABSPATH')) {
    exit; // Prevent direct access
}

// Check if the user is logged in
if (!is_user_logged_in()) {
    error_log("Debug: User not logged in. Redirecting to login page.");
    wp_redirect(wp_login_url());
    exit();
}

// Get the logged-in user's ID
$userId = get_current_user_id();
$isAdmin = current_user_can('manage_options');

// Debugging
error_log("Debug: WordPress detects logged-in user with ID: " . $userId);

// Define the plugin's includes directory path
define('SB_INCLUDES_PATH', plugin_dir_path(__DIR__) . 'includes/');

// Include required files with error handling
$functions_path = SB_INCLUDES_PATH . 'functions.php';
$db_path = SB_INCLUDES_PATH . 'database.php';

if (!file_exists($functions_path)) {
    die("Error: The required file 'functions.php' is missing.");
}
if (!file_exists($db_path)) {
    die("Error: The required file 'database.php' is missing.");
}

require_once $functions_path;
require_once $db_path;

global $wpdb;
$table_name = $wpdb->prefix . 'businesses'; // Ensure the table name is correct

// Check if the table exists
$table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") === $table_name;
if (!$table_exists) {
    error_log("Table {$table_name} does not exist");
    wp_die("The businesses table doesn't exist in the database. Please activate the plugin again to create it.");
}

// Get user's listing
$listing = $wpdb->get_row(
    $wpdb->prepare("SELECT * FROM {$table_name} WHERE user_id = %d", $userId),
    ARRAY_A
);

if (!$listing) {
    // User doesn't have a listing yet
    wp_redirect(home_url('/add-listing'));
    exit();
}

$listingId = $listing['id'];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data
    $business_name = trim($_POST['business_name']);
    $category_id = (int)$_POST['category_id'];
    $business_description = trim($_POST['business_description']);
    $business_address = trim($_POST['business_address']);
    $business_phone = trim($_POST['business_phone']);
    $business_email = trim($_POST['business_email']);
    $business_website = trim($_POST['business_website']);
    $business_facebook = trim($_POST['business_facebook'] ?? '');
    $business_instagram = trim($_POST['business_instagram'] ?? '');
    $business_twitter = trim($_POST['business_twitter'] ?? '');

    // Validation
    $errors = [];
    
    if (empty($business_name)) {
        $errors[] = "Business name is required";
    }
    
    if ($category_id <= 0) {
        $errors[] = "You must select a valid category";
    }
    
    if (empty($business_description)) {
        $errors[] = "Description is required";
    }
    
    if (empty($business_address)) {
        $errors[] = "Address is required";
    }
    
    if (empty($business_phone)) {
        $errors[] = "Phone number is required";
    }
    
    if (empty($business_email) || !filter_var($business_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "A valid email address is required";
    }
    
    if (!empty($business_website) && !filter_var($business_website, FILTER_VALIDATE_URL)) {
        $errors[] = "Website must be a valid URL";
    }

    // Handle logo upload
    $business_logo = $listing['business_logo'] ?? '';
    if (isset($_FILES['business_logo']) && $_FILES['business_logo']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['business_logo']['name'];
        $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (!in_array($file_ext, $allowed)) {
            $errors[] = "Logo must be a JPG, JPEG, PNG, or GIF file";
        } else {
            $upload_dir = '../uploads/logos/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $new_filename = uniqid('logo_') . '.' . $file_ext;
            $destination = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['business_logo']['tmp_name'], $destination)) {
                // Delete old logo if it exists and is not the default
                if (!empty($business_logo) && $business_logo != 'uploads/logos/default.png' && file_exists('../' . $business_logo)) {
                    unlink('../' . $business_logo);
                }
                $business_logo = str_replace('../', '', $destination); // Store relative path in DB
            } else {
                $errors[] = "Failed to upload logo";
            }
        }
    }

    // If no errors, update the listing
    if (empty($errors)) {
        $wpdb->update(
            $table_name,
            [
                'business_name' => $business_name,
                'category_id' => $category_id,
                'business_description' => $business_description,
                'business_address' => $business_address,
                'business_phone' => $business_phone,
                'business_email' => $business_email,
                'business_website' => $business_website,
                'business_facebook' => $business_facebook,
                'business_instagram' => $business_instagram,
                'business_twitter' => $business_twitter,
                'business_logo' => $business_logo,
                'updated_at' => current_time('mysql')
            ],
            ['id' => $listingId, 'user_id' => $userId]
        );

        $_SESSION['message'] = "Your business listing has been updated successfully!";
        $_SESSION['message_type'] = "success";
        wp_redirect(home_url("/view-listing?id={$listingId}"));
        exit();
    }
}

// Include header
include '../includes/header.php';
?>
