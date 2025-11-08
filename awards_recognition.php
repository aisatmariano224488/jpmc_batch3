<?php
require_once 'includes/db_connection.php';

// Fetch awards
$awards = array();
$sql = "SELECT * FROM awards ORDER BY year DESC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $awards[] = $row;
    }
}

// Fetch timeline items
$timeline = array();
$sql = "SELECT * FROM award_timeline ORDER BY date DESC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $timeline[] = $row;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Awards & Recognition | James Polymers</title>

    <!--Tab Icon-->
    <link rel="icon" type="image/png" href="assets/img/tab_icon.png">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- AOS Link -->
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        <?php include 'includes/css/awards_recognition.css'; ?>
    </style>
</head>

<body class="bg-gray-50">
    <?php include 'header.php'; ?>
    <img id="floatingLogo" src="assets/img/JP_BG_WATERMARK_CIRCLE.png" alt="JP Watermark" class="fixed bottom-0 left-0 w-full h-auto opacity-40 pointer-events-none select-none transition-opacity duration-500 ease-in-out" />

    <!-- chatbot.php -->
    <?php include 'chatbot.php'; ?>

    <!-- Debug Info (remove this after testing) -->
    <?php if (isset($_GET['debug'])): ?>
        <div style="background: #f0f0f0; padding: 10px; margin: 10px; border: 1px solid #ccc;">
            <h3>Debug Info:</h3>
            <p>Total awards: <?php echo count($awards); ?></p>
            <?php foreach ($awards as $index => $award): ?>
                <div style="margin: 10px 0; padding: 10px; background: white;">
                    <p><strong>Award <?php echo $index + 1; ?>:</strong></p>
                    <p>Title: <?php echo htmlspecialchars($award['title']); ?></p>
                    <p>Image: <?php echo htmlspecialchars($award['image']); ?></p>
                    <?php if ($award['image']): ?>
                        <?php $image_path = "assets/img/awards/" . $award['image']; ?>
                        <p>Full Path: <?php echo $image_path; ?></p>
                        <p>File exists: <?php echo file_exists($image_path) ? 'YES' : 'NO'; ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <section
        data-aos="fade-down"
        class="relative bg-blue-400 h-96 flex items-center justify-center bg-cover bg-center"
        style="background-image: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('https://www.james-polymers.com/wp-content/uploads/2021/09/awards-banner.jpg')">
        <!-- Inclined overlay image -->
        <img
            src="assets/img/banners/trophies_banner.png"
            alt="Inclined Overlay"
            class="absolute inset-0 w-full h-full object-cover"
            style="mix-blend-mode: multiply; opacity: 1;" />

        <div class="container mx-auto px-4 text-center text-white relative z-10">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Awards & Recognition</h1>
            <div class="flex justify-center items-center text-sm md:text-base">
                <a href="./index.php" class="text-white hover:text-blue-300">Home</a>
                <span class="mx-2">/</span>
                <span class="text-blue-300">Awards</span>
            </div>
        </div>
    </section>

    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">Our Achievements</h2>
                <p class="text-gray-600 max-w-3xl mx-auto">James Polymers has been recognized for excellence in polymer innovation, manufacturing, and business performance by prestigious industry organizations.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($awards as $award): ?>
                    <div
                        data-aos="zoom-in"
                        class="z-10 award-card bg-white rounded-lg shadow-md p-6 cursor-pointer h-120 flex flex-col">
                        <!-- Header with Icon/Title -->
                        <div class="flex items-start mb-4">
                            <?php if (!$award['image']): ?>
                                <div class="bg-primary bg-opacity-10 p-3 rounded-full mr-4 flex-shrink-0">
                                    <i class="fas <?php echo htmlspecialchars($award['icon']); ?> text-primary text-2xl"></i>
                                </div>
                            <?php endif; ?>
                            <div class="flex-1">
                                <h3 class="text-xl font-bold text-gray-800 line-clamp-2"><?php echo htmlspecialchars($award['title']); ?></h3>
                            </div>
                        </div>

                        <!-- Image Section -->
                        <?php if ($award['image']): ?>
                            <div class="mb-4 flex-shrink-0">
                                <?php
                                // Construct the full image path
                                $image_path = "assets/img/awards/" . $award['image'];
                                ?>
                                <img src="<?php echo htmlspecialchars($image_path); ?>"
                                    alt="<?php echo htmlspecialchars($award['title']); ?>"
                                    class="w-full h-48 object-cover rounded-lg award-image">
                            </div>
                        <?php endif; ?>

                        <!-- Description Section - Fixed Height -->
                        <div class="flex-1 mb-4">
                            <?php if ($award['description']): ?>
                                <p class="text-gray-600 text-sm line-clamp-4 h-20 overflow-hidden"><?php echo htmlspecialchars($award['description']); ?></p>
                            <?php else: ?>
                                <div class="h-20"></div>
                            <?php endif; ?>
                        </div>

                        <!-- Year Section - Fixed at Bottom -->
                        <div class="mt-auto">
                            <?php if ($award['year']): ?>
                                <div class="flex items-center text-sm text-gray-500">
                                    <i class="far fa-calendar-alt mr-2"></i>
                                    <span><?php echo htmlspecialchars($award['year']); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Modal for Award Details -->
    <div id="awardModal" class="modal-overlay">
        <div class="modal-content">
            <div class="flex justify-between items-center mb-4">
                <h3 id="awardModalTitle" class="text-2xl font-bold"></h3>
                <button id="closeAwardModal" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <div id="awardModalImage" class="h-64 bg-gray-200 bg-cover bg-center mb-4 rounded-lg"></div>
                </div>
                <div>
                    <p id="awardModalDesc" class="text-gray-700 mb-6"></p>
                    <div id="awardModalDate" class="text-sm text-gray-500 mb-4"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Timeline Section -->
    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">Our Journey</h2>
                <p class="text-gray-600 max-w-3xl mx-auto">Milestones and recognitions that mark our commitment to excellence in polymer solutions.</p>
            </div>

            <div class="relative max-w-4xl mx-auto">
                <?php foreach ($timeline as $index => $item): ?>
                    <div data-aos="fade-up" class="relative timeline-item pl-16 mb-12">
                        <div class="absolute left-0 top-0 flex items-center justify-center w-12 h-12 rounded-full bg-primary text-white">
                            <i class="fas <?php echo htmlspecialchars($item['icon']); ?> text-xl"></i>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-md">
                            <h3 class="text-xl font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($item['title']); ?></h3>
                            <div class="flex items-center text-sm text-gray-500 mb-3">
                                <span><?php echo date('F Y', strtotime($item['date'])); ?></span>
                            </div>
                            <p class="text-gray-600"><?php echo htmlspecialchars($item['description']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative py-16 text-white">
        <!-- Background image with opacity -->
        <div class="absolute inset-0">
            <img
                src="assets/img/banners/trophies_cta.png"
                alt="Banner Background"
                class="w-full h-full object-cover opacity-80" />
            <!-- Optional banner color overlay -->
            <div class="absolute inset-0 bg-primary opacity-60"></div>
        </div>

        <!-- Content -->
        <div class="relative container mx-auto px-4 text-center">
            <h2 class="text-3xl md:text-4xl font-bold mb-6">Partner with an Award-Winning Polymer Solutions Provider</h2>
            <p class="text-xl mb-8 max-w-3xl mx-auto">
                Our recognized expertise ensures you get the highest quality polymer solutions for your specific needs.
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="contact.php" class="bg-white text-primary font-bold py-3 px-8 rounded-lg hover:bg-gray-100 transition duration-300">Contact Us</a>
                <a href="tel:+441234567890" class="bg-transparent border-2 border-white text-white font-bold py-3 px-8 rounded-lg hover:bg-white hover:text-primary transition duration-300">Call Us Now</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'footer.php'; ?>

    <script src="includes/javascript/awards.js"></script>
</body>

</html>