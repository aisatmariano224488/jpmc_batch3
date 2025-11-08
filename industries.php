<?php
// Include database connection
require_once 'includes/db_connection.php';

// In your PHP code where you fetch industries:
function getIndustrySolutions($conn, $industryId, $limit = null)
{
    $solutions = array();
    $sql = "SELECT solution FROM industry_solutions WHERE industry_id = $industryId";
    if ($limit !== null) {
        $sql .= " LIMIT $limit";
    }
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $solutions[] = $row['solution'];
        }
    }
    return $solutions;
}

// When getting industries for the cards:
$sql = "SELECT * FROM industries";
$result = $conn->query($sql);
$industries = array();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Initialize all required array keys
        $row['displayed_solutions'] = getIndustrySolutions($conn, $row['id'], 4); // Limit to 4 for card
        $allSolutions = getIndustrySolutions($conn, $row['id']); // Get all solutions
        $row['total_solutions'] = count($allSolutions); // Get total count

        // Ensure displayed_solutions is always an array
        if (!is_array($row['displayed_solutions'])) {
            $row['displayed_solutions'] = array();
        }

        // Ensure total_solutions is always a number
        if (!isset($row['total_solutions'])) {
            $row['total_solutions'] = 0;
        }

        $industries[] = $row;
    }
}

// Get all industries
$industries = array();
$sql = "SELECT * FROM industries";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $row['displayed_solutions'] = getIndustrySolutions($conn, $row['id'], 4);
        $allSolutions = getIndustrySolutions($conn, $row['id']);
        $row['total_solutions'] = count($allSolutions);

        if (!is_array($row['displayed_solutions'])) {
            $row['displayed_solutions'] = array();
        }

        if (!isset($row['total_solutions'])) {
            $row['total_solutions'] = 0;
        }

        $industries[] = $row;
    }
}

// Sort industries alphabetically by name
usort($industries, function ($a, $b) {
    return strcmp($a['name'], $b['name']);
});

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Industries | James Polymers - High Performance Polymer Solutions</title>


    <!--Tab Icon-->
    <link rel="icon" type="image/png" href="assets/img/tab_icon.png">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        <?php include 'includes/css/industries.css'; ?>
        }
    </style>
</head>

