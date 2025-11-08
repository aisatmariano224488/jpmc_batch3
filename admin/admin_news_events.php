<?php
@set_time_limit(0);
@ini_set('memory_limit', '-1');
@ini_set('upload_max_filesize', '0');
@ini_set('post_max_size', '0');
@ini_set('max_input_time', '0');

// session_start();
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

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Include multimedia helper
require_once 'includes/news_events_multimedia.php';
require_once 'includes/content_sections_multimedia.php';
$multimedia = new NewsEventsMultimedia($conn);
$contentSections = new ContentSectionsMultimedia($conn);

// Initialize message variables
$success_message = '';
$error_message = '';

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $title = $_POST['title'];
                $type = $_POST['type'];
                $date = $_POST['date'];
                $featured = isset($_POST['featured']) ? 1 : 0;
                $show_in_banner = isset($_POST['show_in_banner']) ? 1 : 0;
                if ($show_in_banner == 1) {
                    $conn->query("UPDATE news_events SET show_in_banner = 0");
                }
                // For events, adjust the date based on status
                if ($type === 'event' && isset($_POST['event_status'])) {
                    $event_status = $_POST['event_status'];
                    $event_date = new DateTime($date);
                    if ($event_status === 'past' && $event_date > new DateTime()) {
                        $event_date->modify('-1 day');
                        $date = $event_date->format('Y-m-d');
                    } else if ($event_status === 'upcoming' && $event_date < new DateTime()) {
                        $event_date->modify('+1 day');
                        $date = $event_date->format('Y-m-d');
                    }
                }
                // Insert the news/event first
                $sql = "INSERT INTO news_events (title, type, date, featured, show_in_banner) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssii", $title, $type, $date, $featured, $show_in_banner);
                if ($stmt->execute()) {
                    $newsEventId = $stmt->insert_id;
    // Handle main images for the news/event
    if (isset($_FILES['main_images']) && !empty($_FILES['main_images']['name'][0])) {
        $mainImageFiles = [
            'name' => $_FILES['main_images']['name'],
            'type' => $_FILES['main_images']['type'],
            'tmp_name' => $_FILES['main_images']['tmp_name'],
            'error' => $_FILES['main_images']['error'],
            'size' => $_FILES['main_images']['size'],
        ];
        $mainAltTexts = isset($_POST['main_image_alt_texts']) ? $_POST['main_image_alt_texts'] : [];
        $multimedia->uploadImages($newsEventId, $mainImageFiles, $mainAltTexts);
    }
                    // Handle content sections
                    if (isset($_POST['sections']) && is_array($_POST['sections'])) {
                        foreach ($_POST['sections'] as $i => $section) {
                            $sectionContent = $section['content'] ?? '';
                            $sectionOrder = $i;
                            
                            // Create new section
                            $sectionId = $contentSections->createSection($newsEventId, '', $sectionContent, $sectionOrder);
                            
                            // Handle section images
                            if (isset($_FILES['section_images']['name'][$i]) && !empty($_FILES['section_images']['name'][$i][0])) {
                                $sectionImageFiles = [
                                    'name' => $_FILES['section_images']['name'][$i],
                                    'type' => $_FILES['section_images']['type'][$i],
                                    'tmp_name' => $_FILES['section_images']['tmp_name'][$i],
                                    'error' => $_FILES['section_images']['error'][$i],
                                    'size' => $_FILES['section_images']['size'][$i],
                                ];
                                $altTexts = isset($section['image_alt_texts']) ? $section['image_alt_texts'] : [];
                                $contentSections->uploadSectionImages($sectionId, $sectionImageFiles, $altTexts);
                            }
                            
                            // Handle section local videos
                            if (isset($_FILES['section_local_videos']['name'][$i]) && !empty($_FILES['section_local_videos']['name'][$i][0])) {
                                $sectionVideoFiles = [
                                    'name' => $_FILES['section_local_videos']['name'][$i],
                                    'type' => $_FILES['section_local_videos']['type'][$i],
                                    'tmp_name' => $_FILES['section_local_videos']['tmp_name'][$i],
                                    'error' => $_FILES['section_local_videos']['error'][$i],
                                    'size' => $_FILES['section_local_videos']['size'][$i],
                                ];
                                $videoTitles = isset($section['local_video_titles']) ? $section['local_video_titles'] : [];
                                $videoDescriptions = isset($section['local_video_descriptions']) ? $section['local_video_descriptions'] : [];
                                $contentSections->uploadSectionLocalVideos($sectionId, $sectionVideoFiles, $videoTitles, $videoDescriptions);
                            }
                            // Handle section URL videos
                            if (isset($section['url_videos']) && is_array($section['url_videos'])) {
                                $urlVideos = [];
                                foreach ($section['url_videos'] as $index => $url) {
                                    if (!empty($url)) {
                                        $urlVideos[] = [
                                            'type' => 'url',
                                            'path' => $url,
                                            'title' => $section['url_video_titles'][$index] ?? '',
                                            'description' => $section['url_video_descriptions'][$index] ?? ''
                                        ];
                                    }
                                }
                                if (!empty($urlVideos)) {
                                    $contentSections->addSectionUrlVideos($sectionId, $urlVideos);
                                }
                            }
                        }
                        $contentSections->updateContentSectionsStatus($newsEventId);
                    }
                    $_SESSION['success_message'] = "Successfully added new " . $type;
                } else {
                    $_SESSION['error_message'] = "Error adding " . $type;
                }
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
                break;
                
            case 'edit':
                $id = $_POST['id'];
                $title = $_POST['title'];
                $type = $_POST['type'];
                $date = $_POST['date'];
                $featured = isset($_POST['featured']) ? 1 : 0;
                $show_in_banner = isset($_POST['show_in_banner']) ? 1 : 0;
                if ($show_in_banner == 1) {
                    $conn->query("UPDATE news_events SET show_in_banner = 0 WHERE id != " . intval($id));
                }
                $sql = "UPDATE news_events SET title = ?, type = ?, date = ?, featured = ?, show_in_banner = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssiii", $title, $type, $date, $featured, $show_in_banner, $id);
                if ($stmt->execute()) {
                    // Handle content sections (update, add, delete)
                    if (isset($_POST['sections']) && is_array($_POST['sections'])) {
                        $existingSectionIds = [];
                        foreach ($_POST['sections'] as $i => $section) {
                            $sectionId = isset($section['id']) ? intval($section['id']) : null;
                            $sectionContent = $section['content'] ?? '';
                            $sectionOrder = $i;
                            
                            // Check if new media is being uploaded
                            $hasNewImages = isset($_FILES['section_images']['name'][$i]) && !empty($_FILES['section_images']['name'][$i][0]);
                            $hasNewLocalVideos = isset($_FILES['section_local_videos']['name'][$i]) && !empty($_FILES['section_local_videos']['name'][$i][0]);
                            
                            if ($sectionId) {
                                // Update existing section, preserving media if no new media is uploaded
                                $contentSections->updateSectionWithMedia(
                                    $sectionId, 
                                    '', 
                                    $sectionContent, 
                                    $sectionOrder,
                                    !$hasNewImages,  // preserve images if no new images
                                    !$hasNewLocalVideos  // preserve local videos if no new videos
                                );
                            } else {
                                $sectionId = $contentSections->createSection($id, '', $sectionContent, $sectionOrder);
                            }
                            $existingSectionIds[] = $sectionId;
                            
                            // Handle section images
                            if ($hasNewImages) {
                                $sectionImageFiles = [
                                    'name' => $_FILES['section_images']['name'][$i],
                                    'type' => $_FILES['section_images']['type'][$i],
                                    'tmp_name' => $_FILES['section_images']['tmp_name'][$i],
                                    'error' => $_FILES['section_images']['error'][$i],
                                    'size' => $_FILES['section_images']['size'][$i],
                                ];
                                $altTexts = isset($section['image_alt_texts']) ? $section['image_alt_texts'] : [];
                                $contentSections->uploadSectionImages($sectionId, $sectionImageFiles, $altTexts);
                            }
                            
                            // Handle section local videos
                            if ($hasNewLocalVideos) {
                                $sectionVideoFiles = [
                                    'name' => $_FILES['section_local_videos']['name'][$i],
                                    'type' => $_FILES['section_local_videos']['type'][$i],
                                    'tmp_name' => $_FILES['section_local_videos']['tmp_name'][$i],
                                    'error' => $_FILES['section_local_videos']['error'][$i],
                                    'size' => $_FILES['section_local_videos']['size'][$i],
                                ];
                                $videoTitles = isset($section['local_video_titles']) ? $section['local_video_titles'] : [];
                                $videoDescriptions = isset($section['local_video_descriptions']) ? $section['local_video_descriptions'] : [];
                                $contentSections->uploadSectionLocalVideos($sectionId, $sectionVideoFiles, $videoTitles, $videoDescriptions);
                            }
                            // Handle section URL videos
                            if (isset($section['url_videos']) && is_array($section['url_videos'])) {
                                // Clear existing URL videos first
                                $contentSections->clearSectionUrlVideos($sectionId);
                                
                                $urlVideos = [];
                                foreach ($section['url_videos'] as $index => $url) {
                                    if (!empty($url)) {
                                        $urlVideos[] = [
                                            'type' => 'url',
                                            'path' => $url,
                                            'title' => $section['url_video_titles'][$index] ?? '',
                                            'description' => $section['url_video_descriptions'][$index] ?? ''
                                        ];
                                    }
                                }
                                if (!empty($urlVideos)) {
                                    $contentSections->addSectionUrlVideos($sectionId, $urlVideos);
                                }
                            }
                        }
                        // Delete removed sections
                        $allSections = $contentSections->getSections($id);
                        foreach ($allSections as $sec) {
                            if (!in_array($sec['id'], $existingSectionIds)) {
                                $contentSections->deleteSection($sec['id']);
                            }
                        }
                        $contentSections->updateContentSectionsStatus($id);
                    }
                    $_SESSION['success_message'] = "Successfully updated " . $type;
                } else {
                    $_SESSION['error_message'] = "Error updating " . $type;
                }
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
                break;
                
            case 'delete':
                $id = $_POST['id'];
                
                // Delete all associated images and videos
                $images = $multimedia->getImages($id);
                foreach ($images as $image) {
                    $multimedia->deleteImage($image['id']);
                }
                
                $videos = $multimedia->getVideos($id);
                foreach ($videos as $video) {
                    $multimedia->deleteVideo($video['id']);
                }
                
                // Delete from database
                $sql = "DELETE FROM news_events WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $id);
                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "Successfully deleted item";
                } else {
                    $_SESSION['error_message'] = "Error deleting item";
                }
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
                break;
                
            case 'delete_image':
                $imageId = $_POST['image_id'];
                if ($multimedia->deleteImage($imageId)) {
                    $_SESSION['success_message'] = "Image deleted successfully";
                } else {
                    $_SESSION['error_message'] = "Error deleting image";
                }
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
                break;
                
            case 'delete_video':
                $videoId = $_POST['video_id'];
                if ($multimedia->deleteVideo($videoId)) {
                    $_SESSION['success_message'] = "Video deleted successfully";
                } else {
                    $_SESSION['error_message'] = "Error deleting video";
                }
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
                break;
        }
    }
}

