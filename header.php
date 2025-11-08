<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">

<link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">




<!-- Main styles -->
<style>
    /* Width */
    ::-webkit-scrollbar {
        width: 10px;
    }

    /* Track */
    ::-webkit-scrollbar-track {
        background: #f1f1f1;
        margin-right: 10px;
        /* This has NO effect! But... */
        border-left: 5px solid white;
        /* Simulates spacing */
    }

    /* Handle */
    ::-webkit-scrollbar-thumb {
        background: rgb(3, 79, 129);
        border-radius: 10px;
    }

    /* Handle on hover */
    ::-webkit-scrollbar-thumb:hover {
        background: rgb(1, 120, 199);
    }

    /* Adjustments for screen sizes between 1024px and 1280px */
    @media (min-width: 1024px) and (max-width: 1280px) {

        /* Logo size */
        .logo-responsive {
            height: 140px !important;
            width: 140px !important;
        }

        /* 45 years badge */
        .custom-45 {
            height: 3rem !important;
            /* 48px */
            width: 3rem !important;
        }

        /* Main company name */
        .custom-company-name {
            font-size: 1.5rem !important;
            /* roughly text-2xl */
        }

        /* Subtitle (Manufacturing Corporation) */
        .custom-subtitle {
            font-size: 0.9rem !important;
        }

        /* Tagline */
        .custom-tagline {
            font-size: 0.65rem !important;
        }

    }

    /* Additional responsive breakpoints for logo */
    @media (min-width: 480px) and (max-width: 639px) {
        .logo-responsive {
            height: 100px !important;
            width: 100px !important;
        }
    }

    @media (min-width: 640px) and (max-width: 767px) {
        .logo-responsive {
            height: 120px !important;
            width: 120px !important;
        }
    }

    @media (min-width: 768px) and (max-width: 1023px) {
        .logo-responsive {
            height: 130px !important;
            width: 130px !important;
        }
    }

    @media (min-width: 1281px) and (max-width: 1439px) {
        .logo-responsive {
            height: 160px !important;
            width: 160px !important;
        }
    }

    @media (min-width: 1440px) {
        .logo-responsive {
            height: 180px !important;
            width: 180px !important;
        }
    }

    /* Ensure logo container maintains aspect ratio */
    .logo-container {
        position: relative;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Make sure logo stays circular */
    .logo-responsive {
        aspect-ratio: 1/1;
        object-fit: cover;
        border-radius: 50%;
        flex-shrink: 0;
    }

    /* Hide scrollbar for certification badges */
    .scrollbar-hide {
        -ms-overflow-style: none;
        /* IE and Edge */
        scrollbar-width: none;
        /* Firefox */
    }

    .scrollbar-hide::-webkit-scrollbar {
        display: none;
        /* Chrome, Safari and Opera */
    }

    /* Ensure badges maintain aspect ratio */
    .badge-cert {
        flex-shrink: 0;
        max-width: none;
    }
</style>

<!-- Menu Styles -->
<style>
    /* Show dropdown on hover for desktop */
    @media (min-width: 1280px) {

        /* Changed from 1024px to 1280px */
        .dropdown:hover .dropdown-menu {
            display: block;
        }
    }

    /* Mobile menu styles */
    #mobile-menu {
        height: 100vh;
        overflow-y: auto;
    }

    #mobile-menu.translate-x-0 {
        transform: translateX(0);
    }

    /* Animation for text */
    @keyframes slide-in {
        0% {
            opacity: 0;
            transform: translateX(-30px);
        }

        100% {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .animate-slide-in {
        animation: slide-in 1s ease-out;
    }

    @keyframes fade-in {
        0% {
            opacity: 0;
        }

        100% {
            opacity: 1;
        }
    }

    .animate-fade-in {
        animation: fade-in 2s ease-in;
    }

    @keyframes slide-in-up {
        0% {
            opacity: 0;
            transform: translateY(30px);
        }

        100% {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-slide-in-up {
        animation: slide-in-up 1s cubic-bezier(0.23, 1, 0.32, 1);
    }

    @keyframes fade-in-up {
        0% {
            opacity: 0;
            transform: translateY(20px);
        }

        100% {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fade-in-up {
        animation: fade-in-up 1.5s cubic-bezier(0.23, 1, 0.32, 1);
    }

    .animate-fade-in-up.delay-500 {
        animation-delay: 0.5s;
    }

    .typing-tagline {
        border-right: 2px solid #1976d2;
        white-space: nowrap;
        overflow: hidden;
        width: fit-content;
        min-height: 1.5em;
        font-family: inherit;
        animation: blink-cursor 0.8s steps(1) infinite;
        position: relative;
    }

    .typing-pen {
        display: inline-block;
        margin-left: 2px;
        color: #1976d2;
        font-size: 1.1em;
        vertical-align: middle;
        transition: transform 0.1s;
    }

    @keyframes blink-cursor {

        0%,
        100% {
            border-color: #1976d2;
        }

        50% {
            border-color: transparent;
        }
    }

    .glass-header {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        box-shadow: 0 4px 24px 0 rgba(0, 0, 0, 0.04);
        border-bottom: 1px solid rgba(200, 200, 200, 0.2);
    }

    .menu-link {
        position: relative;
        transition: color 0.2s;
    }

    .menu-link::after {
        content: '';
        position: absolute;
        left: 0;
        right: 0;
        bottom: -2px;
        height: 2px;
        background: #1976d2;
        border-radius: 2px;
        transform: scaleX(0);
        transition: transform 0.3s cubic-bezier(.4, 2, .3, 1);
        transform-origin: left;
    }

    .menu-link:hover::after {
        transform: scaleX(1);
    }

    @font-face {
        font-family: 'WordDiscovery';
        src: url('assets/fonts/world_discovery/WorldDiscovery_PERSONAL_USE_ONLY.otf') format('opentype');
        font-weight: normal;
        font-style: normal;
    }

    #tagline {
        font-family: 'WordDiscovery', 'Inter', Arial, sans-serif !important;
    }

    .logo {
        height: 100%;
    }

    .logo-img {
        height: 120px;
        width: 120px;
    }

    @media (min-width: 640px) {
        .logo-img {
            height: 140px;
            width: 140px;
        }
    }

    @media (min-width: 768px) {
        .logo-img {
            height: 160px;
            width: 160px;
        }
    }

    .jp-times {
        font-family: 'Times New Roman', Times, serif !important;
    }

    .header-hidden {
        transform: translateY(-100%);
        /* Moves the header up by its full height */
    }
</style>

<style>
    /* Slide to the right animations */
    .slide-enter-right {
        opacity: 0;
        transform: translateX(-50px);
    }

    .slide-enter-right-active {
        opacity: 1;
        transform: translateX(0);
        transition: opacity 400ms ease, transform 400ms ease;
    }

    .slide-exit-right {
        opacity: 1;
        transform: translateX(0);
    }

    .slide-exit-right-active {
        opacity: 0;
        transform: translateX(50px);
        transition: opacity 400ms ease, transform 400ms ease;
    }

    /* Slide to the left animations */
    .slide-enter-left {
        opacity: 0;
        transform: translateX(50px);
    }

    .slide-enter-left-active {
        opacity: 1;
        transform: translateX(0);
        transition: opacity 400ms ease, transform 400ms ease;
    }

    .slide-exit-left {
        opacity: 1;
        transform: translateX(0);
    }

    .slide-exit-left-active {
        opacity: 0;
        transform: translateX(-50px);
        transition: opacity 400ms ease, transform 400ms ease;
    }
</style>


<!-- Header -->
<header class="bg-white shadow-sm sticky top-0 z-50 glass-header transition-transform duration-300 ease-out" style="font-family: 'Inter', 'Helvetica Neue', Arial, sans-serif;" id="main-header">
    <div class="container mx-auto px-2 sm:px-4 py-2 sm:py-3 flex items-center justify-between relative">
        <div class="flex flex-nowrap items-start gap-4 w-full mt-2 sm:mt-0">
            <div class="relative w-fit pt-2">
                <div class="relative w-fit pt-2">
                    <img src="assets/img/header/logowhitebgfinal.png" alt="James Polymers"
                        class="rounded-full object-cover border-4 border-blue-500 shadow-lg bg-white
                               aspect-square
                               w-20 sm:w-24 md:w-28 lg:w-32 xl:w-40 2xl:w-48
                               max-w-[80px] sm:max-w-[96px] md:max-w-[112px] lg:max-w-[128px] xl:max-w-[160px] 2xl:max-w-[192px]
                               transition-transform duration-500 ease-in-out hover:scale-105 mx-auto flex-shrink-0" />

                    <img src="assets/img/header/45yrs.png" alt="45 Years"
                        class="absolute -bottom-3 -right-3 sm:-bottom-4 sm:-right-4 md:-bottom-5 md:-right-5 lg:-bottom-6 lg:-right-6
                               w-8 h-8 sm:w-10 sm:h-10 md:w-12 md:h-12 lg:w-14 lg:h-14 xl:w-16 xl:h-16 2xl:w-20 2xl:h-20
                               object-contain transition-transform duration-300 ease-in-out hover:scale-105" />
                </div>
            </div>
            <div class="flex flex-col justify-start">
                <div class="custom-company-name text-lg sm:text-2xl md:text-4xl font-black text-gray-900 tracking-tight leading-tight" style="letter-spacing: -0.5px;">
                    <span style="font-family: 'Times New Roman', serif;" class="text-blue-700 text-4xl sm:text-6xl md:text-6xl italic jp-letter">J</span>AMES
                    <span style="font-family: 'Times New Roman', serif;" class="pe-1 text-red-600 text-4xl sm:text-6xl md:text-6xl italic jp-letter">P</span>OLYMERS
                    <span class="custom-subtitle block text-xs sm:text-base md:text-xl font-semibold text-blue-600 mt-1" style="letter-spacing: 0.25px;">
                        MANUFACTURING CORPORATION
                    </span>
                </div>

                <span id="tagline" class="custom-tagline custom-subblock text-xs sm:text-sm md:text-base font-light text-gray-700 mt-2 typing-tagline tracking-wide flex items-center max-w-full overflow-x-auto" style="letter-spacing: 0.5px;"></span>

                <div class="mt-2">
                    <div class="flex items-center gap-2">
                        <div class="flex flex-wrap items-center justify-start gap-1 xs:gap-2 sm:gap-3 overflow-x-auto scrollbar-hide py-2 px-1">
                            <div id="badgeGroup1" class="flex flex-wrap items-center justify-center gap-1 xs:gap-2 sm:gap-3 flex-shrink-0">
                                <img src="assets/img/footer/best-logo.png" alt="Best Logo"
                                    class="badge-cert h-6 xs:h-8 sm:h-10 md:h-12 lg:h-10 xl:h-12 2xl:h-20 w-auto object-contain flex-shrink-0 transition-transform duration-300 ease-in-out hover:scale-105 hover:drop-shadow-lg" />

                                <img src="assets/img/footer/tqcsi_9001_2.png" alt="TQCSI 9001"
                                    class="badge-cert h-8 xs:h-10 sm:h-12 md:h-14 lg:h-16 xl:h-20 2xl:h-24 w-auto object-contain flex-shrink-0 transition-transform duration-300 ease-in-out hover:scale-105 hover:drop-shadow-lg" />

                                <img src="assets/img/footer/tqcsi_14001_2.png" alt="TQCSI 14001"
                                    class="badge-cert h-8 xs:h-10 sm:h-12 md:h-14 lg:h-16 xl:h-20 2xl:h-24 w-auto object-contain flex-shrink-0 transition-transform duration-300 ease-in-out hover:scale-105 hover:drop-shadow-lg" />

                                <img src="assets/img/footer/cgmp1.png" alt="CGMP"
                                    class="badge-cert h-8 xs:h-10 sm:h-12 md:h-14 lg:h-16 xl:h-20 2xl:h-24 w-auto object-contain flex-shrink-0 transition-transform duration-300 ease-in-out hover:scale-105 hover:drop-shadow-lg" />
                            </div>

                            <div id="badgeGroup2" class="hidden flex flex-wrap items-center justify-center gap-1 xs:gap-2 sm:gap-3 flex-shrink-0">
                                <img src="assets/img/footer/rohs.png" alt="ROHS"
                                    class="badge-cert h-6 xs:h-8 sm:h-10 md:h-12 lg:h-10 xl:h-12 2xl:h-24 w-auto object-contain flex-shrink-0 transition-transform duration-300 ease-in-out hover:scale-105 hover:drop-shadow-lg" />

                                <img src="assets/img/footer/reach.png" alt="REACH"
                                    class="badge-cert h-6 xs:h-8 sm:h-10 md:h-12 lg:h-10 xl:h-12 2xl:h-20 w-auto object-contain flex-shrink-0 transition-transform duration-300 ease-in-out hover:scale-105 hover:drop-shadow-lg" />

                                <img src="assets/img/footer/ISO-9001.png" alt="ISO 9001"
                                    class="badge-cert h-8 xs:h-10 sm:h-12 md:h-14 lg:h-16 xl:h-20 2xl:h-24 w-auto object-contain flex-shrink-0 transition-transform duration-300 ease-in-out hover:scale-105 hover:drop-shadow-lg" />

                                <img src="assets/img/footer/ISO-14001.png" alt="ISO 14001"
                                    class="badge-cert h-8 xs:h-10 sm:h-12 md:h-14 lg:h-16 xl:h-20 2xl:h-24 w-auto object-contain flex-shrink-0 transition-transform duration-300 ease-in-out hover:scale-105 hover:drop-shadow-lg" />
                            </div>
                        </div>

                        <button id="toggleBtn"
                            class="text-xl text-gray-700 hover:text-blue-600 transition duration-300 flex-shrink-0 focus:outline-none">
                            &gt;
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <nav class="hidden xl:flex ml-auto items-center">
            <ul class="flex flex-nowrap space-x-7 items-center">
                <li>
                    <a href="index.php" class="menu-link flex items-center text-gray-800 hover:text-primary font-semibold uppercase text-base">Home</a>
                </li>

                <li class="dropdown relative">
                    <a href="about.php" class="menu-link text-gray-800 hover:text-primary font-semibold uppercase text-base flex items-center">
                        About
                        <!-- <i class="fas fa-chevron-down ml-1 text-xs"></i> -->
                    </a>
                    <!-- <ul class="dropdown-menu absolute left-0 top-full w-56 bg-white shadow-lg rounded-md py-3 z-50 hidden">
                        <li><a href="about.php#our-story" class="block px-4 py-3 text-gray-800 hover:bg-gray-100 text-base">Our Story</a></li>
                        <li><a href="about.php#principle" class="block px-4 py-3 text-gray-800 hover:bg-gray-100 text-base">Principle</a></li>
                        <li><a href="about.php#quality-policy" class="block px-4 py-3 text-gray-800 hover:bg-gray-100 text-base">Quality Policy</a></li>
                        <li><a href="about.php#envinronmental-policy" class="block px-4 py-3 text-gray-800 hover:bg-gray-100 text-base">Environmental Policy</a></li>
                        <li><a href="about.php#history" class="block px-4 py-3 text-gray-800 hover:bg-gray-100 text-base">Our History</a></li>
                    </ul> -->
                </li>

                <li class="dropdown relative">
                    <a href="#" class="menu-link text-gray-800 hover:text-primary font-semibold uppercase text-base flex items-center">
                        Explore
                        <i class="fas fa-chevron-down ml-1 text-xs"></i>
                    </a>
                    <ul class="dropdown-menu absolute left-0 top-full w-56 bg-white shadow-lg rounded-md py-3 z-50 hidden">
                        <li><a href="products.php" class="block px-4 py-3 text-gray-800 hover:bg-gray-100 text-base">Products</a></li>
                        <li><a href="industries.php" class="block px-4 py-3 text-gray-800 hover:bg-gray-100 text-base">Industries</a></li>
                        <li><a href="awards_recognition.php" class="block px-4 py-3 text-gray-800 hover:bg-gray-100 text-base">Awards</a></li>
                    </ul>
                </li>

                <li>
                    <a href="contact.php" class="menu-link flex items-center text-gray-800 hover:text-primary font-semibold uppercase text-base">Contact</a>
                </li>

                <li class="dropdown relative">
                    <a href="#" class="menu-link text-gray-800 hover:text-primary font-semibold uppercase text-base flex items-center">
                        More
                        <i class="fas fa-chevron-down ml-1 text-xs"></i>
                    </a>
                    <ul class="dropdown-menu absolute right-0 left-auto top-full w-56 bg-white shadow-lg rounded-md py-3 z-50 hidden">
                        <li><a href="sustainability.php" class="block px-4 py-3 text-gray-800 hover:bg-gray-100 text-base">Sustainability</a></li>
                        <!-- <li><a href="videos_promotion.php" class="block px-4 py-3 text-gray-800 hover:bg-gray-100 text-base">Videos & Promotion</a></li> -->
                        <li><a href="overviewprocess.php" class="block px-4 py-3 text-gray-800 hover:bg-gray-100 text-base">Overview Process</a></li>
                        <!-- <li><a href="shop.php" class="block px-4 py-3 text-gray-800 hover:bg-gray-100 text-base">Shop</a></li> -->
                        <li><a href="news_events.php" class="block px-4 py-3 text-gray-800 hover:bg-gray-100 text-base">News & Events</a></li>
                        <li><a href="careers.php" class="block px-4 py-3 text-gray-800 hover:bg-gray-100 text-base">Careers & Internships</a></li>
                        <!-- <li><a href="plant_visit.php" class="block px-4 py-3 text-gray-800 hover:bg-gray-100 text-base">Plant Visit</a></li> -->
                        <li><a href="faq.php" class="block px-4 py-3 text-gray-800 hover:bg-gray-100 text-base">FAQ</a></li>
                        <li><a href="privacy-policy.php" class="block px-4 py-3 text-gray-800 hover:bg-gray-100 text-base">Privacy Policy</a></li>
                    </ul>
                </li>
            </ul>
        </nav>

        <button id="mobile-menu-button" class="xl:hidden text-gray-800 ml-2 sm:ml-4 absolute right-2 top-1/2 transform -translate-y-1/2">
            <i class="fas fa-bars text-xl"></i>
        </button>
    </div>
</header>

<!-- Small device Menu Button -->
<div id="mobile-menu" class="xl:hidden fixed inset-0 bg-white z-50 transform -translate-x-full transition-transform duration-300 ease-in-out">
    <div class="p-4">
        <div class="flex justify-between items-center mb-4">
            <div class="logo flex items-center">
                <img src="assets/img/header/logo.jpg" alt="James Polymers" class="logo-responsive w-24 h-24 sm:w-28 sm:h-28 rounded-full object-cover border-4 border-blue-500 shadow-lg">
                <div class="ml-6">
                    <span class="block text-4xl font-extrabold text-gray-900 animate-slide-in"><span class="jp-times text-red-600">J</span>AMES <span class="jp-times text-blue-600">P</span>OLYMERS</span>
                    <span class="block text-xl font-bold text-blue-600 animate-fade-in">MANUFACTURING CORPORATION</span>
                </div>
            </div>
            <button id="close-menu-button" class="text-gray-800">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <nav class="mt-4">
            <ul class="space-y-6">
                <li><a href="index.php" class="block text-gray-800 hover:text-primary font-semibold uppercase text-base py-2">Home</a></li>
                <li>
                    <a href="about.php" class="block text-gray-800 hover:text-primary font-semibold uppercase text-base py-2">About Us</a>
                    <!-- <ul class="pl-4 mt-2 space-y-3">
                        <li><a href="about.php#our-story" class="block text-gray-800 hover:text-primary text-base py-2">Our Story</a></li>
                        <li><a href="about.php#principle" class="block text-gray-800 hover:text-primary text-base py-2">Principle</a></li>
                        <li><a href="about.php#quality-policy" class="block text-gray-800 hover:text-primary text-base py-2">Quality Policy</a></li>
                        <li><a href="about.php#envinronmental-policy" class="block text-gray-800 hover:text-primary text-base py-2">Environmental Policy</a></li>
                        <li><a href="about.php#history" class="block text-gray-800 hover:text-primary text-base py-2">Our History</a></li>
                    </ul> -->
                </li>
                <li><a href="products.php" class="block text-gray-800 hover:text-primary font-semibold uppercase text-base py-2">Products</a></li>
                <li><a href="industries.php" class="block text-gray-800 hover:text-primary font-semibold uppercase text-base py-2">Industries</a></li>
                <li><a href="awards_recognition.php" class="block text-gray-800 hover:text-primary font-semibold uppercase text-base py-2">Awards</a></li>
                <li><a href="contact.php" class="block text-gray-800 hover:text-primary font-semibold uppercase text-base py-2">Contact</a></li>
                <li>
                    <a href="#" class="block text-gray-800 hover:text-primary font-semibold uppercase text-base py-2">More</a>
                    <ul class="pl-4 mt-2 space-y-3">
                        <li><a href="sustainability.php" class="block text-gray-800 hover:text-primary text-base py-2">Sustainability</a></li>
                        <!-- <li><a href="videos_promotion.php" class="block text-gray-800 hover:text-primary text-base py-2">Videos & Promotion</a></li> -->
                        <li><a href="overviewprocess.php" class="block text-gray-800 hover:text-primary text-base py-2">Overview Process</a></li>
                        <!-- <li><a href="shop.php" class="block text-gray-800 hover:text-primary text-base py-2">Shop</a></li> -->
                        <li><a href="news_events.php" class="block text-gray-800 hover:text-primary text-base py-2">News & Events</a></li>
                        <li><a href="careers.php" class="block text-gray-800 hover:text-primary text-base py-2">Careers & Internships</a></li>
                        <!-- <li><a href="plant_visit.php" class="block text-gray-800 hover:text-primary text-base py-2">Plant Visit</a></li> -->
                        <li><a href="faq.php" class="block text-gray-800 hover:text-primary text-base py-2">FAQ</a></li>
                    </ul>
                </li>
            </ul>
        </nav>
    </div>
</div>

<!-- Menu Button / Hamburger -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const closeMenuButton = document.getElementById('close-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');

        mobileMenuButton.addEventListener('click', function() {
            mobileMenu.classList.remove('-translate-x-full');
            mobileMenu.classList.add('translate-x-0');
            document.body.style.overflow = 'hidden';
        });

        closeMenuButton.addEventListener('click', function() {
            mobileMenu.classList.remove('translate-x-0');
            mobileMenu.classList.add('-translate-x-full');
            document.body.style.overflow = '';
        });

        // Continuous typing animation for tagline with pen icon (no erasing, just loops)
        const taglineText = "Our Expertise is your advantage...";
        const taglineElem = document.getElementById('tagline');
        let i = 0;
        let penIcon = '<span class="typing-pen"><i class="fas fa-feather-alt"></i></span>';

        function typeTaglineLoop() {
            if (i <= taglineText.length) {
                taglineElem.innerHTML = taglineText.substring(0, i) + penIcon;
                i++;
                setTimeout(typeTaglineLoop, 60);
            } else {
                i = 0;
                setTimeout(typeTaglineLoop, 800); // Pause before restarting
            }
        }
        typeTaglineLoop();
    });

    document.addEventListener('DOMContentLoaded', function() {
        const header = document.getElementById('main-header');
        let lastScrollY = window.scrollY;
        let isScrollingUp = false;
        let scrollUpStartTime = null;
        const requiredScrollUpTime = 100; // 3 seconds (3000 ms)
        let scrollTimer = null;

        window.addEventListener('scroll', () => {
            let currentScrollY = window.scrollY;

            // Show header only when at the very top of the page (no scroll)
            if (currentScrollY === 0) {
                header.classList.remove('header-hidden');
            } else {
                // Hide header when scrolling down or up (anywhere except at top)
                header.classList.add('header-hidden');
            }

            lastScrollY = currentScrollY;
        });
    });

    //   const toggleBtn = document.getElementById('toggleBtn');
    //   const moreBadges = document.getElementById('moreBadges');

    //   toggleBtn.addEventListener('click', () => {
    //     const isHidden = moreBadges.classList.contains('hidden');
    //     moreBadges.classList.toggle('hidden');
    //     toggleBtn.textContent = isHidden ? '<' : '>';
    //   });

    //       const toggleBtn = document.getElementById('toggleBtn');
    //   const badgeGroup1 = document.getElementById('badgeGroup1');
    //   const badgeGroup2 = document.getElementById('badgeGroup2');

    //   let showingFirstGroup = true;

    //   toggleBtn.addEventListener('click', () => {
    //     if (showingFirstGroup) {
    //       badgeGroup1.classList.add('hidden');
    //       badgeGroup2.classList.remove('hidden');
    //       toggleBtn.innerHTML = '&lt;';  // Change to <
    //     } else {
    //       badgeGroup1.classList.remove('hidden');
    //       badgeGroup2.classList.add('hidden');
    //       toggleBtn.innerHTML = '&gt;';  // Change to >
    //     }
    //     showingFirstGroup = !showingFirstGroup;
    //   });
</script>

<script>
    const toggleBtn = document.getElementById('toggleBtn');
    const badgeGroup1 = document.getElementById('badgeGroup1');
    const badgeGroup2 = document.getElementById('badgeGroup2');

    let showingFirstGroup = true;

    function slideOut(element, direction, callback) {
        element.classList.add(direction === 'left' ? 'slide-exit-left' : 'slide-exit-right');
        requestAnimationFrame(() => {
            element.classList.add(direction === 'left' ? 'slide-exit-left-active' : 'slide-exit-right-active');
            setTimeout(() => {
                element.classList.add('hidden');
                element.classList.remove(direction === 'left' ? 'slide-exit-left' : 'slide-exit-right',
                    direction === 'left' ? 'slide-exit-left-active' : 'slide-exit-right-active');
                if (callback) callback();
            }, 400);
        });
    }

    function slideIn(element, direction) {
        element.classList.remove('hidden');
        element.classList.add(direction === 'left' ? 'slide-enter-left' : 'slide-enter-right');
        requestAnimationFrame(() => {
            element.classList.add(direction === 'left' ? 'slide-enter-left-active' : 'slide-enter-right-active');
            setTimeout(() => {
                element.classList.remove(direction === 'left' ? 'slide-enter-left' : 'slide-enter-right',
                    direction === 'left' ? 'slide-enter-left-active' : 'slide-enter-right-active');
            }, 400);
        });
    }

    toggleBtn.addEventListener('click', () => {
        if (showingFirstGroup) {
            // Going to second group - slide out to right, slide in from left
            slideOut(badgeGroup1, 'right', () => {
                slideIn(badgeGroup2, 'left');
            });
            toggleBtn.innerHTML = '&lt;';
        } else {
            // Going back to first group - slide out to left, slide in from right
            slideOut(badgeGroup2, 'left', () => {
                slideIn(badgeGroup1, 'right');
            });
            toggleBtn.innerHTML = '&gt;';
        }
        showingFirstGroup = !showingFirstGroup;
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>

<!-- Aos -->
<script>
    AOS.init({
        duration: 800,
        once: true,
    });
</script>