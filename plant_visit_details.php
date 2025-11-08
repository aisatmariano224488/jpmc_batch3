<?php
// Include database connection
require_once 'includes/db_connection.php';

// Create connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$visit = null;
$images = [];
$videos = [];

if ($id > 0) {
    // Get plant visit details
    $sql = "SELECT * FROM plant_visits WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $visit = $result->fetch_assoc();

        // Get media for this plant visit
        $media_sql = "SELECT * FROM plant_visit_images WHERE plant_visit_id = ? ORDER BY display_order ASC";
        $media_stmt = $conn->prepare($media_sql);
        $media_stmt->bind_param("i", $id);
        $media_stmt->execute();
        $media_result = $media_stmt->get_result();

        if ($media_result && $media_result->num_rows > 0) {
            while ($media_row = $media_result->fetch_assoc()) {
                if ($media_row['media_type'] === 'image') {
                    $images[] = $media_row;
                } elseif ($media_row['media_type'] === 'video') {
                    $videos[] = $media_row;
                }
            }
        }
        $media_stmt->close();
    }
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $visit ? htmlspecialchars($visit['title']) : 'Plant Visit Not Found'; ?> - Plant Visit - JPMC</title>

    <!--Tab Icon-->
    <link rel="icon" type="image/png" href="assets/img/tab_icon.png">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">

    <!-- AOS (Animate On Scroll) -->
    <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* Carousel Styles */
        .carousel-container {
            position: relative;
            width: 100%;
            margin: 0 auto;
            overflow: hidden;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            background: #000;
        }

        .carousel-track {
            display: flex;
            transition: transform 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            height: 500px;
            width: 100%;
        }

        .carousel-slide {
            min-width: 100%;
            width: 100%;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #000;
            flex-shrink: 0;
        }

        .carousel-slide img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            transition: transform 0.3s ease;
        }

        .carousel-slide:hover img {
            transform: scale(1.02);
        }

        .carousel-slide video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .carousel-slide iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        /* Navigation Controls */
        .carousel-nav {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .carousel-nav:hover {
            background: #e9ecef;
            transform: scale(1.05);
            border-color: #dee2e6;
        }

        .carousel-nav i {
            font-size: 18px;
            color: #495057;
        }

        /* Dots Navigation */
        .carousel-dots {
            display: flex;
            gap: 6px;
        }

        .carousel-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #dee2e6;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .carousel-dot.active {
            background: #0066cc;
            transform: scale(1.1);
            box-shadow: 0 0 8px rgba(0, 102, 204, 0.4);
        }

        .carousel-dot:hover {
            background: #adb5bd;
            transform: scale(1.05);
        }

        /* Slide Counter */
        .carousel-counter {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
            z-index: 10;
            backdrop-filter: blur(10px);
        }

        /* Fullscreen Modal */
        .fullscreen-modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100vw;
            height: 100vh;
            background-color: rgba(0, 0, 0, 0.95);
            backdrop-filter: blur(10px);
        }

        .fullscreen-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            max-width: 90vw;
            max-height: 90vh;
        }

        .fullscreen-content img,
        .fullscreen-content video,
        .fullscreen-content iframe {
            max-width: 100vw;
            max-height: 80vh;
            border-radius: 8px;
        }

        .fullscreen-close {
            position: absolute;
            top: 20px;
            right: 30px;
            color: #fff;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
            z-index: 10000;
            transition: all 0.3s ease;
        }

        .fullscreen-close:hover {
            transform: scale(1.1);
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .container {
                padding-left: 0.5rem !important;
                padding-right: 0.5rem !important;
            }
        }

        @media (max-width: 900px) {
            .carousel-track {
                height: 350px;
            }
        }

        @media (max-width: 768px) {
            .carousel-container {
                border-radius: 10px;
            }

            .carousel-track {
                height: 220px;
            }

            .carousel-nav {
                width: 36px;
                height: 36px;
            }

            .carousel-counter {
                top: 10px;
                right: 10px;
                font-size: 12px;
                padding: 5px 8px;
            }

            .fullscreen-content {
                max-width: 98vw;
                max-height: 80vh;
            }

            .fullscreen-content img,
            .fullscreen-content video,
            .fullscreen-content iframe {
                max-width: 98vw;
                max-height: 60vh;
            }

            h1,
            h3 {
                font-size: 1.2rem !important;
            }
        }

        @media (max-width: 480px) {
            .carousel-track {
                height: 140px;
            }

            .carousel-container {
                border-radius: 6px;
            }

            .carousel-nav {
                width: 28px;
                height: 28px;
            }

            .carousel-dot {
                width: 7px;
                height: 7px;
            }

            .fullscreen-close {
                top: 8px;
                right: 12px;
                font-size: 28px;
            }

            .fullscreen-content {
                max-width: 100vw;
                max-height: 60vh;
            }

            .fullscreen-content img,
            .fullscreen-content video,
            .fullscreen-content iframe {
                max-width: 100vw;
                max-height: 40vh;
            }
        }

        /* Utility for word break on mobile */
        .break-all {
            word-break: break-all;
        }

        .whitespace-normal {
            white-space: normal !important;
        }

        .text-center-mobile {
            text-align: left !important;
        }

        @media (max-width: 768px) {

            .text-left,
            .text-center-mobile {
                text-align: left !important;
            }
        }

        /* End Responsive Enhancements */
    </style>