// Get messages from session and clear them
$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
unset($_SESSION['success_message'], $_SESSION['error_message']);

// Fetch existing news and events
$sql = "SELECT * FROM news_events ORDER BY featured DESC, date DESC";
$result = $conn->query($sql);

// Get current tab from session or default to news
$current_tab = isset($_SESSION['current_tab']) ? $_SESSION['current_tab'] : 'news';

// Get current date for event comparison
$current_date = date('Y-m-d');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News & Events Management | Admin Panel</title>
    
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
    <style>
      .image-preview { max-width: 120px; max-height: 80px; border-radius: 8px; }
      .featured-badge { background: #2563eb; color: #fff; font-size: 0.8rem; padding: 2px 8px; border-radius: 6px; margin-left: 8px; }
      .media-item { border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px; margin-bottom: 12px; }
      .media-preview { max-width: 100px; max-height: 60px; object-fit: cover; border-radius: 4px; }
      .video-preview { width: 100px; height: 60px; background: #f3f4f6; border-radius: 4px; display: flex; align-items: center; justify-content: center; }
    </style>
</head>
<body class="bg-gray-100">
    <?php include 'includes/adminsidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="lg:ml-64 p-8">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">News & Events Management</h1>
                <button class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-secondary transition-colors" onclick="openAddModal()">
                    <i class="fas fa-plus mr-2"></i>Add New
                </button>
            </div>

            <!-- Alert Messages -->
            <?php if ($success_message): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                <p><?php echo $success_message; ?></p>
            </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                <p><?php echo $error_message; ?></p>
            </div>
            <?php endif; ?>
            
            <!-- Tabs -->
            <div class="border-b border-gray-200 mb-6">
                <ul class="flex -mb-px" id="myTab" role="tablist">
                    <li class="mr-2">
                        <button class="tab-button inline-block p-4 border-b-2 <?php echo $current_tab == 'news' ? 'border-primary text-primary' : 'border-transparent hover:text-gray-600 hover:border-gray-300'; ?>" 
                                id="news-tab" 
                                data-bs-toggle="tab" 
                                data-bs-target="#news" 
                                type="button" 
                                role="tab" 
                                aria-controls="news" 
                                aria-selected="<?php echo $current_tab == 'news' ? 'true' : 'false'; ?>"
                                onclick="switchTab('news')">
                            News
                        </button>
                    </li>
                    <li>
                        <button class="tab-button inline-block p-4 border-b-2 <?php echo $current_tab == 'events' ? 'border-primary text-primary' : 'border-transparent hover:text-gray-600 hover:border-gray-300'; ?>" 
                                id="events-tab" 
                                data-bs-toggle="tab" 
                                data-bs-target="#events" 
                                type="button" 
                                role="tab" 
                                aria-controls="events" 
                                aria-selected="<?php echo $current_tab == 'events' ? 'true' : 'false'; ?>"
                                onclick="switchTab('events')">
                            Events
                        </button>
                    </li>
                </ul>
            </div>
            
            <!-- Tab Content -->
            <div class="tab-content" id="myTabContent">
                <!-- News Tab -->
                <div class="tab-pane fade <?php echo $current_tab == 'news' ? 'show active' : ''; ?>" 
                     id="news" 
                     role="tabpanel" 
                     aria-labelledby="news-tab">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php
                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                if ($row['type'] == 'news') {
                                    // Get featured image
                                    $featuredImage = $multimedia->getFeaturedImage($row['id']);
                                    $imagePath = $featuredImage ? $featuredImage['image_path'] : '';
                        ?>
                        <div class="bg-white rounded-lg shadow-md overflow-hidden">
                            <div class="relative">
                                <?php if (!empty($imagePath)): ?>
                                    <img src="../<?php echo $imagePath; ?>" alt="<?php echo $row['title']; ?>" class="w-full h-48 object-cover">
                                <?php else: ?>
                                    <div class="w-full h-48 bg-gray-100 flex items-center justify-center">
                                        <i class="fas fa-newspaper text-4xl text-gray-400"></i>
                                    </div>
                                <?php endif; ?>
                                <?php if ($row['featured']): ?><span class="featured-badge absolute top-2 right-2">Featured</span><?php endif; ?>
                                <?php if ($row['has_multimedia']): ?><span class="featured-badge absolute top-2 left-2" style="background: #059669;">Media</span><?php endif; ?>
                            </div>
                            <div class="p-4">
                                <h3 class="text-lg font-semibold mb-2"><?php echo $row['title']; ?></h3>
                                <p class="text-gray-600 text-sm mb-2"><?php echo date('F j, Y', strtotime($row['date'])); ?></p>
                                <div class="flex justify-between items-center">
                                    <div class="flex space-x-2">
                                        <button class="text-blue-600 hover:text-blue-800" onclick="editItem(<?php echo $row['id']; ?>)"><i class="fas fa-edit"></i></button>
                                        <button class="text-red-600 hover:text-red-800" onclick="deleteItem(<?php echo $row['id']; ?>)"><i class="fas fa-trash"></i></button>
                                    </div>
                                    <a href="../news_detail.php?id=<?php echo $row['id']; ?>" target="_blank" class="text-gray-500 hover:text-blue-600" title="Preview"><i class="fas fa-eye"></i></a>
                                </div>
                            </div>
                        </div>
                        <?php
                                }
                            }
                        }
                        ?>
                    </div>
                </div>
                
                <!-- Events Tab -->
                <div class="tab-pane fade <?php echo $current_tab == 'events' ? 'show active' : ''; ?>" 
                     id="events" 
                     role="tabpanel" 
                     aria-labelledby="events-tab">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php
                        $result->data_seek(0);
                        $hasEvents = false;
                        while($row = $result->fetch_assoc()) {
                            if ($row['type'] == 'event') {
                                $hasEvents = true;
                                // Get featured image
                                $featuredImage = $multimedia->getFeaturedImage($row['id']);
                                $imagePath = $featuredImage ? $featuredImage['image_path'] : '';
                        ?>
                        <div class="bg-white rounded-lg shadow-md overflow-hidden">
                            <div class="relative">
                                <?php if (!empty($imagePath)): ?>
                                    <img src="../<?php echo $imagePath; ?>" alt="<?php echo $row['title']; ?>" class="w-full h-48 object-cover">
                                <?php else: ?>
                                    <div class="w-full h-48 bg-gray-100 flex items-center justify-center">
                                        <i class="fas fa-calendar-alt text-4xl text-gray-400"></i>
                                    </div>
                                <?php endif; ?>
                                <?php if ($row['featured']): ?><span class="featured-badge absolute top-2 right-2">Featured</span><?php endif; ?>
                                <?php if ($row['has_multimedia']): ?><span class="featured-badge absolute top-2 left-2" style="background: #059669;">Media</span><?php endif; ?>
                            </div>
                            <div class="p-4">
                                <h3 class="text-lg font-semibold mb-2"><?php echo $row['title']; ?></h3>
                                <p class="text-gray-600 text-sm mb-4"><?php echo date('F j, Y', strtotime($row['date'])); ?></p>
                                <div class="flex justify-end space-x-2">
                                    <button class="text-blue-600 hover:text-blue-800" onclick="editItem(<?php echo $row['id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="text-red-600 hover:text-red-800" onclick="deleteItem(<?php echo $row['id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php
                            }
                        }
                        if (!$hasEvents) {
                            echo '<div class="col-span-3 text-center text-gray-500 py-8">No events found.</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add/Edit Modal -->
    <div class="modal fade" id="newsEventModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add/Edit News/Event</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="newsEventForm" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Type</label>
                                    <select name="type" class="form-select" required onchange="toggleEventStatus()">
                                        <option value="news">News</option>
                                        <option value="event">Event</option>
                                    </select>
                                </div>
                                <div id="eventStatusDiv" class="mb-3" style="display: none;">
                                    <label class="form-label">Event Status</label>
                                    <select name="event_status" class="form-select">
                                        <option value="upcoming">Upcoming Event</option>
                                        <option value="past">Past Event</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Title</label>
                                    <input type="text" name="title" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Date</label>
                                    <input type="date" name="date" class="form-control" required>
                                </div>
                                <div class="mb-3 form-check">
                                    <input type="checkbox" name="featured" class="form-check-input" id="featuredCheck">
                                    <label class="form-check-label" for="featuredCheck">Mark as Featured</label>
                                </div>
                                <div class="mb-3 form-check">
                                    <input type="checkbox" name="show_in_banner" class="form-check-input" id="showInBannerCheck">
                                    <label class="form-check-label" for="showInBannerCheck">Show in Banner</label>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Main Images</label>
                                    <input type="file" name="main_images[]" class="form-control" accept="image/*" multiple>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div id="contentSectionsContainer">
                            <!-- Content sections will be dynamically added here -->
                        </div>
                        <button type="button" class="btn btn-outline-primary mt-3" onclick="addContentSection()">
                            <i class="fas fa-plus"></i> Add Content Section
                        </button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" form="newsEventForm" class="btn btn-primary">Save</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        let sectionCounter = 0;
        function addContentSection(sectionData = null) {
            const container = document.getElementById('contentSectionsContainer');
            const sectionIndex = sectionCounter++;
            const sectionDiv = document.createElement('div');
            sectionDiv.className = 'content-section border rounded p-3 mb-4 bg-light';
            sectionDiv.innerHTML = `
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6>Content Section <span class="section-number"></span></h6>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeContentSection(this)"><i class="fas fa-trash"></i> Remove</button>
                </div>
                ${sectionData && sectionData.id ? `<input type="hidden" name="sections[${sectionIndex}][id]" value="${sectionData.id}">` : ''}
                <div class="mb-3">
                    <label class="form-label">Section Content</label>
                    <textarea name="sections[${sectionIndex}][content]" class="form-control" rows="4" placeholder="Enter content for this section...">${sectionData ? (sectionData.section_content || '') : ''}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Section Images</label>
                    <input type="file" name="section_images[${sectionIndex}][]" class="form-control" accept="image/*" multiple>
                </div>
                <div class="mb-3">
                    <label class="form-label">Section Local Videos</label>
                    <input type="file" name="section_local_videos[${sectionIndex}][]" class="form-control" accept="video/*" multiple>
                </div>
                <div class="mb-3">
                    <label class="form-label">Section Video URLs</label>
                    <div class="section-url-videos-container"></div>
                    <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="addSectionUrlVideoField(this, ${sectionIndex})"><i class="fas fa-plus"></i> Add URL Video</button>
                </div>
                <hr>
            `;
            container.appendChild(sectionDiv);
            
            // If sectionData has existing URL videos, populate them
            if (sectionData && sectionData.videos) {
                const urlVideos = sectionData.videos.filter(video => video.video_type === 'url');
                const container = sectionDiv.querySelector('.section-url-videos-container');
                urlVideos.forEach(video => {
                    const div = document.createElement('div');
                    div.className = 'media-item mb-2';
                    div.innerHTML = `
                        <input type="url" name="sections[${sectionIndex}][url_videos][]" class="form-control mb-1" placeholder="Video URL" value="${video.video_path || ''}">
                        <input type="text" name="sections[${sectionIndex}][url_video_titles][]" class="form-control mb-1" placeholder="Video title (optional)" value="${video.video_title || ''}">
                        <textarea name="sections[${sectionIndex}][url_video_descriptions][]" class="form-control mb-1" rows="2" placeholder="Video description (optional)">${video.video_description || ''}</textarea>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.parentElement.remove()"><i class="fas fa-trash"></i> Remove</button>
                    `;
                    container.appendChild(div);
                });
            }
            
            // If sectionData has existing images, display them
            if (sectionData && sectionData.images && sectionData.images.length > 0) {
                const imagesContainer = document.createElement('div');
                imagesContainer.className = 'mb-3';
                imagesContainer.innerHTML = '<label class="form-label">Existing Images:</label><div class="d-flex flex-wrap gap-2">';
                
                sectionData.images.forEach(image => {
                    imagesContainer.innerHTML += `
                        <div class="position-relative">
                            <img src="../${image.image_path}" alt="${image.alt_text || 'Section image'}" class="img-thumbnail" style="width: 100px; height: 60px; object-fit: cover;">
                            <small class="d-block text-muted">${image.alt_text || 'No alt text'}</small>
                        </div>
                    `;
                });
                
                imagesContainer.innerHTML += '</div>';
                sectionDiv.querySelector('.mb-3:nth-child(3)').after(imagesContainer);
            }
            
            // If sectionData has existing local videos, display them
            if (sectionData && sectionData.videos) {
                const localVideos = sectionData.videos.filter(video => video.video_type === 'local');
                if (localVideos.length > 0) {
                    const videosContainer = document.createElement('div');
                    videosContainer.className = 'mb-3';
                    videosContainer.innerHTML = '<label class="form-label">Existing Local Videos:</label><div class="d-flex flex-wrap gap-2">';
                    
                    localVideos.forEach(video => {
                        videosContainer.innerHTML += `
                            <div class="position-relative">
                                <video width="100" height="60" controls class="img-thumbnail">
                                    <source src="../${video.video_path}" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                                <small class="d-block text-muted">${video.video_title || 'Untitled'}</small>
                            </div>
                        `;
                    });
                    
                    videosContainer.innerHTML += '</div>';
                    sectionDiv.querySelector('.mb-3:nth-child(4)').after(videosContainer);
                }
            }
            
            updateSectionNumbers();
        }
        function removeContentSection(btn) {
            btn.closest('.content-section').remove();
            updateSectionNumbers();
        }
        function updateSectionNumbers() {
            document.querySelectorAll('.content-section').forEach((section, idx) => {
                section.querySelector('.section-number').textContent = idx + 1;
            });
        }
        function addSectionUrlVideoField(btn, sectionIndex) {
            const container = btn.parentElement.querySelector('.section-url-videos-container');
            const count = container.children.length;
            const div = document.createElement('div');
            div.className = 'media-item mb-2';
            div.innerHTML = `
                <input type="url" name="sections[${sectionIndex}][url_videos][]" class="form-control mb-1" placeholder="Video URL">
                <input type="text" name="sections[${sectionIndex}][url_video_titles][]" class="form-control mb-1" placeholder="Video title (optional)">
                <textarea name="sections[${sectionIndex}][url_video_descriptions][]" class="form-control mb-1" rows="2" placeholder="Video description (optional)"></textarea>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.parentElement.remove()"><i class="fas fa-trash"></i> Remove</button>
            `;
            container.appendChild(div);
        }
        function switchTab(tab) {
            // Update session
            fetch('update_tab.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'tab=' + tab
            });

            // Update UI
            const tabButtons = document.querySelectorAll('.tab-button');
            tabButtons.forEach(button => {
                if (button.id === tab + '-tab') {
                    button.classList.add('border-primary', 'text-primary');
                    button.classList.remove('border-transparent');
                } else {
                    button.classList.remove('border-primary', 'text-primary');
                    button.classList.add('border-transparent');
                }
            });

            // Show/hide content
            const tabPanes = document.querySelectorAll('.tab-pane');
            tabPanes.forEach(pane => {
                if (pane.id === tab) {
                    pane.classList.add('show', 'active');
                } else {
                    pane.classList.remove('show', 'active');
                }
            });
        }

        function openAddModal() {
            document.getElementById('newsEventForm').reset();
            document.getElementById('edit_id').value = '';
            document.getElementById('contentSectionsContainer').innerHTML = '';
            document.querySelector('input[name="action"]').value = 'add';
            
            addContentSection();
            
            new bootstrap.Modal(document.getElementById('newsEventModal')).show();
        }
        
        function editItem(id) {
            // Fetch item details
            fetch('get_news_event.php?id=' + id)
                .then(response => response.json())
                .then(data => {
                    // Populate form
                    document.getElementById('edit_id').value = data.id;
                    document.querySelector('select[name="type"]').value = data.type;
                    document.querySelector('input[name="title"]').value = data.title;
                    document.querySelector('input[name="date"]').value = data.date;
                    document.querySelector('input[name="featured"]').checked = data.featured == 1;
                    document.querySelector('input[name="show_in_banner"]').checked = data.show_in_banner == 1;
                    
                    // Update form action
                    document.querySelector('input[name="action"]').value = 'edit';
                    
                    // Show event status if it's an event
                    toggleEventStatus();
                    
                    // Clear content sections
                    document.getElementById('contentSectionsContainer').innerHTML = '';
                    
                    // Load existing content sections
                    loadExistingContentSections(id);
                    
                    // Show modal
                    new bootstrap.Modal(document.getElementById('newsEventModal')).show();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error fetching item details');
                });
        }
        
        function loadExistingContentSections(newsEventId) {
            // Load existing content sections
            fetch('get_news_event.php?id=' + newsEventId + '&sections=true')
                .then(response => response.json())
                .then(sections => {
                    const container = document.getElementById('contentSectionsContainer');
                    container.innerHTML = '';
                    
                    if (sections.length > 0) {
                        sections.forEach(section => {
                            addContentSection(section);
                        });
                    } else {
                        addContentSection();
                    }
                })
                .catch(error => console.error('Error loading content sections:', error));
        }

        function deleteItem(id) {
            if (confirm('Are you sure you want to delete this item?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function toggleEventStatus() {
            const typeSelect = document.querySelector('select[name="type"]');
            const eventStatusDiv = document.getElementById('eventStatusDiv');
            const dateInput = document.querySelector('input[name="date"]');
            
            if (typeSelect.value === 'event') {
                eventStatusDiv.style.display = 'block';
                // Set default date based on event status
                const eventStatus = document.querySelector('select[name="event_status"]').value;
                const today = new Date();
                if (eventStatus === 'past') {
                    dateInput.value = new Date(today.setDate(today.getDate() - 1)).toISOString().split('T')[0];
                } else {
                    dateInput.value = new Date(today.setDate(today.getDate() + 1)).toISOString().split('T')[0];
                }
            } else {
                eventStatusDiv.style.display = 'none';
                // Reset date to today for news
                dateInput.value = new Date().toISOString().split('T')[0];
                // Reset event status to upcoming
                document.querySelector('select[name="event_status"]').value = 'upcoming';
            }
        }

        // Auto-hide success/error messages after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('[role="alert"]');
            alerts.forEach(function(alert) {
                alert.style.display = 'none';
            });
        }, 5000);
    </script>
</body>
</html>
