<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us | James Polymers - High Performance Polymer Solutions</title>

    <link rel="icon" type="image/png" href="assets/img/tab_icon.png">
    

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

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

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        /*
        * ----------------------------------------
        * GLOBAL/BASE STYLES
        * ----------------------------------------
        */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f8f9fa;
            color: #333;
        }
        
        /* Define the container width for the contact card */
        .container {
            max-width: 700px; 
            margin: 0 auto;
            padding: 0 15px;
        }

        /* ---------------------------------------- */
        /* CONTACT SECTION STYLES */
        /* ---------------------------------------- */
        .contact-section {
            padding: 80px 0;
            background-color: #f8f9fa;
        }

        .contact-card {
            /* Styles to make it look like a unified box */
            background-color: #ffffff;
            padding: 40px;
            border-radius: 8px; /* Soft rounded corners */
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); /* Subtle elevation */
        }
        
        .page-title {
            font-size: 2.5rem;
            margin-bottom: 5px;
            color: #333;
        }

        .breadcrumb {
            font-size: 0.9em;
            color: #6c757d;
            margin-bottom: 40px;
        }


        /* --- TITLE STYLING: REMOVED BLUE/GOLD LINE --- */
        .section-title {
            font-size: 2.5rem;
            margin-bottom: 2rem;
            position: relative;
            display: inline-block;
            /* Ensure NO border/line is drawn here */
            border-bottom: none !important;
        }

        /* Explicitly remove the pseudo-element that was drawing the line */
        .section-title::after {
            content: none !important;
        }

        .send-message-title {
            margin-top: 40px;
        }

        /* ---------------------------------------- */
        /* CONTACT DETAILS (TWO COLUMN LAYOUT) */
        /* ---------------------------------------- */
        .contact-details-grid {
            display: flex;        
            gap: 40px;           
            margin-bottom: 40px;  
            flex-wrap: wrap;      
        }

        .contact-details-left,
        .contact-details-right {
            flex: 1;             
            min-width: 250px;    
            display: flex;
            flex-direction: column;
            gap: 25px; 
            /* Fix for potential blue line/border on left */
            border-left: none !important;
            padding-left: 0 !important;
        }

        .contact-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 0; /* Adjusted from 1.5rem to use gap in parent */
            padding: 1rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            background: rgba(240, 240, 240, 0.5); /* Light background for visibility */
            /* Ensure no unwanted border */
            border-left: none !important;
        }

        .contact-item:hover {
            background: rgba(230, 230, 230, 0.8);
            transform: translateY(-2px);
        }

        .contact-icon {
            font-size: 1.5rem;
            margin-right: 1rem;
            color: #fdbb2d; /* Gold/Yellow color for icons */
            min-width: 30px;
        }

        .contact-text h3 {
            font-size: 1.2rem;
            margin-bottom: 0.2rem;
            /* Ensure no border here */
            border-bottom: none !important;
        }

        .contact-text p {
            line-height: 1.6;
            margin-bottom: 0;
        }

        /* ---------------------------------------- */
        /* BUTTON/FORM STYLES */
        /* ---------------------------------------- */
        .form-container {
            text-align: center; /* Center the form content */
            margin-top: 20px;
        }

        .button-group {
            display: flex;
            justify-content: center; /* Center the button */
            width: 100%;
        }
        
        .btn-google-form {
            /* Existing styles */
            background: #f8c436ff;
            border: 2px solid #fdbb2d;
            color: black;
            padding: 12px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            
            /* Alignment styles */
            width: auto; 
            margin: 0;
        }

        .btn-google-form:hover {
            background: rgba(224, 154, 3, 1);
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        /* ---------------------------------------- */
        /* RESPONSIVENESS */
        /* ---------------------------------------- */
        @media (max-width: 768px) {
            .contact-details-grid {
                flex-direction: column; /* Stack columns vertically on smaller screens */
            }
            
            .section-title {
                font-size: 2rem;
            }
        }
    </style>
</head>

<body class="bg-gray-50">
    <?php
    // PHP variables and logic are kept intact for context
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/error_log.txt');

    

    set_exception_handler(function ($e) {
        error_log("Uncaught Exception: " . $e->getMessage() . "\n" . $e->getTraceAsString());
        echo '<div style="color:red">A server error occurred. Please try again later.</div>';
    });

    if (!function_exists('getContent')) {
        function getContent($page, $section, $key, $default = '') {
            return $default;
        }
    }

    // Include header
    include 'header.php'; // Commented out as files are not provided

    $page = 'contact';
    $form_success = false;
    $form_error = '';
    $form_data = [];

    // Form submission processing logic is omitted for brevity as it's not HTML/CSS related
    ?>

    <section
        data-aos="fade-down"
        class="relative bg-blue-400 h-96 flex items-center justify-center bg-cover bg-center drop-shadow-2xl animate__animated animate__fadeIn"
        style="background-image: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('<?php echo getContent($page, 'banner', 'background_image', 'https://www.james-polymers.com/wp-content/uploads/2021/09/contact-banner.jpg'); ?>')">
        <img
            src="assets/img/banners/contact_banner.jpg"
            alt="Inclined Overlay"
            class="absolute inset-0 w-full h-full object-cover"
            style="mix-blend-mode: multiply; opacity: 1;" />

        <div class="container mx-auto px-4 text-center text-white relative z-10">
            <h1 class="text-4xl md:text-5xl font-bold mb-4"><?php echo getContent($page, 'banner', 'heading', 'Contact Us'); ?></h1>
            <div class="flex justify-center items-center text-sm md:text-base">
                <a href="./index.php" class="text-white hover:text-blue-300">Home</a>
                <span class="mx-2">/</span>
                <span class="text-blue-300"><?php echo getContent($page, 'banner', 'breadcrumb', 'Contact'); ?></span>
            </div>
        </div>
    </section>

    <section class="contact-section">
        <div class="container">
            
            <div class="contact-card"> 
                
                <h1 class="page-title">Contact Us</h1>
                <h2 class="section-title">Get in Touch</h2>
                
                <div class="contact-details-grid"> 
                    
                    <div class="contact-details-left">
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="contact-text">
                                <h3>Address</h3>
                                <p>016 Panapaan 2, Bacoor City, 4102, Cavite, Philippines</p>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-phone-alt"></i>
                            </div>
                            <div class="contact-text">
                                <h3>Phone</h3>
                                <p>+63 (2) 85298978</p>
                            </div>
                        </div>
                    </div> 
                    
                    <div class="contact-details-right">
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="contact-text">
                                <h3>Email</h3>
                                <p>jamespro_asia@yahoo.com</p>
                                <p>jamespolymers.international@gmail.com</p>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="contact-text">
                                <h3>Hours</h3>
                                <p>Monday-Friday: 8:00am - 5:00pm</p>
                            </div>
                        </div>
                    </div> 

                </div> <h2 class="section-title send-message-title">Send Us a Message</h2> 
                
                <div class="form-container">
                    <form id="contactForm">
                        <div class="button-group"> 
                            <button type="button" class="btn-google-form" onclick="openForm()">
                                <i class="fas fa-paper-plane mr-2"></i>Send Message
                            </button>
                        </div>
                    </form>
                </div>
                
            </div> </div>
    </section>
    
    <section id="map" class="h-96 md:h-[500px] bg-gray-100">
        <div class="map-container h-full w-full">
            <iframe src="<?php echo getContent($page, 'map', 'iframe_src', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d488.8849933426653!2d120.95594617157205!3d14.452064021803464!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397cd8f21555555%3A0xa3b07b32dcee1f3d!2sJames%20Polymers%20Manufacturing%20Corporation.!5e0!3m2!1sen!2sph!4v1746000140786!5m2!1sen!2sph'); ?>" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" class="map-container"></iframe>
        </div>
    </section>

    <section class="relative py-16 text-white">
        <div class="absolute inset-0">
            <img
                src="assets/img/banners/contact_cta.jpg"
                alt="Banner Background"
                class="w-full h-full object-cover opacity-80" />
            <div class="absolute inset-0 bg-primary opacity-60"></div>
        </div>

        <div class="relative container mx-auto px-4 text-center">
            <h2 class="text-3xl md:text-4xl font-bold mb-6">
                <?php echo getContent($page, 'cta', 'heading', 'Ready to Discuss Your Project?'); ?>
            </h2>
            <p class="text-xl mb-8 max-w-3xl mx-auto">
                <?php echo getContent($page, 'cta', 'description', 'Our technical sales team is available to help you select the right polymer solution for your application.'); ?>
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="products.php" class="bg-transparent border-2 border-white text-white font-bold py-3 px-8 rounded-lg hover:bg-white hover:text-primary transition duration-300">
                    <?php echo getContent($page, 'cta', 'button2_text', 'View Products'); ?>
                </a>
            </div>
        </div>
    </section>
    
    <?php // include 'chatbot.php'; ?>
    
    <?php  include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Contact Form Google Form Link
        function openForm() {
            window.open("https://forms.gle/Hn4KY5cUWcA8HiP8A", "_blank");
        }

        document.getElementById('contactForm').addEventListener('submit', function(e) {
            e.preventDefault();
            alert('Thank you for your message! We will redirect you to the form.');
            openForm(); // Open the external form
        });
        
        // Removed unnecessary DOMContentLoaded listener and client-side validation for brevity
    </script>

</body>
</html>