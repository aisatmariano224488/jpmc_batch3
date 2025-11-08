<?php
// Include database connection
require_once 'includes/db_connection.php';

// Fetch sustainability-related sections
$sustainability_sections = $conn->query("SELECT * FROM about_sections WHERE section_name IN ('presidents_message', 'mission', 'vision', 'quality_policy', 'environmental_policy', 'csr') ORDER BY display_order");
$certifications = $conn->query("SELECT * FROM about_certifications ORDER BY display_order");
$csrSections = $conn->query("SELECT * FROM about_csr ORDER BY display_order ASC");
$csrCount = $csrSections->num_rows;

// Create an associative array of sustainability sections for easy access
$sustainability_array = [];
while ($section = $sustainability_sections->fetch_assoc()) {
    $sustainability_array[$section['section_name']] = $section;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sustainability | James Polymers - High Performance Polymer Solutions</title>
    <meta name="description"
        content="Learn about James Polymers' commitment to sustainability, quality, and environmental excellence.">

    <!--Tab Icon-->
    <link rel="icon" type="image/png" href="assets/img/tab_icon.png">

    <!-- Favicon -->
    <link rel="icon" href="assets/img/favicon.ico" type="image/x-icon">

    <!-- Open Graph / Social Media Meta Tags -->
    <meta property="og:title" content="Sustainability | James Polymers - High Performance Polymer Solutions">
    <meta property="og:description"
        content="Learn about our commitment to sustainability, quality, and environmental excellence.">
    <meta property="og:image" content="https://www.james-polymers.com/assets/img/social/sustainability-og-image.jpg">
    <meta property="og:url" content="https://www.james-polymers.com/sustainability">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Google Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700&family=Open+Sans:wght@400;500;600&display=swap"
        rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Alice&family=Tinos:ital,wght@0,400;0,700;1,400;1,700&display=swap"
        rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        <?php include 'includes/css/sustainability.css'; ?>
        
        /* Text shadow for better visibility */
        .text-shadow {
            text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.8);
        }
        
        .text-shadow-light {
            text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.6);
        }
    </style>
</head>

