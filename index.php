<?php
// Include database connection
require_once 'includes/db_connection.php';

// Function to get content from the database
function getHomeContent($section_name, $field_name, $default = '')
{
    global $conn;
    $stmt = $conn->prepare("SELECT value FROM home_sections WHERE section_name = ? AND field_name = ?");
    $stmt->bind_param("ss", $section_name, $field_name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        return $row['value'] ?: $default;
    }

    return $default;
}

// Helper function to format image URLs
function formatImageUrl($image, $subfolder = '')
{
    if (filter_var($image, FILTER_VALIDATE_URL)) {
        return $image; // Already a URL
    } else {
        return 'assets/home/' . ($subfolder ? $subfolder . '/' : '') . $image;
    }
}
?>
<?php include 'visitor_counter.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>James Polymers - High Performance Polymer Solutions</title>
    <meta name="description" content="James Polymers offers top-quality plastic injection molding and sustainable polymer solutions for industrial manufacturing. Contact us today!">
    <meta name="keywords" content="James Polymers, Polymer Solutions, Plastic Injection, Manufacturing, Sustainability, Industrial Plastics">
    <meta name="author" content="James Polymers Corporation">

    <!--Tab Icon-->
    <link rel="icon" type="image/png" href="assets/img/tab_icon.png">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#0066cc',
                        secondary: '#004d99',
                        dark: '#222222',
                        light: '#f5f5f5'
                    }
                }
            }
        }
    </script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- AOS (Animate On Scroll) -->
    <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">

    <style>
        <?php include 'includes/css/index.css'; ?>
    </style>
</head>

