<?php
// session_start();

require_once '../includes/db_connection.php';

// Create videos_promotions table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS videos_promotions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    type ENUM('video', 'promotion') NOT NULL,
    url VARCHAR(255) DEFAULT NULL,
    multiple_images TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if (!$conn->query($sql)) {
    die("Error creating table: " . $conn->error);
}

// Create videos_promotion_images table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS videos_promotion_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    videos_promotion_id INT NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    image_title VARCHAR(255) DEFAULT NULL,
    image_description TEXT DEFAULT NULL,
    display_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (videos_promotion_id) REFERENCES videos_promotions(id) ON DELETE CASCADE
)";

if (!$conn->query($sql)) {
    die("Error creating images table: " . $conn->error);
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $title = mysqli_real_escape_string($conn, $_POST['title']);
            $description = mysqli_real_escape_string($conn, $_POST['description']);
            $type = mysqli_real_escape_string($conn, $_POST['type']);
            $url = '';
            $multiple_images = [];
            
            // Handle main media upload (video or image) - now optional
            if (isset($_POST['upload_method']) && $_POST['upload_method'] === 'file' && isset($_FILES['file']) && $_FILES['file']['error'] === 0) {
                $file = $_FILES['file'];
                $allowedTypes = $type === 'video' ? ['video/mp4', 'video/webm', 'video/avi', 'video/mov'] : ['image/jpeg', 'image/png', 'image/gif'];
                $maxSize = $type === 'video' ? 100 * 1024 * 1024 : 50 * 1024 * 1024; // 100MB for videos, 50MB for images
                
                if (!in_array($file['type'], $allowedTypes)) {
                    $_SESSION['error'] = "Invalid file type. For " . $type . "s, please upload " . 
                        ($type === 'video' ? "MP4, WebM, AVI, or MOV" : "JPEG, PNG, or GIF") . " files.";
                } elseif ($file['size'] > $maxSize) {
                    $_SESSION['error'] = "File is too large. Maximum size is " . ($type === 'video' ? "100MB" : "50MB") . ".";
                } else {
                    // Create upload directory if it doesn't exist
                    $uploadDir = '../uploads/' . $type . 's/';
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    
                    // Generate unique filename
                    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $filename = uniqid() . '.' . $extension;
                    $filepath = $uploadDir . $filename;
                    
                    if (move_uploaded_file($file['tmp_name'], $filepath)) {
                        $url = 'uploads/' . $type . 's/' . $filename;
                    } else {
                        $_SESSION['error'] = "Error uploading file.";
                    }
                }
            } elseif (isset($_POST['upload_method']) && $_POST['upload_method'] === 'url' && !empty($_POST['url'])) {
                // Handle URL input - now optional
                $url = mysqli_real_escape_string($conn, $_POST['url']);
            }
            
            // Handle multiple images upload
            if (isset($_FILES['multiple_images']) && !empty($_FILES['multiple_images']['name'][0])) {
                $uploadDir = '../uploads/videos_promotions/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                foreach ($_FILES['multiple_images']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['multiple_images']['error'][$key] === 0) {
                        $file_type = $_FILES['multiple_images']['type'][$key];
                        $file_size = $_FILES['multiple_images']['size'][$key];
                        
                        if (in_array($file_type, ['image/jpeg', 'image/png', 'image/gif']) && $file_size <= 10 * 1024 * 1024) {
                            $extension = pathinfo($_FILES['multiple_images']['name'][$key], PATHINFO_EXTENSION);
                            $filename = uniqid() . '_' . $key . '.' . $extension;
                            $filepath = $uploadDir . $filename;
                            
                            if (move_uploaded_file($tmp_name, $filepath)) {
                                $multiple_images[] = 'uploads/videos_promotions/' . $filename;
                            }
                        }
                    }
                }
            }
            
            if (empty($_SESSION['error'])) {
                $multiple_images_json = !empty($multiple_images) ? json_encode($multiple_images) : null;
                
                $sql = "INSERT INTO videos_promotions (title, description, type, url, multiple_images) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssss", $title, $description, $type, $url, $multiple_images_json);
                
                if ($stmt->execute()) {
                    $videos_promotion_id = $conn->insert_id;
                    
                    // Insert multiple images into videos_promotion_images table
                    if (!empty($multiple_images)) {
                        foreach ($multiple_images as $index => $image_url) {
                            $image_title = isset($_POST['image_titles'][$index]) ? mysqli_real_escape_string($conn, $_POST['image_titles'][$index]) : '';
                            $image_description = isset($_POST['image_descriptions'][$index]) ? mysqli_real_escape_string($conn, $_POST['image_descriptions'][$index]) : '';
                            
                            $sql = "INSERT INTO videos_promotion_images (videos_promotion_id, image_url, image_title, image_description, display_order) VALUES (?, ?, ?, ?, ?)";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("isssi", $videos_promotion_id, $image_url, $image_title, $image_description, $index);
                            $stmt->execute();
                        }
                    }
                    
                    if (!empty($multiple_images)) {
                        $_SESSION['success'] = "Item added successfully! " . count($multiple_images) . " image(s) uploaded.";
                    } else {
                        $_SESSION['success'] = "Item added successfully!";
                    }
                } else {
                    $_SESSION['error'] = "Error adding item: " . $conn->error;
                }
            }
            
            // Redirect to prevent form resubmission
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
            
        } elseif ($_POST['action'] === 'edit' && isset($_POST['id'])) {
            $id = (int)$_POST['id'];
            $title = mysqli_real_escape_string($conn, $_POST['title']);
            $description = mysqli_real_escape_string($conn, $_POST['description']);
            $type = mysqli_real_escape_string($conn, $_POST['type']);
            $url = mysqli_real_escape_string($conn, $_POST['current_url']);
            
            // Handle new main media upload
            if (isset($_FILES['file']) && $_FILES['file']['error'] === 0) {
                $file = $_FILES['file'];
                $allowedTypes = $type === 'video' ? ['video/mp4', 'video/webm', 'video/avi', 'video/mov'] : ['image/jpeg', 'image/png', 'image/gif'];
                $maxSize = $type === 'video' ? 100 * 1024 * 1024 : 50 * 1024 * 1024;
                
                if (!in_array($file['type'], $allowedTypes)) {
                    $_SESSION['error'] = "Invalid file type. For " . $type . "s, please upload " . 
                        ($type === 'video' ? "MP4, WebM, AVI, or MOV" : "JPEG, PNG, or GIF") . " files.";
                } elseif ($file['size'] > $maxSize) {
                    $_SESSION['error'] = "File is too large. Maximum size is " . ($type === 'video' ? "100MB" : "50MB") . ".";
                } else {
                    // Delete old file if it exists
                    if ($url && strpos($url, 'uploads/') === 0) {
                        $old_filepath = '../' . $url;
                        if (file_exists($old_filepath)) {
                            unlink($old_filepath);
                        }
                    }
                    
                    // Upload new file
                    $uploadDir = '../uploads/' . $type . 's/';
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    
                    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $filename = uniqid() . '.' . $extension;
                    $filepath = $uploadDir . $filename;
                    
                    if (move_uploaded_file($file['tmp_name'], $filepath)) {
                        $url = 'uploads/' . $type . 's/' . $filename;
                    } else {
                        $_SESSION['error'] = "Error uploading file.";
                    }
                }
            } elseif (isset($_POST['url']) && !empty($_POST['url'])) {
                $url = mysqli_real_escape_string($conn, $_POST['url']);
            }
            
            // Handle new multiple images upload
            $multiple_images = [];
            if (isset($_FILES['multiple_images']) && !empty($_FILES['multiple_images']['name'][0])) {
                $uploadDir = '../uploads/videos_promotions/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                foreach ($_FILES['multiple_images']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['multiple_images']['error'][$key] === 0) {
                        $file_type = $_FILES['multiple_images']['type'][$key];
                        $file_size = $_FILES['multiple_images']['size'][$key];
                        
                        if (in_array($file_type, ['image/jpeg', 'image/png', 'image/gif']) && $file_size <= 10 * 1024 * 1024) {
                            $extension = pathinfo($_FILES['multiple_images']['name'][$key], PATHINFO_EXTENSION);
                            $filename = uniqid() . '_' . $key . '.' . $extension;
                            $filepath = $uploadDir . $filename;
                            
                            if (move_uploaded_file($tmp_name, $filepath)) {
                                $multiple_images[] = 'uploads/videos_promotions/' . $filename;
                            }
                        }
                    }
                }
            }
            
            if (empty($_SESSION['error'])) {
                // Get existing multiple images
                $sql = "SELECT multiple_images FROM videos_promotions WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                $existing = $result->fetch_assoc();
                $existing_images = [];
                
                if ($existing && $existing['multiple_images']) {
                    $existing_images = json_decode($existing['multiple_images'], true) ?: [];
                }
                
                // Merge existing images with new ones
                if (!empty($multiple_images)) {
                    $all_images = array_merge($existing_images, $multiple_images);
                    $multiple_images_json = json_encode($all_images);
                    
                    // Get existing images from videos_promotion_images table
                    $sql = "SELECT image_url FROM videos_promotion_images WHERE videos_promotion_id = ? ORDER BY display_order";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $existing_db_images = [];
                    while ($row = $result->fetch_assoc()) {
                        $existing_db_images[] = $row['image_url'];
                    }
                    
                    // Insert only new images into videos_promotion_images table
                    foreach ($multiple_images as $index => $image_url) {
                        // Check if image already exists in database
                        if (!in_array($image_url, $existing_db_images)) {
                            $image_title = isset($_POST['image_titles'][$index]) ? mysqli_real_escape_string($conn, $_POST['image_titles'][$index]) : '';
                            $image_description = isset($_POST['image_descriptions'][$index]) ? mysqli_real_escape_string($conn, $_POST['image_descriptions'][$index]) : '';
                            
                            // Get the next display order
                            $sql = "SELECT MAX(display_order) as max_order FROM videos_promotion_images WHERE videos_promotion_id = ?";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("i", $id);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $max_order = $result->fetch_assoc()['max_order'] ?? -1;
                            $display_order = $max_order + 1 + $index;
                            
                            $sql = "INSERT INTO videos_promotion_images (videos_promotion_id, image_url, image_title, image_description, display_order) VALUES (?, ?, ?, ?, ?)";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("isssi", $id, $image_url, $image_title, $image_description, $display_order);
                            $stmt->execute();
                        }
                    }
                } else {
                    // No new images uploaded, keep existing ones
                    $multiple_images_json = $existing['multiple_images'];
                }
                
                $sql = "UPDATE videos_promotions SET title = ?, description = ?, type = ?, url = ?, multiple_images = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssssi", $title, $description, $type, $url, $multiple_images_json, $id);
                
                if ($stmt->execute()) {
                    if (!empty($multiple_images)) {
                        $_SESSION['success'] = "Item updated successfully! " . count($multiple_images) . " new image(s) added.";
                    } else {
                        $_SESSION['success'] = "Item updated successfully!";
                    }
                } else {
                    $_SESSION['error'] = "Error updating item: " . $conn->error;
                }
            }
            
            // Redirect to prevent form resubmission
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
            
        } elseif ($_POST['action'] === 'delete' && isset($_POST['id'])) {
            $id = (int)$_POST['id'];
            
            // Get the URLs before deleting
            $sql = "SELECT url, multiple_images FROM videos_promotions WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $item = $result->fetch_assoc();
            
            // Delete the main file if it's an uploaded file
            if ($item && $item['url'] && strpos($item['url'], 'uploads/') === 0) {
                $filepath = '../' . $item['url'];
                if (file_exists($filepath)) {
                    unlink($filepath);
                }
            }
            
            // Delete multiple images
            if ($item && $item['multiple_images']) {
                $multiple_images = json_decode($item['multiple_images'], true);
                if ($multiple_images) {
                    foreach ($multiple_images as $image_url) {
                        $filepath = '../' . $image_url;
                        if (file_exists($filepath)) {
                            unlink($filepath);
                        }
                    }
                }
            }
            
            // Delete from database
            $sql = "DELETE FROM videos_promotions WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Item deleted successfully!";
            } else {
                $_SESSION['error'] = "Error deleting item: " . $conn->error;
            }
            
            // Redirect to prevent form resubmission
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
            
        } elseif ($_POST['action'] === 'delete_image' && isset($_POST['item_id']) && isset($_POST['image_url'])) {
            $item_id = (int)$_POST['item_id'];
            $image_url = mysqli_real_escape_string($conn, $_POST['image_url']);
            
            // Delete the image file
            if (strpos($image_url, 'uploads/') === 0) {
                $filepath = '../' . $image_url;
                if (file_exists($filepath)) {
                    unlink($filepath);
                }
            }
            
            // Delete from videos_promotion_images table
            $sql = "DELETE FROM videos_promotion_images WHERE videos_promotion_id = ? AND image_url = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("is", $item_id, $image_url);
            
            if ($stmt->execute()) {
                // Update the multiple_images JSON in videos_promotions table
                $sql = "SELECT multiple_images FROM videos_promotions WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $item_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $item = $result->fetch_assoc();
                
                if ($item && $item['multiple_images']) {
                    $multiple_images = json_decode($item['multiple_images'], true);
                    if ($multiple_images) {
                        $key = array_search($image_url, $multiple_images);
                        if ($key !== false) {
                            unset($multiple_images[$key]);
                            $multiple_images = array_values($multiple_images); // Re-index array
                            $multiple_images_json = !empty($multiple_images) ? json_encode($multiple_images) : null;
                            
                            $sql = "UPDATE videos_promotions SET multiple_images = ? WHERE id = ?";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("si", $multiple_images_json, $item_id);
                            $stmt->execute();
                        }
                    }
                }
                
                $_SESSION['success'] = "Image deleted successfully!";
            } else {
                $_SESSION['error'] = "Error deleting image: " . $conn->error;
            }
            
            // Redirect to prevent form resubmission
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    }
}

