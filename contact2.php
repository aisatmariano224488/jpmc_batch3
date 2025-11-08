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
    // Include functions
    // include 'includes/functions.php'; // <-- Commented out because file is missing

    // Include header
    include 'header.php';

    // Current page
    $page = 'contact';

    // Add Composer autoload for Brevo SDK
    require_once __DIR__ . '/vendor/autoload.php';

    use Brevo\Client\Api\TransactionalEmailsApi;
    use Brevo\Client\Configuration;
    use Brevo\Client\Model\SendSmtpEmail;
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

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                <!-- Contact Information -->
                <div class="contact-card bg-white rounded-lg shadow-md p-8">
                    <h3 class="text-2xl font-bold text-gray-800 mb-8"><?php echo getContent($page, 'contact_info', 'headquarters_heading', 'Our Headquarters'); ?></h3>

                    <div class="space-y-6">
                        <div class="flex items-start">
                            <div class="bg-primary bg-opacity-10 p-3 rounded-full mr-4 flex-shrink-0">
                                <i class="fas fa-map-marker-alt text-primary text-xl"></i>
                            </div>
                            <div>
                                <h4 class="text-lg font-semibold text-gray-800 mb-1"><?php echo getContent($page, 'contact_info', 'address_heading', 'Address'); ?></h4>
                                <p class="text-gray-600"><?php echo getContent($page, 'contact_info', 'address', '16 Aguinaldo HI-Way Panapaan II<br> City of Bacoor, Cavite<br>Philippines'); ?></p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="bg-primary bg-opacity-10 p-3 rounded-full mr-4 flex-shrink-0">
                                <i class="fas fa-clock text-primary text-xl"></i>
                            </div>
                            <div>
                                <h4 class="text-lg font-semibold text-gray-800 mb-1"><?php echo getContent($page, 'contact_info', 'hours_heading', 'Business Hours'); ?></h4>
                                <p class="text-gray-600"><?php echo getContent($page, 'contact_info', 'hours', 'Monday - Friday: 8:00am - 5:00pm<br>Sunday: Closed'); ?></p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="bg-primary bg-opacity-10 p-3 rounded-full mr-4 flex-shrink-0">
                                <i class="fas fa-share-alt text-primary text-xl"></i>
                            </div>
                            <div>
                                <h4 class="text-lg font-semibold text-gray-800 mb-1"><?php echo getContent($page, 'contact_info', 'social_heading', 'Connect With Us'); ?></h4>
                                <div class="flex space-x-4 mt-2">
                                    <a href="<?php echo getContent($page, 'contact_info', 'linkedin_url', '#'); ?>" class="text-gray-600 hover:text-primary text-xl transition"><i class="fab fa-linkedin-in"></i></a>
                                    <a href="<?php echo getContent($page, 'contact_info', 'twitter_url', '#'); ?>" class="text-gray-600 hover:text-primary text-xl transition"><i class="fab fa-instagram"></i></a>
                                    <a href="<?php echo getContent($page, 'contact_info', 'youtube_url', '#'); ?>" class="text-gray-600 hover:text-primary text-xl transition"><i class="fab fa-facebook-f"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Form -->
                <div class="bg-white rounded-lg shadow-lg p-8">
                    <h3 class="text-2xl font-bold text-gray-800 mb-8"><?php echo getContent($page, 'form', 'heading', 'Send Us a Message'); ?></h3>

                    <?php
                    // Process form submission
                    if ($_SERVER["REQUEST_METHOD"] == "POST") {
                        // Database connection
                        include 'includes/db_connection.php';

                        // Sanitize input data
                        $name = mysqli_real_escape_string($conn, $_POST['name']);
                        $email = mysqli_real_escape_string($conn, $_POST['email']);
                        $phone = mysqli_real_escape_string($conn, isset($_POST['phone']) ? $_POST['phone'] : '');
                        $company = mysqli_real_escape_string($conn, isset($_POST['company']) ? $_POST['company'] : '');

                        // Handle position field
                        $position = '';
                        if (isset($_POST['position']) && $_POST['position'] === 'Others' && isset($_POST['position_other'])) {
                            $position = mysqli_real_escape_string($conn, $_POST['position_other']);
                        } elseif (isset($_POST['position']) && $_POST['position'] !== '') {
                            $position = mysqli_real_escape_string($conn, $_POST['position']);
                        }

                        $address = mysqli_real_escape_string($conn, $_POST['address']);

                        // Handle subject field
                        $subject = mysqli_real_escape_string($conn, $_POST['subject']);
                        if ($subject === 'other' && !empty($_POST['subject_other'])) {
                            $subject_for_db = mysqli_real_escape_string($conn, $_POST['subject_other']);
                        } else {
                            $subject_for_db = $subject;
                        }

                        $priority = mysqli_real_escape_string($conn, $_POST['priority']);
                        $message = mysqli_real_escape_string($conn, $_POST['message']);
                        $date_submitted = date('Y-m-d H:i:s');
                        $status = 'new';

                        // Insert data into the database
                        $sql = "INSERT INTO inquiries (name, email, phone, company, position, address, subject, priority, message, date_submitted, status) 
                                VALUES ('$name', '$email', '$phone', '$company', '$position', '$address', '$subject_for_db', '$priority', '$message', '$date_submitted', '$status')";

                        if ($conn->query($sql) === TRUE) {
                            $brevo_api_key = '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';

                            // Format subject line based on form subject
                            $subject_text = '';
                            switch ($subject) {
                                case 'product-info':
                                    $subject_text = 'Product Information';
                                    break;
                                case 'technical-support':
                                    $subject_text = 'Technical Support';
                                    break;
                                case 'sales':
                                    $subject_text = 'Sales Inquiry';
                                    break;
                                case 'careers':
                                    $subject_text = 'Careers';
                                    break;
                                case 'other':
                                    $subject_text = !empty($_POST['subject_other']) ? mysqli_real_escape_string($conn, $_POST['subject_other']) : 'Other';
                                    break;
                                default:
                                    $subject_text = $subject;
                            }

                            // Restore the HTML email template
                            $email_content = "
                            <html>
                            <head>
                                <style>
                                    @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap');
                                    body { font-family: 'Roboto', sans-serif; line-height: 1.6; color: #333; background-color: #f9f9f9; margin: 0; padding: 0; }
                                    .email-container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
                                    .email-header { background-color: #0066cc; padding: 20px; text-align: center; }
                                    .logo-container { margin-bottom: 15px; }
                                    .logo { width: 180px; height: auto; }
                                    .email-content { padding: 30px; }
                                    h2 { color: #0066cc; margin-top: 0; font-size: 24px; font-weight: 600; }
                                    .info { margin-bottom: 25px; background-color: #ffffff; border-radius: 6px; }
                                    .info-row { padding: 10px 0; border-bottom: 1px solid #f0f0f0; display: flex; }
                                    .info-row:last-child { border-bottom: none; }
                                    .label { font-weight: 600; flex: 0 0 120px; color: #555; }
                                    .value { flex: 1; }
                                    .message-box { background-color: #f5f5f5; padding: 20px; border-radius: 6px; border-left: 4px solid #0066cc; }
                                    .message-title { font-weight: 600; color: #0066cc; margin-bottom: 10px; }
                                    .email-footer { background-color: #f5f5f5; padding: 15px; text-align: center; font-size: 13px; color: #777; border-top: 1px solid #e5e5e5; }
                                    .cta-button { display: inline-block; background-color: #0066cc; color: white; text-decoration: none; padding: 10px 20px; border-radius: 4px; font-weight: 500; margin-top: 15px; }
                                </style>
                            </head>
                            <body>
                                <div class='email-container'>
                                    <div class='email-header'>
                                        <div class='logo-container'>
                                            <img src='assets/img/logo.jpg' alt='James Polymers' class='logo' />
                                        </div>
                                        <h1 style='color: white; margin: 0; font-size: 20px;'>New Inquiry Notification</h1>
                                    </div>
                                    
                                    <div class='email-content'>
                                        <h2>New Inquiry Received</h2>
                                        <p>A new inquiry has been submitted through the James Polymers website contact form:</p>
                                        
                                        <div class='info'>
                                            <div class='info-row'>
                                                <div class='label'>Name:</div>
                                                <div class='value'>$name</div>
                                            </div>
                                            <div class='info-row'>
                                                <div class='label'>Email:</div>
                                                <div class='value'>$email</div>
                                            </div>
                                            <div class='info-row'>
                                                <div class='label'>Phone:</div>
                                                <div class='value'>" . ($phone ?: 'Not provided') . "</div>
                                            </div>
                                            <div class='info-row'>
                                                <div class='label'>Company:</div>
                                                <div class='value'>" . ($company ?: 'Not provided') . "</div>
                                            </div>
                                            <div class='info-row'>
                                                <div class='label'>Position:</div>
                                                <div class='value'>" . ($position ?: 'Not provided') . "</div>
                                            </div>
                                            <div class='info-row'>
                                                <div class='label'>Address:</div>
                                                <div class='value'>$address</div>
                                            </div>
                                            <div class='info-row'>
                                                <div class='label'>Subject:</div>
                                                <div class='value'>$subject_text</div>
                                            </div>
                                            <div class='info-row'>
                                                <div class='label'>Priority:</div>
                                                <div class='value'>$priority</div>
                                            </div>
                                            <div class='info-row'>
                                                <div class='label'>Date:</div>
                                                <div class='value'>$date_submitted</div>
                                            </div>
                                        </div>
                                        
                                        <div class='message-box'>
                                            <div class='message-title'>Message:</div>                                           <div>" . nl2br($message) . "</div>
                                        </div>
                                        
                                        <p style='margin-top: 25px;'>This inquiry requires your attention. Please respond as soon as possible.</p>
                                        <a href='https://www.james-polymers.com/admin' class='cta-button'>View in Dashboard</a>
                                    </div>
                                    
                                    <div class='email-footer'>
                                        <p>&copy; " . date('Y') . " James Polymers Ltd. All rights reserved.</p>
                                    </div>
                                </div>
                            </body>
                            </html>
                            ";

                            // Prepare Brevo SDK config
                            $config = Configuration::getDefaultConfiguration()->setApiKey('api-key', $brevo_api_key);
                            $apiInstance = new TransactionalEmailsApi(new GuzzleHttp\Client(), $config);

                            $sendSmtpEmail = new SendSmtpEmail([
                                'subject' => "New Contact Form Submission: $subject_text",
                                'sender' => ['name' => 'JPMC', 'email' => 'danielrossevia@gmail.com'],
                                'to' => [['email' => 'danielrossevia@gmail.com', 'name' => 'Daniel Rossevia']],
                                'htmlContent' => $email_content
                            ]);

                            try {
                                $result = $apiInstance->sendTransacEmail($sendSmtpEmail);
                                echo '<div class="p-4 mb-6 bg-green-100 text-green-700 border-l-4 border-green-500 rounded-md">
                                        <p>Thank you for your message! We will get back to you soon.</p>
                                      </div>';
                            } catch (Exception $e) {
                                echo '<div class="p-4 mb-6 bg-red-100 text-red-700 border-l-4 border-red-500 rounded-md">
                                        <p>Error sending email: ' . $e->getMessage() . '</p>
                                      </div>';
                            }
                        } else {
                            echo '<div class="p-4 mb-6 bg-red-100 text-red-700 border-l-4 border-red-500 rounded-md">
                                    <p>Error: ' . $conn->error . '</p>
                                  </div>';
                        }

                        $conn->close();
                    }
                    ?>

                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                        <div class="mb-6">
                            <label for="company" class="block text-gray-700 font-semibold mb-2">Company Name (Optional)</label>
                            <input type="text" id="company" name="company" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div class="mb-6">
                            <label for="position" class="block text-gray-700 font-semibold mb-2">Position in the Company (Optional)</label>
                            <select id="position" name="position" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="">Select a position</option>
                                <option value="President">President</option>
                                <option value="Vice President">Vice President</option>
                                <option value="Chief Executive Officer (CEO)">Chief Executive Officer (CEO)</option>
                                <option value="Chief Operating Officer (COO)">Chief Operating Officer (COO)</option>
                                <option value="Chief Financial Officer (CFO)">Chief Financial Officer (CFO)</option>
                                <option value="Chief Technology Officer (CTO)">Chief Technology Officer (CTO)</option>
                                <option value="General Manager">General Manager</option>
                                <option value="Director">Director</option>
                                <option value="Manager">Manager</option>
                                <option value="Supervisor">Supervisor</option>
                                <option value="Team Lead">Team Lead</option>
                                <option value="Senior Engineer">Senior Engineer</option>
                                <option value="Engineer">Engineer</option>
                                <option value="Technician">Technician</option>
                                <option value="Operator">Operator</option>
                                <option value="Analyst">Analyst</option>
                                <option value="Coordinator">Coordinator</option>
                                <option value="Specialist">Specialist</option>
                                <option value="Assistant">Assistant</option>
                                <option value="Clerk">Clerk</option>
                                <option value="Intern">Intern</option>
                                <option value="Others">Others</option>
                            </select>
                            <input type="text" id="position_other" name="position_other" placeholder="Please specify your position" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent mt-2" style="display: none;">
                        </div>
                        <div class="mb-6">
                            <label for="name" class="block text-gray-700 font-semibold mb-2">Full Name*</label>
                            <input type="text" id="name" name="name" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div class="mb-6">
                            <label for="email" class="block text-gray-700 font-semibold mb-2">Email Address*</label>
                            <input type="email" id="email" name="email" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div class="mb-6">
                            <label for="phone" class="block text-gray-700 font-semibold mb-2">Phone Number*</label>
                            <input type="tel" id="phone" name="phone" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div class="mb-6">
                            <label for="address" class="block text-gray-700 font-semibold mb-2">Company Address*</label>
                            <input type="text" id="address" name="address" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div class="mb-6">
                            <label for="subject" class="block text-gray-700 font-semibold mb-2">Subject*</label>
                            <select id="subject" name="subject" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="">Select a subject</option>
                                <option value="product-info">Product Information</option>
                                <option value="technical-support">Technical Support</option>
                                <option value="sales">Sales Inquiry</option>
                                <option value="careers">Careers</option>
                                <option value="other">Other</option>
                            </select>
                            <input type="text" id="subject_other" name="subject_other" placeholder="Please specify your subject" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent mt-2" style="display: none;">
                        </div>
                        <div class="mb-6">
                            <label for="priority" class="block text-gray-700 font-semibold mb-2">Priority Level*</label>
                            <select id="priority" name="priority" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="">Select priority</option>
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                        <div class="mb-6">
                            <label for="message" class="block text-gray-700 font-semibold mb-2">Your Message*</label>
                            <textarea id="message" name="message" required rows="5" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
                        </div>
                        <button type="submit" class="w-full bg-primary hover:bg-secondary text-white font-bold py-3 px-6 rounded-lg transition duration-300">Send Message</button>
                    </form>
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