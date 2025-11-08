<?php
// Override PHP upload limits - Use appropriate values instead of 0
ini_set('upload_max_filesize', '2G');
ini_set('post_max_size', '2G');
ini_set('max_execution_time', '300');
ini_set('max_input_time', '300');
ini_set('memory_limit', '512M');
ini_set('max_file_uploads', '100');

// // Start session
// session_start();

// // Check if user is logged in
// if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
//     header("Location: admin_login.php");
//     exit;
// }

// Database credentials
$servername = "localhost"; 
$username = "u637871113_jamespolymers"; 
$password = "j@m3sP0lymers!@@"; 
$dbname = "u637871113_jpmc";
// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Add video support columns if they don't exist
$sql = "SHOW COLUMNS FROM plant_visit_images LIKE 'media_type'";
$result = $conn->query($sql);
if ($result->num_rows === 0) {
    $sql = "ALTER TABLE `plant_visit_images` 
            ADD COLUMN `media_type` enum('image','video') DEFAULT 'image' AFTER `image`,
            ADD COLUMN `video_url` varchar(500) DEFAULT NULL AFTER `media_type`,
            ADD COLUMN `video_type` enum('youtube','vimeo','uploaded') DEFAULT 'youtube' AFTER `video_url`,
            ADD COLUMN `video_title` varchar(255) DEFAULT NULL AFTER `video_type`,
            ADD COLUMN `video_description` text DEFAULT NULL AFTER `video_title`";
    $conn->query($sql);
    
    // Update existing records
    $sql = "UPDATE plant_visit_images SET media_type = 'image' WHERE media_type IS NULL OR media_type = ''";
    $conn->query($sql);
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $title = $_POST['title'];
                $description = $_POST['description'];
                
                // Insert plant visit with empty image to satisfy NOT NULL constraint
                $sql = "INSERT INTO plant_visits (title, description, image) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $empty_image = '';
                $stmt->bind_param("sss", $title, $description, $empty_image);
                $stmt->execute();
                $plant_visit_id = $conn->insert_id;
                
                // Handle media uploads (images and videos)
                if (isset($_FILES['media_files'])) {
                    // Use absolute paths for upload directories
                    $target_dir_images = $_SERVER['DOCUMENT_ROOT'] . "/images/plant_visit/";
                    $target_dir_videos = $_SERVER['DOCUMENT_ROOT'] . "/videos/plant_visit/";
                    
                    if (!file_exists($target_dir_images)) {
                        mkdir($target_dir_images, 0777, true);
                    }
                    if (!file_exists($target_dir_videos)) {
                        mkdir($target_dir_videos, 0777, true);
                    }
                    
                    foreach ($_FILES['media_files']['tmp_name'] as $key => $tmp_name) {
                        if ($_FILES['media_files']['error'][$key] === 0) {
                            $file_type = $_FILES['media_files']['type'][$key];
                            $file_size = $_FILES['media_files']['size'][$key];
                            
                            // Determine if it's an image or video
                            $is_video = strpos($file_type, 'video/') === 0;
                            $is_image = strpos($file_type, 'image/') === 0;
                            
                            if ($is_image) {
                                // Handle image upload
                                $filename = time() . '_' . basename($_FILES['media_files']['name'][$key]);
                                $target_file = $target_dir_images . $filename;
                                
                                if (move_uploaded_file($tmp_name, $target_file)) {
                                    $sql = "INSERT INTO plant_visit_images (plant_visit_id, image, media_type, display_order) VALUES (?, ?, 'image', ?)";
                                    $stmt = $conn->prepare($sql);
                                    $display_order = $key;
                                    $stmt->bind_param("isi", $plant_visit_id, $filename, $display_order);
                                    $stmt->execute();
                                    // Debug output for image upload
                                    echo '<!-- Uploaded image to: ' . $target_file . ' | Public URL: /images/plant_visit/' . $filename . ' -->';
                                }
                            } elseif ($is_video && $file_size <= 100 * 1024 * 1024) { // 100MB limit for videos
                                // Handle video upload
                                $filename = time() . '_' . basename($_FILES['media_files']['name'][$key]);
                                $target_file = $target_dir_videos . $filename;
                                
                                if (move_uploaded_file($tmp_name, $target_file)) {
                                    $video_url = 'videos/plant_visit/' . $filename;
                                    $sql = "INSERT INTO plant_visit_images (plant_visit_id, image, media_type, video_url, video_type, display_order) VALUES (?, ?, 'video', ?, 'uploaded', ?)";
                                    $stmt = $conn->prepare($sql);
                                    $display_order = $key;
                                    $stmt->bind_param("issi", $plant_visit_id, $filename, $video_url, $display_order);
                                    $stmt->execute();
                                    // Debug output for video upload
                                    echo '<!-- Uploaded video to: ' . $target_file . ' | Public URL: /videos/plant_visit/' . $filename . ' -->';
                                }
                            }
                        }
                    }
                }
                
                // Handle video URLs
                if (isset($_POST['video_urls']) && is_array($_POST['video_urls'])) {
                    foreach ($_POST['video_urls'] as $key => $video_url) {
                        if (!empty(trim($video_url))) {
                            $video_type = 'youtube';
                            if (strpos($video_url, 'vimeo.com') !== false) {
                                $video_type = 'vimeo';
                            }
                            
                            $video_title = isset($_POST['video_titles'][$key]) ? $_POST['video_titles'][$key] : '';
                            $video_description = isset($_POST['video_descriptions'][$key]) ? $_POST['video_descriptions'][$key] : '';
                            
                            $sql = "INSERT INTO plant_visit_images (plant_visit_id, image, media_type, video_url, video_type, video_title, video_description, display_order) VALUES (?, ?, 'video', ?, ?, ?, ?, ?)";
                            $stmt = $conn->prepare($sql);
                            $display_order = $key + 1000; // Offset to put URLs after uploaded files
                            $placeholder_image = ''; // Empty string for video URLs
                            $stmt->bind_param("isssssi", $plant_visit_id, $placeholder_image, $video_url, $video_type, $video_title, $video_description, $display_order);
                            
                            if ($stmt->execute()) {
                                // Debug: Log successful video URL insertion
                                error_log("Successfully added video URL: " . $video_url);
                            } else {
                                // Debug: Log error
                                error_log("Error adding video URL: " . $stmt->error);
                            }
                        }
                    }
                }

                // Handle chunked uploads (both images and videos)
                if (isset($_POST['chunked_files']) && is_array($_POST['chunked_files'])) {
                    foreach ($_POST['chunked_files'] as $key => $file_path) {
                        if (!empty($file_path)) {
                            $filename = basename($file_path);
                            $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                            $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp']);
                            
                            if ($isImage) {
                                // Handle image
                                $sql = "INSERT INTO plant_visit_images (plant_visit_id, image, media_type, display_order) VALUES (?, ?, 'image', ?)";
                                $stmt = $conn->prepare($sql);
                                $display_order = $key + 5000; // Offset to avoid collision with normal uploads
                                $stmt->bind_param("isi", $plant_visit_id, $filename, $display_order);
                                $stmt->execute();
                            } else {
                                // Handle video
                                $sql = "INSERT INTO plant_visit_images (plant_visit_id, image, media_type, video_url, video_type, display_order) VALUES (?, ?, 'video', ?, 'uploaded', ?)";
                                $stmt = $conn->prepare($sql);
                                $display_order = $key + 5000; // Offset to avoid collision with normal uploads
                                $stmt->bind_param("issi", $plant_visit_id, $filename, $file_path, $display_order);
                                $stmt->execute();
                            }
                        }
                    }
                }
                break;
                
            case 'edit':
                $id = $_POST['id'];
                $title = $_POST['title'];
                $description = $_POST['description'];
                
                // Update plant visit
                $sql = "UPDATE plant_visits SET title = ?, description = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssi", $title, $description, $id);
                $stmt->execute();
                
                // Handle new media uploads
                if (isset($_FILES['media_files'])) {
                    $target_dir_images = $_SERVER['DOCUMENT_ROOT'] . "/images/plant_visit/";
                    $target_dir_videos = $_SERVER['DOCUMENT_ROOT'] . "/videos/plant_visit/";
                    
                    // Get current max display order
                    $sql = "SELECT MAX(display_order) as max_order FROM plant_visit_images WHERE plant_visit_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();
                    $display_order = $row['max_order'] + 1;
                    
                    foreach ($_FILES['media_files']['tmp_name'] as $key => $tmp_name) {
                        if ($_FILES['media_files']['error'][$key] === 0) {
                            $file_type = $_FILES['media_files']['type'][$key];
                            $file_size = $_FILES['media_files']['size'][$key];
                            
                            $is_video = strpos($file_type, 'video/') === 0;
                            $is_image = strpos($file_type, 'image/') === 0;
                            
                            if ($is_image) {
                                $filename = time() . '_' . basename($_FILES['media_files']['name'][$key]);
                                $target_file = $target_dir_images . $filename;
                                
                                if (move_uploaded_file($tmp_name, $target_file)) {
                                    $sql = "INSERT INTO plant_visit_images (plant_visit_id, image, media_type, display_order) VALUES (?, ?, 'image', ?)";
                                    $stmt = $conn->prepare($sql);
                                    $stmt->bind_param("isi", $id, $filename, $display_order);
                                    $stmt->execute();
                                    $display_order++;
                                    // Debug output for image upload (edit)
                                    echo '<!-- Uploaded image to: ' . $target_file . ' | Public URL: /images/plant_visit/' . $filename . ' -->';
                                }
                            } elseif ($is_video && $file_size <= 100 * 1024 * 1024) {
                                $filename = time() . '_' . basename($_FILES['media_files']['name'][$key]);
                                $target_file = $target_dir_videos . $filename;
                                
                                if (move_uploaded_file($tmp_name, $target_file)) {
                                    $video_url = 'videos/plant_visit/' . $filename;
                                    $sql = "INSERT INTO plant_visit_images (plant_visit_id, image, media_type, video_url, video_type, display_order) VALUES (?, ?, 'video', ?, 'uploaded', ?)";
                                    $stmt = $conn->prepare($sql);
                                    $stmt->bind_param("issi", $id, $filename, $video_url, $display_order);
                                    $stmt->execute();
                                    $display_order++;
                                    // Debug output for video upload (edit)
                                    echo '<!-- Uploaded video to: ' . $target_file . ' | Public URL: /videos/plant_visit/' . $filename . ' -->';
                                }
                            }
                        }
                    }
                }
                
                // Handle new video URLs
                if (isset($_POST['video_urls']) && is_array($_POST['video_urls'])) {
                    foreach ($_POST['video_urls'] as $key => $video_url) {
                        if (!empty(trim($video_url))) {
                            $video_type = 'youtube';
                            if (strpos($video_url, 'vimeo.com') !== false) {
                                $video_type = 'vimeo';
                            }
                            
                            $video_title = isset($_POST['video_titles'][$key]) ? $_POST['video_titles'][$key] : '';
                            $video_description = isset($_POST['video_descriptions'][$key]) ? $_POST['video_descriptions'][$key] : '';
                            
                            $sql = "INSERT INTO plant_visit_images (plant_visit_id, image, media_type, video_url, video_type, video_title, video_description, display_order) VALUES (?, ?, 'video', ?, ?, ?, ?, ?)";
                            $stmt = $conn->prepare($sql);
                            $placeholder_image = ''; // Empty string for video URLs
                            $stmt->bind_param("isssssi", $id, $placeholder_image, $video_url, $video_type, $video_title, $video_description, $display_order);
                            
                            if ($stmt->execute()) {
                                // Debug: Log successful video URL insertion
                                error_log("Successfully added video URL (edit): " . $video_url);
                            } else {
                                // Debug: Log error
                                error_log("Error adding video URL (edit): " . $stmt->error);
                            }
                            $display_order++;
                        }
                    }
                }

                // Handle chunked uploads (both images and videos)
                if (isset($_POST['chunked_files']) && is_array($_POST['chunked_files'])) {
                    // Get current max display order
                    $sql = "SELECT MAX(display_order) as max_order FROM plant_visit_images WHERE plant_visit_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();
                    $display_order = $row['max_order'] + 1;
                    
                    foreach ($_POST['chunked_files'] as $file_path) {
                        if (!empty($file_path)) {
                            $filename = basename($file_path);
                            $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                            $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp']);
                            
                            if ($isImage) {
                                // Handle image
                                $sql = "INSERT INTO plant_visit_images (plant_visit_id, image, media_type, display_order) VALUES (?, ?, 'image', ?)";
                                $stmt = $conn->prepare($sql);
                                $stmt->bind_param("isi", $id, $filename, $display_order);
                                $stmt->execute();
                            } else {
                                // Handle video
                                $sql = "INSERT INTO plant_visit_images (plant_visit_id, image, media_type, video_url, video_type, display_order) VALUES (?, ?, 'video', ?, 'uploaded', ?)";
                                $stmt = $conn->prepare($sql);
                                $stmt->bind_param("issi", $id, $filename, $file_path, $display_order);
                                $stmt->execute();
                            }
                            $display_order++;
                        }
                    }
                }
                break;
                
            case 'delete':
                $id = $_POST['id'];
                $sql = "DELETE FROM plant_visits WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                break;
                
            case 'delete_media':
                $media_id = $_POST['media_id'];
                $sql = "DELETE FROM plant_visit_images WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $media_id);
                $stmt->execute();
                break;
        }
        
        // Redirect to prevent form resubmission
        header("Location: admin_plant_visit.php");
        exit;
    }
}

