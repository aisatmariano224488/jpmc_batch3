<?php
// Include database connection
require_once 'includes/db_connection.php';

// Function to get page configuration
function get_page_config($page_name, $conn)
{
    $sql = "SELECT * FROM page_configs WHERE page_name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $page_name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }

    // Return default configuration if page not found
    return array(
        'main_bg' => 'images/sustainability/jpmclogo.png',
        'header_bg' => 'images/overviewprocess/header.png',
        'header_title' => 'OVERVIEW PROCESS',
        'header_overlay' => 'rgba(37,80,200,0.38)',
        'left_badge' => 'images/sustainability/beslogo.png',
        'right_badge' => 'images/sustainability/beslogo.png',
        'coming_soon' => 'images/sustainability/comingsoon.jfif'
    );
}

// Get page configuration
$config = get_page_config('overviewprocess', $conn);

// Fetch overview_process_info data
$overview_heading = '';
$overview_description = '';
$infoResult = $conn->query("SELECT * FROM overview_process_info LIMIT 1");
if ($infoResult && $infoResult->num_rows > 0) {
    $row = $infoResult->fetch_assoc();
    $overview_heading = $row['heading'];
    $overview_description = $row['description'];
}

// Fetch processes grouped by type
$processes = [
    'plastic' => [],
    'rubber' => []
];
$result = $conn->query("SELECT * FROM overview_processes ORDER BY created_at DESC");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $type = $row['process_type'];
        $processes[$type][] = $row;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Overview Process | James Polymers - High Performance Polymer Solutions</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!--Tab Icon-->
    <link rel="icon" type="image/png" href="assets/img/tab_icon.png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

    <style>
        <?php include 'includes/css/overviewprocess.css'; ?>
    </style>
</head>

<body class="bg-gray-100">
    <?php include 'header.php'; ?>

    <img id="floatingLogo" src="assets/img/JP_BG_WATERMARK_CIRCLE.png" alt="JP Watermark"
        class="fixed inset-0 w-full h-full object-cover opacity-20 pointer-events-none select-none transition-opacity duration-500 ease-in-out" />

    <!-- Hero Section -->
    <section
        data-aos="fade-down"
        class="relative bg-blue-400 h-96 flex items-center justify-center bg-cover bg-center"
        style="background-image: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('<?php echo !empty($config['header_bg']) ? $config['header_bg'] : 'assets/img/banners/overview_banner.jpg'; ?>')">
        <!-- Inclined overlay image -->
        <img
            src="assets/img/banners/overview_banner.jpg"
            alt="Inclined Overlay"
            class="absolute inset-0 w-full h-full object-cover"
            style="mix-blend-mode: multiply; opacity: 1;" />

        <div class="container mx-auto px-4 text-center text-white relative z-10">
            <h1 class="text-4xl md:text-5xl font-bold mb-4"><?php echo $config['header_title']; ?></h1>
        </div>
    </section>

    <!-- Dynamic Overview Title & Description -->
    <div data-aos="fade-in" class="w-full my-10 px-12 py-6 text-center">
        <h2 class="text-3xl my-4 font-bold"><?= htmlspecialchars($overview_heading) ?></h2>
        <h3 class="max-w-3xl mx-auto text-lg text-gray-700"><?= nl2br(htmlspecialchars($overview_description)) ?></h3>
    </div>

    <!-- Toggle Buttons -->
    <div data-aos="zoom-in" class="flex justify-center mt-8 space-x-4">
        <button onclick="showProcess('plastic')" id="btn-plastic"
            class="font-bold px-10 py-2 border rounded-full bg-white text-gray-700 hover:bg-blue-700 transition">
            Plastic Injection
        </button>

        <button onclick="showProcess('rubber')" id="btn-rubber"
            class="font-bold px-10 py-2 border rounded-full bg-white text-gray-700 hover:bg-blue-700 transition">
            Rubber Molding
        </button>
    </div>

    <!-- Dynamic Content -->
    <div data-aos="fade-up" id="process-container" class="p-6 max-w-5xl mx-auto space-y-6 mt-6">
        <!-- Plastic Section -->
        <div id="plastic-section" class="process-section transition-opacity duration-500 opacity-100">
            <?php if (!empty($processes['plastic'])): ?>
                <?php foreach ($processes['plastic'] as $item): ?>
                    <div class="bg-white rounded shadow p-4 mb-24">
                        <h3 class="px-10 pt-6 text-2xl font-bold text-gray-800"><?= htmlspecialchars($item['title']) ?></h3>
                        <p class="px-10 text-lg text-gray-700 mt-2"><?= nl2br(htmlspecialchars($item['description'])) ?></p>
                        <?php if ($item['image']): ?>
                            <div class="px-10 pb-6 mt-4">
                                <img
                                    src="assets/img/overview_process/<?= htmlspecialchars($item['image']) ?>"
                                    alt="Process Image"
                                    class="w-full border rounded cursor-pointer"
                                    onclick="openImageModal('assets/img/overview_process/<?= htmlspecialchars($item['image']) ?>')">
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-gray-500 italic">No plastic injection process data available.</p>
            <?php endif; ?>
        </div>

        <!-- Rubber Section -->
        <div id="rubber-section" class="process-section transition-opacity duration-500 opacity-0 absolute w-full top-0 left-0">
            <?php if (!empty($processes['rubber'])): ?>
                <?php foreach ($processes['rubber'] as $item): ?>
                    <div class="bg-white rounded shadow p-4 mb-6">
                        <h3 class="px-10 pt-6 text-2xl font-bold text-gray-800"><?= htmlspecialchars($item['title']) ?></h3>
                        <p class="px-10 text-lg text-gray-700 mt-2"><?= nl2br(htmlspecialchars($item['description'])) ?></p>
                        <?php if ($item['image']): ?>
                            <div class="px-10 pb-6 mt-4">
                                <img
                                    src="assets/img/overview_process/<?= htmlspecialchars($item['image']) ?>"
                                    alt="Process Image"
                                    class="w-full border rounded cursor-pointer"
                                    onclick="openImageModal('assets/img/overview_process/<?= htmlspecialchars($item['image']) ?>')">
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-gray-500 italic">No rubber molding process data available.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Fullscreen Image Modal -->
    <div id="imageModal" class="fixed inset-0 bg-black bg-opacity-80 flex items-center justify-center hidden z-50">
        <button id="closeModal" class="absolute top-4 right-4 text-white text-3xl font-bold focus:outline-none">&times;</button>
        <img id="modalImage" src="" alt="Full Screen Image" class="max-w-full max-h-full rounded shadow-lg">
    </div>

    <!-- JavaScript -->
    <script src="includes/javascript/overviewprocess.js"></script>
    
    <!-- Chatbot Script -->
    <?php include 'chatbot.php'; ?>

    <!-- Footer -->
    <?php include 'footer.php'; ?>
</body>

</html>