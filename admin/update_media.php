<?php
// session_start();
// if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
//     http_response_code(403);
//     exit(json_encode(['success' => false, 'message' => 'Unauthorized']));
// }

// Database credentials
$servername = "sql102.infinityfree.com";
$username = "if0_39268761"; // Default XAMPP MySQL username
$password = "KlHiP075oQ7fV4T"; // Default XAMPP MySQL password (empty)
$dbname = "if0_39268761_jpmc";
// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    http_response_code(500);
    exit(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit(json_encode(['success' => false, 'message' => 'Invalid request method']));
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'update_image_alt':
        $imageId = intval($_POST['image_id'] ?? 0);
        $altText = $_POST['alt_text'] ?? '';
        
        if ($imageId > 0) {
            $sql = "UPDATE news_events_images SET alt_text = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $altText, $imageId);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Database error']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid image ID']);
        }
        break;
        
    case 'update_video_title':
        $videoId = intval($_POST['video_id'] ?? 0);
        $title = $_POST['title'] ?? '';
        
        if ($videoId > 0) {
            $sql = "UPDATE news_events_videos SET video_title = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $title, $videoId);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Database error']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid video ID']);
        }
        break;
        
    case 'update_video_description':
        $videoId = intval($_POST['video_id'] ?? 0);
        $description = $_POST['description'] ?? '';
        
        if ($videoId > 0) {
            $sql = "UPDATE news_events_videos SET video_description = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $description, $videoId);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Database error']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid video ID']);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

$conn->close();
?> 