// Fetch all videos and promotions with their images
$sql = "SELECT vp.*, GROUP_CONCAT(vpi.image_url ORDER BY vpi.display_order SEPARATOR '|') as additional_images 
        FROM videos_promotions vp 
        LEFT JOIN videos_promotion_images vpi ON vp.id = vpi.videos_promotion_id 
        GROUP BY vp.id 
        ORDER BY vp.created_at DESC";
$result = $conn->query($sql);
$items = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Parse additional images
        if ($row['additional_images']) {
            $row['additional_images'] = explode('|', $row['additional_images']);
        } else {
            $row['additional_images'] = [];
        }
        
        // Parse multiple_images JSON
        if ($row['multiple_images']) {
            $row['multiple_images'] = json_decode($row['multiple_images'], true);
        } else {
            $row['multiple_images'] = [];
        }
        
        $items[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Videos & Promotions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .admin-card {
            transition: all 0.3s ease;
            border: 1px solid #e5e7eb;
        }
        .admin-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            border-color: #d1d5db;
        }
        .admin-content {
            transition: margin-left 0.3s ease;
        }
        @media (min-width: 1024px) {
            .admin-content {
                margin-left: 16rem; /* w-64 = 16rem */
            }
        }
        .image-preview {
            max-width: 100px;
            max-height: 100px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #e5e7eb;
            transition: all 0.2s ease;
        }
        .image-preview:hover {
            border-color: #3b82f6;
            transform: scale(1.05);
        }
        .image-container {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 10px;
        }
        .file-input-container {
            position: relative;
            display: inline-block;
            cursor: pointer;
        }
        .file-input-container input[type=file] {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        .carousel-container {
            position: relative;
            overflow: hidden;
            border-radius: 0.5rem;
            border: 2px solid #e5e7eb;
        }
        .carousel-slide {
            display: none;
            width: 100%;
            height: 100%;
        }
        .carousel-slide.active {
            display: block;
        }
        .carousel-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0, 0, 0, 0.6);
            color: white;
            border: none;
            padding: 0.5rem;
            cursor: pointer;
            border-radius: 50%;
            z-index: 10;
            transition: all 0.2s ease;
        }
        .carousel-nav:hover {
            background: rgba(0, 0, 0, 0.8);
            transform: translateY(-50%) scale(1.1);
        }
        .carousel-prev {
            left: 10px;
        }
        .carousel-next {
            right: 10px;
        }
        .carousel-indicators {
            position: absolute;
            bottom: 10px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 5px;
        }
        .carousel-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.5);
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .carousel-indicator.active {
            background: white;
            transform: scale(1.2);
        }
        .carousel-indicator:hover {
            background: rgba(255, 255, 255, 0.8);
        }
        .admin-description {
            height: 8rem; /* 128px */
            overflow-y: auto;
            overflow-x: hidden;
            line-height: 1.5;
            word-wrap: break-word;
            word-break: break-word;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-radius: 0.5rem;
            padding: 0.75rem;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
            scrollbar-width: thin;
            scrollbar-color: #cbd5e0 #f1f5f9;
            margin-bottom: 1rem;
            animation: fadeInUp 0.6s ease-out;
            scroll-behavior: smooth;
        }
        .admin-description:hover {
            background: linear-gradient(135deg, #f1f5f9 0%, #dbeafe 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .admin-description::-webkit-scrollbar {
            width: 6px;
        }
        .admin-description::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 3px;
        }
        .admin-description::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 3px;
        }
        .admin-description::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        
        /* Animation for descriptions */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .admin-description {
            animation: fadeInUp 0.6s ease-out;
        }
        
        /* Focus styles for better accessibility */
        .admin-description:focus {
            outline: 2px solid #3b82f6;
            outline-offset: 2px;
        }
        
        /* Delete button styles */
        .delete-btn {
            transition: all 0.2s ease;
            z-index: 10;
        }
        .delete-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.4);
        }
        /* Image container improvements */
        .image-container {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 10px;
        }
        
        .image-preview {
            max-width: 100px;
            max-height: 100px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #e5e7eb;
            transition: all 0.2s ease;
        }
        
        .image-preview:hover {
            border-color: #3b82f6;
            transform: scale(1.05);
        }
        
        /* Grid-based image preview containers */
        .image-preview-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 8px;
            max-height: 300px;
            overflow-y: auto;
            padding: 10px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background-color: #f9fafb;
        }
        
        .image-preview-container::-webkit-scrollbar {
            width: 6px;
        }
        
        .image-preview-container::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 3px;
        }
        
        .image-preview-container::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 3px;
        }
        
        .image-preview-container::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        
        .image-preview-item {
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .image-preview-item img {
            width: 100%;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #e5e7eb;
            transition: all 0.2s ease;
        }
        
        .image-preview-item img:hover {
            border-color: #3b82f6;
            transform: scale(1.05);
        }
    </style>
</head>
<body class="bg-gray-50">
    <?php include 'includes/adminsidebar.php'; ?>

    <div class="admin-content p-4 sm:p-6 lg:p-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Videos & Promotions Management</h1>
            <button class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors" 
                    onclick="document.getElementById('addModal').classList.remove('hidden')">
                <i class="fas fa-plus mr-2"></i>Add New Item
            </button>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?= $_SESSION['success'] ?></span>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?= $_SESSION['error'] ?></span>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Grid Layout for Items -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($items as $item): ?>
                <div class="admin-card bg-white rounded-lg shadow-md overflow-hidden" data-item-id="<?= $item['id'] ?>">
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-4">
                            <h3 class="text-xl font-semibold text-gray-800"><?= htmlspecialchars($item['title']) ?></h3>
                            <span class="px-3 py-1 rounded-full text-sm <?= $item['type'] === 'video' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' ?>">
                                <?= ucfirst($item['type']) ?>
                            </span>
                        </div>
                        <p class="text-gray-600 mb-4 admin-description"><?= htmlspecialchars($item['description']) ?></p>
                        
                        <!-- Main Media Preview -->
                        <div class="mb-4">
                            <?php if ($item['type'] === 'video' && $item['url']): ?>
                                <?php if (strpos($item['url'], 'youtube.com') !== false || strpos($item['url'], 'youtu.be') !== false): ?>
                                    <div class="bg-gray-200 rounded-lg p-4 text-center">
                                        <i class="fas fa-play-circle text-4xl text-gray-400"></i>
                                        <p class="text-sm text-gray-600 mt-2">YouTube Video</p>
                                    </div>
                                <?php else: ?>
                                    <video controls class="w-full rounded-lg" style="max-height: 200px;">
                                        <source src="../<?= htmlspecialchars($item['url']) ?>" type="video/mp4">
                                    </video>
                                <?php endif; ?>
                            <?php elseif ($item['url']): ?>
                                <img src="../<?= htmlspecialchars($item['url']) ?>" alt="<?= htmlspecialchars($item['title']) ?>" class="w-full rounded-lg" style="max-height: 200px; object-fit: cover;">
                            <?php else: ?>
                                <!-- Carousel for items without main media -->
                                <?php 
                                $display_images = !empty($item['additional_images']) ? $item['additional_images'] : $item['multiple_images'];
                                if (!empty($display_images)): 
                                ?>
                                    <div class="carousel-container" style="height: 200px;">
                                        <?php foreach ($display_images as $index => $image): ?>
                                            <div class="carousel-slide <?= $index === 0 ? 'active' : '' ?>" data-index="<?= $index ?>">
                                                <div class="relative w-full h-full">
                                                    <img src="../<?= htmlspecialchars($image) ?>" alt="Image <?= $index + 1 ?>" class="w-full h-full object-cover">
                                                    <button type="button" 
                                                            onclick="deleteImage(<?= $item['id'] ?>, '<?= htmlspecialchars($image) ?>', event)"
                                                            class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600 transition-colors delete-btn">
                                                        ×
                                                    </button>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                        
                                        <?php if (count($display_images) > 1): ?>
                                            <button class="carousel-nav carousel-prev" onclick="changeSlide(<?= $item['id'] ?>, -1)">&#10094;</button>
                                            <button class="carousel-nav carousel-next" onclick="changeSlide(<?= $item['id'] ?>, 1)">&#10095;</button>
                                            <div class="carousel-indicators">
                                                <?php foreach ($display_images as $index => $image): ?>
                                                    <div class="carousel-indicator <?= $index === 0 ? 'active' : '' ?>" onclick="goToSlide(<?= $item['id'] ?>, <?= $index ?>)"></div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="bg-gray-200 rounded-lg p-4 text-center">
                                        <i class="fas fa-image text-4xl text-gray-400"></i>
                                        <p class="text-sm text-gray-600 mt-2">No Media</p>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Multiple Images Preview -->
                        <?php 
                        $display_images = !empty($item['additional_images']) ? $item['additional_images'] : $item['multiple_images'];
                        if (!empty($display_images) && $item['url']): 
                        ?>
                            <div class="mb-4">
                                <h4 class="text-sm font-semibold text-gray-700 mb-2">Additional Images:</h4>
                                <div class="image-container">
                                    <?php foreach (array_slice($display_images, 0, 4) as $index => $image): ?>
                                        <div class="relative inline-block" style="margin: 5px;">
                                            <img src="../<?= htmlspecialchars($image) ?>" alt="Additional Image" class="image-preview">
                                            <button type="button" 
                                                    onclick="deleteImage(<?= $item['id'] ?>, '<?= htmlspecialchars($image) ?>', event)"
                                                    class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600 transition-colors delete-btn">
                                                ×
                                            </button>
                                        </div>
                                    <?php endforeach; ?>
                                    <?php if (count($display_images) > 4): ?>
                                        <div class="image-preview bg-gray-200 flex items-center justify-center text-gray-500 text-sm">
                                            +<?= count($display_images) - 4 ?> more
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="flex justify-between items-center">
                            <?php if ($item['url']): ?>
                                <a href="../<?= htmlspecialchars($item['url']) ?>" target="_blank" class="text-blue-600 hover:text-blue-800">
                                    <i class="fas fa-external-link-alt mr-2"></i>View
                                </a>
                            <?php else: ?>
                                <span class="text-gray-400">
                                    <i class="fas fa-image mr-2"></i>No Media
                                </span>
                            <?php endif; ?>
                            <div class="flex space-x-2">
                                <button type="button" onclick="openEditModal(<?= htmlspecialchars(json_encode($item)) ?>)" 
                                        class="text-blue-600 hover:text-blue-800">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this item?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                    <button type="submit" class="text-red-600 hover:text-red-800">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Add New Item Modal -->
    <div id="addModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-8 max-w-2xl w-full mx-4 max-h-screen overflow-y-auto">
            <h2 class="text-2xl font-bold mb-6">Add New Item</h2>
            <form method="POST" class="space-y-4" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-700 mb-2">Title</label>
                        <input type="text" name="title" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 mb-2">Type</label>
                        <select name="type" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500" onchange="toggleUploadMethod(this.value)">
                            <option value="video">Video</option>
                            <option value="promotion">Promotion</option>
                        </select>
                    </div>
                </div>
                
                <div>
                    <label class="block text-gray-700 mb-2">Description</label>
                    <textarea name="description" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500" rows="3"></textarea>
                </div>

                <!-- Main Media Section -->
                <div>
                    <label class="block text-gray-700 mb-2">Main Media (Optional)</label>
                    <p class="text-sm text-gray-500 mb-3">Upload a video or image, or leave empty to use only additional images</p>
                    
                    <!-- Upload Method Selection -->
                    <div class="mb-4">
                        <div class="flex space-x-4">
                            <label class="flex items-center">
                                <input type="radio" name="upload_method" value="url" checked 
                                       class="form-radio text-blue-600" onchange="toggleUploadMethod(document.querySelector('select[name=type]').value)">
                                <span class="ml-2">URL</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="upload_method" value="file" 
                                       class="form-radio text-blue-600" onchange="toggleUploadMethod(document.querySelector('select[name=type]').value)">
                                <span class="ml-2">File Upload</span>
                            </label>
                        </div>
                    </div>
                    
                    <!-- URL Input -->
                    <div id="urlInput" class="upload-method">
                        <label class="block text-gray-700 mb-2">URL (Optional)</label>
                        <input type="url" name="url" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500" 
                               placeholder="Enter YouTube URL or image URL">
                        <p class="text-sm text-gray-500 mt-1">For videos, enter a YouTube URL. For promotions, enter an image URL. Leave empty if you only want to use additional images.</p>
                    </div>
                    
                    <!-- File Upload -->
                    <div id="fileUpload" class="upload-method hidden">
                        <label class="block text-gray-700 mb-2">Upload File (Optional)</label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                            <input type="file" name="file" class="hidden" id="fileInput" accept="video/*,image/*">
                            <label for="fileInput" class="cursor-pointer">
                                <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-2"></i>
                                <p class="text-gray-600">Click to upload or drag and drop</p>
                                <p class="text-sm text-gray-500 mt-1" id="fileAcceptText">MP4, WebM, AVI, MOV, or image files (max 100MB for videos, 50MB for images)</p>
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- Multiple Images Upload -->
                <div>
                    <label class="block text-gray-700 mb-2">Additional Images (Optional)</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                        <input type="file" name="multiple_images[]" class="hidden" id="multipleImagesInput" accept="image/*" multiple>
                        <label for="multipleImagesInput" class="cursor-pointer">
                            <i class="fas fa-images text-4xl text-gray-400 mb-2"></i>
                            <p class="text-gray-600">Click to upload multiple images</p>
                            <p class="text-sm text-gray-500 mt-1">JPEG, PNG, or GIF files (max 10MB each)</p>
                            <p class="text-xs text-blue-600 mt-2">You can select multiple images at once</p>
                        </label>
                    </div>
                    <div id="imagePreviewContainer" class="mt-4 image-preview-container"></div>
                </div>
                
                <div class="flex justify-end space-x-4 mt-6">
                    <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')" 
                            class="px-6 py-2 border rounded-lg hover:bg-gray-100">
                        Cancel
                    </button>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Add Item
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Item Modal -->
    <div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-8 max-w-2xl w-full mx-4 max-h-screen overflow-y-auto">
            <h2 class="text-2xl font-bold mb-6">Edit Item</h2>
            <form method="POST" class="space-y-4" enctype="multipart/form-data">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <input type="hidden" name="current_url" id="edit_current_url">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-700 mb-2">Title</label>
                        <input type="text" name="title" id="edit_title" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 mb-2">Type</label>
                        <select name="type" id="edit_type" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500" onchange="toggleEditUploadMethod(this.value)">
                            <option value="video">Video</option>
                            <option value="promotion">Promotion</option>
                        </select>
                    </div>
                </div>
                
                <div>
                    <label class="block text-gray-700 mb-2">Description</label>
                    <textarea name="description" id="edit_description" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500" rows="3"></textarea>
                </div>

                <!-- Main Media Section -->
                <div>
                    <label class="block text-gray-700 mb-2">Main Media (Optional)</label>
                    <p class="text-sm text-gray-500 mb-3">Upload a new video or image, or leave empty to keep current media</p>
                    
                    <!-- Current Media Display -->
                    <div id="edit_current_media_display" class="mb-4 p-4 bg-gray-50 rounded-lg">
                        <h4 class="text-sm font-semibold text-gray-700 mb-2">Current Media:</h4>
                        <div id="edit_current_media_content"></div>
                    </div>
                    
                    <!-- Upload Method Selection -->
                    <div class="mb-4">
                        <div class="flex space-x-4">
                            <label class="flex items-center">
                                <input type="radio" name="edit_upload_method" value="url" checked 
                                       class="form-radio text-blue-600" onchange="toggleEditUploadMethod(document.getElementById('edit_type').value)">
                                <span class="ml-2">URL</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="edit_upload_method" value="file" 
                                       class="form-radio text-blue-600" onchange="toggleEditUploadMethod(document.getElementById('edit_type').value)">
                                <span class="ml-2">File Upload</span>
                            </label>
                        </div>
                    </div>
                    
                    <!-- URL Input -->
                    <div id="editUrlInput" class="upload-method">
                        <label class="block text-gray-700 mb-2">New URL (Optional)</label>
                        <input type="url" name="url" id="edit_url" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500" 
                               placeholder="Enter YouTube URL or image URL">
                        <p class="text-sm text-gray-500 mt-1">For videos, enter a YouTube URL. For promotions, enter an image URL. Leave empty to keep current media.</p>
                    </div>
                    
                    <!-- File Upload -->
                    <div id="editFileUpload" class="upload-method hidden">
                        <label class="block text-gray-700 mb-2">Upload New File (Optional)</label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                            <input type="file" name="file" class="hidden" id="editFileInput" accept="video/*,image/*">
                            <label for="editFileInput" class="cursor-pointer">
                                <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-2"></i>
                                <p class="text-gray-600">Click to upload or drag and drop</p>
                                <p class="text-sm text-gray-500 mt-1" id="editFileAcceptText">MP4, WebM, AVI, MOV, or image files (max 100MB for videos, 50MB for images)</p>
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- Multiple Images Upload -->
                <div>
                    <label class="block text-gray-700 mb-2">Additional Images (Optional)</label>
                    
                    <!-- Current Images Display -->
                    <div id="edit_current_images_display" class="mb-4 p-4 bg-gray-50 rounded-lg">
                        <h4 class="text-sm font-semibold text-gray-700 mb-2">Current Additional Images:</h4>
                        <div id="edit_current_images_content" class="image-container"></div>
                    </div>
                    
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                        <input type="file" name="multiple_images[]" class="hidden" id="editMultipleImagesInput" accept="image/*" multiple>
                        <label for="editMultipleImagesInput" class="cursor-pointer">
                            <i class="fas fa-images text-4xl text-gray-400 mb-2"></i>
                            <p class="text-gray-600">Click to upload new additional images</p>
                            <p class="text-sm text-gray-500 mt-1">JPEG, PNG, or GIF files (max 10MB each)</p>
                            <p class="text-xs text-blue-600 mt-2">New images will be added to existing ones</p>
                        </label>
                    </div>
                    <div id="editImagePreviewContainer" class="mt-4 image-preview-container"></div>
                </div>
                
                <div class="flex justify-end space-x-4 mt-6">
                    <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')" 
                            class="px-6 py-2 border rounded-lg hover:bg-gray-100">
                        Cancel
                    </button>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Update Item
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleUploadMethod(type) {
            const uploadMethod = document.querySelector('input[name="upload_method"]:checked').value;
            const urlInput = document.getElementById('urlInput');
            const fileUpload = document.getElementById('fileUpload');
            const fileInput = document.getElementById('fileInput');
            const fileAcceptText = document.getElementById('fileAcceptText');
            
            // Show/hide appropriate upload method
            if (uploadMethod === 'url') {
                urlInput.classList.remove('hidden');
                fileUpload.classList.add('hidden');
                fileInput.removeAttribute('required');
                document.querySelector('input[name="url"]').removeAttribute('required');
            } else {
                urlInput.classList.add('hidden');
                fileUpload.classList.remove('hidden');
                fileInput.removeAttribute('required');
                document.querySelector('input[name="url"]').removeAttribute('required');
            }
            
            // Update file input accept attribute and text based on type
            if (type === 'video') {
                fileInput.setAttribute('accept', 'video/*');
                fileAcceptText.textContent = 'MP4, WebM, AVI, MOV files (max 100MB)';
            } else {
                fileInput.setAttribute('accept', 'image/*');
                fileAcceptText.textContent = 'JPEG, PNG, or GIF files (max 50MB)';
            }
        }

        // Multiple images preview
        document.getElementById('multipleImagesInput').addEventListener('change', function(e) {
            const container = document.getElementById('imagePreviewContainer');
            container.innerHTML = '';
            
            if (e.target.files.length > 0) {
                // Add a header showing number of images
                const header = document.createElement('div');
                header.className = 'col-span-full text-sm font-medium text-gray-700 mb-2';
                header.textContent = `${e.target.files.length} image(s) selected:`;
                container.appendChild(header);
            }
            
            for (let i = 0; i < e.target.files.length; i++) {
                const file = e.target.files[i];
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const imageItem = document.createElement('div');
                        imageItem.className = 'image-preview-item';
                        
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.alt = 'Preview Image';
                        
                        imageItem.appendChild(img);
                        container.appendChild(imageItem);
                    };
                    reader.readAsDataURL(file);
                }
            }
        });

        // Carousel functionality
        function changeSlide(itemId, direction) {
            const container = document.querySelector(`[data-item-id="${itemId}"] .carousel-container`);
            const slides = container.querySelectorAll('.carousel-slide');
            const indicators = container.querySelectorAll('.carousel-indicator');
            let currentIndex = 0;
            
            slides.forEach((slide, index) => {
                if (slide.classList.contains('active')) {
                    currentIndex = index;
                }
            });
            
            const newIndex = (currentIndex + direction + slides.length) % slides.length;
            
            slides[currentIndex].classList.remove('active');
            slides[newIndex].classList.add('active');
            
            indicators[currentIndex].classList.remove('active');
            indicators[newIndex].classList.add('active');
        }

        function goToSlide(itemId, index) {
            const container = document.querySelector(`[data-item-id="${itemId}"] .carousel-container`);
            const slides = container.querySelectorAll('.carousel-slide');
            const indicators = container.querySelectorAll('.carousel-indicator');
            
            slides.forEach(slide => slide.classList.remove('active'));
            indicators.forEach(indicator => indicator.classList.remove('active'));
            
            slides[index].classList.add('active');
            indicators[index].classList.add('active');
        }

        // Initialize upload method on page load
        document.addEventListener('DOMContentLoaded', function() {
            toggleUploadMethod(document.querySelector('select[name=type]').value);
        });

        // Edit modal functions
        function openEditModal(item) {
            // Populate form fields
            document.getElementById('edit_id').value = item.id;
            document.getElementById('edit_title').value = item.title;
            document.getElementById('edit_description').value = item.description;
            document.getElementById('edit_type').value = item.type;
            document.getElementById('edit_current_url').value = item.url || '';
            
            // Display current media
            const currentMediaContent = document.getElementById('edit_current_media_content');
            if (item.url) {
                if (item.type === 'video') {
                    if (item.url.includes('youtube.com') || item.url.includes('youtu.be')) {
                        currentMediaContent.innerHTML = `
                            <div class="bg-gray-200 rounded-lg p-4 text-center">
                                <i class="fas fa-play-circle text-4xl text-gray-400"></i>
                                <p class="text-sm text-gray-600 mt-2">YouTube Video</p>
                                <p class="text-xs text-gray-500">${item.url}</p>
                            </div>
                        `;
                    } else {
                        currentMediaContent.innerHTML = `
                            <video controls class="w-full rounded-lg" style="max-height: 200px;">
                                <source src="../${item.url}" type="video/mp4">
                            </video>
                        `;
                    }
                } else {
                    currentMediaContent.innerHTML = `
                        <img src="../${item.url}" alt="${item.title}" class="w-full rounded-lg" style="max-height: 200px; object-fit: cover;">
                    `;
                }
            } else {
                currentMediaContent.innerHTML = `
                    <div class="bg-gray-200 rounded-lg p-4 text-center">
                        <i class="fas fa-image text-4xl text-gray-400"></i>
                        <p class="text-sm text-gray-600 mt-2">No Main Media</p>
                    </div>
                `;
            }
            
            // Display current additional images
            const currentImagesContent = document.getElementById('edit_current_images_content');
            const displayImages = item.additional_images && item.additional_images.length > 0 ? item.additional_images : (item.multiple_images || []);
            if (displayImages.length > 0) {
                currentImagesContent.innerHTML = '';
                
                // Add a header showing number of current images
                const header = document.createElement('div');
                header.className = 'col-span-full text-sm font-medium text-gray-700 mb-2';
                header.textContent = `${displayImages.length} current image(s):`;
                currentImagesContent.appendChild(header);
                
                displayImages.forEach((image, index) => {
                    const imageContainer = document.createElement('div');
                    imageContainer.className = 'image-preview-item';
                    
                    const img = document.createElement('img');
                    img.src = '../' + image;
                    img.alt = 'Additional Image';
                    
                    const deleteBtn = document.createElement('button');
                    deleteBtn.type = 'button';
                    deleteBtn.className = 'absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600 transition-colors delete-btn';
                    deleteBtn.innerHTML = '×';
                    deleteBtn.onclick = function(event) {
                        if (confirm('Are you sure you want to delete this image?')) {
                            // Show loading state
                            const originalText = deleteBtn.innerHTML;
                            deleteBtn.innerHTML = '...';
                            deleteBtn.disabled = true;
                            deleteBtn.style.opacity = '0.6';
                            
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.innerHTML = `
                                <input type="hidden" name="action" value="delete_image">
                                <input type="hidden" name="item_id" value="${item.id}">
                                <input type="hidden" name="image_url" value="${image}">
                            `;
                            document.body.appendChild(form);
                            form.submit();
                        }
                    };
                    
                    imageContainer.appendChild(img);
                    imageContainer.appendChild(deleteBtn);
                    currentImagesContent.appendChild(imageContainer);
                });
            } else {
                currentImagesContent.innerHTML = `
                    <div class="text-gray-500 text-sm">No additional images</div>
                `;
            }
            
            // Initialize upload method
            toggleEditUploadMethod(item.type);
            
            // Show modal
            document.getElementById('editModal').classList.remove('hidden');
        }

        function toggleEditUploadMethod(type) {
            const uploadMethod = document.querySelector('input[name="edit_upload_method"]:checked').value;
            const urlInput = document.getElementById('editUrlInput');
            const fileUpload = document.getElementById('editFileUpload');
            const fileInput = document.getElementById('editFileInput');
            const fileAcceptText = document.getElementById('editFileAcceptText');
            
            // Show/hide appropriate upload method
            if (uploadMethod === 'url') {
                urlInput.classList.remove('hidden');
                fileUpload.classList.add('hidden');
                fileInput.removeAttribute('required');
                document.getElementById('edit_url').removeAttribute('required');
            } else {
                urlInput.classList.add('hidden');
                fileUpload.classList.remove('hidden');
                fileInput.removeAttribute('required');
                document.getElementById('edit_url').removeAttribute('required');
            }
            
            // Update file input accept attribute and text based on type
            if (type === 'video') {
                fileInput.setAttribute('accept', 'video/*');
                fileAcceptText.textContent = 'MP4, WebM, AVI, MOV files (max 100MB)';
            } else {
                fileInput.setAttribute('accept', 'image/*');
                fileAcceptText.textContent = 'JPEG, PNG, or GIF files (max 50MB)';
            }
        }

        // Edit multiple images preview
        document.getElementById('editMultipleImagesInput').addEventListener('change', function(e) {
            const container = document.getElementById('editImagePreviewContainer');
            container.innerHTML = '';
            
            if (e.target.files.length > 0) {
                // Add a header showing number of images
                const header = document.createElement('div');
                header.className = 'col-span-full text-sm font-medium text-gray-700 mb-2';
                header.textContent = `${e.target.files.length} new image(s) to add:`;
                container.appendChild(header);
            }
            
            for (let i = 0; i < e.target.files.length; i++) {
                const file = e.target.files[i];
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const imageItem = document.createElement('div');
                        imageItem.className = 'image-preview-item';
                        
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.alt = 'Preview Image';
                        
                        imageItem.appendChild(img);
                        container.appendChild(imageItem);
                    };
                    reader.readAsDataURL(file);
                }
            }
        });

        // Function to delete individual images
        function deleteImage(itemId, imageUrl, event) {
            if (confirm('Are you sure you want to delete this image?')) {
                // Show loading state
                const deleteBtn = event.target;
                const originalText = deleteBtn.innerHTML;
                deleteBtn.innerHTML = '...';
                deleteBtn.disabled = true;
                deleteBtn.style.opacity = '0.6';
                
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_image">
                    <input type="hidden" name="item_id" value="${itemId}">
                    <input type="hidden" name="image_url" value="${imageUrl}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>
