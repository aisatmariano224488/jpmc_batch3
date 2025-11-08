<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us | James Polymers - High Performance Polymer Solutions</title>

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

    <style>
        <?php include 'includes/css/contact.css'; ?>
        
        /* Additional styles for form feedback */
        .success-message {
            background-color: #d1fae5;
            border: 1px solid #10b981;
            color: #065f46;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .error-message {
            background-color: #fee2e2;
            border: 1px solid #ef4444;
            color: #7f1d1d;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .form-error {
            border-color: #ef4444 !important;
        }

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

        .contact-section {
            padding: 5rem 0;
            background: white;
            color: black;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        .flex-container {
            display: flex;
            flex-wrap: wrap;
            gap: 3rem;
        }

        .contact-info, .contact-form {
            flex: 1;
            min-width: 300px;
        }

        .section-title {
            font-size: 2.5rem;
            margin-bottom: 2rem;
            position: relative;
            display: inline-block;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 60px;
            height: 4px;
            background-color: #fdbb2d;
            border-radius: 2px;
        }

        .contact-details {
            margin-top: 2rem;
        }

        .contact-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 1.5rem;
            padding: 1rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.1);
        }

        .contact-item:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-5px);
        }

        .contact-icon {
            font-size: 1.5rem;
            margin-right: 1rem;
            color: #fdbb2d;
            min-width: 30px;
        }

        .contact-text h3 {
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
        }

        .contact-text p {
            line-height: 1.6;
        }

        .form-container {
            background: rgba(255, 255, 255, 0.1);
            padding: 2rem;
            border-radius: 12px;
            backdrop-filter: blur(10px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .form-control {
            width: 100%;
            padding: 0.8rem 1rem;
            border: none;
            border-radius: 6px;
            background: rgba(255, 255, 255, 0.9);
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            background: white;
            box-shadow: 0 0 0 3px rgba(253, 187, 45, 0.5);
        }

        textarea.form-control {
            min-height: 150px;
            resize: vertical;
        }

        .btn {
            display: inline-block;
            padding: 0.8rem 2rem;
            background: #fdbb2d;
            color: #1a2a6c;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn:hover {
            background: #ffcc44;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .btn-google-form {
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
        }

        .btn-google-form:hover {
            background: rgba(224, 154, 3, 1);
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        .button-group {
            display: flex;
            justify-content: center; /* Center the button */
            flex-wrap: wrap;
            gap: 1rem;
            width: 100%;
        }

        @media (max-width: 768px) {
            .flex-container {
                flex-direction: column;
            }
            
            .contact-info, .contact-form {
                width: 100%;
            }
            
            .section-title {
                font-size: 2rem;
            }
            
            .button-group {
                flex-direction: column;
                align-items: center; /* Center on mobile too */
            }
            
            .btn-google-form {
                margin-left: 0;
                margin-top: 0;
            }
        }
    </style>
</head>

<body class="bg-gray-50">
    <?php
    // Enable error reporting for debugging
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    // Log errors to a file
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/error_log.txt');

    // Log uncaught exceptions
    set_exception_handler(function ($e) {
        error_log("Uncaught Exception: " . $e->getMessage() . "\n" . $e->getTraceAsString());
        echo '<div style="color:red">A server error occurred. Please try again later.</div>';
    });

    // Fallback for getContent if not defined
    if (!function_exists('getContent')) {
        function getContent($page, $section, $key, $default = '') {
            return $default;
        }
    }

    // Include header
    include 'header.php';

    // Current page
    $page = 'contact';

    // Contact Form Processing
    $form_success = false;
    $form_error = '';
    $form_data = [];

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Updated sanitization for PHP 8.1+
        $form_data = [
            'fullName' => htmlspecialchars(trim($_POST["fullName"] ?? ''), ENT_QUOTES, 'UTF-8'),
            'email' => filter_var(trim($_POST["email"] ?? ''), FILTER_SANITIZE_EMAIL),
            'phone' => htmlspecialchars(trim($_POST["phone"] ?? ''), ENT_QUOTES, 'UTF-8'),
            'companyName' => htmlspecialchars(trim($_POST["companyName"] ?? ''), ENT_QUOTES, 'UTF-8'),
            'position' => htmlspecialchars(trim($_POST["position"] ?? ''), ENT_QUOTES, 'UTF-8'),
            'companyAddress' => htmlspecialchars(trim($_POST["companyAddress"] ?? ''), ENT_QUOTES, 'UTF-8'),
            'subject' => htmlspecialchars(trim($_POST["subject"] ?? ''), ENT_QUOTES, 'UTF-8'),
            'priority' => htmlspecialchars(trim($_POST["priority"] ?? ''), ENT_QUOTES, 'UTF-8'),
            'message' => htmlspecialchars(trim($_POST["message"] ?? ''), ENT_QUOTES, 'UTF-8')
        ];

        // Validate required fields
        $required_fields = ['fullName', 'email', 'phone', 'companyAddress', 'subject', 'priority', 'message'];
        $missing_fields = [];
        
        foreach ($required_fields as $field) {
            if (empty($form_data[$field])) {
                $missing_fields[] = $field;
            }
        }

        // Validate email format
        if (!empty($form_data['email']) && !filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
            $form_error = "Please enter a valid email address.";
        }

        // If no validation errors, send email
        if (empty($missing_fields) && empty($form_error)) {
            // Your email address where you want to receive messages
            $to = "aisat.castillo222436@gmail.com";
            
            // Email subject
            $email_subject = "New Contact Form Submission: " . $form_data['subject'];
            
            // Email content
            $email_body = "
            CONTACT FORM SUBMISSION - JAMES POLYMERS WEBSITE
            =============================================
            
            Personal Information:
            --------------------
            Full Name: {$form_data['fullName']}
            Email: {$form_data['email']}
            Phone: {$form_data['phone']}
            
            Company Information:
            -------------------
            Company Name: " . ($form_data['companyName'] ?: 'Not provided') . "
            Position: " . ($form_data['position'] ?: 'Not provided') . "
            Company Address: {$form_data['companyAddress']}
            
            Message Details:
            ---------------
            Subject: {$form_data['subject']}
            Priority: " . ucfirst($form_data['priority']) . "
            
            Message:
            --------
            {$form_data['message']}
            
            =============================================
            This message was sent from the contact form on James Polymers website.
            ";
            
            // Email headers
            $headers = "From: {$form_data['email']}\r\n";
            $headers .= "Reply-To: {$form_data['email']}\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
            $headers .= "X-Mailer: PHP/" . phpversion();
            
            // Send email
            if (mail($to, $email_subject, $email_body, $headers)) {
                $form_success = true;
                // Clear form data after successful submission
                $form_data = [];
            } else {
                $form_error = "Sorry, there was an error sending your message. Please try again later or contact us directly at jamespro.asia101@gmail.com";
            }
        } elseif (!empty($missing_fields)) {
            $form_error = "Please fill in all required fields marked with *.";
        }
    }
    ?>

    <!-- Hero Section -->
    <section
        data-aos="fade-down"
        class="relative bg-blue-400 h-96 flex items-center justify-center bg-cover bg-center drop-shadow-2xl animate__animated animate__fadeIn mt-[14vh]"
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

   <!-- Contact Section -->
    <section class="contact-section">
        <div class="container">
            <div class="flex-container">
                <!-- Contact Information -->
                <div class="contact-info">
                    <h2 class="section-title">Get in Touch</h2>
                    <div class="contact-details">
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
                </div>

                <!-- Contact Form -->
                <div class="contact-form">
                    <h2 class="section-title">Send Us a Message</h2>
                    <div class="form-container">
                        <form id="contactForm">
                            <div class="button-group">
                                <button type="button" class="btn-google-form" onclick="openForm()">
                                    <i class="fas fa-paper-plane mr-2"></i>Send Message
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
    

    <!-- Map Section -->
    <section id="map" class="h-96 md:h-[500px] bg-gray-100">
        <div class="map-container h-full w-full">
            <iframe src="<?php echo getContent($page, 'map', 'iframe_src', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d488.8849933426653!2d120.95594617157205!3d14.452064021803464!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397cd8f21555555%3A0xa3b07b32dcee1f3d!2sJames%20Polymers%20Manufacturing%20Corporation.!5e0!3m2!1sen!2sph!4v1746000140786!5m2!1sen!2sph'); ?>" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" class="map-container"></iframe>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative py-16 text-white">
        <!-- Background image with opacity -->
        <div class="absolute inset-0">
            <img
                src="assets/img/banners/contact_cta.jpg"
                alt="Banner Background"
                class="w-full h-full object-cover opacity-80" />
            <!-- Optional banner color overlay -->
            <div class="absolute inset-0 bg-primary opacity-60"></div>
        </div>

        <!-- Content -->
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
      <!-- Chatbot -->
    <?php include 'chatbot.php'; ?>

    <!-- Footer -->
    <?php include 'footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JavaScript -->
    <script>
        // Form validation and enhancement
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('contact-form');
            
            if (form) {
                form.addEventListener('submit', function(e) {
                    // Basic client-side validation
                    const requiredFields = form.querySelectorAll('[required]');
                    let isValid = true;
                    
                    requiredFields.forEach(field => {
                        if (!field.value.trim()) {
                            isValid = false;
                            field.classList.add('form-error');
                        } else {
                            field.classList.remove('form-error');
                        }
                    });
                    
                    if (!isValid) {
                        e.preventDefault();
                        alert('Please fill in all required fields.');
                    }
                });
                
                // Auto-scroll to form on error
                <?php if ($form_error): ?>
                    document.getElementById('contact-form').scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                <?php endif; ?>
            }
        });
        // Contact Form Google Form Link
        function openForm() {
            window.open("https://forms.gle/Hn4KY5cUWcA8HiP8A", "_blank");
        }

        document.getElementById('contactForm').addEventListener('submit', function(e) {
            e.preventDefault();
            alert('Thank you for your message! We will get back to you soon.');
            this.reset();
        });
    </script>

</body>
</html>