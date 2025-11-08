<?php
// Enable error reporting for debugging (remove or comment out in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'page_config.php';
$config = get_page_config('videos_promotion');

// Include database connection
require_once 'includes/db_connection.php';

// Fetch videos and promotions with their additional images
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

        // Combine all images and remove duplicates
        $all_images = array_merge($row['multiple_images'], $row['additional_images']);
        $row['all_images'] = array_unique($all_images);

        $items[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Videos & Promotion | James Polymers - High Performance Polymer Solutions</title>

    <!--Tab Icon-->
    <link rel="icon" type="image/png" href="assets/img/tab_icon.png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .dropdown:hover .dropdown-menu {
            display: block;
        }

        body {
            background: #ffffff !important;
            font-family: 'Inter', 'Segoe UI', sans-serif;
            color: #1f2937;
        }

        .page-header {
            background: linear-gradient(90deg, #0066cc 0%, #004d99 100%);
            border-bottom: 4px solid #ff3b30;
        }

        .filter-bar {
            background-color: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
            position: sticky;
            top: 0;
            z-index: 40;
        }

        .filter-button {
            padding: 0.5rem 1.25rem;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.2s;
            background: white;
            border: 1px solid #e5e7eb;
        }

        .filter-button:hover {
            background: #f3f4f6;
        }

        .filter-button.active {
            background: #0066cc;
            color: white;
            border-color: #0066cc;
        }

        .content-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .content-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        .card-media {
            width: 100%;
            aspect-ratio: 16/9;
            object-fit: cover;
            background: #f3f3f3;
        }

        .card-tag {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            font-weight: 600;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            display: inline-block;
        }

        .tag-video {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .tag-promotion {
            background-color: #dcfce7;
            color: #166534;
        }

        .featured-item {
            background: white;
            border-radius: 1.5rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            overflow: hidden;
        }

        .featured-media img,
        .featured-media video,
        .featured-media iframe {
            border-radius: 1rem;
            width: 100%;
            aspect-ratio: 16/9;
            object-fit: cover;
        }

        .featured-content h2 {
            font-size: 2rem;
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 0.75rem;
        }

        .featured-badge {
            font-size: 0.875rem;
            font-weight: 600;
            padding: 0.35rem 1rem;
            border-radius: 9999px;
            display: inline-block;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .featured-content p {
            font-size: 1.125rem;
            color: #4b5563;
            line-height: 1.7;
            margin-bottom: 1.5rem;
        }

        .social-share a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 9999px;
            background-color: #f3f4f6;
            color: #0066cc;
            margin-right: 0.75rem;
            transition: all 0.2s;
        }

        .social-share a:hover {
            background-color: #0066cc;
            color: white;
            transform: translateY(-2px);
        }

        .watch-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: #0066cc;
            color: white;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            transition: all 0.2s;
        }

        .watch-button:hover {
            background: #004d99;
            transform: translateY(-2px);
        }

        .chat-bubble {
            display: flex;
            align-items: center;
            justify-content: center;
            background: #0066cc;
            color: white;
            width: 60px;
            height: 60px;
            border-radius: 9999px;
            box-shadow: 0 10px 15px -3px rgba(0, 102, 204, 0.3);
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            z-index: 50;
            transition: transform 0.3s;
        }

        .chat-bubble:hover {
            transform: scale(1.1);
        }

        .section-title {
            position: relative;
            display: inline-block;
            font-weight: 700;
            font-size: 1.5rem;
            margin-bottom: 2rem;
        }

        .section-title::after {
            content: "";
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 80px;
            height: 4px;
            background: #ff3b30;
            border-radius: 2px;
        }

        .image-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(80px, 1fr));
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .gallery-image {
            width: 100%;
            aspect-ratio: 1;
            object-fit: cover;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .gallery-image:hover {
            transform: scale(1.05);
        }

        .gallery-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            padding: 20px;
            box-sizing: border-box;
        }

        .gallery-modal {
            max-width: 90vw;
            max-height: 90vh;
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            height: 100%;
        }

        .gallery-modal img {
            max-width: 90vw;
            max-height: 90vh;
            width: auto;
            height: auto;
            object-fit: contain;
            border-radius: 0.5rem;
            display: block;
        }

        .gallery-close {
            position: absolute;
            top: -40px;
            right: 0;
            color: white;
            font-size: 2rem;
            cursor: pointer;
            background: none;
            border: none;
        }

        .gallery-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            padding: 1rem 0.5rem;
            cursor: pointer;
            border-radius: 0.25rem;
            transition: background 0.2s;
            z-index: 10;
        }

        .gallery-nav:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .gallery-prev {
            left: -60px;
        }

        .gallery-next {
            right: -60px;
        }

        @media (max-width: 768px) {
            .gallery-prev {
                left: 10px;
            }

            .gallery-next {
                right: 10px;
            }

            .gallery-nav {
                padding: 0.75rem 0.25rem;
                font-size: 0.875rem;
            }
        }

        .carousel-container {
            position: relative;
            overflow: hidden;
            border-radius: 0.5rem;
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
            background: rgba(0, 0, 0, 0.5);
            color: white;
            border: none;
            padding: 0.5rem;
            cursor: pointer;
            border-radius: 50%;
            z-index: 10;
            transition: background 0.2s;
        }

        .carousel-nav:hover {
            background: rgba(0, 0, 0, 0.7);
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
            transition: background 0.2s;
        }

        .carousel-indicator.active {
            background: white;
        }

        .description-fixed {
            height: 4.5rem;
            /* 72px - 3 lines of text */
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
        }

        .description-fixed:hover {
            background: linear-gradient(135deg, #f1f5f9 0%, #dbeafe 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .description-fixed::-webkit-scrollbar {
            width: 6px;
        }

        .description-fixed::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 3px;
        }

        .description-fixed::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 3px;
        }

        .description-fixed::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .featured-description {
            min-height: 10rem;
            /* 160px - for featured item */
            max-height: 12rem;
            /* 192px - maximum height */
            overflow-y: auto;
            overflow-x: hidden;
            line-height: 1.5;
            word-wrap: break-word;
            word-break: break-word;
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border-radius: 0.75rem;
            padding: 1rem;
            border: 1px solid #bfdbfe;
            transition: all 0.3s ease;
            scrollbar-width: thin;
            scrollbar-color: #93c5fd #dbeafe;
        }

        .featured-description:hover {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.15);
        }

        .featured-description::-webkit-scrollbar {
            width: 8px;
        }

        .featured-description::-webkit-scrollbar-track {
            background: #dbeafe;
            border-radius: 4px;
        }

        .featured-description::-webkit-scrollbar-thumb {
            background: #93c5fd;
            border-radius: 4px;
        }

        .featured-description::-webkit-scrollbar-thumb:hover {
            background: #60a5fa;
        }

        .card-description {
            height: 6rem;
            /* 96px - for cards */
            overflow-y: auto;
            overflow-x: hidden;
            line-height: 1.5;
            word-wrap: break-word;
            word-break: break-word;
            background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
            border-radius: 0.5rem;
            padding: 0.75rem;
            border: 1px solid #e5e7eb;
            transition: all 0.3s ease;
            scrollbar-width: thin;
            scrollbar-color: #d1d5db #f9fafb;
            margin-bottom: 1rem;
        }

        .card-description:hover {
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .card-description::-webkit-scrollbar {
            width: 6px;
        }

        .card-description::-webkit-scrollbar-track {
            background: #f9fafb;
            border-radius: 3px;
        }

        .card-description::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 3px;
        }

        .card-description::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
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

        .description-fixed,
        .featured-description,
        .card-description {
            animation: fadeInUp 0.6s ease-out;
        }

        /* Smooth scroll behavior for descriptions */
        .description-fixed,
        .featured-description,
        .card-description {
            scroll-behavior: smooth;
        }

        /* Focus styles for better accessibility */
        .description-fixed:focus,
        .featured-description:focus,
        .card-description:focus {
            outline: 2px solid #3b82f6;
            outline-offset: 2px;
        }

        .truncate-title {
            display: -webkit-box;
            line-clamp: 2;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            word-break: break-word;
            white-space: normal;
        }
    </style>
