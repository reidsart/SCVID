<?php
/**
 * Edit Listing Page
 * 
 * This page allows users to edit their own business listing.
 * Non-admin users can only edit their own listing.
 */

// Include required files - adjusted for templates folder placement
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['message'] = "You must be logged in to edit your listing.";
    $_SESSION['message_type'] = "error";
    header("Location: ../login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
$conn = getDbConnection();

// Get user's listing
$stmt = $conn->prepare("SELECT * FROM businesses WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // User doesn't have a listing yet
    $_SESSION['message'] = "You don't have a business listing yet. Create one first.";
    $_SESSION['message_type'] = "error";
    header("Location: ../add-listing.php");
    exit();
}

$listing = $result->fetch_assoc();
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
        $stmt = $conn->prepare("UPDATE businesses SET 
            business_name = ?, 
            category_id = ?, 
            business_description = ?, 
            business_address = ?, 
            business_phone = ?, 
            business_email = ?, 
            business_website = ?, 
            business_facebook = ?, 
            business_instagram = ?, 
            business_twitter = ?, 
            business_logo = ?,
            updated_at = NOW()
            WHERE id = ? AND user_id = ?");
            
        $stmt->bind_param("sisssssssssii", 
            $business_name, 
            $category_id, 
            $business_description, 
            $business_address, 
            $business_phone, 
            $business_email, 
            $business_website, 
            $business_facebook, 
            $business_instagram, 
            $business_twitter, 
            $business_logo,
            $listingId,
            $userId
        );
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Your business listing has been updated successfully!";
            $_SESSION['message_type'] = "success";
            header("Location: ../view-listing.php?id=" . $listingId);
            exit();
        } else {
            $errors[] = "Failed to update listing: " . $conn->error;
        }
    }
}

// Get all categories for dropdown
$categories = [];
$categoryResult = $conn->query("SELECT * FROM categories ORDER BY name");
while ($category = $categoryResult->fetch_assoc()) {
    $categories[] = $category;
}

// Include header
include '../includes/header.php';
?>

<div class="container mt-5 mb-5">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title mb-0">Edit Your Business Listing</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="business_name" class="form-label">Business Name *</label>
                            <input type="text" class="form-control" id="business_name" name="business_name" value="<?php echo htmlspecialchars($listing['business_name']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="category_id" class="form-label">Category *</label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">Select a category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" <?php echo ($category['id'] == $listing['category_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="business_description" class="form-label">Description *</label>
                            <textarea class="form-control" id="business_description" name="business_description" rows="4" required><?php echo htmlspecialchars($listing['business_description']); ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="business_address" class="form-label">Address *</label>
                            <textarea class="form-control" id="business_address" name="business_address" rows="2" required><?php echo htmlspecialchars($listing['business_address']); ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="business_phone" class="form-label">Phone Number *</label>
                            <input type="tel" class="form-control" id="business_phone" name="business_phone" value="<?php echo htmlspecialchars($listing['business_phone']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="business_email" class="form-label">Email Address *</label>
                            <input type="email" class="form-control" id="business_email" name="business_email" value="<?php echo htmlspecialchars($listing['business_email']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="business_website" class="form-label">Website</label>
                            <input type="url" class="form-control" id="business_website" name="business_website" value="<?php echo htmlspecialchars($listing['business_website']); ?>" placeholder="https://example.com">
                        </div>
                        
                        <div class="mb-3">
                            <label for="business_facebook" class="form-label">Facebook</label>
                            <input type="text" class="form-control" id="business_facebook" name="business_facebook" value="<?php echo htmlspecialchars($listing['business_facebook']); ?>" placeholder="Facebook page URL or username">
                        </div>
                        
                        <div class="mb-3">
                            <label for="business_instagram" class="form-label">Instagram</label>
                            <input type="text" class="form-control" id="business_instagram" name="business_instagram" value="<?php echo htmlspecialchars($listing['business_instagram']); ?>" placeholder="Instagram handle">
                        </div>
                        
                        <div class="mb-3">
                            <label for="business_twitter" class="form-label">Twitter</label>
                            <input type="text" class="form-control" id="business_twitter" name="business_twitter" value="<?php echo htmlspecialchars($listing['business_twitter']); ?>" placeholder="Twitter handle">
                        </div>
                        
                        <div class="mb-3">
                            <label for="business_logo" class="form-label">Logo</label>
                            <?php if (!empty($listing['business_logo'])): ?>
                                <div class="mb-2">
                                    <img src="<?php echo '../' . htmlspecialchars($listing['business_logo']); ?>" alt="Current Logo" class="img-thumbnail" style="max-height: 100px;">
                                    <p class="small text-muted">Current logo</p>
                                </div>
                            <?php endif; ?>
                            <input type="file" class="form-control" id="business_logo" name="business_logo" accept="image/jpeg,image/png,image/gif">
                            <div class="form-text">Upload a new logo if you want to change the current one. Leave empty to keep the current logo.</div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Update Listing</button>
                            <a href="../view-listing.php?id=<?php echo $listingId; ?>" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
