<?php
require_once 'page_config.php';
$config = get_page_config('news_events');

// Include database connection
require_once 'includes/db_connection.php';

// Include multimedia helper for images
require_once __DIR__ . '/admin/includes/news_events_multimedia.php';
$multimedia = new NewsEventsMultimedia($conn);

// Fetch all news (type = 'news') with batch information
$sql = "SELECT *, COALESCE(batch, 1) as batch FROM news_events WHERE type = 'news' ORDER BY batch ASC, date DESC";
$result = $conn->query($sql);
$news = [];
$testimonial_groups = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $news[] = $row;
        
        // Group testimonials by batch number
        if (!isset($testimonial_groups[$row['batch']])) {
            $testimonial_groups[$row['batch']] = [
                'name' => getTestimonialGroupName($row['batch']),
                'items' => []
            ];
        }
        $testimonial_groups[$row['batch']]['items'][] = $row;
    }
}

// Function to get testimonial group names
function getTestimonialGroupName($batch) {
    $group_names = [
        1 => 'Customer Success Stories',
        2 => 'Industry Recognition',
        3 => 'Partnership Testimonials',
        4 => 'Innovation Highlights',
        5 => 'Quality Excellence',
        6 => 'Sustainability Impact',
        7 => 'Community Engagement',
        8 => 'Technical Achievements',
        9 => 'Global Expansion',
        10 => 'Future Vision'
    ];
    
    return isset($group_names[$batch]) ? $group_names[$batch] : "Testimonial Group $batch";
}

// Fetch all events (type = 'event') with batch information
$event_sql = "SELECT *, COALESCE(batch, 1) as batch FROM news_events WHERE type = 'event' ORDER BY date DESC";
$event_result = $conn->query($event_sql);
$events = [];
if ($event_result && $event_result->num_rows > 0) {
    while ($row = $event_result->fetch_assoc()) {
        $events[] = $row;
    }
}
// Debug: Check if events exist
error_log("Events found: " . count($events));

// Fetch all videos & promotions
$videos_sql = "SELECT * FROM videos_promotions ORDER BY created_at DESC";
$videos_result = $conn->query($videos_sql);
$videos_promotions = [];
if ($videos_result && $videos_result->num_rows > 0) {
    while ($row = $videos_result->fetch_assoc()) {
        $videos_promotions[] = $row;
    }
}
// Debug: Check if videos exist
error_log("Videos & Promotions found: " . count($videos_promotions));

// Fetch all plant visits
$plant_visits_sql = "SELECT * FROM plant_visits ORDER BY created_at DESC";
$plant_visits_result = $conn->query($plant_visits_sql);
$plant_visits = [];
if ($plant_visits_result && $plant_visits_result->num_rows > 0) {
    while ($row = $plant_visits_result->fetch_assoc()) {
        $plant_visits[] = $row;
    }
}
// Debug: Check if plant visits exist
error_log("Plant Visits found: " . count($plant_visits));

// Fetch the latest featured item
$banner_sql = "SELECT * FROM news_events WHERE show_in_banner = 1 ORDER BY date DESC LIMIT 1";
$banner_result = $conn->query($banner_sql);
$featured = null;
if ($banner_result && $banner_result->num_rows > 0) {
    $featured = $banner_result->fetch_assoc();
}

// Fetch the active headline article
$headline_sql = "SELECT * FROM headline_articles WHERE is_active = 1 ORDER BY date DESC LIMIT 1";
$headline_result = $conn->query($headline_sql);
$headline_article = null;
if ($headline_result && $headline_result->num_rows > 0) {
    $headline_article = $headline_result->fetch_assoc();
}