</head>

<body>
    <?php include 'header.php'; ?>

    <!-- Chatbot Integration -->
    <?php include 'chatbot.php'; ?>

    <!-- Header Section -->
    <div class="page-header w-full py-8 flex flex-col items-center justify-center" style="background: url('<?php echo $config['header_bg']; ?>') center/cover no-repeat; position: relative;">
        <div class="absolute inset-0" style="background: <?php echo $config['header_overlay']; ?>"></div>
        <div class="container mx-auto px-4 relative z-10">
            <h1 class="text-white text-4xl font-bold mb-2"><?php echo $config['header_title']; ?></h1>
            <p class="text-white text-lg opacity-90 max-w-2xl">Discover our latest promotional content and informative videos about our products and services</p>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar py-3">
        <div class="container mx-auto px-4">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <button class="filter-button active" data-filter="all">All Content</button>
                    <button class="filter-button" data-filter="video">Videos</button>
                    <button class="filter-button" data-filter="promotion">Promotions</button>
                    <button class="filter-button" data-filter="latest">Latest</button>
                </div>
                <div class="relative">
                    <input type="search" placeholder="Search content..." class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-12" style="background: url('<?php echo $config['main_bg']; ?>') center/cover no-repeat;">
        <?php if (empty($items)): ?>
            <!-- Empty State Design -->
            <div class="relative z-10 flex flex-col md:flex-row justify-center items-center py-16 gap-8 md:gap-12" style="min-height: 60vh;">
                <!-- Left Badge -->
                <div class="w-48 md:w-72 h-48 md:h-72">
                    <img
                        src="<?php echo $config['left_badge']; ?>"
                        alt="Best Award Badge"
                        class="w-full h-full object-cover rounded-full shadow-lg" />
                </div>

                <!-- Coming Soon -->
                <div class="flex flex-col items-center justify-center text-center px-4">
                    <img
                        src="<?php echo $config['coming_soon']; ?>"
                        alt="Coming Soon"
                        class="w-48 md:w-72 h-48 md:h-72 object-contain opacity-90 mb-6" />
                    <h2 class="text-3xl font-bold text-gray-800 mb-4">Content Coming Soon!</h2>
                    <p class="text-xl text-gray-600 mb-8 max-w-md">We're working on creating amazing videos and promotions for you. Check back later for updates!</p>
                    <div class="flex flex-col sm:flex-row justify-center gap-4">
                        <a href="/" class="px-6 py-3 bg-primary text-white font-medium rounded-lg hover:bg-secondary transition-colors">
                            <i class="fas fa-home mr-2"></i> Back to Home
                        </a>
                        <a href="#notify" class="px-6 py-3 bg-white text-primary font-medium rounded-lg border border-primary hover:bg-gray-50 transition-colors">
                            <i class="fas fa-bell mr-2"></i> Notify Me
                        </a>
                    </div>
                </div>

                <!-- Right Badge -->
                <div class="w-48 md:w-72 h-48 md:h-72">
                    <img
                        src="<?php echo $config['right_badge']; ?>"
                        alt="Best Award Badge"
                        class="w-full h-full object-cover rounded-full shadow-lg" />
                </div>
            </div>
        <?php else: ?>
            <!-- Featured Content -->
            <?php $featured = $items[0]; ?>
            <div class="featured-item mb-16 overflow-hidden">
                <div class="grid md:grid-cols-2 gap-8">
                    <div class="featured-media p-4">
                        <?php if ($featured['type'] === 'video' && $featured['url']): ?>
                            <?php
                            if (strpos($featured['url'], 'youtube.com') !== false || strpos($featured['url'], 'youtu.be') !== false) {
                                $video_id = '';
                                if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $featured['url'], $match)) {
                                    $video_id = $match[1];
                                }
                            ?>
                                <iframe src="https://www.youtube.com/embed/<?= $video_id ?>" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen class="w-full aspect-video rounded-xl shadow-lg"></iframe>
                            <?php } else { ?>
                                <video controls class="w-full rounded-xl shadow-lg">
                                    <source src="<?= htmlspecialchars($featured['url']) ?>" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                            <?php } ?>
                        <?php elseif ($featured['url']): ?>
                            <img src="<?= htmlspecialchars($featured['url']) ?>" alt="<?= htmlspecialchars($featured['title']) ?>" class="w-full rounded-xl shadow-lg" />
                        <?php else: ?>
                            <!-- Carousel for items without main media -->
                            <?php if (!empty($featured['all_images'])): ?>
                                <div class="carousel-container relative overflow-hidden rounded-xl shadow-lg" style="aspect-ratio: 16/9;">
                                    <?php foreach ($featured['all_images'] as $index => $image): ?>
                                        <div class="carousel-slide <?= $index === 0 ? 'active' : '' ?>" data-index="<?= $index ?>">
                                            <img src="<?= htmlspecialchars($image) ?>" alt="Image <?= $index + 1 ?>" class="w-full h-full object-cover">
                                        </div>
                                    <?php endforeach; ?>

                                    <?php if (count($featured['all_images']) > 1): ?>
                                        <button class="carousel-nav carousel-prev" onclick="changeSlide('featured', -1)">&#10094;</button>
                                        <button class="carousel-nav carousel-next" onclick="changeSlide('featured', 1)">&#10095;</button>
                                        <div class="carousel-indicators">
                                            <?php foreach ($featured['all_images'] as $index => $image): ?>
                                                <div class="carousel-indicator <?= $index === 0 ? 'active' : '' ?>" onclick="goToSlide('featured', <?= $index ?>)"></div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="bg-gray-200 rounded-xl shadow-lg flex items-center justify-center" style="aspect-ratio: 16/9;">
                                    <div class="text-center">
                                        <i class="fas fa-image text-6xl text-gray-400 mb-4"></i>
                                        <p class="text-gray-600">No media available</p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>

                        <!-- Additional Images Gallery for Featured Item -->
                        <?php if (!empty($featured['all_images']) && $featured['url']): ?>
                            <div class="mt-4">
                                <h4 class="text-sm font-semibold text-gray-700 mb-2">Additional Images:</h4>
                                <div class="image-gallery">
                                    <?php foreach (array_slice($featured['all_images'], 0, 6) as $index => $image): ?>
                                        <img src="<?= htmlspecialchars($image) ?>"
                                            alt="Additional Image <?= $index + 1 ?>"
                                            class="gallery-image"
                                            onclick="openGallery(<?= htmlspecialchars(json_encode($featured['all_images'])) ?>, <?= $index ?>)">
                                    <?php endforeach; ?>
                                    <?php if (count($featured['all_images']) > 6): ?>
                                        <div class="gallery-image bg-gray-200 flex items-center justify-center text-gray-500 text-sm cursor-pointer" onclick="openGallery(<?= htmlspecialchars(json_encode($featured['all_images'])) ?>, 6)">
                                            +<?= count($featured['all_images']) - 6 ?> more
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="featured-content p-6 flex flex-col">
                        <div class="flex flex-col">
                            <div class="mb-3">
                                <span class="featured-badge <?= $featured['type'] === 'video' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' ?>">
                                    <?= ucfirst($featured['type']) ?>
                                </span>
                                <span class="text-sm text-gray-500 ml-3"><?= date('F j, Y', strtotime($featured['created_at'])) ?></span>
                            </div>
                            <h2 class="truncate-title"><?= htmlspecialchars($featured['title']) ?></h2>
                            <div class="featured-description flex-grow"><?= htmlspecialchars($featured['description']) ?></div>
                        </div>
                        <div class="flex items-center justify-between mt-auto pt-6">
                            <div class="social-share">
                                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($featured['url']) ?>" target="_blank" aria-label="Share on Facebook">
                                    <i class="fab fa-facebook-f"></i>
                                </a>
                                <a href="https://twitter.com/intent/tweet?url=<?= urlencode($featured['url']) ?>&text=<?= urlencode($featured['title']) ?>" target="_blank" aria-label="Share on Twitter">
                                    <i class="fab fa-twitter"></i>
                                </a>
                                <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?= urlencode($featured['url']) ?>&title=<?= urlencode($featured['title']) ?>" target="_blank" aria-label="Share on LinkedIn">
                                    <i class="fab fa-linkedin-in"></i>
                                </a>
                                <a href="mailto:?subject=<?= urlencode($featured['title']) ?>&body=<?= urlencode($featured['url']) ?>" aria-label="Share via Email">
                                    <i class="fas fa-envelope"></i>
                                </a>
                            </div>
                            <?php if ($featured['type'] === 'video'): ?>
                                <a href="<?= htmlspecialchars($featured['url']) ?>" target="_blank" class="watch-button">
                                    <i class="fas fa-play"></i>
                                    <?= strpos($featured['url'], 'youtube.com') !== false ? 'Watch on YouTube' : 'Watch Video' ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section Title -->
            <h3 class="section-title mb-10">More Content</h3>

            <!-- Content Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach (array_slice($items, 1, 6) as $item): ?>
                    <div class="content-card" data-type="<?= $item['type'] ?>" data-item-id="<?= $item['id'] ?>">
                        <div class="relative">
                            <?php if ($item['type'] === 'video' && $item['url']): ?>
                                <?php
                                if (strpos($item['url'], 'youtube.com') !== false || strpos($item['url'], 'youtu.be') !== false) {
                                    $video_id = '';
                                    if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $item['url'], $match)) {
                                        $video_id = $match[1];
                                    }
                                ?>
                                    <iframe src="https://www.youtube.com/embed/<?= $video_id ?>" allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen class="card-media"></iframe>
                                <?php } else { ?>
                                    <video controls class="card-media">
                                        <source src="<?= htmlspecialchars($item['url']) ?>" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>
                                <?php } ?>
                            <?php elseif ($item['url']): ?>
                                <img src="<?= htmlspecialchars($item['url']) ?>" alt="<?= htmlspecialchars($item['title']) ?>" class="card-media" />
                            <?php else: ?>
                                <!-- Carousel for items without main media -->
                                <?php if (!empty($item['all_images'])): ?>
                                    <div class="carousel-container" style="aspect-ratio: 16/9;">
                                        <?php foreach ($item['all_images'] as $index => $image): ?>
                                            <div class="carousel-slide <?= $index === 0 ? 'active' : '' ?>" data-index="<?= $index ?>">
                                                <img src="<?= htmlspecialchars($image) ?>" alt="Image <?= $index + 1 ?>" class="w-full h-full object-cover">
                                            </div>
                                        <?php endforeach; ?>

                                        <?php if (count($item['all_images']) > 1): ?>
                                            <button class="carousel-nav carousel-prev" onclick="changeSlide(<?= $item['id'] ?>, -1)">&#10094;</button>
                                            <button class="carousel-nav carousel-next" onclick="changeSlide(<?= $item['id'] ?>, 1)">&#10095;</button>
                                            <div class="carousel-indicators">
                                                <?php foreach ($item['all_images'] as $index => $image): ?>
                                                    <div class="carousel-indicator <?= $index === 0 ? 'active' : '' ?>" onclick="goToSlide(<?= $item['id'] ?>, <?= $index ?>)"></div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="bg-gray-200 flex items-center justify-center" style="aspect-ratio: 16/9;">
                                        <div class="text-center">
                                            <i class="fas fa-image text-4xl text-gray-400 mb-2"></i>
                                            <p class="text-sm text-gray-600">No media</p>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                            <div class="absolute top-3 right-3">
                                <span class="card-tag <?= $item['type'] === 'video' ? 'tag-video' : 'tag-promotion' ?>">
                                    <?= ucfirst($item['type']) ?>
                                </span>
                            </div>
                        </div>
                        <div class="p-5">
                            <p class="text-sm text-gray-500 mb-1"><?= date('F j, Y', strtotime($item['created_at'])) ?></p>
                            <h4 class="truncate-title text-xl font-bold mb-3"><?= htmlspecialchars($item['title']) ?></h4>
                            <div class="card-description"><?= htmlspecialchars($item['description']) ?></div>

                            <!-- Additional Images Gallery for Cards -->
                            <?php if (!empty($item['all_images'])): ?>
                                <div class="mb-4">
                                    <div class="image-gallery">
                                        <?php foreach (array_slice($item['all_images'], 0, 4) as $index => $image): ?>
                                            <img src="<?= htmlspecialchars($image) ?>"
                                                alt="Additional Image <?= $index + 1 ?>"
                                                class="gallery-image"
                                                onclick="openGallery(<?= htmlspecialchars(json_encode($item['all_images'])) ?>, <?= $index ?>)">
                                        <?php endforeach; ?>
                                        <?php if (count($item['all_images']) > 4): ?>
                                            <div class="gallery-image bg-gray-200 flex items-center justify-center text-gray-500 text-xs cursor-pointer" onclick="openGallery(<?= htmlspecialchars(json_encode($item['all_images'])) ?>, 4)">
                                                +<?= count($item['all_images']) - 4 ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="flex items-center justify-between">
                                <div class="flex space-x-2">
                                    <a href="#" class="text-primary hover:text-secondary">
                                        <i class="far fa-heart"></i>
                                    </a>
                                    <a href="#" class="text-primary hover:text-secondary">
                                        <i class="far fa-bookmark"></i>
                                    </a>
                                </div>
                                <a href="videos_promotion_details.php?id=<?= $item['id'] ?>" class="text-primary hover:text-secondary font-medium flex items-center">
                                    View details <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <div class="flex justify-center mt-12">
                <nav class="inline-flex rounded-md shadow">
                    <a href="#" class="py-2 px-4 bg-white border border-gray-300 rounded-l-md hover:bg-gray-50">Previous</a>
                    <a href="#" class="py-2 px-4 bg-primary text-white border border-primary">1</a>
                    <a href="#" class="py-2 px-4 bg-white border border-gray-300 hover:bg-gray-50">2</a>
                    <a href="#" class="py-2 px-4 bg-white border border-gray-300 hover:bg-gray-50">3</a>
                    <a href="#" class="py-2 px-4 bg-white border border-gray-300 rounded-r-md hover:bg-gray-50">Next</a>
                </nav>
            </div>

        <?php endif; ?>
    </div>

    <!-- Image Gallery Modal -->
    <div id="galleryModal" class="gallery-overlay">
        <div class="gallery-modal">
            <button class="gallery-close" onclick="closeGallery()">&times;</button>
            <button class="gallery-nav gallery-prev" onclick="changeImage(-1)">&#10094;</button>
            <button class="gallery-nav gallery-next" onclick="changeImage(1)">&#10095;</button>
            <img id="galleryImage" src="" alt="Gallery Image">
        </div>
    </div>

    <!-- Newsletter Section -->
    <section class="bg-gray-100 py-16">
        <div class="container mx-auto px-4">
            <div class="max-w-3xl mx-auto text-center">
                <h3 class="text-2xl font-bold mb-4">Stay Updated</h3>
                <p class="text-gray-600 mb-6">Subscribe to our newsletter to get notified about new videos and promotions</p>
                <form class="flex flex-col sm:flex-row gap-3 max-w-md mx-auto">
                    <input type="email" placeholder="Your email address" class="flex-grow px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                    <button type="submit" class="px-6 py-3 bg-primary text-white font-medium rounded-lg hover:bg-secondary transition-colors whitespace-nowrap">
                        Subscribe
                    </button>
                </form>
            </div>
        </div>
    </section>

    <?php include 'footer.php'; ?>

    <script src="includes/javascript/videos_promotion.js"></script>
</body>

</html>