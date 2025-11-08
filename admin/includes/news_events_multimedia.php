<?php
/**
 * News Events Multimedia Helper Functions
 * Handles multiple images and videos for news and events
 */

class NewsEventsMultimedia {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * Upload multiple images for a news/event
     */
    public function uploadImages($newsEventId, $imageFiles, $altTexts = []) {
        $uploadedImages = [];
        $target_dir = "../uploads/news_events/";
        
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        foreach ($imageFiles['tmp_name'] as $key => $tmp_name) {
            if ($imageFiles['error'][$key] == 0) {
                $file_extension = strtolower(pathinfo($imageFiles["name"][$key], PATHINFO_EXTENSION));
                $new_filename = time() . '_' . uniqid() . '_' . $key . '.' . $file_extension;
                $target_file = $target_dir . $new_filename;
                
                // Check if image file is a actual image
                $check = getimagesize($tmp_name);
                if ($check !== false) {
                    // Allow certain file formats
                    if (in_array($file_extension, ["jpg", "png", "jpeg", "gif"])) {
                        if (move_uploaded_file($tmp_name, $target_file)) {
                            $image_path = 'uploads/news_events/' . $new_filename;
                            $alt_text = isset($altTexts[$key]) ? $altTexts[$key] : '';
                            // Set is_featured = 1 for the first image, 0 for others
                            $is_featured = ($key == 0) ? 1 : 0;
                            
                            // Insert into database
                            $sql = "INSERT INTO news_events_images (news_event_id, image_path, alt_text, display_order, is_featured) VALUES (?, ?, ?, ?, ?)";
                            $stmt = $this->conn->prepare($sql);
                            $stmt->bind_param("issii", $newsEventId, $image_path, $alt_text, $key, $is_featured);
                            if ($stmt->execute()) {
                                $uploadedImages[] = [
                                    'id' => $stmt->insert_id,
                                    'path' => $image_path,
                                    'alt_text' => $alt_text
                                ];
                            }
                        }
                    }
                }
            }
        }
        
        return $uploadedImages;
    }
    
    /**
     * Add videos for a news/event
     */
    public function addVideos($newsEventId, $videos) {
        $addedVideos = [];
        
        foreach ($videos as $index => $video) {
            if (!empty($video['path'])) {
                $sql = "INSERT INTO news_events_videos (news_event_id, video_type, video_path, video_title, video_description, display_order) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("issssi", 
                    $newsEventId, 
                    $video['type'], 
                    $video['path'], 
                    $video['title'], 
                    $video['description'], 
                    $index
                );
                
                if ($stmt->execute()) {
                    $addedVideos[] = [
                        'id' => $stmt->insert_id,
                        'type' => $video['type'],
                        'path' => $video['path'],
                        'title' => $video['title'],
                        'description' => $video['description']
                    ];
                }
            }
        }
        