<body class="bg-transparent">
    <?php include 'header.php'; ?>

    <!-- Chatbot Integration -->
    <?php include 'chatbot.php'; ?>

    <!-- Individual Background Sections -->
    <div class="background-section bg-section-1 active" data-section="1"></div>
    <div class="background-section bg-section-2" data-section="2"></div>
    <div class="background-section bg-section-3" data-section="3"></div>
    <div class="background-section bg-section-4" data-section="4"></div>

    <!-- HERO SECTION -->
    <section class="snap-section hero-section" data-aos="fade-down" data-background="1">
        <!-- Uncomment the floating shapes section below if you want animated background shapes -->
        <div class="floating-shapes">
            <div class="shape shape-1 animate-float"></div>
            <div class="shape shape-2 animate-float animate-delay-1"></div>
        </div>
        <div class="hero-content">
            <h1 class="text-4xl md:text-5xl font-bold mb-6 text-shadow">Our Commitment to Sustainability</h1>
            <p class="text-xl md:text-2xl mb-8 text-shadow-light">Driving progress through sustainable practices and responsible
                manufacturing</p>
            <div class="flex flex-wrap justify-center gap-4" data-aos="fade-up" data-aos-delay="400">
                <a href="#presidents-message"
                    class="bg-primary hover:bg-secondary text-white font-bold py-3 px-8 rounded-lg transition duration-300 transform hover:scale-105 shadow-lg">
                    Learn More
                </a>
                <a href="contact.php"
                    class="bg-transparent border-2 border-white text-white font-bold py-3 px-8 rounded-lg hover:bg-white hover:text-primary transition duration-300 transform hover:scale-105 shadow-lg">
                    Contact Our Team
                </a>
            </div>
        </div>
    </section>

    <!-- PRESIDENT'S MESSAGE -->
    <?php if (isset($sustainability_array['presidents_message'])): ?>
        <section id="presidents-message" class="snap-section content-section" data-aos="fade-up" data-background="2">
            <div class="section-container">
                <!-- Uncomment the line below if you want a blurred background effect behind the president's message card -->
                <!-- <div class="absolute w-full max-w-4xl h-full rounded-2xl blur-2xl opacity-50 bg-accent/40 z-0"></div> -->
                <div class="relative z-10 border-[3px] border-primary/70 rounded-2xl p-10 md:p-14 max-w-4xl w-full mx-auto">
                    <!-- Title -->
                    <?php if (!empty($sustainability_array['presidents_message']['title'])): ?>
                        <h2
                            class="text-4xl md:text-5xl font-extrabold text-primary text-center mb-10 tracking-tight leading-tight">
                            <?php echo htmlspecialchars($sustainability_array['presidents_message']['title']); ?>
                        </h2>
                    <?php endif; ?>

                    <?php if (!empty($sustainability_array['presidents_message']['content'])): ?>
                        <div class="relative pl-6 border-l-4 border-accent/70 mb-8">
                            <div class="prose prose-lg max-w-none text-gray-800 text-[17px] leading-relaxed text-justify">
                                <?php echo htmlspecialchars($sustainability_array['presidents_message']['content']); ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Audio Play Button -->
                    <?php if (!empty($sustainability_array['presidents_message']['audio'])): ?>
                        <div class="flex justify-end mr-5 mt-6">
                            <button id="playPauseBtn" class="flex items-center gap-2 bg-accent text-white p-2 rounded-full
                        transition ease-in-out duration-200 transform
                        hover:bg-[#ff6b00] hover:-translate-y-1 hover:scale-105">

                                <!--Icon -->
                                <span id="playIcon">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                        stroke="currentColor" class="size-[30px]">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15.91 11.672a.375.375 0 0 1 0 .656l-5.603 3.113a.375.375 0 0 1-.557-.328V8.887c0-.286.307-.466.557-.327l5.603 3.112Z" />
                                    </svg>
                                </span>

                                <span id="pauseIcon" class="hidden">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                        stroke="currentColor" class="size-[30px]">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M14.25 9v6m-4.5 0V9M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                </span>
                            </button>

                            <audio id="customAudio" class="hidden">
                                <source
                                    src="assets/audio/<?php echo htmlspecialchars($sustainability_array['presidents_message']['audio']); ?>"
                                    type="audio/mpeg">
                                <source
                                    src="assets/audio/<?php echo htmlspecialchars($sustainability_array['presidents_message']['audio']); ?>"
                                    type="audio/wav">
                                Your browser does not support the audio element.
                            </audio>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- CORPORATE VALUES SECTION -->
    <section class="snap-section content-section" data-aos="fade-up" data-background="3">
        <div class="section-container">
            <div class="p-6 sm:p-8 relative">
                <h3 class="text-3xl sm:text-4xl md:text-5xl font-semibold text-dark mb-8 text-center">Our Corporate
                    Values</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 relative">
                    <!-- Center Circle Arrow (Hidden on Mobile) -->
                    <div
                        class="absolute left-1/2 top-1/2 transform -translate-x-1/2 -translate-y-1/2 z-30 hidden lg:flex justify-center items-center pointer-events-none">
                        <img src="assets/img/icons/globe_hands.png" alt="Front Image"
                            class="w-64 h-64 rotate-[-1deg]" />
                    </div>

                    <!-- Value Cards -->
                    <div class="value-card border-2 border-primary z-10 relative bg-white rounded-lg p-5">
                        <div
                            class="value-icon bg-primary mb-3 rounded-full w-16 h-16 flex items-center justify-center text-white text-xl">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <h4 class="text-lg font-bold mb-2">Just & Fair</h4>
                        <p
                            class="text-sm leading-relaxed text-center md:text-left max-w-[350px] w-full mx-auto md:mx-0">
                            Just Fair on the price that both parties should be satisfy</p>
                        <img src="assets/img/icons/arrow_blue.png" alt="Arrow Blue"
                            class="rotate-180 w-[100px] h-[100px] absolute bottom-[25px] right-5 hidden lg:block" />
                    </div>

                    <div class="value-card border-2 border-accent z-20 relative bg-white rounded-lg p-5">
                        <div class="value-icon bg-accent mb-3 rounded-full w-16 h-16 flex items-center justify-center">
                            <img src="assets/img/icons/purpose-icon1.png" alt="Purpose Icon"
                                class="w-12 h-12 object-contain" />
                        </div>
                        <h4 class="text-lg font-bold mb-2">Purpose</h4>
                        <p
                            class="text-sm leading-relaxed text-center md:text-left max-w-[350px] w-full mx-auto md:mx-0">
                            The purpose should be attainment of improvement of the organization through
                            training and development in all aspect and impact of the IMS.</p>
                        <img src="assets/img/icons/arrow_orange.png" alt="Arrow Orange"
                            class="rotate-180 w-[100px] h-[100px] absolute bottom-[25px] left-5 hidden lg:block" />
                    </div>

                    <div class="value-card border-2 border-accent z-20 relative bg-white rounded-lg p-5">
                        <div class="value-icon bg-accent mb-3 rounded-full w-16 h-16 flex items-center justify-center">
                            <img src="assets/img/icons/customer_satisfaction2.png" alt="Customer Satisfaction"
                                class="w-12 h-12 object-contain" />
                        </div>
                        <h4 class="text-lg font-bold mb-2">Customer Satisfaction</h4>
                        <p
                            class="text-sm leading-relaxed text-center md:text-left max-w-[350px] w-full mx-auto md:mx-0">
                            Customer satisfaction should be base on the performance of our organization and met the
                            target on time.</p>
                        <img src="assets/img/icons/arrow_orange.png" alt="Arrow Orange"
                            class="rotate-180 w-[100px] h-[100px] absolute bottom-[150px] right-5 rotate-[360deg] hidden lg:block" />
                    </div>

                    <div class="value-card border-2 border-primary z-20 relative bg-white rounded-lg p-5">
                        <div class="value-icon bg-primary mb-3 rounded-full w-20 h-20 flex items-center justify-center">
                            <img src="assets/img/icons/modernization.png" alt="Modernization Icon"
                                class="w-16 h-16 object-contain" />
                        </div>
                        <h4 class="text-lg font-bold mb-2">Modernization</h4>
                        <p
                            class="text-sm leading-relaxed text-center md:text-left max-w-[350px] w-full mx-auto md:mx-0">
                            Modernization should be the management instinct to give additional innovation for the purpose
                            of the business achievement.</p>
                        <img src="assets/img/icons/arrow_blue.png" alt="Arrow Blue"
                            class="rotate-180 w-[100px] h-[100px] absolute bottom-[150px] left-[20px] rotate-[360deg] hidden lg:block" />
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- INTEGRATED POLICY SECTION -->
    <section id="quality-policy" class="snap-section content-section" data-aos="fade-up" data-background="4">
        <div class="section-container">
            <div class="text-center mb-16">
                <div class="text-center px-4 sm:px-6 lg:px-8 py-6">
                    <span
                        class="text-accent font-bold tracking-widest text-[40px] sm:text-4xl md:text-5xl lg:text-6xl leading-tight">
                        <span class="text-5xl sm:text-5xl md:text-6xl lg:text-7xl">O</span>ur
                        <span class="text-5xl sm:text-5xl md:text-6xl lg:text-7xl">C</span>ommitment
                    </span>

                    <h2
                        class="policy-title font-semibold text-[40px] sm:text-4xl md:text-5xl lg:text-6xl mt-2 leading-snug">
                        <span class="lg:underline lg:underline-offset-[7px] lg:decoration-accent">In</span>tegrated
                        Quality, Environmental & Safety Policy
                    </h2>
                </div>
                <p class="section-subtitle">Our comprehensive approach to responsible manufacturing and continuous
                    improvement</p>
            </div>

            <div class="max-w-4xl mx-auto p-8 md:p-12 rounded-xl transition-all duration-700" data-aos="zoom-in">
                <div class="text-center mb-8">
                    <div class="inline-block text-5xl text-black px-6 py-2 rounded-full mb-4" data-aos="fade-up">
                        <h4 class="underline decoration-accent underline-offset-[15px] font-bold tracking-widest">POLICY
                        </h4>
                    </div>
                    <p class="text-lg font-medium" data-aos="fade-up">
                        James Polymers Manufacturing Corporation is committed to sustainable plastic and rubber
                        injection operations to protect the environment and ensure consistent customer satisfaction by:
                    </p>
                </div>

                <div class="grid md:grid-cols-2 gap-8">
                    <div data-aos="fade-up">
                        <h5 class="font-bold text-lg mb-4 flex items-center">
                            <i class="fas fa-check-circle text-accent mr-2"></i> Our Commitments
                        </h5>
                        <ul class="space-y-3 font-heading">
                            <li class="flex items-start" data-aos="fade-up">
                                <i class="fas fa-check text-accent mt-1 mr-2"></i>
                                <span>Fulfilling applicable statutory, customer & other requirements, commitments and
                                    compliance obligations relevant to the context of the organization</span>
                            </li>
                            <li class="flex items-start" data-aos="fade-up">
                                <i class="fas fa-check text-accent mt-1 mr-2"></i>
                                <span>Prevention of pollution through implementation of waste management</span>
                            </li>
                            <li class="flex items-start" data-aos="fade-up">
                                <i class="fas fa-check text-accent mt-1 mr-2"></i>
                                <span>Efficient operations leading to energy and other resources conservation, quality
                                    and reliable product thereby reducing nonconforming products</span>
                            </li>
                        </ul>
                    </div>

                    <div data-aos="fade-up">
                        <h5 class="font-bold text-lg mb-4 flex items-center">
                            <i class="fas fa-shield-alt text-accent mr-2"></i> Safety Focus
                        </h5>
                        <ul class="space-y-3 font-heading">
                            <li class="flex items-start" data-aos="fade-up">
                                <i class="fas fa-check text-accent mt-1 mr-2"></i>
                                <span>Providing a safe and healthy work environment with proper employee training on
                                    machinery operation</span>
                            </li>
                            <li class="flex items-start" data-aos="fade-up">
                                <i class="fas fa-check text-accent mt-1 mr-2"></i>
                                <span>Following established safety procedures and utilizing appropriate personal
                                    protective equipment</span>
                            </li>
                            <li class="flex items-start" data-aos="fade-up">
                                <i class="fas fa-check text-accent mt-1 mr-2"></i>
                                <span>Maintaining equipment in good condition and adhering to strict quality control
                                    standards</span>
                            </li>
                            <li class="flex items-start" data-aos="fade-up">
                                <i class="fas fa-check text-accent mt-1 mr-2"></i>
                                <span>Promoting hazard awareness and conducting regular safety audits for continuous
                                    improvement</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="mt-8 bg-primary/10 p-6 rounded-lg transition-all duration-300" data-aos="fade-up">
                    <p class="mb-4 text-lg">
                        All employees are made aware of the quality and environment management system related activities
                        to instill commitment to continual improvement aiming to enhance quality and environmental
                        performance.
                    </p>
                    <p class="text-primary font-bold text-lg text-center">
                        "SATISFACTION THRU QUALITY and SUSTAINABILITY is our ultimate commitment to keep relevant
                        focusing on customer and other relevant parties' needs and expectations."
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- QUALITY & ENVIRONMENTAL POLICY SECTION -->
    <section id="environmental-policy" class="snap-section content-section" data-aos="fade-up" data-background="4">
        <div class="section-container">

            <!-- Title -->
            <div class="text-center px-4 sm:px-6 lg:px-8 py-6">
                <span
                    class="text-accent font-bold tracking-widest text-[40px] sm:text-4xl md:text-5xl lg:text-6xl leading-tight">
                    <span class="text-5xl sm:text-5xl md:text-6xl lg:text-7xl">O</span>ur
                    <span class="text-5xl sm:text-5xl md:text-6xl lg:text-7xl">S</span>tandard
                </span>

                <h2 class="font-semibold text-[40px] sm:text-4xl md:text-5xl lg:text-6xl mt-2 leading-snug">
                    <span class="lg:underline lg:underline-offset-[15px] lg:decoration-accent">Qu</span>uality &
                    Environmental Excellence
                </h2>
            </div>

            <!-- Policies Side by Side -->
            <div class="flex flex-col lg:flex-row gap-12">
                <!-- Quality Policy -->
                <div class="w-full lg:w-1/2 p-8" data-aos="fade-up">
                    <h3 class="text-3xl font-bold tracking-wide mb-6 flex items-center">
                        <i class="fas fa-award text-primary mr-3 mt-[7px]"></i>
                        <?php echo htmlspecialchars($sustainability_array['quality_policy']['title']); ?>
                    </h3>
                    <div class="prose max-w-none font-heading">
                        <?php echo $sustainability_array['quality_policy']['content']; ?>
                    </div>
                </div>

                <!-- Environmental Policy -->
                <div class="w-full lg:w-1/2 p-8" data-aos="fade-up">
                    <h3 class="text-3xl font-bold tracking-wide mb-6 flex items-center">
                        <i class="fas fa-leaf text-green-700 mr-3"></i>
                        <?php echo htmlspecialchars($sustainability_array['environmental_policy']['title']); ?>
                    </h3>
                    <div class="prose max-w-none font-heading">
                        <?php echo $sustainability_array['environmental_policy']['content']; ?>
                    </div>
                </div>
            </div>

            <!-- Certificates Section -->
            <div class="mt-12" data-aos="fade-up">
                <h3 class="text-2xl font-bold mb-8 text-center">Our Certifications</h3>

                <!-- First 4 Certificates -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-8">
                    <?php
                    $certs = [];
                    while ($cert = $certifications->fetch_assoc()) {
                        $certs[] = $cert;
                    }

                    for ($i = 0; $i < min(4, count($certs)); $i++): ?>
                        <div class="certification-badge" data-aos="flip-left" data-aos-delay="200">
                            <div class="text-center">
                                <img src="assets/img/certifications/<?php echo htmlspecialchars($certs[$i]['image']); ?>"
                                    alt="<?php echo htmlspecialchars($certs[$i]['title']); ?>"
                                    class="max-w-full h-auto mb-3"
                                    title="<?php echo htmlspecialchars($certs[$i]['title']); ?>" data-bs-toggle="tooltip"
                                    data-bs-placement="top">
                                <h4 class="text-sm font-semibold text-gray-800 mt-2">
                                    <?php echo htmlspecialchars($certs[$i]['title']); ?>
                                </h4>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>

                <!-- Remaining Certificates -->
                <?php if (count($certs) > 4): ?>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                        <?php for ($i = 4; $i < count($certs); $i++): ?>
                            <div class="certification-badge" data-aos="flip-left" data-aos-delay="200">
                                <div class="text-center">
                                    <img src="assets/img/certifications/<?php echo htmlspecialchars($certs[$i]['image']); ?>"
                                        alt="<?php echo htmlspecialchars($certs[$i]['title']); ?>"
                                        class="max-w-full h-auto mb-3"
                                        title="<?php echo htmlspecialchars($certs[$i]['title']); ?>" data-bs-toggle="tooltip"
                                        data-bs-placement="top">
                                    <h4 class="text-sm font-semibold text-gray-800 mt-2">
                                        <?php echo htmlspecialchars($certs[$i]['title']); ?>
                                    </h4>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Continual Improvement & Kaizen (inside container now) -->
            <div class="w-full mx-auto text-black px-6 md:px-8 py-12">
                <div class="flex flex-col md:flex-row gap-12 items-start">

                    <!-- Left Column - Images -->
                    <div class="flex flex-col items-center gap-10 w-full md:w-1/2" data-aos="fade-up">
                        <div class="flex flex-col items-center">
                            <img src="assets/img/icons/continuous_improvent_1.png" alt="Continual Improvement"
                                class="rounded-lg w-full max-w-[350px] h-auto object-cover transition-transform duration-300 hover:scale-105" />
                        </div>
                        <div class="flex flex-col items-center">
                            <img src="assets/img/icons/Kaizen.png" alt="Kaizen"
                                class="rounded-lg w-full max-w-[350px] h-auto object-cover transition-transform duration-300 hover:scale-105" />
                        </div>
                    </div>

                    <!-- Right Column - Text Sections -->
                    <div class="w-full md:w-1/2 flex flex-col gap-12" data-aos="fade-up">

                        <!-- First Section - Continual Improvement -->
                        <div>
                            <h3 class="text-4xl sm:text-5xl font-heading font-bold mb-4">
                                Continual Improvement
                            </h3>
                            <p class="mb-6 text-base sm:text-lg font-heading">
                                We regularly review and enhance our management systems to ensure they remain
                                effective and aligned with industry best practices.
                            </p>
                            <ul class="space-y-4">
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle mt-1 mr-3 text-accent flex-shrink-0"></i>
                                    <span class="font-heading">Recognize areas for improvement</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle mt-1 mr-3 text-accent flex-shrink-0"></i>
                                    <span class="font-heading">Set a clear improvement goal</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle mt-1 mr-3 text-accent flex-shrink-0"></i>
                                    <span class="font-heading">Understand the process in detail</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle mt-1 mr-3 text-accent flex-shrink-0"></i>
                                    <span class="font-heading">Create and prepare the solution</span>
                                </li>
                            </ul>
                        </div>

                        <!-- Second Section - Kaizen -->
                        <div>
                            <h3 class="text-4xl sm:text-5xl font-heading font-bold mb-4">
                                Kaizen Approach
                            </h3>
                            <p class="mb-6 text-base sm:text-lg font-heading">
                                Kaizen emphasizes continuous, incremental improvements that involve every employee at
                                every level of the organization.
                            </p>
                            <ul class="space-y-4">
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle mt-1 mr-3 text-accent flex-shrink-0"></i>
                                    <span class="font-heading">Encourage small, consistent changes</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle mt-1 mr-3 text-accent flex-shrink-0"></i>
                                    <span class="font-heading">Engage employees in problem-solving</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle mt-1 mr-3 text-accent flex-shrink-0"></i>
                                    <span class="font-heading">Monitor and refine processes regularly</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle mt-1 mr-3 text-accent flex-shrink-0"></i>
                                    <span class="font-heading">Foster a culture of improvement</span>
                                </li>
                            </ul>
                        </div>

                    </div>
                </div>
            </div>

        </div> <!-- End section-container -->
    </section>



    <!-- CSR SECTION -->
    <section id="csr" class="snap-section content-section" data-aos="fade-up" data-background="4">
        <div class="section-container">
            <div class="text-center px-4 sm:px-6 lg:px-8 py-6">
                <span
                    class="text-accent font-bold tracking-normal text-[40px] sm:text-4xl md:text-5xl lg:text-6xl leading-tight">
                    <span class="text-5xl sm:text-5xl md:text-6xl lg:text-7xl">O</span>ur
                    <span class="text-5xl sm:text-5xl md:text-6xl lg:text-7xl">C</span>orporate
                    <span class="text-5xl sm:text-5xl md:text-6xl lg:text-7xl">S</span>ocial
                    <span class="text-5xl sm:text-5xl md:text-6xl lg:text-7xl">R</span>esponsibility (CSR)
                </span>

                <h2 class="font-semibold text-[40px] sm:text-4xl md:text-5xl lg:text-6xl mt-2">
                    <span class="lg:underline lg:underline-offset-[15px] lg:decoration-accent">Co</span>mmitment
                </h2>
            </div>

            <div class="relative flex flex-col items-center">
                <?php
                $csrSections->data_seek(0);
                $i = 0;
                while ($csr = $csrSections->fetch_assoc()):
                ?>
                    <div
                        class="relative flex flex-col <?php echo $i % 2 !== 0 ? 'md:flex-row-reverse' : 'md:flex-row'; ?> items-center gap-8 mb-12 w-full">
                        <div class="w-full md:w-1/2 flex items-center justify-center relative z-20"
                            style="height:420px; min-width:320px; max-width:full;" data-aos="fade-up">
                            <img src="assets/img/about/<?php echo htmlspecialchars($csr['image']); ?>" alt="CSR Image"
                                class="rounded-xl shadow-2xl w-full h-full object-cover"
                                style="height:100%; width:100%; object-fit:cover;">

                            <p class="absolute bottom-2 right-2 text-xs text-white bg-black/60 px-2 py-1 rounded">
                                <?php echo htmlspecialchars($csr['author_credit']); ?>
                            </p>
                        </div>

                        <div class="w-full md:w-1/2 flex flex-col items-center <?php echo $i % 2 !== 0 ? 'md:items-end md:justify-end text-right' : 'md:items-start md:justify-start text-left'; ?> relative z-10"
                            data-aos="<?php echo $i % 2 !== 0 ? 'fade-right' : 'fade-left'; ?>">
                            <h4 class="text-4xl font-alice font-bold mb-2">
                                <?php echo htmlspecialchars($csr['title']); ?>
                            </h4>
                            <p class="text-lg font-sans"><?php echo htmlspecialchars($csr['subtitle']); ?></p>
                        </div>
                    </div>
                <?php $i++;
                endwhile; ?>
            </div>
        </div>
    </section>

    <!-- CTA SECTION -->
    <section class="snap-section content-section py-16 text-white" data-aos="fade-up" data-background="4">
        <!-- Content -->
        <div class="text-center">
            <h2 class="text-3xl md:text-4xl font-bold mb-6">Ready to Partner with a Sustainable Manufacturer?
            </h2>
            <p class="text-xl mb-8 max-w-3xl mx-auto">Join us in our commitment to environmental responsibility
                and
                sustainable manufacturing practices.</p>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="contact.php"
                    class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 px-8 rounded-full transition duration-300 transform hover:scale-105 shadow-lg">
                    Get in Touch
                </a>
                <a href="products.php"
                    class="bg-transparent border-2 border-white text-white font-bold py-3 px-8 rounded-full hover:bg-white hover:text-blue-700 transition duration-300 transform hover:scale-105 shadow-lg">
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
    <script src="includes/javascript/sustainability.js"></script>
</body>

</html>