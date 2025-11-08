<?php
require_once 'includes/db_connection.php';

// Function to get product features
function getProductFeatures($conn, $productId)
{
    $features = array();
    $sql = "SELECT feature FROM product_features WHERE product_id = $productId";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $features[] = $row['feature'];
        }
    }
    return $features;
}

// Get all products
$products = array();
$sql = "SELECT * FROM products";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $row['features'] = getProductFeatures($conn, $row['id']);
        $products[] = $row;
    }
}

// Get all services
$services = array();
$sql = "SELECT * FROM services";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $services[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products | James Polymers - High Performance Polymer Solutions</title>

    <!--Tab Icon-->
    <link rel="icon" type="image/png" href="assets/img/tab_icon.png">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        <?php include 'includes/css/products.css'; ?>
    </style>
</head>

<body class="bg-gray-50">
    <!-- Header -->
    <?php include 'header.php'; ?>

    <!-- Chatbot Integration -->
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
        class="relative bg-blue-400 h-96 flex items-center justify-center bg-cover bg-center drop-shadow-2xl animate__animated animate__fadeIn"
        style="background-image: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('https://www.james-polymers.com/wp-content/uploads/2021/09/products-banner.jpg')">
        <!-- Inclined overlay image -->
        <img
            src="assets/img/banners/products_banner.jpg"
            alt="Inclined Overlay"
            class="absolute inset-0 w-full h-full object-cover"
            style="mix-blend-mode: multiply; opacity: 1;" />

        <div class="container mx-auto px-4 text-center text-white relative z-10">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Our Products & Services</h1>
            <div class="flex justify-center items-center text-sm md:text-base">
                <a href="./index.php" class="text-white hover:text-blue-300">Home</a>
                <span class="mx-2">/</span>
                <span class="text-blue-300">Products</span>
            </div>
        </div>
    </section>

    <!-- Products Section -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-3">Precision Polymer Components</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">High-quality polymer parts for various industries and applications</p>
            </div>

            <!-- Product Category Tabs -->
            <div class="flex flex-wrap justify-center gap-3 mb-12">
                <button class="tab-btn bg-primary text-white px-6 py-2 rounded-full font-medium transition" data-category="all">All Products</button>
                <button class="tab-btn bg-gray-200 hover:bg-gray-300 px-6 py-2 rounded-full font-medium transition" data-category="appliance">Appliance Parts</button>
                <button class="tab-btn bg-gray-200 hover:bg-gray-300 px-6 py-2 rounded-full font-medium transition" data-category="automotive">Automotive Parts</button>
                <button class="tab-btn bg-gray-200 hover:bg-gray-300 px-6 py-2 rounded-full font-medium transition" data-category="industrial">Industrial Components</button>
            </div>

            <!-- Product Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($products as $index => $product): ?>
                    <div data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>" class="product-card bg-white rounded-lg overflow-hidden shadow-md hover:shadow-2xl transition-shadow duration-300 animate__animated animate__fadeInUp" data-category="<?php echo $product['category']; ?>">
                        <div class="h-64 bg-gray-200 bg-cover bg-center" style="background-image: url('<?php echo $product['image_url']; ?>');"></div>
                        <div class="p-6">
                            <div class="flex items-center mb-3">
                                <div class="bg-primary bg-opacity-10 p-2 rounded-full mr-3 flex-shrink-0">
                                    <i class="fas <?php
                                                    switch ($product['category']) {
                                                        case 'appliance':
                                                            echo 'fa-home';
                                                            break;
                                                        case 'automotive':
                                                            echo 'fa-car';
                                                            break;
                                                        case 'industrial':
                                                            echo 'fa-industry';
                                                            break;
                                                        default:
                                                            echo 'fa-box';
                                                    }
                                                    ?> text-primary"></i>
                                </div>
                                <h3 class="text-xl font-bold text-gray-800"><?php echo $product['name']; ?></h3>
                            </div>
                            <p class="text-gray-600 mb-4"><?php echo $product['description']; ?></p>

                            <a href="#"
                                class="view-details inline-flex items-center text-primary font-semibold hover:text-secondary transition"
                                data-product-id="<?php echo $product['id']; ?>"
                                data-description="<?php echo htmlspecialchars($product['description'], ENT_QUOTES); ?>"
                                data-features='<?php echo json_encode($product["features"]); ?>'>
                                View Details <i class="fas fa-arrow-right ml-2"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-3">Our Manufacturing Services</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">Comprehensive polymer manufacturing and finishing services</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 ">
                <?php foreach ($services as $index => $service): ?>
                    <div data-aos="flip-right" data-aos-delay="<?php echo $index * 100; ?>" class="z-10 service-card bg-white rounded-lg shadow-md hover:shadow-2xl transition-shadow duration-300 animate__animated animate__fadeInUp overflow-hidden">
                        <div class="h-48 bg-gray-200 bg-cover bg-center" style="background-image: url('<?php echo $service['image_url']; ?>');"></div>
                        <div class="p-6">
                            <div class="flex items-center mb-3">
                                <div class="bg-primary bg-opacity-10 p-2 rounded-full mr-3 flex-shrink-0">
                                    <i class="fas fa-cogs text-primary"></i>
                                </div>
                                <h3 class="text-xl font-bold text-gray-800"><?php echo $service['name']; ?></h3>
                            </div>
                            <p class="text-gray-600 mb-4"><?php echo $service['description']; ?></p>
                            <a href="#" class="learn-more inline-flex items-center text-primary font-semibold hover:text-secondary transition" data-service-id="<?php echo $service['id']; ?>" data-youtube-url="<?php echo isset($service['youtube_url']) ? $service['youtube_url'] : ''; ?>">
                                Learn More <i class="fas fa-arrow-right ml-2"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Modal for Product Details -->
    <div id="productModal" class="modal-overlay">
        <div class="modal-content">
            <div class="flex justify-between items-center mb-4">
                <h3 id="modalProductTitle" class="text-2xl font-bold"></h3>
                <button id="closeModal" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <div id="modalProductImage" class="h-64 bg-gray-200 bg-cover bg-center mb-4 rounded-lg"></div>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-2">Description</h4>
                    <p id="modalProductDescription" class="text-gray-700 mb-6"></p>

                    <h4 class="text-lg font-semibold mb-2">Features</h4>
                    <ul id="modalProductFeatures" class="space-y-2 mb-6"></ul>

                    <div class="flex space-x-4">
                        <a href="contact.php" class="bg-primary hover:bg-secondary text-white font-bold py-2 px-6 rounded-lg transition duration-300">
                            Contact Us
                        </a>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Service Details -->
    <div id="serviceModal" class="modal-overlay">
        <div class="modal-content max-w-5xl w-full mx-4">
            <div class="flex justify-between items-center mb-3">
                <h3 id="modalServiceTitle" class="text-xl md:text-2xl font-bold"></h3>
                <button id="closeServiceModal" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="grid grid-cols-1 gap-4">
                <!-- Video Container -->
                <div id="modalServiceVideo" class="w-full">
                    <!-- YouTube video will be inserted here by JavaScript -->
                </div>
                
                <!-- Description -->
                <div>
                    <p id="modalServiceDescription" class="text-gray-700 text-sm md:text-base mb-4"></p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <div class="text-primary text-xl mb-1">
                                <i class="fas fa-tools"></i>
                            </div>
                            <h4 class="font-semibold text-sm mb-1">Custom Solutions</h4>
                            <p class="text-xs text-gray-600">Tailored to your specific requirements</p>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <div class="text-primary text-xl mb-1">
                                <i class="fas fa-cogs"></i>
                            </div>
                            <h4 class="font-semibold text-sm mb-1">Modern Equipment</h4>
                            <p class="text-xs text-gray-600">State-of-the-art technology</p>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <div class="text-primary text-xl mb-1">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <h4 class="font-semibold text-sm mb-1">Quality Assured</h4>
                            <p class="text-xs text-gray-600">Rigorous quality control</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="mt-4 flex justify-center space-x-3">
                <a href="contact.php" class="bg-primary hover:bg-secondary text-white font-bold py-2 px-4 text-sm rounded-lg transition duration-300">
                    <i class="fas fa-envelope mr-1"></i> Contact Us
                </a>
                <a href="tel:+1234567890" class="bg-white border-2 border-primary text-primary hover:bg-gray-50 font-bold py-2 px-4 text-sm rounded-lg transition duration-300">
                    <i class="fas fa-phone-alt mr-1"></i> Call Now
                </a>
            </div>
        </div>
    </div>

    <!-- CTA Section -->
    <section class="relative py-16 text-white">
        <!-- Background image with opacity -->
        <div class="absolute inset-0">
            <img
                src="assets/img/banners/products_cta.jpg"
                alt="Banner Background"
                class="w-full h-full object-cover opacity-80" />
            <!-- Optional banner color overlay -->
            <div class="absolute inset-0 bg-primary opacity-60"></div>
        </div>

        <!-- Content -->
        <div class="relative container mx-auto px-4 text-center">
            <h2 class="text-3xl md:text-4xl font-bold mb-6">Need Custom Polymer Solutions?</h2>
            <p class="text-xl mb-8 max-w-3xl mx-auto">
                Our team can develop and manufacture polymer components tailored to your exact specifications.
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

    <!-- JavaScript -->
    <script src="includes/javascript/products.js"></script>
</body>

</html>