        return $addedVideos;
    }
    
    /**
     * Upload local video files
     */
    public function uploadLocalVideos($newsEventId, $videoFiles, $titles = [], $descriptions = []) {
        $uploadedVideos = [];
        $target_dir = "../uploads/news_events/videos/";
        
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        foreach ($videoFiles['tmp_name'] as $key => $tmp_name) {
            if ($videoFiles['error'][$key] == 0) {
                $file_extension = strtolower(pathinfo($videoFiles["name"][$key], PATHINFO_EXTENSION));
                $new_filename = time() . '_' . uniqid() . '_' . $key . '.' . $file_extension;
                $target_file = $target_dir . $new_filename;
                
                // Allow video formats
                if (in_array($file_extension, ["mp4", "avi", "mov", "wmv", "flv", "webm"])) {
                    if (move_uploaded_file($tmp_name, $target_file)) {
                        $video_path = 'uploads/news_events/videos/' . $new_filename;
                        $title = isset($titles[$key]) ? $titles[$key] : '';
                        $description = isset($descriptions[$key]) ? $descriptions[$key] : '';
                        
                        // Insert into database
                        $sql = "INSERT INTO news_events_videos (news_event_id, video_type, video_path, video_title, video_description, display_order) VALUES (?, 'local', ?, ?, ?, ?)";
                        $stmt = $this->conn->prepare($sql);
                        $stmt->bind_param("isssi", $newsEventId, $video_path, $title, $description, $key);
                        if ($stmt->execute()) {
                            $uploadedVideos[] = [
                                'id' => $stmt->insert_id,
                                'path' => $video_path,
                                'title' => $title,
                                'description' => $description
                            ];
                        }
                    }
                }
            }
        }
        
        return $uploadedVideos;
    }
    
    /**
     * Get all images for a news/event
     */
    public function getImages($newsEventId) {
        $sql = "SELECT * FROM news_events_images WHERE news_event_id = ? ORDER BY display_order ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $newsEventId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $images = [];
        while ($row = $result->fetch_assoc()) {
            $images[] = $row;
        }
        
        return $images;
    }
    
    /**
     * Get all videos for a news/event
     */
    public function getVideos($newsEventId) {
        $sql = "SELECT * FROM news_events_videos WHERE news_event_id = ? ORDER BY display_order ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $newsEventId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $videos = [];
        while ($row = $result->fetch_assoc()) {
            $videos[] = $row;
        }
        
        return $videos;
    }
    
    /**
     * Delete an image
     */
    public function deleteImage($imageId) {
        // Get image path first
        $sql = "SELECT image_path FROM news_events_images WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $imageId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            // Delete file
            $file_path = "../" . $row['image_path'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            
            // Delete from database
            $sql = "DELETE FROM news_events_images WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $imageId);
            return $stmt->execute();
        }
        
        return false;
    }
    
    /**
     * Delete a video
     */
    public function deleteVideo($videoId) {
        // Get video path first
        $sql = "SELECT video_path, video_type FROM news_events_videos WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $videoId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            // Delete file if it's a local video
            if ($row['video_type'] == 'local') {
                $file_path = "../" . $row['video_path'];
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }
            
            // Delete from database
            $sql = "DELETE FROM news_events_videos WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $videoId);
            return $stmt->execute();
        }
        
        return false;
    }
    
    /**
     * Update multimedia status for a news/event
     */
    public function updateMultimediaStatus($newsEventId) {
        // Check if there are any images or videos
        $sql = "SELECT 
                    (SELECT COUNT(*) FROM news_events_images WHERE news_event_id = ?) as image_count,
                    (SELECT COUNT(*) FROM news_events_videos WHERE news_event_id = ?) as video_count";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $newsEventId, $newsEventId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        $hasMultimedia = ($row['image_count'] > 0 || $row['video_count'] > 0) ? 1 : 0;
        
        // Update the news_events table
        $sql = "UPDATE news_events SET has_multimedia = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $hasMultimedia, $newsEventId);
        return $stmt->execute();
    }
    
    /**
     * Get featured image for a news/event
     */
    public function getFeaturedImage($newsEventId) {
        $sql = "SELECT * FROM news_events_images WHERE news_event_id = ? AND is_featured = 1 ORDER BY display_order ASC LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $newsEventId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        // If no featured image, get the first image
        $sql = "SELECT * FROM news_events_images WHERE news_event_id = ? ORDER BY display_order ASC LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $newsEventId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->num_rows > 0 ? $result->fetch_assoc() : null;
    }
    
    /**
     * Set featured image
     */
    public function setFeaturedImage($imageId, $newsEventId) {
        // Remove featured status from all images in this news/event
        $sql = "UPDATE news_events_images SET is_featured = 0 WHERE news_event_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $newsEventId);
        $stmt->execute();
        
        // Set the specified image as featured
        $sql = "UPDATE news_events_images SET is_featured = 1 WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $imageId);
        return $stmt->execute();
    }
}
?> 