</head>

<body class="bg-gray-100">
    <?php include 'header.php'; ?>

    <!-- Chatbot Integration -->
    <?php include 'chatbot.php'; ?>

    <div class="container mx-auto px-4 py-10 max-w-4xl lg:px-0">
        <a href="news_events.php" class="text-blue-700 hover:underline text-sm mb-4 inline-block"><i class="fas fa-arrow-left mr-1"></i> Back to News & Events</a>
        <?php if ($visit): ?>
            <h1 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-extrabold text-blue-900 mb-3 text-left leading-tight tracking-tight break-words whitespace-normal">
                <?php echo htmlspecialchars($visit['title']); ?>
            </h1>
            <div class="text-black text-lg font-medium mb-3 text-left">
                <?php echo date('F j, Y', strtotime($visit['created_at'])); ?>
            </div>
            <div class="flex items-center text-gray-500 text-base mb-8 space-x-5 text-left">
                <a href="#" class="text-gray-500 hover:text-blue-600"><i class="fab fa-facebook fa-lg"></i></a>
                <a href="#" class="text-gray-500 hover:text-blue-600"><i class="fab fa-twitter fa-lg"></i></a>
                <a href="#" class="text-gray-500 hover:text-blue-600"><i class="fab fa-linkedin fa-lg"></i></a>
                <a href="#" class="text-gray-500 hover:text-blue-600"><i class="fab fa-instagram fa-lg"></i></a>
            </div>

            <!-- Videos Gallery on Top -->
            <?php if (!empty($videos)): ?>
                <div class="w-full mb-8">
                    <h3 class="text-2xl font-bold text-blue-900 mb-6 text-center">Video Gallery</h3>
                    <div class="carousel-container" id="videoCarousel">
                        <?php if (count($videos) > 1): ?>
                            <div class="carousel-counter">
                                <span id="videoCurrentSlide">1</span> / <span id="videoTotalSlides"><?php echo count($videos); ?></span>
                            </div>
                        <?php endif; ?>

                        <div class="carousel-track" id="videoCarouselTrack">
                            <?php
                            foreach ($videos as $index => $video):
                            ?>
                                <div class="carousel-slide <?php echo $index === 0 ? 'active' : ''; ?>"
                                    data-index="<?php echo $index; ?>">
                                    <?php if ($video['video_type'] === 'uploaded'): ?>
                                        <video controls preload="metadata" title="<?php echo htmlspecialchars($video['video_title'] ?? ''); ?>">
                                            <source src="<?php echo htmlspecialchars($video['video_url']); ?>" type="video/mp4">
                                            Your browser does not support the video tag.
                                        </video>
                                    <?php else: ?>
                                        <?php
                                        // Extract video ID from URL
                                        $videoUrl = $video['video_url'];
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
                                                <iframe src="https://player.vimeo.com/video/<?php echo $videoId; ?>"
                                                    frameborder="0"
                                                    allow="autoplay; fullscreen; picture-in-picture"
                                                    allowfullscreen
                                                    title="<?php echo htmlspecialchars($video['video_title'] ?? 'Vimeo Video'); ?>"></iframe>
                                            <?php else: ?>
                                                <iframe src="https://www.youtube.com/embed/<?php echo $videoId; ?>"
                                                    frameborder="0"
                                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                                    allowfullscreen
                                                    title="<?php echo htmlspecialchars($video['video_title'] ?? 'YouTube Video'); ?>"></iframe>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <div class="video-preview">
                                                <i class="fas fa-video text-gray-400 text-4xl"></i>
                                                <p class="text-gray-500 mt-2">Video not available</p>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Navigation Controls (Below Videos) -->
                    <?php if (count($videos) > 1): ?>
                        <div class="flex items-center justify-center mt-4 space-x-4">
                            <!-- Previous Button -->
                            <button class="carousel-nav prev" onclick="videoPrevSlide()">
                                <i class="fas fa-chevron-left"></i>
                            </button>

                            <!-- Dots Navigation -->
                            <div class="carousel-dots" id="videoCarouselDots">
                                <?php for ($i = 0; $i < count($videos); $i++): ?>
                                    <div class="carousel-dot <?php echo $i === 0 ? 'active' : ''; ?>"
                                        onclick="videoGoToSlide(<?php echo $i; ?>)"></div>
                                <?php endfor; ?>
                            </div>

                            <!-- Next Button -->
                            <button class="carousel-nav next" onclick="videoNextSlide()">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Main Image Gallery (if no videos, show images here) -->
            <?php if (empty($videos) && !empty($images)): ?>
                <div class="w-full mb-8">
                    <h3 class="text-2xl font-bold text-blue-900 mb-6 text-center">Photo Gallery</h3>
                    <div class="carousel-container" id="mainImageCarousel">
                        <?php if (count($images) > 1): ?>
                            <div class="carousel-counter">
                                <span id="mainImageCurrentSlide">1</span> / <span id="mainImageTotalSlides"><?php echo count($images); ?></span>
                            </div>
                        <?php endif; ?>

                        <div class="carousel-track" id="mainImageCarouselTrack">
                            <?php
                            foreach ($images as $index => $image):
                            ?>
                                <div class="carousel-slide <?php echo $index === 0 ? 'active' : ''; ?>"
                                    data-index="<?php echo $index; ?>">
                                    <img src="assets/img/plant_visit/<?php echo htmlspecialchars($image['image']); ?>"
                                        alt="<?php echo htmlspecialchars($visit['title']); ?>"
                                        loading="lazy"
                                        onclick="openFullscreen('assets/img/plant_visit/<?php echo htmlspecialchars($image['image']); ?>', 'image')"
                                        onerror="this.onerror=null; this.src='assets/img/placeholder.png'; this.style.opacity='0.7';">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Navigation Controls (Below Main Images) -->
                    <?php if (count($images) > 1): ?>
                        <div class="flex items-center justify-center mt-4 space-x-4">
                            <!-- Previous Button -->
                            <button class="carousel-nav prev" onclick="mainImagePrevSlide()">
                                <i class="fas fa-chevron-left"></i>
                            </button>

                            <!-- Dots Navigation -->
                            <div class="carousel-dots" id="mainImageCarouselDots">
                                <?php for ($i = 0; $i < count($images); $i++): ?>
                                    <div class="carousel-dot <?php echo $i === 0 ? 'active' : ''; ?>"
                                        onclick="mainImageGoToSlide(<?php echo $i; ?>)"></div>
                                <?php endfor; ?>
                            </div>

                            <!-- Next Button -->
                            <button class="carousel-nav next" onclick="mainImageNextSlide()">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- No Media Available - Show Placeholder -->
            <?php if (empty($videos) && empty($images)): ?>
                <div class="w-full mb-8 flex items-center justify-center">
                    <div class="bg-gray-100 rounded-2xl shadow-lg flex flex-col items-center justify-center" style="min-width:320px; min-height:220px; max-width:100%; width:100%;">
                        <i class="fas fa-image text-gray-300 text-7xl mb-4"></i>
                        <p class="text-gray-500 text-lg text-center px-4">No images or videos available for this plant visit</p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Description Content -->
            <?php if (!empty($visit['description'])): ?>
                <div class="mb-8">
                    <div class="text-gray-800 text-lg leading-relaxed font-serif text-left break-words whitespace-normal">
                        <?php echo nl2br(htmlspecialchars($visit['description'])); ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Images Carousel at Bottom (only show if there are videos) -->
            <?php if (!empty($videos) && !empty($images)): ?>
                <div class="mt-8">
                    <h3 class="text-2xl font-bold text-blue-900 mb-6 text-center">Photo Gallery</h3>
                    <div class="carousel-container" id="galleryCarousel">
                        <?php if (count($images) > 1): ?>
                            <div class="carousel-counter">
                                <span id="galleryCurrentSlide">1</span> / <span id="galleryTotalSlides"><?php echo count($images); ?></span>
                            </div>
                        <?php endif; ?>

                        <div class="carousel-track" id="galleryCarouselTrack">
                            <?php
                            foreach ($images as $index => $image):
                            ?>
                                <div class="carousel-slide <?php echo $index === 0 ? 'active' : ''; ?>"
                                    data-index="<?php echo $index; ?>">
                                    <img src="assets/img/plant_visit/<?php echo htmlspecialchars($image['image']); ?>"
                                        alt="<?php echo htmlspecialchars($visit['title']); ?>"
                                        loading="lazy"
                                        onclick="openFullscreen('assets/img/plant_visit/<?php echo htmlspecialchars($image['image']); ?>', 'image')"
                                        onerror="this.onerror=null; this.src='assets/img/placeholder.png'; this.style.opacity='0.7';">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Navigation Controls (Below Gallery) -->
                    <?php if (count($images) > 1): ?>
                        <div class="flex items-center justify-center mt-4 space-x-4">
                            <!-- Previous Button -->
                            <button class="carousel-nav prev" onclick="galleryPrevSlide()">
                                <i class="fas fa-chevron-left"></i>
                            </button>

                            <!-- Dots Navigation -->
                            <div class="carousel-dots" id="galleryCarouselDots">
                                <?php for ($i = 0; $i < count($images); $i++): ?>
                                    <div class="carousel-dot <?php echo $i === 0 ? 'active' : ''; ?>"
                                        onclick="galleryGoToSlide(<?php echo $i; ?>)"></div>
                                <?php endfor; ?>
                            </div>

                            <!-- Next Button -->
                            <button class="carousel-nav next" onclick="galleryNextSlide()">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="text-center text-gray-500 text-xl py-20">Plant visit not found.</div>
        <?php endif; ?>
    </div>

    <!-- Fullscreen Modal -->
    <div id="fullscreenModal" class="fullscreen-modal">
        <span class="fullscreen-close" onclick="closeFullscreen()">&times;</span>
        <div class="fullscreen-content" id="fullscreenContent"></div>
    </div>

    <!-- Footer -->
    <?php include 'footer.php'; ?>

    <!-- AOS JavaScript -->
    <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            once: true,
        });
    </script>

    <script>
        // Carousel functionality with autoplay
        let videoCurrentSlide = 0;
        let mainImageCurrentSlide = 0;
        let galleryCurrentSlide = 0;

        // Autoplay intervals
        let mainImageAutoPlayInterval = null;
        let galleryAutoPlayInterval = null;

        // Move these inside DOMContentLoaded to ensure elements exist
        let videoSlides, mainImageSlides, gallerySlides;
        let videoDots, mainImageDots, galleryDots;

        // Video carousel functions
        function videoGoToSlide(index) {
            if (!videoSlides || index < 0 || index >= videoSlides.length) return;
            videoCurrentSlide = index;
            updateVideoCarousel();
        }

        function videoNextSlide() {
            if (!videoSlides) return;
            videoGoToSlide((videoCurrentSlide + 1) % videoSlides.length);
        }

        function videoPrevSlide() {
            if (!videoSlides) return;
            videoGoToSlide(videoCurrentSlide === 0 ? videoSlides.length - 1 : videoCurrentSlide - 1);
        }

        function updateVideoCarousel() {
            const track = document.getElementById('videoCarouselTrack');
            if (track && videoSlides) {
                track.style.transform = `translateX(-${videoCurrentSlide * 100}%)`;
            }
            // Update dots
            if (videoDots) {
                videoDots.forEach((dot, index) => {
                    dot.classList.toggle('active', index === videoCurrentSlide);
                });
            }
            // Update counter
            const currentSlideElement = document.getElementById('videoCurrentSlide');
            if (currentSlideElement) {
                currentSlideElement.textContent = videoCurrentSlide + 1;
            }
        }

        // Main Image carousel functions (when no videos)
        function mainImageGoToSlide(index) {
            if (!mainImageSlides || index < 0 || index >= mainImageSlides.length) return;
            mainImageCurrentSlide = index;
            updateMainImageCarousel();
        }

        function mainImageNextSlide() {
            if (!mainImageSlides) return;
            mainImageGoToSlide((mainImageCurrentSlide + 1) % mainImageSlides.length);
        }

        function mainImagePrevSlide() {
            if (!mainImageSlides) return;
            mainImageGoToSlide(mainImageCurrentSlide === 0 ? mainImageSlides.length - 1 : mainImageCurrentSlide - 1);
        }

        function updateMainImageCarousel() {
            const track = document.getElementById('mainImageCarouselTrack');
            if (track && mainImageSlides) {
                track.style.transform = `translateX(-${mainImageCurrentSlide * 100}%)`;
            }
            // Update dots
            if (mainImageDots) {
                mainImageDots.forEach((dot, index) => {
                    dot.classList.toggle('active', index === mainImageCurrentSlide);
                });
            }
            // Update counter
            const currentSlideElement = document.getElementById('mainImageCurrentSlide');
            if (currentSlideElement) {
                currentSlideElement.textContent = mainImageCurrentSlide + 1;
            }
        }

        // Main Image autoplay functions
        function startMainImageAutoPlay() {
            if (mainImageAutoPlayInterval) {
                clearInterval(mainImageAutoPlayInterval);
            }
            mainImageAutoPlayInterval = setInterval(() => {
                mainImageNextSlide();
            }, 5000); // Auto-advance every 5 seconds
        }

        function stopMainImageAutoPlay() {
            if (mainImageAutoPlayInterval) {
                clearInterval(mainImageAutoPlayInterval);
                mainImageAutoPlayInterval = null;
            }
        }

        // Gallery carousel functions (bottom images when videos exist)
        function galleryGoToSlide(index) {
            if (!gallerySlides || index < 0 || index >= gallerySlides.length) return;
            galleryCurrentSlide = index;
            updateGalleryCarousel();
        }

        function galleryNextSlide() {
            if (!gallerySlides) return;
            galleryGoToSlide((galleryCurrentSlide + 1) % gallerySlides.length);
        }

        function galleryPrevSlide() {
            if (!gallerySlides) return;
            galleryGoToSlide(galleryCurrentSlide === 0 ? gallerySlides.length - 1 : galleryCurrentSlide - 1);
        }

        function updateGalleryCarousel() {
            const track = document.getElementById('galleryCarouselTrack');
            if (track && gallerySlides) {
                track.style.transform = `translateX(-${galleryCurrentSlide * 100}%)`;
            }
            // Update dots
            if (galleryDots) {
                galleryDots.forEach((dot, index) => {
                    dot.classList.toggle('active', index === galleryCurrentSlide);
                });
            }
            // Update counter
            const currentSlideElement = document.getElementById('galleryCurrentSlide');
            if (currentSlideElement) {
                currentSlideElement.textContent = galleryCurrentSlide + 1;
            }
        }

        // Gallery autoplay functions
        function startGalleryAutoPlay() {
            if (galleryAutoPlayInterval) {
                clearInterval(galleryAutoPlayInterval);
            }
            galleryAutoPlayInterval = setInterval(() => {
                galleryNextSlide();
            }, 5000); // Auto-advance every 5 seconds
        }

        function stopGalleryAutoPlay() {
            if (galleryAutoPlayInterval) {
                clearInterval(galleryAutoPlayInterval);
                galleryAutoPlayInterval = null;
            }
        }

        // Fullscreen functionality
        function openFullscreen(src, type) {
            const modal = document.getElementById('fullscreenModal');
            const content = document.getElementById('fullscreenContent');

            if (type === 'image') {
                content.innerHTML = `<img src="${src}" alt="Fullscreen view">`;
            } else if (type === 'video') {
                content.innerHTML = `<video controls autoplay><source src="${src}" type="video/mp4"></video>`;
            }

            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function closeFullscreen() {
            const modal = document.getElementById('fullscreenModal');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';

            // Stop any playing videos
            const video = modal.querySelector('video');
            if (video) {
                video.pause();
            }
        }

        // Close fullscreen when clicking outside
        document.getElementById('fullscreenModal').addEventListener('click', (e) => {
            if (e.target.id === 'fullscreenModal') {
                closeFullscreen();
            }
        });

        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeFullscreen();
            }
        });

        // Initialize carousels and autoplay when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Query elements after DOM is loaded
            videoSlides = document.querySelectorAll('#videoCarouselTrack .carousel-slide');
            mainImageSlides = document.querySelectorAll('#mainImageCarouselTrack .carousel-slide');
            gallerySlides = document.querySelectorAll('#galleryCarouselTrack .carousel-slide');
            videoDots = document.querySelectorAll('#videoCarouselDots .carousel-dot');
            mainImageDots = document.querySelectorAll('#mainImageCarouselDots .carousel-dot');
            galleryDots = document.querySelectorAll('#galleryCarouselDots .carousel-dot');

            // Initialize carousels to first slide
            updateVideoCarousel();
            updateMainImageCarousel();
            updateGalleryCarousel();

            // Start autoplay for main image carousel if it exists and has multiple slides
            const mainImageCarousel = document.getElementById('mainImageCarousel');
            if (mainImageCarousel && mainImageSlides.length > 1) {
                startMainImageAutoPlay();

                // Pause autoplay on hover
                mainImageCarousel.addEventListener('mouseenter', stopMainImageAutoPlay);
                mainImageCarousel.addEventListener('mouseleave', startMainImageAutoPlay);
            }

            // Start autoplay for gallery carousel if it exists and has multiple slides
            const galleryCarousel = document.getElementById('galleryCarousel');
            if (galleryCarousel && gallerySlides.length > 1) {
                startGalleryAutoPlay();

                // Pause autoplay on hover
                galleryCarousel.addEventListener('mouseenter', stopGalleryAutoPlay);
                galleryCarousel.addEventListener('mouseleave', startGalleryAutoPlay);
            }
        });

        // Cleanup intervals when page is unloaded
        window.addEventListener('beforeunload', function() {
            stopMainImageAutoPlay();
            stopGalleryAutoPlay();
        });
    </script>
</body>

</html>