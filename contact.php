<?php
// contact.php

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
require_once 'includes/db_connection.php';

// Load PHPMailer
require_once __DIR__ . '/vendor/autoload.php';
use Brevo\Client\Api\TransactionalEmailsApi;
use Brevo\Client\Configuration;
use Brevo\Client\Model\SendSmtpEmail;

$form_success = '';
$form_error = '';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $name = $conn->real_escape_string($_POST['fullName']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $companyName = $conn->real_escape_string($_POST['companyName']);
    $position = $conn->real_escape_string($_POST['position']);
    $companyAddress = $conn->real_escape_string($_POST['companyAddress']);
    $subject = $conn->real_escape_string($_POST['subject']);
    $priority = $conn->real_escape_string($_POST['priority']);
    $message = $conn->real_escape_string($_POST['message']);
    $date_submitted = date('Y-m-d H:i:s');
    $status = 'new';

    // Insert into inquiries table
    $sql = "INSERT INTO inquiries 
    (name, email, phone, company, position, address, subject, priority, message, status, date_submitted) 
    VALUES 
    ('$name', '$email', '$phone', '$companyName', '$position', '$companyAddress', '$subject', '$priority', '$message', '$status', '$date_submitted')";

    if ($conn->query($sql)) {
        $form_success = "Your message has been sent successfully!";

        // Send notification email using Brevo SMTP
       // Send notification email using Brevo SDK
try {
    $brevo_api_key = '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';
    $config = Configuration::getDefaultConfiguration()->setApiKey('api-key', $brevo_api_key);
    $apiInstance = new TransactionalEmailsApi(new GuzzleHttp\Client(), $config);

    // Create email content
    $email_content = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #0066cc; color: white; padding: 20px; text-align: center; }
            .content { background-color: #f9f9f9; padding: 20px; border: 1px solid #ddd; }
            .field { margin-bottom: 15px; }
            .label { font-weight: bold; color: #0066cc; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>New Inquiry from Website</h2>
            </div>
            <div class='content'>
                <div class='field'>
                    <span class='label'>Name:</span> " . htmlspecialchars($name) . "
                </div>
                <div class='field'>
                    <span class='label'>Email:</span> " . htmlspecialchars($email) . "
                </div>
                <div class='field'>
                    <span class='label'>Phone:</span> " . htmlspecialchars($phone) . "
                </div>
                <div class='field'>
                    <span class='label'>Company:</span> " . htmlspecialchars($companyName) . "
                </div>
                <div class='field'>
                    <span class='label'>Position:</span> " . htmlspecialchars($position) . "
                </div>
                <div class='field'>
                    <span class='label'>Address:</span> " . htmlspecialchars($companyAddress) . "
                </div>
                <div class='field'>
                    <span class='label'>Subject:</span> " . htmlspecialchars($subject) . "
                </div>
                <div class='field'>
                    <span class='label'>Priority:</span> " . htmlspecialchars($priority) . "
                </div>
                <div class='field'>
                    <span class='label'>Message:</span><br>
                    " . nl2br(htmlspecialchars($message)) . "
                </div>
                <div class='field'>
                    <span class='label'>Submitted at:</span> " . htmlspecialchars($date_submitted) . "
                </div>
            </div>
        </div>
    </body>
    </html>
    ";

    $sendSmtpEmail = new SendSmtpEmail([
        'subject' => 'New Inquiry from Website - ' . $subject,
        'sender' => ['name' => 'JPMC Website', 'email' => 'aisat.castillo222436@gmail.com'],
        'to' => [['email' => 'aisat.castillo222436@gmail.com', 'name' => 'Admin']],
        'replyTo' => ['email' => $email, 'name' => $name],
        'htmlContent' => $email_content
    ]);

    $apiInstance->sendTransacEmail($sendSmtpEmail);
} catch (Exception $e) {
    $form_error .= " But email notification could not be sent. Error: " . $e->getMessage();
}
    } else {
        $form_error = "Something went wrong while saving your inquiry. Please try again.";
    }
}
?>


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
        function getContent($page, $section, $key, $default = '')
        {
            return $default;
        }
    }

    // Include functions
    // include 'includes/functions.php'; // <-- Commented out because file is missing

    // Include header
    include 'header.php';

    // Current page
    $page = 'contact';

    // Add Composer autoload for Brevo SDK
    ?>

    <!-- Hero Section (copied from products.php, customizable background) -->
    <section
        data-aos="fade-down"
        class="relative bg-blue-400 h-96 flex items-center justify-center bg-cover bg-center drop-shadow-2xl animate__animated animate__fadeIn"
        style="background-image: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('<?php echo getContent($page, 'banner', 'background_image', 'https://www.james-polymers.com/wp-content/uploads/2021/09/contact-banner.jpg'); ?>')">
        <!-- Inclined overlay image (optional, replace src to customize) -->
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
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4"><?php echo getContent($page, 'contact_info', 'heading', 'Get In Touch'); ?></h2>
                <p class="text-gray-600 max-w-3xl mx-auto"><?php echo getContent($page, 'contact_info', 'subheading', 'Our team is ready to assist you with any questions about our polymer solutions and services.'); ?></p>
            </div>

            <!-- Contact Methods -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16">
                <div class="contact-method bg-white rounded-lg shadow-md hover:shadow-2xl transition-shadow duration-300 animate__animated animate__fadeInUp p-6 text-center">
                    <div class="bg-primary bg-opacity-10 w-16 h-16 rounded-full flex items-center justify-center mb-4 mx-auto">
                        <i class="fas fa-phone-alt text-primary text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2"><?php echo getContent($page, 'contact_info', 'phone_title', 'Call Us'); ?></h3>
                    <p class="text-gray-600 mb-4"><?php echo getContent($page, 'contact_info', 'phone_description', 'Speak directly with our technical team'); ?></p>
                    <a href="tel:<?php echo getContent($page, 'contact_info', 'phone_number', '+(02) 8529 8978'); ?>" class="text-primary font-semibold hover:text-secondary transition"><?php echo getContent($page, 'contact_info', 'phone_display', '+63 (2) 852989785'); ?></a>
                </div>

                <div class="contact-method bg-white rounded-lg shadow-md p-6 text-center">
                    <div class="bg-primary bg-opacity-10 w-16 h-16 rounded-full flex items-center justify-center mb-4 mx-auto">
                        <i class="fas fa-envelope text-primary text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2"><?php echo getContent($page, 'contact_info', 'email_title', 'Email Us'); ?></h3>
                    <p class="text-gray-600 mb-4"><?php echo getContent($page, 'contact_info', 'email_description', 'Get detailed information about our products'); ?></p>
                    <a href="mailto:<?php echo getContent($page, 'contact_info', 'email_address', 'jamespro.asia101@gmail.com'); ?>" class="text-primary font-semibold hover:text-secondary transition"><?php echo getContent($page, 'contact_info', 'email_display', 'jamespro.asia101@gmail.com'); ?></a>
                </div>

                <div class="contact-method bg-white rounded-lg shadow-md p-6 text-center">
                    <div class="bg-primary bg-opacity-10 w-16 h-16 rounded-full flex items-center justify-center mb-4 mx-auto">
                        <i class="fas fa-map-marker-alt text-primary text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2"><?php echo getContent($page, 'contact_info', 'location_title', 'Visit Us'); ?></h3>
                    <p class="text-gray-600 mb-4"><?php echo getContent($page, 'contact_info', 'location_description', 'Schedule a visit to our facilities'); ?></p>
                    <a href="#map" class="text-primary font-semibold hover:text-secondary transition"><?php echo getContent($page, 'contact_info', 'location_link_text', 'View Location'); ?></a>
                </div>
            </div>



        <div class="border border-gray-800 shadow-lg shadow-gray-800/50 py-24 rounded-lg">
            <!-- Your Contact Form Section -->
<div class="bg-white rounded-xl shadow-lg shadow-gray-900/80 p-8 max-w-5xl mx-auto border border-gray-300 mb-10">
    <h2 class="text-2xl font-bold text-center mb-6">Send us a message</h2>

    <!-- Success/Error Messages -->
    <?php if($form_success): ?>
        <div class="text-green-600 font-bold text-center mb-4"><?php echo $form_success; ?></div>
    <?php endif; ?>
    <?php if($form_error): ?>
        <div class="text-red-600 font-bold text-center mb-4"><?php echo $form_error; ?></div>
    <?php endif; ?>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Left Column -->
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1" for="fullName">Full Name*</label>
                <input type="text" name="fullName" id="fullName" required class="w-full border border-gray-300 rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-blue-400" placeholder="John Doe">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1" for="email">Email Address*</label>
                <input type="email" name="email" id="email" required class="w-full border border-gray-300 rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-blue-400" placeholder="example@email.com">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1" for="phone">Phone Number*</label>
                <input type="text" name="phone" id="phone" required class="w-full border border-gray-300 rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-blue-400" placeholder="+123 456 7890">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1" for="companyName">Company Name (Optional)</label>
                <input type="text" name="companyName" id="companyName" class="w-full border border-gray-300 rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-blue-400" placeholder="Company Inc.">
            </div>
        </div>

        <!-- Right Column -->
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1" for="position">Position in the Company (Optional)</label>
                <select name="position" id="position" class="w-full border border-gray-300 rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <option value="">Select a position</option>
                    <option value="ceo">CEO / President / Owner</option>
                    <option value="director">Director</option>
                    <option value="manager">Manage Team / Team Lead</option>
                    <option value="supervisor">Supervisor</option>
                    <option value="senior">Senior Staff / Senior Associate</option>
                    <option value="staff">Staff / Associate / Officer</option>
                    <option value="intern">Intern / Trainee</option>
                    <option value="consultant">Consultant / Advisor</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1" for="companyAddress">Company Address*</label>
                <input type="text" name="companyAddress" id="companyAddress" required class="w-full border border-gray-300 rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-blue-400" placeholder="123 Main St.">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1" for="subject">Subject*</label>
                <select name="subject" id="subject" required class="w-full border border-gray-300 rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <option value="">Select a subject</option>
                    <option value="inquiry">Inquiry</option>
                    <option value="support">Support</option>
                    <option value="feedback">Feedback</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1" for="priority">Priority Level*</label>
                <select name="priority" id="priority" required class="w-full border border-gray-300 rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <option value="">Select a priority</option>
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                </select>
            </div>
        </div>

        <!-- Message (Full Width) -->
        <div class="md:col-span-2">
            <label class="block text-sm font-medium mb-1" for="message">Message*</label>
            <textarea name="message" id="message" required rows="5" placeholder="Enter your message..." class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none h-[200px]"></textarea>
        </div>

        <!-- Submit Button -->
        <div class="md:col-span-2 text-center">
            <input type="submit" value="Submit" class="bg-blue-600 text-white py-2 px-6 rounded-lg cursor-pointer hover:bg-blue-700 transition duration-200">
        </div>
    </form>
</div>


            <!-- Your Headquarters Section -->
            <div class="bg-white border border-gray-300 rounded-md p-10 shadow-lg shadow-gray-900/80 max-w-5xl mx-auto">
                <p class="text-center text-2xl font-bold text-black mb-6">Our Headquarters</p>

                <div class="flex flex-col md:flex-row justify-evenly gap-6">
                    <!-- Location -->
                    <div class="flex flex-col text-center w-[280px] h-[230px] justify-start pt-8 p-4 items-center text-black border border-gray-300 shadow-lg rounded-md">
                        <div class="flex border bg-gray-200 rounded-full h-[45px] w-[45px] justify-center items-center mb-4">
                            <i class="fas fa-map-marker-alt text-black text-xl"></i>
                        </div>
                        <p class="font-bold text-lg mb-2">Location</p>
                        <p class="text-sm">16 Aguinaldo Hi way Panapaan II, <br>City of Bacoor, Cavite <br>Philippines</p>
                    </div>

                    <!-- Business Hours -->
                    <div class="flex flex-col text-center w-[280px] h-[230px] justify-start pt-8 p-4 items-center text-black border border-gray-300 shadow-lg rounded-md">
                        <div class="flex border bg-gray-200 rounded-full h-[45px] w-[45px] justify-center items-center mb-4">
                            <i class="fas fa-clock text-black text-xl"></i>
                        </div>
                        <p class="font-bold text-lg mb-1">Business Hours</p>
                        <p class="text-sm font-semibold mb-0">Weekdays:</p>
                        <p class="text-sm mt-0">Monday - Friday: 8:00am - 5:00pm</p>
                        <p class="text-sm font-semibold mb-0 mt-1">Weekends:</p>
                        <p class="text-sm mt-0">Saturday - Sunday: Closed</p>
                    </div>

                    <!-- Social Media -->
                    <div class="flex flex-col text-center w-[280px] h-[230px] justify-start pt-8 p-4 items-center text-black border border-gray-300 shadow-lg rounded-md">
                        <div class="flex border bg-gray-200 rounded-full h-[45px] w-[45px] justify-center items-center mb-4">
                            <i class="fas fa-share-alt text-black text-xl"></i>
                        </div>
                        <p class="font-bold text-lg mb-1">Connect with Us</p>
                        <p class="text-sm mb-1">Our Social Media Platforms:</p>
                        <div class="flex flex-row items-center gap-4 mt-2 justify-center">
                            <a href="https://www.linkedin.com" target="_blank" class="flex flex-col items-center text-blue-700 hover:text-blue-500">
                                <i class="fab fa-linkedin text-3xl"></i>
                                <span class="text-xs text-black">LinkedIn</span>
                            </a>

                            <a href="https://www.instagram.com" target="_blank" class="flex flex-col items-center text-pink-500 hover:text-pink-400">
                                <i class="fab fa-instagram text-3xl"></i>
                                <span class="text-xs text-black">Instagram</span>
                            </a>

                            <a href="https://www.facebook.com" target="_blank" class="flex flex-col items-center text-blue-600 hover:text-blue-500">
                                <i class="fab fa-facebook text-3xl"></i>
                                <span class="text-xs text-black">Facebook</span>
                            </a>
                        </div>
                    </div>
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

    <!-- CTA Section (copied from products.php, customizable background) -->
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
    <script src="includes/javascript/contact.js"></script>
</body>

</html>