<?php
// Include database connection
require_once 'includes/db_connection.php';

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Include multimedia helper
require_once 'admin/includes/news_events_multimedia.php';
require_once 'admin/includes/content_sections_multimedia.php';
$multimedia = new NewsEventsMultimedia($conn);
$contentSections = new ContentSectionsMultimedia($conn);

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$article = null;
$images = [];
$videos = [];
$content_sections = [];
if ($id > 0) {
    $sql = "SELECT * FROM news_events WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $article = $result->fetch_assoc();

        // Get multimedia data for main article (not content sections)
        $images = $multimedia->getImages($id);
        $videos = $multimedia->getVideos($id);
        // Get content sections
        $content_sections = $contentSections->getSections($id);
    }
    $stmt->close();
}

// Process content sections to include their media
$processed_content_sections = [];
$section1_content = ''; // Store section 1 content separately
$section1_media = [];
foreach ($content_sections as $index => $section) {
    $section_images = $contentSections->getSectionImages($section['id']);
    $section_videos = $contentSections->getSectionVideos($section['id']);

    // Filter out empty media entries
    $section_images = array_filter($section_images, function ($img) {
        return !empty($img['image_path']) && file_exists($img['image_path']) && filesize($img['image_path']) > 0;
    });
    $section_videos = array_filter($section_videos, function ($video) {
        return !empty($video['video_path']);
    });

    $section['media'] = array_merge($section_images, $section_videos);

    if ($index === 0) {
        // Store section 1 content and media separately
        $section1_content = $section['section_content'];
        $section1_media = $section['media'];
    } else {
        $processed_content_sections[] = $section;
    }
}

// Combine main media with content section 1 media (if it exists)
$mainMedia = array_merge($images, $videos);

// Filter out empty main media entries
$mainMedia = array_filter($mainMedia, function ($media) {
    if (isset($media['image_path'])) {
        return !empty($media['image_path']) && file_exists($media['image_path']) && filesize($media['image_path']) > 0;
    } elseif (isset($media['video_path'])) {
        return !empty($media['video_path']);
    }
    return false;
});