// Combine all content for "All" filter
$all_content = array_merge($news, $events, $videos_promotions, $plant_visits);
usort($all_content, function ($a, $b) {
    $date_a = isset($a['date']) ? $a['date'] : $a['created_at'];
    $date_b = isset($b['date']) ? $b['date'] : $b['created_at'];
    return strtotime($date_b) - strtotime($date_a);
});
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News & Events | James Polymers - High Performance Polymer Solutions</title>

    <!--Tab Icon-->
    <link rel="icon" type="image/png" href="assets/img/tab_icon.png">

    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        <?php include 'includes/css/news_events.css'; ?>
    </style>
</head>

<body class="bg-gradient-to-br from-gray-50 to-blue-50 min-h-screen">
    <?php include 'header.php'; ?>

    <!-- Hero Section -->
    <section
        data-aos="fade-down"
        class="relative bg-blue-400 h-96 flex items-center justify-center bg-cover bg-center"
        style="background-image: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('https://www.james-polymers.com/wp-content/uploads/2021/09/products-banner.jpg')">
        <!-- Inclined overlay image -->
        <img
            src="assets/img/banners/news_events_banner.jpg"
            alt="Inclined Overlay"
            class="absolute inset-0 w-full h-full object-cover"
            style="mix-blend-mode: multiply; opacity: 1;" />

        <div class="container mx-auto px-4 text-center text-white relative z-10">
            <h1 class="text-4xl md:text-5xl font-bold mb-4"><?php echo $config['header_title']; ?></h1>
            <p class="text-xl md:text-2xl text-white font-light max-w-3xl mx-auto leading-relaxed mb-4">
                Stay updated with the latest news, achievements, events, videos, and plant visits from JPMC
            </p>
            <div class="flex justify-center items-center text-sm md:text-base">
                <a href="./index.php" class="text-white hover:text-blue-300">Home</a>
                <span class="mx-2">/</span>
                <span class="text-blue-300">News & Events</span>
            </div>
        </div>
    </section>

    <!-- Headline Article Section -->
    <?php if ($headline_article): ?>
        <section class="relative py-16 bg-white">
            <div class="container mx-auto px-6">
                <div class="mb-12 text-center">
                    <h2 class="text-4xl font-bold mb-4" style="color: #1484c0;">Latest Headlines</h2>
                    <p class="text-gray-600 text-lg max-w-2xl mx-auto">Stay informed with our most important news and announcements</p>
                </div>

                <!-- Headline Article -->
                <div class="group cursor-pointer card-hover mb-16 headline-article">
                    <div class="relative overflow-hidden rounded-2xl shadow-2xl">
                        <img
                            src="<?php echo htmlspecialchars($headline_article['image_path']); ?>"
                            alt="<?php echo htmlspecialchars($headline_article['title']); ?>"
                            class="w-full h-96 md:h-[500px] object-cover transform group-hover:scale-105 transition-transform duration-700">
                        <!-- Gradient overlay for better text readability -->
                        <div class="absolute inset-0 headline-gradient-overlay"></div>

                        <!-- Featured badge -->
                        <div class="absolute top-6 left-6">
                            <span class="px-4 py-2 bg-red-500 text-white text-sm font-bold rounded-full shadow-lg headline-badge">
                                <i class="fas fa-star mr-2"></i>BREAKING NEWS
                            </span>
                        </div>

                        <!-- Date badge -->
                        <div class="absolute top-6 right-6">
                            <span class="px-4 py-2 bg-white/90 text-gray-800 text-sm font-semibold rounded-full shadow-lg headline-badge">
                                <i class="fas fa-calendar mr-2"></i><?php echo date('F j, Y', strtotime($headline_article['date'])); ?>
                            </span>
                        </div>

                        <!-- Content overlay -->
                        <div class="absolute bottom-0 left-0 w-full p-8 md:p-12 headline-content" style="color: #1484c0;">
                            <div class="max-w-4xl">
                                <h3 class="text-white/90 font-black text-3xl md:text-5xl lg:text-6xl leading-tight mb-6">
                                    <?php echo htmlspecialchars($headline_article['title']); ?>
                                </h3>
                                <p class="text-white/90 text-lg md:text-xl leading-relaxed mb-8 max-w-5xl">
                                    <?php echo htmlspecialchars($headline_article['description']); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php if (empty($all_content)): ?>
        <!-- Empty State with Enhanced Design -->
        <div class="relative z-10 py-20">
            <div class="container mx-auto px-6">
                <!-- Main Content: Enhanced Badge Layout -->
                <div class="flex flex-col lg:flex-row justify-center items-center gap-16 mb-16">
                    <!-- Left Badge -->
                    <div class="slide-in-left">
                        <div class="relative group">
                            <div class="absolute inset-0 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full blur-xl opacity-30 group-hover:opacity-50 transition-opacity duration-500"></div>
                            <img
                                src="<?php echo $config['left_badge']; ?>"
                                alt="Best Award Badge"
                                class="relative w-80 h-80 object-cover rounded-full shadow-2xl floating-badge" />
                        </div>
                    </div>

                    <!-- Coming Soon Section -->
                    <div class="fade-in">
                        <div class="text-center">
                            <div class="relative inline-block">
                                <div class="absolute inset-0 bg-gradient-to-r from-yellow-400 to-orange-500 rounded-2xl blur-lg opacity-30 pulse-glow"></div>
                                <img src="<?php echo $config['coming_soon']; ?>" alt="Coming Soon" class="relative w-80 h-80 object-contain" />
                            </div>
                            <div class="mt-8">
                                <h2 class="text-4xl font-bold text-gradient mb-4">Coming Soon</h2>
                                <p class="text-gray-600 text-lg max-w-md mx-auto">
                                    We're preparing something exciting for you. Stay tuned for the latest updates!
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Right Badge -->
                    <div class="slide-in-right">
                        <div class="relative group">
                            <div class="absolute inset-0 bg-gradient-to-r from-green-500 to-teal-600 rounded-full blur-xl opacity-30 group-hover:opacity-50 transition-opacity duration-500"></div>
                            <img
                                src="<?php echo $config['right_badge']; ?>"
                                alt="Best Award Badge"
                                class="relative w-80 h-80 object-cover rounded-full shadow-2xl floating-badge" />
                        </div>
                    </div>
                </div>

                <!-- Enhanced No Content Message -->
                <div class="text-center py-16">
                    <div class="glass-effect rounded-3xl p-12 max-w-2xl mx-auto">
                        <div class="text-6xl mb-6">📰</div>
                        <h3 class="text-3xl font-bold text-gray-800 mb-4">No Content Available</h3>
                        <p class="text-gray-600 text-lg leading-relaxed">
                            We're currently updating our content section. Check back soon for the latest news,
                            events, videos, and plant visits from JPMC.
                        </p>
                        <div class="mt-8">
                            <a href="./index.php" class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-full hover:from-blue-700 hover:to-purple-700 transition-all duration-300 transform hover:scale-105 shadow-lg">
                                <i class="fas fa-home mr-2"></i>
                                Back to Home
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Enhanced Content with Filterable Tabs -->
        <div class="container mx-auto px-6 py-16">
            <!-- Filter Tabs -->
            <div class="mb-12">
                <!-- Content Type Filter Tabs -->
                <div class="flex flex-wrap justify-center gap-4 mb-8">
                    <button class="filter-tab active px-6 py-3 text-lg font-semibold rounded-lg transition-all duration-300" data-filter="news">
                        <i class="fas fa-newspaper mr-2"></i>News
                    </button>
                    <button class="filter-tab px-6 py-3 text-lg font-semibold rounded-lg transition-all duration-300" data-filter="events">
                        <i class="fas fa-calendar-alt mr-2"></i>Events
                    </button>
                    <button class="filter-tab px-6 py-3 text-lg font-semibold rounded-lg transition-all duration-300" data-filter="videos">
                        <i class="fas fa-video mr-2"></i>Videos & Promotion
                    </button>
                    <button class="filter-tab px-6 py-3 text-lg font-semibold rounded-lg transition-all duration-300" data-filter="plant">
                        <i class="fas fa-industry mr-2"></i>Plant Visit
                    </button>
                </div>


        

            <!-- Featured Content -->
            <?php if ($featured): ?>
                <div class="mb-16 fade-in">
                    <h2 class="text-3xl font-bold mb-8 text-center" style="color: #1484c0;">Featured Content</h2>
                    <div class="group cursor-pointer card-hover">
                        <a href="news_detail.php?id=<?php echo $featured['id']; ?>">
                            <div class="relative overflow-hidden rounded-2xl shadow-2xl">
                                <?php
                                $featuredImage = $multimedia->getFeaturedImage($featured['id']);
                                $featuredImagePath = $featuredImage ? $featuredImage['image_path'] : 'assets/img/placeholder.png';
                                ?>
                                <img src="<?php echo $featuredImagePath; ?>" alt="<?php echo htmlspecialchars($featured['title']); ?>" class="w-full h-96 object-cover">
                                <div class="absolute inset-0 featured-overlay"></div>
                                <div class="absolute top-4 left-4">
                                    <span class="content-badge badge-news">Featured</span>
                                </div>
                                <div class="absolute bottom-0 left-0 w-full p-8 z-10">
                                    <div class="flex items-center text-white/80 text-sm mb-4">
                                        <span class="banner-date"><?php echo date('F j, Y', strtotime($featured['date'])); ?></span>
                                        <span class="ml-4 px-3 py-1 bg-red-500 text-white text-xs font-semibold rounded-full">FEATURED</span>
                                    </div>
                                    <h3 class="text-white font-black text-4xl md:text-5xl leading-tight mb-6" style="text-shadow: 0 2px 4px rgba(0,0,0,0.5);">
                                        <?php echo $featured['title']; ?>
                                    </h3>
                                    <div class="w-full">
                                        <div class="inline-flex items-center font-semibold banner-learn-more">
                                            Learn More
                                            <i class="fas fa-arrow-right ml-2"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Content Grid -->
            <div id="content-grid">
                <!-- Batch Content Display -->
                <div id="batch-content" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8" style="display: none;">
                    <!-- Content will be dynamically populated here -->
                </div>

                <!-- News Items with Testimonial Grouping -->
                <div class="news-content" data-type="news">
                    <?php if (empty($news)): ?>
                        <div class="col-span-full text-center py-12">
                            <div class="bg-white rounded-xl shadow-lg p-8">
                                <i class="fas fa-newspaper text-6xl text-gray-300 mb-4"></i>
                                <h3 class="text-2xl font-bold text-gray-700 mb-2">No News Available</h3>
                                <p class="text-gray-500">Check back soon for the latest news and updates.</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($testimonial_groups as $batch_number => $group): ?>
                            <!-- Testimonial Group Header -->
                            <div class="testimonial-group-header mb-6">
                                <div class="bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-600 rounded-xl p-4 text-white shadow-lg relative overflow-hidden">
                                    <div class="absolute inset-0 bg-gradient-to-r from-white/10 to-transparent opacity-20"></div>
                                    <div class="relative z-10">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center">
                                                <div class="bg-white/20 rounded-full p-2 mr-3">
                                                    <i class="fas fa-quote-left text-lg"></i>
                                                </div>
                                                <div>
                                                    <h3 class="text-xl font-bold mb-0">Batch <?php echo $batch_number; ?></h3>
                                                    <p class="text-blue-100 text-sm"><?php echo count($group['items']); ?> <?php echo count($group['items']) !== 1 ? 'stories' : 'story'; ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Testimonial Items Grid -->
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
                                <?php foreach ($group['items'] as $item): ?>
                                    <div class="content-item news-item group cursor-pointer card-hover testimonial-item" data-type="news" data-batch-number="<?php echo htmlspecialchars($item['batch']); ?>" data-batch-name="<?php echo htmlspecialchars($group['name']); ?>">
                                        <a href="news_detail.php?id=<?php echo $item['id']; ?>" class="block">
                                            <div class="bg-white rounded-xl shadow-lg overflow-hidden flex flex-col h-full min-h-[420px] hover:shadow-2xl transition-all duration-300">
                                                <div class="relative">
                                                    <?php
                                                    $itemImage = $multimedia->getFeaturedImage($item['id']);
                                                    $itemImagePath = $itemImage ? $itemImage['image_path'] : 'assets/img/placeholder.png';
                                                    ?>
                                                    <img src="<?php echo $itemImagePath; ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="w-full h-48 object-cover">
                                                    <span class="content-badge badge-news">Batch <?php echo $batch_number; ?></span>
                                                </div>
                                                <div class="p-6 flex flex-col flex-1">
                                                    <div class="flex items-center text-sm text-gray-500 mb-3">
                                                        <i class="fas fa-calendar mr-2"></i>
                                                        <?php echo date('F j, Y', strtotime($item['date'])); ?>
                                                    </div>
                                                    <h4 class="font-bold text-xl leading-tight mb-4 title-underline-link min-h-[56px]" style="color: #1484c0 !important;">
                                                        <?php echo $item['title']; ?>
                                                    </h4>
                                                    <div class="flex-1"></div>
                                                    <div class="w-full mt-2">
                                                        <div class="inline-flex items-center font-semibold learn-more-link">
                                                            Read Story
                                                            <i class="fas fa-arrow-right ml-2"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Events -->
                <div class="events-content grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8" data-type="events" style="display: none;">
                    <?php if (empty($events)): ?>
                        <div class="col-span-full text-center py-12">
                            <div class="bg-white rounded-xl shadow-lg p-8">
                                <i class="fas fa-calendar-alt text-6xl text-gray-300 mb-4"></i>
                                <h3 class="text-2xl font-bold text-gray-700 mb-2">No Events Available</h3>
                                <p class="text-gray-500">Check back soon for upcoming events and activities.</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($events as $item): ?>
                            <div class="content-item event-item group cursor-pointer card-hover" data-type="events" data-batch-number="<?php echo htmlspecialchars($item['batch']); ?>" data-batch-name="Batch <?php echo htmlspecialchars($item['batch']); ?> Events">
                                <a href="news_detail.php?id=<?php echo $item['id']; ?>" class="block">
                                    <div class="bg-white rounded-xl shadow-lg overflow-hidden flex flex-col h-full min-h-[420px]">
                                        <div class="relative">
                                            <?php
                                            $itemImage = $multimedia->getFeaturedImage($item['id']);
                                            $itemImagePath = $itemImage ? $itemImage['image_path'] : 'assets/img/placeholder.png';
                                            ?>
                                            <img src="<?php echo $itemImagePath; ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="w-full h-48 object-cover">
                                            <span class="content-badge badge-event">Event</span>
                                        </div>
                                        <div class="p-6 flex flex-col flex-1">
                                            <div class="flex items-center text-sm text-gray-500 mb-3">
                                                <i class="fas fa-calendar mr-2"></i>
                                                <?php echo date('F j, Y', strtotime($item['date'])); ?>
                                            </div>
                                            <h4 class="font-bold text-xl leading-tight mb-4 title-underline-link min-h-[56px]" style="color: #1484c0 !important;">
                                                <?php echo $item['title']; ?>
                                            </h4>
                                            <div class="flex-1"></div>
                                            <div class="w-full mt-2">
                                                <div class="inline-flex items-center font-semibold learn-more-link">
                                                    Learn More
                                                    <i class="fas fa-arrow-right ml-2"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Videos & Promotions -->
                <div class="videos-content grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8" data-type="videos" style="display: none;">
                    <?php if (empty($videos_promotions)): ?>
                        <div class="col-span-full text-center py-12">
                            <div class="bg-white rounded-xl shadow-lg p-8">
                                <i class="fas fa-video text-6xl text-gray-300 mb-4"></i>
                                <h3 class="text-2xl font-bold text-gray-700 mb-2">No Videos & Promotions Available</h3>
                                <p class="text-gray-500">Check back soon for new videos and promotional content.</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($videos_promotions as $item): ?>
                            <div class="content-item video-item group cursor-pointer card-hover" data-type="videos" data-batch-number="general" data-batch-name="General Videos">
                                <div class="bg-white rounded-xl shadow-lg overflow-hidden flex flex-col h-full min-h-[420px]">
                                    <div class="relative">
                                        <?php
                                        // Get first image from videos_promotion_images
                                        $image_sql = "SELECT image_url FROM videos_promotion_images WHERE videos_promotion_id = ? ORDER BY display_order ASC LIMIT 1";
                                        $image_stmt = $conn->prepare($image_sql);
                                        $image_stmt->bind_param("i", $item['id']);
                                        $image_stmt->execute();
                                        $image_result = $image_stmt->get_result();
                                        $itemImagePath = 'assets/img/placeholder.png';
                                        if ($image_result && $image_result->num_rows > 0) {
                                            $image_row = $image_result->fetch_assoc();
                                            $itemImagePath = $image_row['image_url'];
                                        }
                                        ?>
                                        <img src="<?php echo $itemImagePath; ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="w-full h-48 object-cover">
                                        <span class="content-badge badge-video"><?php echo ucfirst($item['type']); ?></span>
                                        <div class="absolute inset-0 bg-black bg-opacity-40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                            <i class="fas fa-play text-white text-4xl"></i>
                                        </div>
                                    </div>
                                    <div class="p-6 flex flex-col flex-1">
                                        <div class="flex items-center text-sm text-gray-500 mb-3">
                                            <i class="fas fa-clock mr-2"></i>
                                            <?php echo date('F j, Y', strtotime($item['created_at'])); ?>
                                        </div>
                                        <h4 class="font-bold text-xl leading-tight mb-2 title-underline-link min-h-[56px]" style="color: #1484c0 !important;">
                                            <?php echo $item['title']; ?>
                                        </h4>
                                        <p class="text-gray-600 text-sm mb-4 line-clamp-3 min-h-[48px] flex items-center"><?php echo htmlspecialchars($item['description']); ?></p>
                                        <div class="flex-1"></div>
                                        <div class="w-full mt-2">
                                            <button class="inline-flex items-center font-semibold learn-more-link video-detail-btn" data-video-id="<?php echo $item['id']; ?>">
                                                Watch Now
                                                <i class="fas fa-arrow-right ml-2"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Plant Visits -->
                <div class="plant-content grid grid-cols-1 md:grid-cols-2 gap-8" data-type="plant" style="display: none;">
                    <?php if (empty($plant_visits)): ?>
                        <div class="col-span-full text-center py-12">
                            <div class="bg-white rounded-xl shadow-lg p-8">
                                <i class="fas fa-industry text-6xl text-gray-300 mb-4"></i>
                                <h3 class="text-2xl font-bold text-gray-700 mb-2">No Plant Visits Available</h3>
                                <p class="text-gray-500">Check back soon for plant visit updates and tours.</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($plant_visits as $item): ?>
                            <div class="content-item plant-item group cursor-pointer card-hover" data-type="plant" data-batch-number="general" data-batch-name="General Plant Visits">
                                <div class="bg-white rounded-xl shadow-lg overflow-hidden flex flex-col h-full min-h-[420px]">
                                    <div class="relative">
                                        <?php
                                        // Get first image from plant_visit_images
                                        $image_sql = "SELECT image FROM plant_visit_images WHERE plant_visit_id = ? AND media_type = 'image' ORDER BY display_order ASC LIMIT 1";
                                        $image_stmt = $conn->prepare($image_sql);
                                        $image_stmt->bind_param("i", $item['id']);
                                        $image_stmt->execute();
                                        $image_result = $image_stmt->get_result();
                                        $itemImagePath = 'assets/img/placeholder.png';
                                        if ($image_result && $image_result->num_rows > 0) {
                                            $image_row = $image_result->fetch_assoc();
                                            $itemImagePath = 'assets/img/plant_visit/' . $image_row['image'];
                                        }
                                        ?>
                                        <img src="<?php echo $itemImagePath; ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="w-full h-48 object-cover">
                                        <span class="content-badge badge-plant">Plant Visit</span>
                                    </div>
                                    <div class="p-6 flex flex-col flex-1">
                                        <div class="flex items-center text-sm text-gray-500 mb-3">
                                            <i class="fas fa-industry mr-2"></i>
                                            <?php echo date('F j, Y', strtotime($item['created_at'])); ?>
                                        </div>
                                        <h4 class="font-bold text-xl leading-tight mb-2 title-underline-link min-h-[56px]" style="color: #1484c0 !important;">
                                            <?php echo $item['title']; ?>
                                        </h4>
                                        <p class="text-gray-600 text-sm mb-4 line-clamp-3 min-h-[48px] flex items-center"><?php echo htmlspecialchars($item['description']); ?></p>
                                        <div class="flex-1"></div>
                                        <div class="w-full mt-2">
                                            <button class="inline-flex items-center font-semibold learn-more-link plant-detail-btn" data-plant-id="<?php echo $item['id']; ?>">
                                                Learn More
                                                <i class="fas fa-arrow-right ml-2"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Load More Button -->
            <div class="text-center mt-12">
                <!-- <button class="load-more-btn px-8 py-4 text-white font-semibold rounded-full text-lg">
                <i class="fas fa-plus mr-2"></i>Load More Content
            </button> -->
            </div>
        </div>
    <?php endif; ?>

    <!-- Videos & Promotions Detail Modal -->
    <div id="video-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between p-6 border-b">
                    <h3 class="text-2xl font-bold text-gray-800">Video & Promotion Details</h3>
                    <button id="close-video-modal" class="text-gray-500 hover:text-gray-700 text-2xl font-bold focus:outline-none">&times;</button>
                </div>
                <div id="video-modal-content" class="p-6">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <script src="includes/javascript/news_events.js"></script>
    
    <!-- Additional inline script for tab switching -->
    <script>
        // Simple tab switching function
        function switchTab(filterType) {
            console.log('Switching to:', filterType);
            
            // Hide all content sections
            const sections = document.querySelectorAll('.news-content, .events-content, .videos-content, .plant-content');
            sections.forEach(section => {
                section.style.display = 'none';
            });
            
            // Show selected section
            const targetSection = document.querySelector('.' + filterType + '-content');
            if (targetSection) {
                if (filterType === 'news') {
                    targetSection.style.display = 'block';
                } else {
                    targetSection.style.display = 'grid';
                }
                console.log('Showing section:', filterType, 'with display:', targetSection.style.display);
            }
            
            // Update tab styles
            const tabs = document.querySelectorAll('.filter-tab');
            tabs.forEach(tab => {
                tab.classList.remove('active');
                tab.style.backgroundColor = '';
                tab.style.color = '';
            });
            
            const activeTab = document.querySelector('.filter-tab[data-filter="' + filterType + '"]');
            if (activeTab) {
                activeTab.classList.add('active');
                activeTab.style.backgroundColor = '#1484c0';
                activeTab.style.color = 'white';
            }
        }
        
        // Initialize tabs when page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Set up click handlers for all tabs
            const tabs = document.querySelectorAll('.filter-tab');
            tabs.forEach(tab => {
                tab.addEventListener('click', function(e) {
                    e.preventDefault();
                    const filterType = this.getAttribute('data-filter');
                    switchTab(filterType);
                });
            });
            
            // Initialize with news content
            setTimeout(() => {
                switchTab('news');
            }, 100);
        });
    </script>
    

    <!-- Footer -->
    <?php include 'footer.php'; ?>
    <?php include 'chatbot.php'; ?>
</body>

</html>