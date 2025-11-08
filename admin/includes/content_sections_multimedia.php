<?php
/**
 * Content Sections Multimedia Helper Functions
 * Handles multiple content sections with images and videos for news and events
 */

class ContentSectionsMultimedia {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * Create a new content section
     */
    public function createSection($newsEventId, $sectionTitle, $sectionContent, $displayOrder = 0) {
        $sql = "INSERT INTO news_events_content_sections (news_event_id, section_title, section_content, display_order) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("issi", $newsEventId, $sectionTitle, $sectionContent, $displayOrder);
        
        if ($stmt->execute()) {
            return $stmt->insert_id;
        }
        return false;
    }
    
    /**
     * Update a content section
     */
    public function updateSection($sectionId, $sectionTitle, $sectionContent, $displayOrder = 0) {
        $sql = "UPDATE news_events_content_sections SET section_title = ?, section_content = ?, display_order = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssii", $sectionTitle, $sectionContent, $displayOrder, $sectionId);
        return $stmt->execute();
    }
    
    /**
     * Update a content section and preserve existing media
     */
    public function updateSectionWithMedia($sectionId, $sectionTitle, $sectionContent, $displayOrder = 0, $preserveImages = true, $preserveLocalVideos = true) {
        // Update the section content
        $result = $this->updateSection($sectionId, $sectionTitle, $sectionContent, $displayOrder);
        
        // If preserve flags are false, delete existing media
        if (!$preserveImages) {
            $images = $this->getSectionImages($sectionId);
            foreach ($images as $image) {
                $this->deleteSectionImage($image['id']);
            }
        }
        
        if (!$preserveLocalVideos) {
            $videos = $this->getSectionVideos($sectionId);
            foreach ($videos as $video) {
                if ($video['video_type'] == 'local') {
                    $this->deleteSectionVideo($video['id']);
                }
            }
        }
        
        return $result;
    }
    
    /**
     * Delete a content section and all its media
     */
    public function deleteSection($sectionId) {
        // Delete all images for this section
        $images = $this->getSectionImages($sectionId);
        foreach ($images as $image) {
            $this->deleteSectionImage($image['id']);
        }
        
        // Delete all videos for this section
        $videos = $this->getSectionVideos($sectionId);
        foreach ($videos as $video) {
            $this->deleteSectionVideo($video['id']);
        }
        
        // Delete the section
        $sql = "DELETE FROM news_events_content_sections WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $sectionId);
        return $stmt->execute();
    }
    
    /**
     * Get all content sections for a news/event
     */
    public function getSections($newsEventId) {
        $sql = "SELECT * FROM news_events_content_sections WHERE news_event_id = ? ORDER BY display_order ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $newsEventId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $sections = [];
        while ($row = $result->fetch_assoc()) {
            $sections[] = $row;
        }
        
        return $sections;
    }
    
    /**
     * Get a specific content section
     */
    public function getSection($sectionId) {
        $sql = "SELECT * FROM news_events_content_sections WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $sectionId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }
    
    /**
     * Upload images for a content section
     */
    public function uploadSectionImages($sectionId, $imageFiles, $altTexts = []) {
        $uploadedImages = [];
        $target_dir = "../uploads/news_events/content_sections/";
        
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
                            $image_path = 'uploads/news_events/content_sections/' . $new_filename;
                            $alt_text = isset($altTexts[$key]) ? $altTexts[$key] : '';
                            
                            // Insert into database
                            $sql = "INSERT INTO content_section_images (section_id, image_path, alt_text, display_order) VALUES (?, ?, ?, ?)";
                            $stmt = $this->conn->prepare($sql);
                            $stmt->bind_param("issi", $sectionId, $image_path, $alt_text, $key);
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
     * Upload local videos for a content section
     */
    public function uploadSectionLocalVideos($sectionId, $videoFiles, $titles = [], $descriptions = []) {
        $uploadedVideos = [];
        $target_dir = "../uploads/news_events/content_sections/videos/";
        
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        foreach ($videoFiles['tmp_name'] as $key => $tmp_name) {
            if ($videoFiles['error'][$key] == 0) {
                $file_extension = strtolower(pathinfo($videoFiles["name"][$key], PATHINFO_EXTENSION));
                $new_filename = time() . '_' . uniqid() . '_' . $key . '.' . $file_extension;
                $target_file = $target_dir . $new_filename;
                
                // Allow certain video formats
                if (in_array($file_extension, ["mp4", "avi", "mov", "wmv", "flv", "webm"])) {
                    if (move_uploaded_file($tmp_name, $target_file)) {
                        $video_path = 'uploads/news_events/content_sections/videos/' . $new_filename;
                        $title = isset($titles[$key]) ? $titles[$key] : '';
                        $description = isset($descriptions[$key]) ? $descriptions[$key] : '';
                        
                        // Insert into database
                        $sql = "INSERT INTO content_section_videos (section_id, video_type, video_path, video_title, video_description, display_order) VALUES (?, 'local', ?, ?, ?, ?)";
                        $stmt = $this->conn->prepare($sql);
                        $stmt->bind_param("isssi", $sectionId, $video_path, $title, $description, $key);
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
     * Add URL videos for a content section
     */
    public function addSectionUrlVideos($sectionId, $videos) {
        $addedVideos = [];
        
        foreach ($videos as $index => $video) {
            if (!empty($video['path'])) {
                $sql = "INSERT INTO content_section_videos (section_id, video_type, video_path, video_title, video_description, display_order) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("issssi", 
                    $sectionId, 
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
     * Clear existing URL videos for a content section
     */
    public function clearSectionUrlVideos($sectionId) {
        $sql = "DELETE FROM content_section_videos WHERE section_id = ? AND video_type = 'url'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $sectionId);
        return $stmt->execute();
    }
    
    /**
     * Get all images for a content section
     */
    public function getSectionImages($sectionId) {
        $sql = "SELECT * FROM content_section_images WHERE section_id = ? ORDER BY display_order ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $sectionId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $images = [];
        while ($row = $result->fetch_assoc()) {
            $images[] = $row;
        }
        
        return $images;
    }
    
    /**
     * Get all videos for a content section
     */
    public function getSectionVideos($sectionId) {
        $sql = "SELECT * FROM content_section_videos WHERE section_id = ? ORDER BY display_order ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $sectionId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $videos = [];
        while ($row = $result->fetch_assoc()) {
            $videos[] = $row;
        }
        
        return $videos;
    }
    
    /**
     * Delete a section image
     */
    public function deleteSectionImage($imageId) {
        // Get image path first
        $sql = "SELECT image_path FROM content_section_images WHERE id = ?";
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
            $sql = "DELETE FROM content_section_images WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $imageId);
            return $stmt->execute();
        }
        
        return false;
    }
    
    /**
     * Delete a section video
     */
    public function deleteSectionVideo($videoId) {
        // Get video path first
        $sql = "SELECT video_path, video_type FROM content_section_videos WHERE id = ?";
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
            $sql = "DELETE FROM content_section_videos WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $videoId);
            return $stmt->execute();
        }
        
        return false;
    }
    
    /**
     * Update content sections status for a news/event
     */
    public function updateContentSectionsStatus($newsEventId) {
        // Check if there are any content sections
        $sql = "SELECT COUNT(*) as section_count FROM news_events_content_sections WHERE news_event_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $newsEventId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        $hasContentSections = ($row['section_count'] > 0) ? 1 : 0;
        
        // Update the news_events table
        $sql = "UPDATE news_events SET has_content_sections = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $hasContentSections, $newsEventId);
        return $stmt->execute();
    }
    
    /**
     * Update image alt text
     */
    public function updateSectionImageAltText($imageId, $altText) {
        $sql = "UPDATE content_section_images SET alt_text = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $altText, $imageId);
        return $stmt->execute();
    }
    
    /**
     * Update video title
     */
    public function updateSectionVideoTitle($videoId, $title) {
        $sql = "UPDATE content_section_videos SET video_title = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $title, $videoId);
        return $stmt->execute();
    }
    
    /**
     * Update video description
     */
    public function updateSectionVideoDescription($videoId, $description) {
        $sql = "UPDATE content_section_videos SET video_description = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $description, $videoId);
        return $stmt->execute();
    }
} 