if (!empty($section1_media)) {
    $mainMedia = array_merge($mainMedia, $section1_media);
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $article ? htmlspecialchars($article['title']) : 'News Not Found'; ?> - News - JPMC</title>

    <!--Tab Icon-->
    <link rel="icon" type="image/png" href="assets/img/tab_icon.png">

    <!-- Google Fonts - Required for header -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">

    <!-- AOS (Animate On Scroll) - Required for header -->
    <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        <?php include 'includes/css/news_details.css'; ?>
    </style>
</head>

<body class="bg-gray-100">
    <?php include 'header.php'; ?>

    <!-- Chatbot Integration -->
    <?php include 'chatbot.php'; ?>

    <!-- Main content centered and restricted, header/nav is always full width -->
    <div class="container mx-auto px-4 py-10 max-w-4xl lg:px-0">
        <a href="news_events.php" class="text-blue-700 hover:underline text-sm mb-4 inline-block"><i
                class="fas fa-arrow-left mr-1"></i> Back to News & Events</a>
        <?php if ($article): ?>
            <h1
                class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-extrabold text-blue-900 mb-3 text-left leading-tight tracking-tight break-words whitespace-normal">
                <?php echo htmlspecialchars($article['title']); ?>
            </h1>
            <div class="text-black text-lg font-medium mb-3 text-left">
                <?php echo date('F j, Y', strtotime($article['date'])); ?>
            </div>
            <div class="flex items-center text-gray-500 text-base mb-8 space-x-5 text-left">
                <a href="#" class="text-gray-500 hover:text-blue-600"><i class="fab fa-facebook fa-lg"></i></a>
                <a href="#" class="text-gray-500 hover:text-blue-600"><i class="fab fa-twitter fa-lg"></i></a>
                <a href="#" class="text-gray-500 hover:text-blue-600"><i class="fab fa-linkedin fa-lg"></i></a>
                <a href="#" class="text-gray-500 hover:text-blue-600"><i class="fab fa-instagram fa-lg"></i></a>
            </div>

            <!-- World-Class Carousel -->
            <?php
            if (!empty($mainMedia)):
            ?>
                <div class="w-full mb-8">
                    <div class="carousel-container">
                        <?php if (count($mainMedia) > 1): ?>
                            <div class="touch-indicator" id="touchIndicator">
                                <i class="fas fa-hand-pointer mr-1"></i> Swipe to navigate
                            </div>
                        <?php endif; ?>

                        <div class="carousel-track" id="carouselTrack">
                            <?php
                            foreach ($mainMedia as $index => $media):
                                // Prepare media data for JavaScript
                                $mediaData = [];
                                if (isset($media['image_path'])) {
                                    // Image data
                                    $mediaData['type'] = 'image';
                                    $mediaData['alt_text'] = $media['alt_text'] ?? '';
                                    $mediaData['title'] = $media['alt_text'] ?? '';
                                } elseif (isset($media['video_path'])) {
                                    // Video data
                                    $mediaData['type'] = 'video';
                                    $mediaData['title'] = $media['video_title'] ?? '';
                                    $mediaData['description'] = $media['video_description'] ?? '';
                                    $mediaData['video_type'] = $media['video_type'] ?? 'url';
                                }
                            ?>
                                <div class="carousel-slide <?php echo $index === 0 ? 'active' : ''; ?>"
                                    data-index="<?php echo $index; ?>" data-media='<?php echo json_encode($mediaData); ?>'>
                                    <?php if (isset($media['image_path'])): // Image 
                                    ?>
                                        <img src="<?php echo $media['image_path']; ?>"
                                            alt="<?php echo htmlspecialchars($media['alt_text'] ?: $article['title']); ?>"
                                            loading="lazy" onclick="openFullscreen('<?php echo $media['image_path']; ?>', 'image')"
                                            onerror="this.onerror=null; this.src='assets/img/placeholder.png'; this.style.opacity='0.7';">
                                    <?php elseif (isset($media['video_path'])): // Video 
                                    ?>
                                        <?php if ($media['video_type'] == 'local'): ?>
                                            <video controls preload="metadata"
                                                title="<?php echo htmlspecialchars($media['video_title'] ?? ''); ?>">
                                                <source src="<?php echo $media['video_path']; ?>" type="video/mp4">
                                                Your browser does not support the video tag.
                                            </video>
                                        <?php else: ?>
                                            <?php
                                            // Extract video ID from URL
                                            $videoUrl = $media['video_path'];
                                            $videoId = '';
                                            if (strpos($videoUrl, 'youtube.com/watch?v=') !== false) {
                                                $videoId = substr($videoUrl, strpos($videoUrl, 'v=') + 2);
                                                $videoId = strtok($videoId, '&');
                                            } elseif (strpos($videoUrl, 'youtu.be/') !== false) {
                                                $videoId = substr($videoUrl, strpos($videoUrl, 'youtu.be/') + 9);
                                            } elseif (strpos($videoUrl, 'vimeo.com/') !== false) {
                                                $videoId = substr($videoUrl, strpos($videoUrl, 'vimeo.com/') + 10);
                                                $videoId = strtok($videoId, '?');
                                            }
                                            ?>
                                            <?php if ($videoId): ?>
                                                <?php if (strpos($videoUrl, 'vimeo.com') !== false): ?>
                                                    <iframe src="https://player.vimeo.com/video/<?php echo $videoId; ?>" frameborder="0"
                                                        allow="autoplay; fullscreen; picture-in-picture" allowfullscreen
                                                        title="<?php echo htmlspecialchars($media['video_title'] ?? 'Vimeo Video'); ?>"></iframe>
                                                <?php else: ?>
                                                    <iframe src="https://www.youtube.com/embed/<?php echo $videoId; ?>" frameborder="0"
                                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                                        allowfullscreen
                                                        title="<?php echo htmlspecialchars($media['video_title'] ?? 'YouTube Video'); ?>"></iframe>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <div class="video-preview">
                                                    <i class="fas fa-video text-gray-400 text-4xl"></i>
                                                    <p class="text-gray-500 mt-2">Video not available</p>
                                                </div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Navigation Arrows (Overlay) - REMOVED -->

                        <!-- Slide Counter -->
                        <?php if (count($mainMedia) > 1): ?>
                            <div class="carousel-counter">
                                <span id="currentSlide">1</span> / <span id="totalSlides"><?php echo count($mainMedia); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Navigation Controls (Below Media) -->
                    <?php if (count($mainMedia) > 1): ?>
                        <div class="flex items-center justify-center mt-4 space-x-4">
                            <!-- Previous Button -->
                            <button class="carousel-nav prev" onclick="prevSlide()"
                                style="position: static; transform: none; background: #f8f9fa; border: 2px solid #e9ecef; width: 45px; height: 45px;">
                                <i class="fas fa-chevron-left" style="color: #495057;"></i>
                            </button>

                            <!-- Dots Navigation -->
                            <div class="carousel-dots" id="carouselDots" style="position: static; transform: none; gap: 6px;">
                                <?php for ($i = 0; $i < count($mainMedia); $i++): ?>
                                    <div class="carousel-dot <?php echo $i === 0 ? 'active' : ''; ?>"
                                        onclick="goToSlide(<?php echo $i; ?>)"
                                        style="background: <?php echo $i === 0 ? '#0066cc' : '#dee2e6'; ?>; width: 10px; height: 10px; border-radius: 50%; cursor: pointer; transition: all 0.3s ease; <?php echo $i === 0 ? 'transform: scale(1.1); box-shadow: 0 0 8px rgba(0, 102, 204, 0.4);' : ''; ?>">
                                    </div>
                                <?php endfor; ?>
                            </div>

                            <!-- Next Button -->
                            <button class="carousel-nav next" onclick="nextSlide()"
                                style="position: static; transform: none; background: #f8f9fa; border: 2px solid #e9ecef; width: 45px; height: 45px;">
                                <i class="fas fa-chevron-right" style="color: #495057;"></i>
                            </button>
                        </div>
                    <?php endif; ?>

                    <!-- Media Info (Below Navigation) -->
                    <?php if (!empty($mainMedia)): ?>
                        <div class="mt-4 text-center">
                            <div id="mediaInfo" class="text-gray-600 text-lg">
                                <?php
                                $firstMedia = $mainMedia[0];
                                $description = '';

                                if (isset($firstMedia['alt_text']) && $firstMedia['alt_text']) {
                                    $description = $firstMedia['alt_text'];
                                } elseif (isset($firstMedia['video_title']) && $firstMedia['video_title']) {
                                    $description = $firstMedia['video_title'];
                                    if (isset($firstMedia['video_description']) && $firstMedia['video_description']) {
                                        $description .= ' - ' . $firstMedia['video_description'];
                                    }
                                } elseif (isset($firstMedia['video_description']) && $firstMedia['video_description']) {
                                    $description = $firstMedia['video_description'];
                                }

                                if ($description): ?>
                                    <?php echo htmlspecialchars($description); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <!-- No Media Available - Show Placeholder -->
                <div class="w-full mb-8 flex items-center justify-center">
                    <div class="bg-gray-100 rounded-2xl shadow-lg flex flex-col items-center justify-center"
                        style="min-width:320px; min-height:220px; max-width:100%; width:100%;">
                        <i class="fas fa-image text-gray-300 text-7xl mb-4"></i>
                        <p class="text-gray-500 text-lg text-center px-4">No images or videos available for this news article
                        </p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Section 1 Content (if exists) -->
            <?php if (!empty($section1_content)): ?>
                <div class="mb-8">
                    <div class="text-gray-800 text-lg leading-relaxed font-serif center-text">
                        <?php echo nl2br(htmlspecialchars($section1_content)); ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Content Sections -->
            <?php if (!empty($processed_content_sections)): ?>
                <div class="mt-8">
                    <?php foreach ($processed_content_sections as $sectionIndex => $section): ?>
                        <div class="mb-8">
                            <!-- Content Section Media -->
                            <?php
                            // Filter out empty media entries for display
                            $display_media = array_filter($section['media'], function ($media) {
                                if (isset($media['image_path'])) {
                                    return !empty($media['image_path']) && file_exists($media['image_path']) && filesize($media['image_path']) > 0;
                                } elseif (isset($media['video_path'])) {
                                    return !empty($media['video_path']);
                                }
                                return false;
                            });
                            ?>
                            <?php if (!empty($display_media)): ?>
                                <div class="mb-6">
                                    <div class="carousel-container" id="sectionCarousel<?php echo $sectionIndex; ?>">
                                        <?php if (count($display_media) > 1): ?>
                                            <div class="touch-indicator" id="sectionTouchIndicator<?php echo $sectionIndex; ?>">
                                                <i class="fas fa-hand-pointer mr-1"></i> Swipe to navigate
                                            </div>
                                        <?php endif; ?>

                                        <div class="carousel-track" id="sectionCarouselTrack<?php echo $sectionIndex; ?>">
                                            <?php
                                            foreach ($display_media as $mediaIndex => $media):
                                                // Prepare media data for JavaScript
                                                $mediaData = [];
                                                if (isset($media['image_path'])) {
                                                    // Image data
                                                    $mediaData['type'] = 'image';
                                                    $mediaData['alt_text'] = $media['alt_text'] ?? '';
                                                    $mediaData['title'] = $media['alt_text'] ?? '';
                                                } elseif (isset($media['video_path'])) {
                                                    // Video data
                                                    $mediaData['type'] = 'video';
                                                    $mediaData['title'] = $media['video_title'] ?? '';
                                                    $mediaData['description'] = $media['video_description'] ?? '';
                                                    $mediaData['video_type'] = $media['video_type'] ?? 'url';
                                                }
                                            ?>
                                                <div class="carousel-slide <?php echo $mediaIndex === 0 ? 'active' : ''; ?>"
                                                    data-index="<?php echo $mediaIndex; ?>"
                                                    data-media='<?php echo json_encode($mediaData); ?>'>
                                                    <?php if (isset($media['image_path'])): // Image 
                                                    ?>
                                                        <img src="<?php echo $media['image_path']; ?>"
                                                            alt="<?php echo htmlspecialchars($media['alt_text'] ?: 'Section image'); ?>"
                                                            loading="lazy"
                                                            onclick="openFullscreen('<?php echo $media['image_path']; ?>', 'image')"
                                                            onerror="this.onerror=null; this.src='assets/img/placeholder.png'; this.style.opacity='0.7';">
                                                    <?php elseif (isset($media['video_path'])): // Video 
                                                    ?>
                                                        <?php if ($media['video_type'] == 'local'): ?>
                                                            <video controls preload="metadata"
                                                                title="<?php echo htmlspecialchars($media['video_title'] ?? ''); ?>">
                                                                <source src="<?php echo $media['video_path']; ?>" type="video/mp4">
                                                                Your browser does not support the video tag.
                                                            </video>
                                                        <?php else: ?>
                                                            <?php
                                                            // Extract video ID from URL
                                                            $videoUrl = $media['video_path'];
                                                            $videoId = '';
                                                            if (strpos($videoUrl, 'youtube.com/watch?v=') !== false) {
                                                                $videoId = substr($videoUrl, strpos($videoUrl, 'v=') + 2);
                                                                $videoId = strtok($videoId, '&');
                                                            } elseif (strpos($videoUrl, 'youtu.be/') !== false) {
                                                                $videoId = substr($videoUrl, strpos($videoUrl, 'youtu.be/') + 9);
                                                            } elseif (strpos($videoUrl, 'vimeo.com/') !== false) {
                                                                $videoId = substr($videoUrl, strpos($videoUrl, 'vimeo.com/') + 10);
                                                                $videoId = strtok($videoId, '?');
                                                            }
                                                            ?>
                                                            <?php if ($videoId): ?>
                                                                <?php if (strpos($videoUrl, 'vimeo.com') !== false): ?>
                                                                    <iframe src="https://player.vimeo.com/video/<?php echo $videoId; ?>" frameborder="0"
                                                                        allow="autoplay; fullscreen; picture-in-picture" allowfullscreen
                                                                        title="<?php echo htmlspecialchars($media['video_title'] ?? 'Vimeo Video'); ?>"></iframe>
                                                                <?php else: ?>
                                                                    <iframe src="https://www.youtube.com/embed/<?php echo $videoId; ?>" frameborder="0"
                                                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                                                        allowfullscreen
                                                                        title="<?php echo htmlspecialchars($media['video_title'] ?? 'YouTube Video'); ?>"></iframe>
                                                                <?php endif; ?>
                                                            <?php else: ?>
                                                                <div class="video-preview">
                                                                    <i class="fas fa-video text-gray-400 text-4xl"></i>
                                                                    <p class="text-gray-500 mt-2">Video not available</p>
                                                                </div>
                                                            <?php endif; ?>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>

                                        <!-- Navigation Arrows (Overlay) - REMOVED -->

                                        <!-- Slide Counter -->
                                        <?php if (count($display_media) > 1): ?>
                                            <div class="carousel-counter">
                                                <span id="sectionCurrentSlide<?php echo $sectionIndex; ?>">1</span> / <span
                                                    id="sectionTotalSlides<?php echo $sectionIndex; ?>"><?php echo count($display_media); ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Navigation Controls (Below Media) -->
                                    <?php if (count($display_media) > 1): ?>
                                        <div class="flex items-center justify-center mt-4 space-x-4">
                                            <!-- Previous Button -->
                                            <button class="carousel-nav prev" onclick="sectionPrevSlide(<?php echo $sectionIndex; ?>)"
                                                style="position: static; transform: none; background: #f8f9fa; border: 2px solid #e9ecef; width: 45px; height: 45px;">
                                                <i class="fas fa-chevron-left" style="color: #495057;"></i>
                                            </button>

                                            <!-- Dots Navigation -->
                                            <div class="carousel-dots" id="sectionCarouselDots<?php echo $sectionIndex; ?>"
                                                style="position: static; transform: none; gap: 6px;">
                                                <?php for ($i = 0; $i < count($display_media); $i++): ?>
                                                    <div class="carousel-dot <?php echo $i === 0 ? 'active' : ''; ?>"
                                                        onclick="sectionGoToSlide(<?php echo $sectionIndex; ?>, <?php echo $i; ?>)"
                                                        style="background: <?php echo $i === 0 ? '#0066cc' : '#dee2e6'; ?>; width: 10px; height: 10px; border-radius: 50%; cursor: pointer; transition: all 0.3s ease; <?php echo $i === 0 ? 'transform: scale(1.1); box-shadow: 0 0 8px rgba(0, 102, 204, 0.4);' : ''; ?>">
                                                    </div>
                                                <?php endfor; ?>
                                            </div>

                                            <!-- Next Button -->
                                            <button class="carousel-nav next" onclick="sectionNextSlide(<?php echo $sectionIndex; ?>)"
                                                style="position: static; transform: none; background: #f8f9fa; border: 2px solid #e9ecef; width: 45px; height: 45px;">
                                                <i class="fas fa-chevron-right" style="color: #495057;"></i>
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Section Content (Below Media) -->
                            <div class="text-gray-800 text-lg leading-relaxed font-serif left-text">
                                <?php echo nl2br(htmlspecialchars($section['section_content'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="text-center text-gray-500 text-xl py-20">News article not found.</div>
        <?php endif; ?>
    </div>

    <!-- Fullscreen Modal -->
    <div id="fullscreenModal" class="fullscreen-modal">
        <span class="fullscreen-close" onclick="closeFullscreen()">&times;</span>
        <div class="fullscreen-content" id="fullscreenContent"></div>
    </div>

    <!-- Footer -->
    <?php include 'footer.php'; ?>

    <!-- AOS JavaScript - Required for header animations -->
    <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            once: true,
        });
    </script>
    <!-- Javascript -->
    <script src="includes/javascript/news_detail.js"></script>
</body>

</html>