<body class="bg-gray-50">
    <!-- Header -->
    <?php include 'header.php'; ?>

    <!-- chatbot.php -->
    <?php include 'chatbot.php'; ?>

    <!-- Multiple JP BG -->
    <img id="floatingLogo"
        src="assets/img/JP_BG_WATERMARK_CIRCLE.png"
        alt="JP Watermark"
        class="fixed bottom-0 left-0 w-full h-auto 
                opacity-40
                pointer-events-none select-none 
                transition-opacity duration-500 ease-in-out " />

    <!-- Page Banner -->
    <section
        data-aos="fade-down"
        class="relative bg-blue-400 h-96 flex items-center justify-center bg-cover bg-center"
        style="background-image: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('https://www.james-polymers.com/wp-content/uploads/2021/09/industries-banner.jpg')">
        <!-- Inclined overlay image -->
        <img
            src="assets/img/banners/industries_banner.jpg"
            alt="Inclined Overlay"
            class="absolute inset-0 w-full h-full object-cover"
            style="mix-blend-mode: multiply; opacity: 1;" />

        <div class="container mx-auto px-4 text-center text-white relative z-10">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Industries We Serve</h1>
            <div class="flex justify-center items-center text-sm md:text-base">
                <a href="./index.php" class="text-white hover:text-blue-300">Home</a>
                <span class="mx-2">/</span>
                <span class="text-blue-300">Industries</span>
            </div>
        </div>
    </section>


    <!-- Industries Section -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-3">Specialized Polymer Solutions Across Industries</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">Our high-performance polymer compounds are engineered to meet the unique challenges of diverse industrial applications.</p>
            </div>

            <!-- Industries Swipe Carousel -->
            <div class="industries-carousel-container relative">
                <!-- Scroll Indicator -->
                <div class="text-center mb-4 text-gray-600 text-sm">
                    <i class="fas fa-hand-pointer mr-1"></i> Scroll freely or click to navigate
                </div>

                <!-- Carousel Track -->
                <div class="industries-carousel-track overflow-hidden">
                    <div class="industries-carousel-slides flex transition-transform duration-600 ease-in-out" id="industriesCarouselTrack">
                        <?php foreach ($industries as $index => $industry):
                            $delay = ($index % 4) * 150;
                            $displayedSolutions = isset($industry['displayed_solutions']) ? $industry['displayed_solutions'] : array();
                            $totalSolutions = isset($industry['total_solutions']) ? $industry['total_solutions'] : 0;
                        ?>
                            <div class="industries-carousel-slide w-full sm:w-1/2 lg:w-1/3 xl:w-1/4 flex-shrink-0 px-3 <?php echo $index === 0 ? 'active' : ''; ?>" data-index="<?php echo $index; ?>">
                                <div data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>" class="industry-card bg-white rounded-lg shadow-md overflow-hidden <?php echo isset($industry['coming_soon']) && $industry['coming_soon'] ? 'relative coming-soon' : ''; ?> flex flex-col h-full">
                                    <!-- Image at top -->
                                    <div class="h-48 bg-gray-200 bg-cover bg-center" style="background-image: url('<?php echo isset($industry['image_url']) ? htmlspecialchars($industry['image_url']) : ''; ?>');"></div>

                                    <!-- Card content with flex-grow to push button to bottom -->
                                    <div class="p-6 flex flex-col flex-grow">
                                        <div class="flex items-center mb-3">
                                            <div class="bg-primary bg-opacity-10 p-2 rounded-full mr-3 flex-shrink-0">
                                                <i class="fas <?php echo isset($industry['icon_class']) ? htmlspecialchars($industry['icon_class']) : 'fa-industry'; ?> text-primary"></i>
                                            </div>
                                            <h3 class="text-xl font-bold text-gray-800"><?php echo isset($industry['name']) ? htmlspecialchars($industry['name']) : ''; ?></h3>
                                        </div>
                                        <p class="text-gray-600 mb-4 flex-grow"><?php echo isset($industry['description']) ? htmlspecialchars($industry['description']) : ''; ?></p>

                                        <!-- View Solutions button at the bottom -->
                                        <div class="mt-auto pt-4">
                                            <a href="#"
                                                class="group view-details w-full inline-flex items-center justify-center 
                                                text-primary font-semibold 
                                                border border-primary rounded-lg py-2 px-4 
                                                transition duration-300 ease-in-out
                                                hover:bg-primary hover:shadow-lg hover:scale-105"
                                                data-industry-id="<?php echo isset($industry['id']) ? htmlspecialchars($industry['id']) : ''; ?>"
                                                data-industry-name="<?php echo isset($industry['name']) ? htmlspecialchars($industry['name']) : ''; ?>"
                                                data-industry-description="<?php echo isset($industry['description']) ? htmlspecialchars($industry['description']) : ''; ?>"
                                                data-industry-image="<?php echo isset($industry['image_url']) ? htmlspecialchars($industry['image_url']) : ''; ?>">

                                                <span class="group-hover:text-white transition">View Solutions</span>
                                                <i class="fas fa-arrow-right ml-2 group-hover:text-white transition"></i>

                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Navigation Controls -->
                <div class="industries-carousel-controls flex justify-center items-center mt-8 gap-4">
                    <button class="industries-carousel-nav prev bg-gray-100 hover:bg-gray-200 border-2 border-gray-300 rounded-full w-12 h-12 flex items-center justify-center transition duration-300" onclick="industriesPrevSlide()">
                        <i class="fas fa-chevron-left text-gray-600"></i>
                    </button>

                    <div class="industries-carousel-counter text-sm text-gray-600">
                        <span id="industriesCurrentSlide">1</span> / <span id="industriesTotalSlides"><?php echo count($industries); ?></span>
                    </div>

                    <div class="industries-carousel-dots flex gap-2" id="industriesCarouselDots">
                        <?php for ($i = 0; $i < count($industries); $i++): ?>
                            <div class="industries-carousel-dot w-3 h-3 rounded-full bg-gray-300 cursor-pointer transition duration-300 <?php echo $i === 0 ? 'active bg-primary' : ''; ?>" onclick="industriesGoToSlide(<?php echo $i; ?>)"></div>
                        <?php endfor; ?>
                    </div>

                    <button class="industries-carousel-nav next bg-gray-100 hover:bg-gray-200 border-2 border-gray-300 rounded-full w-12 h-12 flex items-center justify-center transition duration-300" onclick="industriesNextSlide()">
                        <i class="fas fa-chevron-right text-gray-600"></i>
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Industry Benefits Section -->
    <section data-aos="zoom-in" class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-3">Industry-Specific Advantages</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">Our polymer solutions are tailored to address the unique challenges of each industry we serve</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="benefit-card bg-white rounded-lg shadow-sm p-6">
                    <div class="bg-primary bg-opacity-10 w-16 h-16 rounded-full flex items-center justify-center mb-4 mx-auto">
                        <i class="fas fa-certificate text-primary text-2xl"></i>
                    </div>
                    <h4 class="text-xl font-bold text-gray-800 mb-3 text-center">Regulatory Compliance</h4>
                    <p class="text-gray-600 text-center">Materials formulated to meet industry-specific standards including cGMP, ISO, RoHS, REACH, and more.</p>
                </div>

                <div class="benefit-card bg-white rounded-lg shadow-sm p-6">
                    <div class="bg-primary bg-opacity-10 w-16 h-16 rounded-full flex items-center justify-center mb-4 mx-auto">
                        <i class="fas fa-lightbulb text-primary text-2xl"></i>
                    </div>
                    <h4 class="text-xl font-bold text-gray-800 mb-3 text-center">Application Expertise</h4>
                    <p class="text-gray-600 text-center">Deep understanding of industry requirements to develop optimal material solutions.</p>
                </div>

                <div class="benefit-card bg-white rounded-lg shadow-sm p-6">
                    <div class="bg-primary bg-opacity-10 w-16 h-16 rounded-full flex items-center justify-center mb-4 mx-auto">
                        <i class="fas fa-flask text-primary text-2xl"></i>
                    </div>
                    <h4 class="text-xl font-bold text-gray-800 mb-3 text-center">Material Innovation</h4>
                    <p class="text-gray-600 text-center">Continuous R&D to develop new formulations that address emerging industry challenges.</p>
                </div>

                <div class="benefit-card bg-white rounded-lg shadow-sm p-6">
                    <div class="bg-primary bg-opacity-10 w-16 h-16 rounded-full flex items-center justify-center mb-4 mx-auto">
                        <i class="fas fa-cogs text-primary text-2xl"></i>
                    </div>
                    <h4 class="text-xl font-bold text-gray-800 mb-3 text-center">Processing Support</h4>
                    <p class="text-gray-600 text-center">Technical assistance with material selection, processing parameters, and troubleshooting.</p>
                </div>
            </div>
        </div>
    </section>


    <!-- CTA Section -->
    <section class="relative py-16 text-white">
        <!-- Background image with opacity -->
        <div class="absolute inset-0">
            <img
                src="assets/img/banners/industries_cta.jpg"
                alt="Banner Background"
                class="w-full h-full object-cover opacity-80" />
            <!-- Optional banner color overlay -->
            <div class="absolute inset-0 bg-primary opacity-60"></div>
        </div>

        <!-- Content -->
        <div class="relative container mx-auto px-4 text-center">
            <h2 class="text-3xl md:text-4xl font-bold mb-6">Need Industry-Specific Solutions?</h2>
            <p class="text-xl mb-8 max-w-3xl mx-auto">
                Our technical team has extensive experience developing polymer solutions for diverse industrial applications.
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="contact.php" class="bg-white text-primary font-bold py-3 px-8 rounded-lg hover:bg-gray-100 transition duration-300">Contact Us</a>
                <a href="tel:+441234567890" class="bg-transparent border-2 border-white text-white font-bold py-3 px-8 rounded-lg hover:bg-white hover:text-primary transition duration-300">Call Us Now</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Modal for Industry Details -->
    <div id="industryModal" class="modal-overlay">
        <div class="modal-content">
            <div class="flex justify-between items-center mb-4">
                <h3 id="modalIndustryTitle" class="text-2xl font-bold"></h3>
                <button id="closeModal" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <div id="modalIndustryImage" class="h-64 bg-gray-200 bg-cover bg-center mb-4 rounded-lg"></div>
                    <a href="contact.php" class="bg-primary hover:bg-secondary text-white font-bold py-2 px-6 rounded-lg transition duration-300">
                        Contact Us
                    </a>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-2">Description</h4>
                    <p id="modalIndustryDescription" class="text-gray-700 mb-6"></p>

                    <h4 class="text-lg font-semibold mb-2">Solutions</h4>
                    <ul id="modalIndustrySolutions" class="space-y-2 mb-6 max-h-64 overflow-y-auto"></ul>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="includes/javascript/industries.js" defer></script>

</body>

</html>