<body class="bg-gray-50 overflow-x-hidden">

    <script>
    // Global error handler - catches getAttribute errors
    window.addEventListener('error', function(e) {
        if (e.message && e.message.includes('getAttribute')) {
            e.preventDefault();
            console.log('Caught and suppressed getAttribute error');
            return true;
        }
    }, true);
    </script>
    <?php

    // Include loader
    /* include 'loader.php'; */

    // Include header
    include 'header.php';
    ?>

    <!-- chatbot.php -->
    <?php include 'chatbot.php'; ?>


    <!-- Multiple JP BG -->
    <img id="floatingLogo"
        src="assets/img/JP_BG_WATERMARK_CIRCLE.png"
        alt="JP Watermark"
        class="fixed bottom-0 left-0 w-full h-auto 
                opacity-60
                pointer-events-none select-none 
                transition-opacity duration-500 ease-in-out " />


    <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>

    <!-- Hero Section -->
    <section class="relative bg-blue-900 h-screen max-h-[600px] flex items-center justify-center bg-cover bg-center" style="background-image: url('<?php echo formatImageUrl(getHomeContent('hero_section', 'background_image', 'hero-image.jpg')); ?>');">
        <div id="particles-js" class="absolute inset-0 w-full h-full z-0"></div>
        <div class="container mx-auto px-4 text-center text-white relative z-10 flex justify-center">
            <div class="inline-block bg-black bg-opacity-50 rounded-2xl shadow-2xl px-8 py-10 max-w-2xl w-full" style="backdrop-filter: blur(2px);">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-6" data-aos="fade-up">
                    <?php echo getHomeContent('hero_section', 'heading', 'James Polymers Manufacturing Corporation'); ?>
                </h1>
                <p class="text-xl md:text-2xl mb-8 max-w-3xl mx-auto" data-aos="fade-up" data-aos-delay="200">
                    <?php echo getHomeContent('hero_section', 'subheading', 'Delivering high-performance polymer compounds tailored to your specific requirements with over 15 years of expertise'); ?>
                </p>
                <div class="flex flex-col sm:flex-row justify-center gap-4" data-aos="fade-up" data-aos-delay="400">
                    <a href="./products.php" class="bg-primary hover:bg-secondary text-white font-bold py-3 px-8 rounded-lg transition duration-300 transform hover:scale-105 shadow-lg">
                        <?php echo getHomeContent('hero_section', 'button1_text', 'Explore Our Capabilities'); ?>
                    </a>
                    <a href="./contact.php" class="bg-transparent border-2 border-white text-white font-bold py-3 px-8 rounded-lg hover:bg-white hover:text-primary transition duration-300 transform hover:scale-105 shadow-lg">
                        <?php echo getHomeContent('hero_section', 'button2_text', 'Contact Us'); ?>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Products & Services Section -->
    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl md:text-4xl font-bold text-center text-black mb-4">
                <?php echo getHomeContent('products_services', 'heading', 'Products & Services'); ?>
            </h2>
            <p class="text-xl text-center text-black max-w-3xl mx-auto mb-12">
                <?php echo getHomeContent('products_services', 'subheading', 'We offer a comprehensive range of polymer compounds and value-added services to meet the most demanding material requirements across various industries.'); ?>
            </p>

            <div class="row g-4">
                <?php
                // Categories content from database
                $categories = [
                    [
                        'image' => formatImageUrl(getHomeContent('products_services', 'cat1_image', 'product-1.jpg')),
                        'title' => getHomeContent('products_services', 'cat1_title', 'Thermoplastic Elastomers'),
                        'description' => getHomeContent('products_services', 'cat1_description', 'High-performance TPE compounds offering excellent flexibility, durability, and processing characteristics for diverse applications.')
                    ],
                    [
                        'image' => formatImageUrl(getHomeContent('products_services', 'cat2_image', 'product-2.jpg')),
                        'title' => getHomeContent('products_services', 'cat2_title', 'Engineering Plastics'),
                        'description' => getHomeContent('products_services', 'cat2_description', 'Specialized compounds designed for demanding mechanical, thermal, and chemical resistance applications.')
                    ],
                    [
                        'image' => formatImageUrl(getHomeContent('products_services', 'cat3_image', 'product-3.jpg')),
                        'title' => getHomeContent('products_services', 'cat3_title', 'Custom Compounds'),
                        'description' => getHomeContent('products_services', 'cat3_description', 'Tailored polymer solutions developed to meet your specific performance, regulatory, and processing requirements.')
                    ],
                    [
                        'image' => formatImageUrl(getHomeContent('products_services', 'cat4_image', 'service-1.jpg')),
                        'title' => getHomeContent('products_services', 'cat4_title', 'Technical Services'),
                        'description' => getHomeContent('products_services', 'cat4_description', 'Comprehensive support including material selection, testing, processing optimization, and troubleshooting.')
                    ]
                ];

                // Output categories
                foreach ($categories as $index => $category):
                ?>
                    <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                        <div class="category-card bg-white rounded-lg shadow-md overflow-hidden transition duration-300 h-full flex flex-col">
                            <div class="h-48 bg-cover bg-center" style="background-image: url('<?php echo $category['image']; ?>');"></div>
                            <div class="p-6 flex flex-col flex-grow">
                                <h3 class="text-xl font-bold text-gray-800 mb-3"><?php echo $category['title']; ?></h3>
                                <p class="text-gray-600 mb-4 flex-grow"><?php echo $category['description']; ?></p>
                                <div class="mt-auto">
                                    <a href="products.php" class="text-primary font-semibold hover:text-secondary transition">Learn More <i class="fas fa-arrow-right ml-1"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Industries Section -->
    <section class="py-16 bg-white relative">
        <div class="container mx-auto px-4 flex items-start">
            <div class="flex-1">
                <h2 class="text-3xl md:text-4xl font-bold text-center text-gray-800 mb-4">
                    <?php echo getHomeContent('industries', 'heading', 'Industries We Cater'); ?>
                </h2>
                <p class="text-xl text-center text-gray-600 max-w-3xl mx-auto mb-12">
                    <?php echo getHomeContent('industries', 'subheading', 'Our advanced polymer solutions serve critical applications across multiple industries, enabling innovation and performance enhancement.'); ?>
                </p>
                <div class="row g-4 position-relative" style="position: relative;">
                    <?php
                    $industries = [
                        [
                            'icon' => 'fa-plane',
                            'title' => getHomeContent('industries', 'industry6_title', 'Aerospace'),
                            'description' => getHomeContent('industries', 'industry6_description', 'High-performance materials for aircraft interiors and components.')
                        ],
                        [
                            'icon' => 'fa-car',
                            'title' => getHomeContent('industries', 'industry1_title', 'Automotive'),
                            'description' => getHomeContent('industries', 'industry1_description', 'Lightweight, durable materials for interior, exterior, and under-the-hood applications.')
                        ],
                        [
                            'icon' => 'fa-home',
                            'title' => getHomeContent('industries', 'industry3_title', 'Consumer'),
                            'description' => getHomeContent('industries', 'industry3_description', 'High-performance materials for appliances, tools, and household products.')
                        ],
                        [
                            'icon' => 'fa-microchip',
                            'title' => getHomeContent('industries', 'industry5_title', 'Electronics'),
                            'description' => getHomeContent('industries', 'industry5_description', 'Specialized compounds with electrical properties and flame retardancy.')
                        ],
                        [
                            'icon' => 'fa-cogs',
                            'title' => getHomeContent('industries', 'industry4_title', 'Industrial'),
                            'description' => getHomeContent('industries', 'industry4_description', 'Robust materials for machinery, fluid handling, and industrial components.')
                        ],
                        [
                            'icon' => 'fa-medkit',
                            'title' => getHomeContent('industries', 'industry2_title', 'Medical'),
                            'description' => getHomeContent('industries', 'industry2_description', 'Biocompatible, sterilizable compounds for devices and equipment.')
                        ],
                    ];

                    foreach ($industries as $index => $industry):
                    ?>
                        <div class="col-6 col-md-4 col-lg-2" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                            <div class="industry-card bg-gray-50 rounded-lg p-6 text-center transition duration-300 h-full">
                                <div class="bg-primary bg-opacity-10 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas <?php echo $industry['icon']; ?> text-primary text-2xl"></i>
                                </div>
                                <h3 class="text-lg font-bold text-gray-800"><?php echo $industry['title']; ?></h3>
                                <p class="text-sm text-gray-600 mt-2"><?php echo $industry['description']; ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <!-- Absolute right arrow button -->
            <a href="industries.php" class="d-flex align-items-center justify-content-center bg-primary text-white rounded-full shadow w-12 h-12 position-absolute" style="left: 50%; top: 100%; transform: translate(-50%, -50%); font-size: 1.5rem; z-index: 10; box-shadow: 0 4px 16px rgba(0,0,0,0.10);" title="View all industries">
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </section>

    <!-- Awards Section -->
    <section class="mt-10 py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl md:text-4xl font-bold text-center text-gray-800 mb-4">
                <?php echo getHomeContent('awards', 'heading', 'Awards & Recognition'); ?>
            </h2>
            <p class="text-xl text-center text-gray-600 max-w-3xl mx-auto mb-12">
                <?php echo getHomeContent('awards', 'subheading', 'Our commitment to excellence and innovation has been recognized by industry organizations and publications.'); ?>
            </p>

            <div class="row g-4">
                <?php
                // Awards
                $awards = [
                    [
                        'icon' => 'fa-trophy',
                        'title' => getHomeContent('awards', 'award1_title', '2022 Polymer Innovation Award'),
                        'description' => getHomeContent('awards', 'award1_description', 'Recognized for our breakthrough in high-temperature resistant TPE compounds by the International Polymer Association.')
                    ],
                    [
                        'icon' => 'fa-trophy',
                        'title' => getHomeContent('awards', 'award2_title', '2021 Excellence in Sustainability'),
                        'description' => getHomeContent('awards', 'award2_description', 'Awarded for our eco-friendly polymer series with 50% recycled content without compromising performance.')
                    ],
                    [
                        'icon' => 'fa-certificate',
                        'title' => getHomeContent('awards', 'award3_title', '2020 Supplier of the Year'),
                        'description' => getHomeContent('awards', 'award3_description', 'Honored by our automotive clients for consistent quality and innovation in material solutions.')
                    ],
                    [
                        'icon' => 'fa-certificate',
                        'title' => getHomeContent('awards', 'award4_title', 'ISO 9001:2015 Certified'),
                        'description' => getHomeContent('awards', 'award4_description', 'Our quality management system meets international standards for consistency and continuous improvement.')
                    ]
                ];

                foreach ($awards as $index => $award):
                ?>
                    <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                        <div class="award-card bg-white rounded-lg shadow-md p-6 h-full transition duration-300">
                            <div class="flex items-center mb-4">
                                <div class="bg-primary bg-opacity-10 p-3 rounded-full mr-4">
                                    <i class="fas <?php echo $award['icon']; ?> text-primary text-2xl"></i>
                                </div>
                                <h3 class="text-lg font-bold text-gray-800"><?php echo $award['title']; ?></h3>
                            </div>
                            <p class="text-gray-600"><?php echo $award['description']; ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Customers Section -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl md:text-4xl font-bold text-center text-gray-800 mb-4">
                <?php echo getHomeContent('customers', 'heading', 'Our Valued Customers'); ?>
            </h2>
            <p class="text-xl text-center text-gray-600 max-w-3xl mx-auto mb-12">
                <?php echo getHomeContent('customers', 'subheading', "We're proud to partner with industry leaders across various sectors, providing them with high-performance polymer solutions."); ?>
            </p>

            <!-- Honeycomb Layout -->
            <div class="hex-wrapper">
                <?php
                $layout = [
                    [null, 1, null],
                    [null, 2, 3, null],
                    [null, 4, 5, 6, null],
                    [null, 7, 8, 9, 10, null],
                    [null, 11, 12, 13, 14, 15, null]
                ];
                $max_logo = 1000;
                $logo_imgs = [];
                for ($i = 1; $i <= $max_logo; $i++) {
                    $logo = formatImageUrl(getHomeContent('customers', "customer{$i}_logo", ""));
                    $logo_imgs[$i] = ($logo && strpos($logo, 'assets/home/') !== strlen($logo) - 11) ? $logo : null;
                }

                $hexIndex = 0; // Counter for animation delay

                foreach ($layout as $row_idx => $row) {
                    $is_offset = $row_idx % 2 !== 0 ? ' offset' : '';
                    echo '<div class="hex-row' . $is_offset . '">';
                    foreach ($row as $cell) {
                        if ($cell === null) {
                            echo '<div class="hexagon invisible"><div class="hex-inner"></div></div>';
                        } else {
                            $delay = $hexIndex * 5;
                            echo '<div class="hexagon" data-aos="flip-right" data-aos-delay="' . $delay . '">';
                            echo '<div class="hex-inner">';
                            if (isset($logo_imgs[$cell]) && $logo_imgs[$cell]) {
                                echo '<img src="' . $logo_imgs[$cell] . '" alt="Customer Logo">';
                            } else {
                                echo '<span class="coming-soon">Coming Soon</span>';
                            }
                            echo '</div></div>';
                            $hexIndex++;
                        }
                    }
                    echo '</div>';
                }
                ?>
            </div>
        </div>
    </section>

    <!-- Example Animated Counters Section -->
    <section class="py-16 bg-white" data-aos="fade-up">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
                <div>
                    <div class="text-7xl font-black text-primary counter" data-target="45">0</div>
                    <div class="mt-2 text-2xl font-semibold text-gray-700">Years of Expertise</div>
                </div>
                <div>
                    <div
                        class="text-7xl font-black text-primary counter"
                        id="visitorCounter"
                        data-target="<?php echo $counter; ?>">
                        0
                    </div>
                    <div class="mt-2 text-2xl font-semibold text-gray-700">
                        Number of Visitors
                    </div>
                </div>
                <div>
                    <div class="text-7xl font-black text-primary counter" data-target="33">0</div>
                    <div class="mt-2 text-2xl font-semibold text-gray-700">Companies Served</div>
                </div>
            </div>
        </div>
        <svg class="absolute bottom-0 left-0 w-full h-16" viewBox="0 0 1440 320" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path fill="#f5f5f5" fill-opacity="1" d="M0,160L60,170.7C120,181,240,203,360,197.3C480,192,600,160,720,133.3C840,107,960,85,1080,101.3C1200,117,1320,171,1380,197.3L1440,224L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,60,320L0,320Z"></path>
        </svg>
    </section>

    <!-- Footer -->
    <?php include 'footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.10.2/lottie.min.js"></script>

    <!-- Javascript -->
    <script src="includes/javascript/index.js"> </script>

</body>

</html>