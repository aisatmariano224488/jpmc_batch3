<?php
// Include database connection
require_once 'includes/db_connection.php';

// Fetch all sections
$sections = $conn->query("SELECT * FROM about_sections ORDER BY display_order");
$timeline = $conn->query("SELECT * FROM about_timeline ORDER BY display_order");
$certifications = $conn->query("SELECT * FROM about_certifications ORDER BY display_order");
$csrSections = $conn->query("SELECT * FROM about_csr ORDER BY display_order ASC");
$csrSections = $conn->query("SELECT * FROM about_csr ORDER BY display_order ASC");
$csrCount = $csrSections->num_rows;

// Create an associative array of sections for easy access
$sections_array = [];
while ($section = $sections->fetch_assoc()) {
    $sections_array[$section['section_name']] = $section;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us | James Polymers - High Performance Polymer Solutions</title>
    <meta name="description" content="Discover James Polymers' journey, mission, and commitment to excellence in polymer solutions. Learn about our history, values, and quality standards.">

    <!--Tab Icon-->
    <link rel="icon" type="image/png" href="assets/img/tab_icon.png">

    <!-- Favicon -->
    <link rel="icon" href="assets/img/favicon.ico" type="image/x-icon">

    <!-- Open Graph / Social Media Meta Tags -->
    <meta property="og:title" content="About James Polymers - High Performance Polymer Solutions">
    <meta property="og:description" content="Discover our journey, mission, and commitment to excellence in polymer solutions.">
    <meta property="og:image" content="https://www.james-polymers.com/assets/img/social/about-og-image.jpg">
    <meta property="og:url" content="https://www.james-polymers.com/about">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700&family=Open+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Alice&family=Tinos:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">


    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        <?php include 'includes/css/about.css'; ?>
    </style>

</head>

<body class="bg-gray-50">
    <?php include 'header.php'; ?>

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

    <!-- HERO SECTION -->
<section
    data-aos="fade-down"
    class="relative bg-blue-400 h-[calc(100vh-100px)] flex items-center justify-center bg-cover bg-center mt-[0]"
    style="background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('https://www.james-polymers.com/wp-content/uploads/2021/09/products-banner.jpg');">
    
    <img
        src="assets/img/banners/about_banner.jpg"
        alt="Inclined Overlay"
        class="absolute inset-0 w-full h-full object-cover"
        style="mix-blend-mode: multiply; opacity: 1;">
    
    <div class="hero-content text-center text-white z-10 px-4">
        <h1 class="text-4xl md:text-5xl font-bold mb-6">
            Innovating Polymer & Rubber Solutions Since 1980
        </h1>
        <p class="text-xl md:text-2xl mb-8">
            Driving progress through advanced polymer technologies and sustainable manufacturing practices
        </p>
        <div class="flex flex-wrap justify-center gap-4" data-aos="fade-up" data-aos-delay="400">
            <a href="#our-story" class="bg-primary hover:bg-secondary text-white font-bold py-3 px-8 rounded-lg transition duration-300 transform hover:scale-105 shadow-lg">
                Explore Our Story
            </a>
            <a href="contact.php" class="bg-transparent border-2 border-white text-white font-bold py-3 px-8 rounded-lg hover:bg-white hover:text-primary transition duration-300 transform hover:scale-105 shadow-lg">
                Contact Our Team
            </a>
        </div>
    </div>
</section>


    <!-- COMPANY PROFILE -->
    <section id="our-story" class="section-modern" data-aos="fade-up">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <div class="text-center px-4 sm:px-6 lg:px-8 py-6">

                    <span class="text-accent font-bold text-[40px] sm:text-4xl md:text-5xl lg:text-6xl   leading-tight">
                        <span class="text-5xl sm:text-5xl md:text-6xl lg:text-7xl">W</span>ho
                        <span class="text-5xl sm:text-5xl md:text-6xl lg:text-7xl">W</span>e
                        <span class="text-5xl sm:text-5xl md:text-6xl lg:text-7xl">A</span>re
                    </span>

                    <h2 class="section-title font-semibold text-[40px] sm:text-4xl md:text-5xl lg:text-6xl leading-snug">
                        <?php echo htmlspecialchars($sections_array['company_profile']['title']); ?>
                    </h2>

                </div>
                <p class="section-subtitle"><?php echo htmlspecialchars($sections_array['company_profile']['subtitle']); ?></p>
            </div>
            <div class="flex flex-col lg:flex-row gap-12 items-center">
                <div class="w-full lg:w-1/2" data-aos="fade-right">
                    <div class="relative rounded-xl overflow-hidden shadow-2xl">
                        <img src="assets/img/about/<?php echo htmlspecialchars($sections_array['company_profile']['image']); ?>"
                            alt="James Polymers Facility"
                            class="w-full h-[200px] md:h-[350px] lg:h-[500px] object-cover transition-transform duration-500 hover:scale-105">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent flex items-end p-6">
                            <div class="text-white">
                                <h3 class="text-xl font-bold">Our State-of-the-Art Manufacturing Facility</h3>
                                <p>ISO-certified production with cutting-edge technology</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="w-full lg:w-1/2" data-aos="fade-left">
                    <div class="prose max-w-none">
                        <div class="text-justify">
                            <?php echo $sections_array['company_profile']['content']; ?>
                        </div>
                    </div>
                    <div class="mt-8 grid grid-cols-2 md:grid-cols-3 gap-4">
                        <div class="stats-card flex flex-col items-center justify-center">
                            <div class="stats-number">45</div>
                            <div class="stats-label">Years Experience</div>
                        </div>
                        <div class="stats-card flex flex-col items-center justify-center">
                            <div class="stats-number">22</div>
                            <div class="stats-label">Employees</div>
                        </div>
                        <div class="stats-card flex flex-col items-center justify-center">
                            <div class="stats-number">33</div>
                            <div class="stats-label">Company Served</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- MISSION, VISION, VALUES -->
    <section id="principle" class="section-modern bg-light py-16 px-4 sm:px-6 lg:px-8" data-aos="fade-up">
        <div class="container mx-auto">
            <div class="text-center mb-16">
                <span class="text-accent font-bold text-[40px] sm:text-4xl md:text-5xl lg:text-6xl">
                    <span class="text-5xl sm:text-5xl md:text-6xl lg:text-7xl">O</span>ur <span class="text-5xl sm:text-5xl md:text-6xl lg:text-7xl">F</span>oundation
                </span>
                <h2 class="font-semibold text-[40px] sm:text-4xl md:text-5xl lg:text-6xl"><span class="lg:underline lg:underline-offset-[7px] lg:decoration-accent">Co</span>re Principles That Guide Us</h2>
                <p class="section-subtitle mt-2 text-base sm:text-lg">The pillars that define our corporate identity and drive our decision-making</p>
            </div>

            <!-- Mission / Logo / Vision Section -->
            <div class="flex flex-col lg:flex-row gap-8">
                <!-- Mission -->
                <div class="w-full lg:w-1/3 bg-white p-6 sm:p-8 rounded-xl shadow-lg transition-transform duration-300 ease-in-out hover:scale-105" data-aos="fade-right">
                    <div class="flex flex-col items-center mb-6">
                        <div class="value-icon bg-primary mb-4 flex items-center justify-center rounded-full w-20 h-20">
                            <img src="assets/img/icons/mission2.png" alt="Mission Icon" class="w-14 h-14 object-contain" />
                        </div>
                        <h3 class="text-xl sm:text-2xl font-bold text-dark text-center">
                            <?php echo htmlspecialchars($sections_array['mission']['title']); ?>
                        </h3>
                    </div>
                    <div class="prose max-w-none text-center text-sm sm:text-base">
                        <?php echo $sections_array['mission']['content']; ?>
                    </div>
                </div>

                <!-- Logo -->
                <div class="w-full lg:w-1/3 flex items-center justify-center" data-aos="fade-up">
                    <div class="bg-white p-5 rounded-full shadow-lg border-8 border-primary/10 overflow-hidden w-64 h-64 sm:w-80 sm:h-80 md:w-96 md:h-96 flex items-center justify-center">
                        <img src="assets/img/header/logowhitebgfinal.png" alt="James Polymers Logo" class="w-full h-full object-cover scale-125">
                    </div>
                </div>

                <!-- Vision -->
                <div class="w-full lg:w-1/3 bg-white p-6 sm:p-8 rounded-xl shadow-lg transition-transform duration-300 ease-in-out hover:scale-105" data-aos="fade-left">
                    <div class="flex flex-col items-center mb-6">
                        <div class="value-icon bg-accent mb-4 flex items-center justify-center rounded-full w-20 h-20">
                            <img src="assets/img/icons/vision1.png" alt="Vision Icon" class="w-14 h-14 transition-transform duration-300 ease-in-out hover:scale-110" />
                        </div>
                        <h3 class="text-xl sm:text-2xl font-bold text-dark text-center">
                            <?php echo htmlspecialchars($sections_array['vision']['title']); ?>
                        </h3>
                    </div>
                    <div class="prose max-w-none text-center text-sm sm:text-base">
                        <?php echo $sections_array['vision']['content']; ?>
                    </div>
                </div>
            </div>

            <!-- TIMELINE / HISTORY -->
            <section id="history" class="section-modern group transition-all duration-700" data-aos="fade-up">
                <div class="w-full px-4">
                    <div class="text-center mb-16">
                        <span class="text-accent font-bold tracking-widest text-[40px] sm:text-4xl md:text-5xl lg:text-6xl">
                            <span class="text-5xl sm:text-5xl md:text-6xl lg:text-7xl">O</span>ur
                            <span class="text-5xl sm:text-5xl md:text-6xl lg:text-7xl">J</span>ourney
                        </span>
                        <h2 class="font-semibold text-[40px] sm:text-4xl md:text-5xl lg:text-6xl"><span class="lg:underline lg:underline-offset-[15px] lg:decoration-accent">Mi</span>lestones in Polymer Innovation</h2>
                        <p class="section-subtitle">Key moments that shaped our company's growth and success</p>
                    </div>

                    <div class="timeline-modern relative transition-all duration-700">

                        <!-- START label -->
                        <div class="absolute -top-8 w-full flex justify-center z-10" data-aos="fade-down">
                            <div class="text-white bg-accent rounded-full font-bold font-heading text-2xl px-[15px] py-[10px]">
                                START
                            </div>
                        </div>

                        <?php $i = 0;
                        while ($event = $timeline->fetch_assoc()): $i++; ?>
                            <div class="timeline-event<?php echo $i % 2 == 0 ? ' even' : ''; ?> transition-all duration-700"
                                data-aos="<?php echo $i % 2 == 0 ? 'fade-left' : 'fade-right'; ?>">

                                <div class="timeline-dot transition-all duration-300"></div>

                                <!-- Card with click-to-toggle group -->
                                <div onclick="toggleImage(this)" class="timeline-content relative transition-all duration-300 cursor-pointer select-none">
                                    <span class="timeline-year"><?php echo htmlspecialchars($event['year']); ?></span>
                                    <h4 class="text-xl font-bold text-primary mb-3"><?php echo htmlspecialchars($event['title']); ?></h4>
                                    <p class="text-gray-600"><?php echo htmlspecialchars($event['description']); ?></p>

                                    <?php if (!empty($event['photo']) && file_exists("assets/img/timeline/" . $event['photo'])): ?>
                                        <div class="timeline-image mt-[10px] max-h-0 opacity-0 overflow-hidden transition-all duration-700 ease-in-out">
                                            <img src="assets/img/timeline/<?php echo htmlspecialchars($event['photo']); ?>"
                                                alt="<?php echo htmlspecialchars($event['title']); ?>"
                                                class="rounded-lg shadow-sm w-full h-auto">
                                        </div>

                                        <!-- Show arrow only when photo exists -->
                                        <div class="flex justify-center items-center mt-4">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6 text-accent">
                                                <path fill-rule="evenodd" d="M12.53 16.28a.75.75 0 0 1-1.06 0l-7.5-7.5a.75.75 0 0 1 1.06-1.06L12 14.69l6.97-6.97a.75.75 0 1 1 1.06 1.06l-7.5 7.5Z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>

                        <!-- PRESENT label -->
                        <div class="absolute -bottom-8 w-full flex justify-center z-10" data-aos="fade-up">
                            <div class="text-white bg-accent rounded-full font-bold font-heading text-2xl px-[15px] py-[10px]">
                                PRESENT
                            </div>
                        </div>

                    </div>
                </div>
            </section>
    </section>

    <!-- CTA SECTION -->
    <section class="relative py-16 text-white">
        <!-- Background image with opacity -->
        <div class="absolute inset-0">
            <img
                src="assets/img/banners/about_cta.jpg"
                alt="Banner Background"
                class="w-full h-full object-cover opacity-80" />
            <!-- Optional banner color overlay -->
            <div class="absolute inset-0 bg-primary opacity-60"></div>
        </div>

        <!-- Content -->
        <div class="relative w-full px-4 text-center">
            <h2 class="section-title text-white">Ready to Collaborate on Your Next Project?</h2>
            <p class="section-subtitle text-white-100 mb-8">Our team in James Polymers Manifacturing Corporation is ready to help you solve your most challenging materials requirements.</p>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="contact.php" class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 px-8 rounded-full transition duration-300 transform hover:scale-105 shadow-lg">
                    Get in Touch
                </a>
                <a href="products.php" class="bg-transparent border-2 border-white text-white font-bold py-3 px-8 rounded-full hover:bg-white hover:text-blue-700 transition duration-300 transform hover:scale-105 shadow-lg">
                    Explore Our Products
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- AOS Animation -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    <!-- Javascript -->
    <script src="includes/javascript/about.js"></script>

</body>

</html>