// Get all plant visits with their media (images and videos)
$plant_visits = array();
$sql = "SELECT pv.*, GROUP_CONCAT(pvi.id, ':', pvi.image, ':', pvi.media_type, ':', COALESCE(pvi.video_url, ''), ':', COALESCE(pvi.video_type, ''), ':', COALESCE(pvi.video_title, ''), ':', COALESCE(pvi.video_description, '') ORDER BY pvi.display_order) as media 
        FROM plant_visits pv 
        LEFT JOIN plant_visit_images pvi ON pv.id = pvi.plant_visit_id 
        GROUP BY pv.id 
        ORDER BY pv.created_at DESC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $media = array();
        if ($row['media']) {
            foreach (explode(',', $row['media']) as $item) {
                $parts = explode(':', $item);
                if (count($parts) >= 7) {
                    $media[] = array(
                        'id' => $parts[0],
                        'filename' => $parts[1],
                        'media_type' => $parts[2],
                        'video_url' => $parts[3],
                        'video_type' => $parts[4],
                        'video_title' => $parts[5],
                        'video_description' => $parts[6]
                    );
                }
            }
        }
        $row['media'] = $media;
        $plant_visits[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plant Visit Management | James Polymers</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#0066cc',
                        secondary: '#004d99',
                        dark: '#222222',
                        light: '#f5f5f5'
                    }
                }
            }
        }
    </script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Resumable.js for chunked uploads -->
    <script src="https://cdn.jsdelivr.net/npm/resumablejs@1.1.0/resumable.js"></script>
    
    <style>
        .admin-content {
            transition: margin-left 0.3s ease;
        }
        
        @media (min-width: 1024px) {
            .admin-content {
                margin-left: 16rem;
            }
        }
        
        .card-hover {
            transition: all 0.3s ease;
        }
        
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .media-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.9);
            overflow: auto;
        }
        .media-modal-content {
            margin: auto;
            display: block;
            max-width: 90%;
            max-height: 90vh;
            margin-top: 5vh;
        }
        .media-modal-close {
            position: absolute;
            top: 15px;
            right: 35px;
            color: #f1f1f1;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
        }
        .clickable-media {
            cursor: pointer;
            transition: opacity 0.3s ease;
        }
        .clickable-media:hover {
            opacity: 0.9;
        }
        .media-gallery {
            position: relative;
            overflow: hidden;
        }
        .gallery-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0, 0, 0, 0.5);
            color: white;
            padding: 1rem;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .gallery-nav:hover {
            background: rgba(0, 0, 0, 0.8);
        }
        .gallery-prev {
            left: 0;
        }
        .gallery-next {
            right: 0;
        }
        .media-thumbnails {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.5rem;
            overflow-x: auto;
            padding: 0.5rem 0;
        }
        .media-thumbnail {
            width: 60px;
            height: 60px;
            object-fit: cover;
            cursor: pointer;
            border: 2px solid transparent;
            transition: border-color 0.3s;
        }
        .media-thumbnail.active {
            border-color: #0066cc;
        }
        .video-thumbnail {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #000;
            color: white;
        }
        .video-thumbnail::before {
            content: '\f144';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: 1.5rem;
        }
        .media-type-badge {
            position: absolute;
            top: 0.5rem;
            left: 0.5rem;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .video-url-section {
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-top: 1rem;
        }
        .video-url-item {
            display: flex;
            gap: 1rem;
            align-items: center;
            margin-bottom: 1rem;
            padding: 1rem;
            background: #f9fafb;
            border-radius: 0.5rem;
        }
        .remove-video-url {
            background: #ef4444;
            color: white;
            border: none;
            padding: 0.5rem;
            border-radius: 0.25rem;
            cursor: pointer;
        }
        .remove-video-url:hover {
            background: #dc2626;
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Include Admin Sidebar -->
    <?php include 'includes/adminsidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="admin-content p-4 sm:p-6 lg:p-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Plant Visit Management</h1>
                <p class="text-gray-600">Manage plant visit content, images, and videos</p>
            </div>
            <button onclick="openAddModal()" class="bg-primary hover:bg-secondary text-white px-4 py-2 rounded-lg flex items-center">
                <i class="fas fa-plus mr-2"></i>
                Add New Visit
            </button>
        </div>
        
        <!-- Plant Visits Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($plant_visits as $visit): ?>
            <div class="card-hover bg-white rounded-lg shadow overflow-hidden" data-visit-id="<?php echo $visit['id']; ?>">
                <div class="relative media-gallery">
                    <?php if (!empty($visit['media'])): ?>
                        <?php 
                        $first_media = $visit['media'][0];
                        if ($first_media['media_type'] === 'video'): 
                        ?>
                            <div class="w-full h-48 bg-black flex items-center justify-center relative">
                                <?php if ($first_media['video_type'] === 'uploaded'): ?>
                                    <video class="w-full h-full object-cover clickable-media" 
                                           onclick="openMediaModal('video', '<?php echo htmlspecialchars($first_media['video_url']); ?>', <?php echo htmlspecialchars(json_encode($visit['media'])); ?>)">
                                        <source src="<?php echo htmlspecialchars($first_media['video_url']); ?>" type="video/mp4">
                                    </video>
                                <?php else: ?>
                                    <div class="w-full h-full bg-gray-800 flex items-center justify-center clickable-media" 
                                         onclick="openMediaModal('video', '<?php echo htmlspecialchars($first_media['video_url']); ?>', <?php echo htmlspecialchars(json_encode($visit['media'])); ?>)">
                                        <i class="fas fa-play-circle text-4xl text-white"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="media-type-badge">Video</div>
                            </div>
                        <?php else: ?>
                            <img src="../images/plant_visit/<?php echo htmlspecialchars($first_media['filename']); ?>" 
                                 alt="<?php echo htmlspecialchars($visit['title']); ?>" 
                                 class="w-full h-48 object-cover clickable-media"
                                 onclick="openMediaModal('image', '../images/plant_visit/<?php echo htmlspecialchars($first_media['filename']); ?>', <?php echo htmlspecialchars(json_encode($visit['media'])); ?>)">
                            <div class="media-type-badge">Image</div>
                        <?php endif; ?>
                        
                        <?php if (count($visit['media']) > 1): ?>
                            <div class="gallery-nav gallery-prev" onclick="event.stopPropagation(); navigateGallery(this, -1)">
                                <i class="fas fa-chevron-left"></i>
                            </div>
                            <div class="gallery-nav gallery-next" onclick="event.stopPropagation(); navigateGallery(this, 1)">
                                <i class="fas fa-chevron-right"></i>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <div class="absolute top-2 right-2 flex space-x-2">
                        <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($visit)); ?>)" 
                                class="bg-primary text-white p-2 rounded-full hover:bg-secondary">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="confirmDelete(<?php echo $visit['id']; ?>)" 
                                class="bg-red-500 text-white p-2 rounded-full hover:bg-red-600">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                
                <div class="p-4">
                    <h3 class="text-lg font-semibold text-gray-800 mb-2"><?php echo htmlspecialchars($visit['title']); ?></h3>
                    <p class="text-gray-600 text-sm mb-3"><?php echo htmlspecialchars(substr($visit['description'], 0, 100)) . (strlen($visit['description']) > 100 ? '...' : ''); ?></p>
                    
                    <div class="flex justify-between items-center text-sm text-gray-500">
                        <span><?php echo date('M d, Y', strtotime($visit['created_at'])); ?></span>
                        <span><?php echo count($visit['media']); ?> media items</span>
                    </div>
                    
                    <?php if (!empty($visit['media'])): ?>
                        <div class="media-thumbnails">
                            <?php foreach (array_slice($visit['media'], 0, 5) as $index => $media): ?>
                                <div class="media-thumbnail <?php echo $index === 0 ? 'active' : ''; ?> <?php echo $media['media_type'] === 'video' ? 'video-thumbnail' : ''; ?>"
                                     onclick="showMedia(<?php echo $visit['id']; ?>, <?php echo $index; ?>)">
                                    <?php if ($media['media_type'] === 'image'): ?>
                                        <img src="../images/plant_visit/<?php echo htmlspecialchars($media['filename']); ?>" 
                                             alt="Thumbnail" class="w-full h-full object-cover">
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                            <?php if (count($visit['media']) > 5): ?>
                                <div class="media-thumbnail bg-gray-300 flex items-center justify-center text-gray-600 text-xs">
                                    +<?php echo count($visit['media']) - 5; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    <div id="visitModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-4xl mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-800" id="modalTitle">Add New Plant Visit</h2>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="visitForm" method="POST" action="admin_plant_visit.php" enctype="multipart/form-data" class="space-y-4">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="id" id="visitId">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                    <input type="text" name="title" id="visitTitle" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" id="visitDescription" required rows="4"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Media Files (Images & Videos)</label>
                    <input type="file" name="media_files[]" id="visitMedia" accept="image/*,video/*" multiple
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                    <p class="text-sm text-gray-500 mt-1">You can select multiple images and videos. Videos must be MP4, WebM, AVI, or MOV format (max 100MB)</p>
                    <progress id="uploadProgress" value="0" max="100" style="display:none;width:100%"></progress>
                </div>
                
                <!-- Video URLs Section -->
                <div class="video-url-section">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Video URLs (YouTube, Vimeo, etc.)</label>
                    <div id="videoUrlsContainer">
                        <div class="video-url-item">
                            <div class="flex-1">
                                <input type="url" name="video_urls[]" placeholder="Enter video URL (YouTube, Vimeo, etc.)" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary mb-2">
                                <input type="text" name="video_titles[]" placeholder="Video title (optional)" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary mb-2">
                                <textarea name="video_descriptions[]" placeholder="Video description (optional)" rows="2"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"></textarea>
                            </div>
                            <button type="button" class="remove-video-url" onclick="removeVideoUrl(this)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <button type="button" onclick="addVideoUrl()" class="mt-2 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                        <i class="fas fa-plus mr-2"></i>Add Another Video URL
                    </button>
                </div>
                
                <div id="currentMedia" class="hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Current Media</label>
                    <div class="grid grid-cols-4 gap-4" id="mediaGrid"></div>
                </div>
                
                <div id="resumable-drop" class="hidden border-2 border-dashed border-blue-400 rounded-lg p-6 text-center mb-4">
                    <p class="text-blue-700">Drop images and videos here to upload in chunks (recommended for large files)</p>
                    <div id="resumable-progress" class="hidden mt-2">
                        <progress id="resumableProgressBar" value="0" max="100" style="width:100%"></progress>
                        <span id="resumableProgressText"></span>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeModal()" 
                            class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-secondary">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Confirm Delete</h3>
            <p class="text-gray-600 mb-6">Are you sure you want to delete this plant visit? This action cannot be undone.</p>
            <div class="flex justify-end space-x-3">
                <button onclick="closeDeleteModal()" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                    Cancel
                </button>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="deleteId">
                    <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600">
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Media Modal -->
    <div id="mediaModal" class="media-modal">
        <span class="media-modal-close" onclick="closeMediaModal()">&times;</span>
        <div class="media-modal-content">
            <img id="modalImage" src="" alt="Modal Image" style="display: none;">
            <video id="modalVideo" controls style="display: none;">
                <source src="" type="video/mp4">
                Your browser does not support the video tag.
            </video>
            <iframe id="modalIframe" style="display: none; width: 100%; height: 500px; border: none;"></iframe>
        </div>
    </div>

    <script>
        let currentMediaIndex = 0;
        let currentMediaArray = [];

        function openAddModal() {
            document.getElementById('modalTitle').textContent = 'Add New Plant Visit';
            document.getElementById('formAction').value = 'add';
            document.getElementById('visitForm').reset();
            document.getElementById('currentMedia').classList.add('hidden');
            document.getElementById('visitModal').classList.remove('hidden');
            document.getElementById('visitModal').classList.add('flex');
        }

        function openEditModal(visit) {
            document.getElementById('modalTitle').textContent = 'Edit Plant Visit';
            document.getElementById('formAction').value = 'edit';
            document.getElementById('visitId').value = visit.id;
            document.getElementById('visitTitle').value = visit.title;
            document.getElementById('visitDescription').value = visit.description;
            
            // Show current media
            if (visit.media && visit.media.length > 0) {
                document.getElementById('currentMedia').classList.remove('hidden');
                const mediaGrid = document.getElementById('mediaGrid');
                mediaGrid.innerHTML = '';
                
                visit.media.forEach((media, index) => {
                    const mediaDiv = document.createElement('div');
                    mediaDiv.className = 'relative';
                    
                    if (media.media_type === 'video') {
                        mediaDiv.innerHTML = `
                            <div class="w-full h-24 bg-black rounded flex items-center justify-center">
                                <i class="fas fa-play-circle text-white text-xl"></i>
                            </div>
                            <div class="absolute top-1 left-1 bg-red-500 text-white text-xs px-1 rounded">Video</div>
                            <button type="button" onclick="deleteMedia(${media.id})" class="absolute top-1 right-1 bg-red-500 text-white text-xs px-1 rounded">×</button>
                        `;
                    } else {
                        mediaDiv.innerHTML = `
                            <img src="../images/plant_visit/${media.filename}" alt="Media" class="w-full h-24 object-cover rounded">
                            <div class="absolute top-1 left-1 bg-blue-500 text-white text-xs px-1 rounded">Image</div>
                            <button type="button" onclick="deleteMedia(${media.id})" class="absolute top-1 right-1 bg-red-500 text-white text-xs px-1 rounded">×</button>
                        `;
                    }
                    
                    mediaGrid.appendChild(mediaDiv);
                });
            } else {
                document.getElementById('currentMedia').classList.add('hidden');
            }
            
            document.getElementById('visitModal').classList.remove('hidden');
            document.getElementById('visitModal').classList.add('flex');
        }

        function closeModal() {
            document.getElementById('visitModal').classList.add('hidden');
            document.getElementById('visitModal').classList.remove('flex');
        }

        function confirmDelete(id) {
            document.getElementById('deleteId').value = id;
            document.getElementById('deleteModal').classList.remove('hidden');
            document.getElementById('deleteModal').classList.add('flex');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
            document.getElementById('deleteModal').classList.remove('flex');
        }

        function deleteMedia(mediaId) {
            if (confirm('Are you sure you want to delete this media item?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_media">
                    <input type="hidden" name="media_id" value="${mediaId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function openMediaModal(type, src, mediaArray) {
            currentMediaArray = mediaArray;
            currentMediaIndex = 0;
            
            const modal = document.getElementById('mediaModal');
            const modalImage = document.getElementById('modalImage');
            const modalVideo = document.getElementById('modalVideo');
            const modalIframe = document.getElementById('modalIframe');
            
            modalImage.style.display = 'none';
            modalVideo.style.display = 'none';
            modalIframe.style.display = 'none';
            
            if (type === 'image') {
                modalImage.src = src;
                modalImage.style.display = 'block';
            } else if (type === 'video') {
                if (src.includes('youtube.com') || src.includes('youtu.be')) {
                    const videoId = extractYouTubeId(src);
                    modalIframe.src = `https://www.youtube.com/embed/${videoId}`;
                    modalIframe.style.display = 'block';
                } else if (src.includes('vimeo.com')) {
                    const videoId = extractVimeoId(src);
                    modalIframe.src = `https://player.vimeo.com/video/${videoId}`;
                    modalIframe.style.display = 'block';
                } else {
                    modalVideo.src = src;
                    modalVideo.style.display = 'block';
                }
            }
            
            modal.style.display = 'block';
        }

        function closeMediaModal() {
            document.getElementById('mediaModal').style.display = 'none';
        }

        function extractYouTubeId(url) {
            const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
            const match = url.match(regExp);
            return (match && match[2].length === 11) ? match[2] : null;
        }

        function extractVimeoId(url) {
            const regExp = /vimeo\.com\/([0-9]+)/;
            const match = url.match(regExp);
            return match ? match[1] : null;
        }

        function navigateGallery(element, direction) {
            const card = element.closest('.card-hover');
            const media = JSON.parse(card.querySelector('.clickable-media').getAttribute('onclick').match(/\[(.*)\]/)[1]);
            const currentIndex = parseInt(card.querySelector('.media-thumbnail.active').getAttribute('onclick').match(/\d+/)[0]);
            const newIndex = (currentIndex + direction + media.length) % media.length;
            
            showMedia(parseInt(card.querySelector('.card-hover').getAttribute('data-visit-id')), newIndex);
        }

        function showMedia(visitId, index) {
            // Update active thumbnail
            const card = document.querySelector(`[data-visit-id="${visitId}"]`);
            card.querySelectorAll('.media-thumbnail').forEach((thumb, i) => {
                thumb.classList.toggle('active', i === index);
            });
            
            // Update main media display
            const media = JSON.parse(card.querySelector('.clickable-media').getAttribute('onclick').match(/\[(.*)\]/)[1]);
            const mediaItem = media[index];
            
            if (mediaItem.media_type === 'video') {
                // Handle video display
                const videoContainer = card.querySelector('.bg-black');
                if (mediaItem.video_type === 'uploaded') {
                    videoContainer.innerHTML = `<video class="w-full h-full object-cover clickable-media" onclick="openMediaModal('video', '${mediaItem.video_url}', ${JSON.stringify(media)})"><source src="${mediaItem.video_url}" type="video/mp4"></video>`;
                } else {
                    videoContainer.innerHTML = `<div class="w-full h-full bg-gray-800 flex items-center justify-center clickable-media" onclick="openMediaModal('video', '${mediaItem.video_url}', ${JSON.stringify(media)})"><i class="fas fa-play-circle text-4xl text-white"></i></div>`;
                }
            } else {
                // Handle image display
                const img = card.querySelector('.clickable-media');
                img.src = `../images/plant_visit/${mediaItem.filename}`;
            }
        }

        function addVideoUrl() {
            const container = document.getElementById('videoUrlsContainer');
            const newItem = document.createElement('div');
            newItem.className = 'video-url-item';
            newItem.innerHTML = `
                <div class="flex-1">
                    <input type="url" name="video_urls[]" placeholder="Enter video URL (YouTube, Vimeo, etc.)" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary mb-2">
                    <input type="text" name="video_titles[]" placeholder="Video title (optional)" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary mb-2">
                    <textarea name="video_descriptions[]" placeholder="Video description (optional)" rows="2"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"></textarea>
                </div>
                <button type="button" class="remove-video-url" onclick="removeVideoUrl(this)">
                    <i class="fas fa-trash"></i>
                </button>
            `;
            container.appendChild(newItem);
        }

        function removeVideoUrl(button) {
            const container = document.getElementById('videoUrlsContainer');
            if (container.children.length > 1) {
                button.closest('.video-url-item').remove();
            }
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            const visitModal = document.getElementById('visitModal');
            const deleteModal = document.getElementById('deleteModal');
            const mediaModal = document.getElementById('mediaModal');
            
            if (event.target === visitModal) {
                closeModal();
            }
            if (event.target === deleteModal) {
                closeDeleteModal();
            }
            if (event.target === mediaModal) {
                closeMediaModal();
            }
        }



        document.getElementById('visitForm').addEventListener('submit', function(e) {
            // Simple test - just log and submit normally
            console.log('Form submitted');
            console.log('Form action:', this.action);
            console.log('Form method:', this.method);
            
            // Let the form submit normally (no AJAX)
            return true;
        });

        // Resumable.js chunked upload for both images and videos
        const resumable = new Resumable({
            target: 'upload_chunked_video.php',
            query: {},
            fileType: ['mp4','mov','avi','webm','jpg','jpeg','png','gif','bmp'],
            chunkSize: 2 * 1024 * 1024, // 2MB
            simultaneousUploads: 1,
            testChunks: false,
            throttleProgressCallbacks: 1
        });

        const dropArea = document.getElementById('resumable-drop');
        const progressBox = document.getElementById('resumable-progress');
        const progressBar = document.getElementById('resumableProgressBar');
        const progressText = document.getElementById('resumableProgressText');

        resumable.assignDrop(dropArea);
        resumable.assignBrowse(document.getElementById('visitMedia'));

        resumable.on('fileAdded', function(file) {
            dropArea.classList.remove('hidden');
            progressBox.classList.remove('hidden');
            resumable.upload();
        });

        resumable.on('fileProgress', function(file) {
            const percent = Math.floor(file.progress() * 100);
            progressBar.value = percent;
            progressText.textContent = percent + '%';
        });

        resumable.on('fileSuccess', function(file, response) {
            progressText.textContent = 'Upload complete!';
            try {
                var res = JSON.parse(response);
                if (res.success && res.file) {
                    // Add a hidden input to the form with the uploaded file path
                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'chunked_files[]';
                    input.value = res.file;
                    document.getElementById('visitForm').appendChild(input);
                    // Optionally clear the file input to prevent double upload
                    document.getElementById('visitMedia').value = '';
                }
            } catch (e) {}
        });

        resumable.on('fileError', function(file, message) {
            progressText.textContent = 'Upload failed: ' + message;
        });
    </script>
</body>
</html> 