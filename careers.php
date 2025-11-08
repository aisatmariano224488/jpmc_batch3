<?php
require_once 'page_config.php';
$config = get_page_config('careers');
require_once __DIR__ . '/vendor/autoload.php';
require_once 'includes/db_connection.php';

use Brevo\Client\Api\TransactionalEmailsApi;
use Brevo\Client\Configuration;
use Brevo\Client\Model\SendSmtpEmail;

// Fetch positions and their qualifications
$positions = [];
$sql = "SELECT * FROM careers_positions WHERE is_active = 1 ORDER BY type, title";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $position_id = $row['id'];
        $qualifications = [];
        $qual_sql = "SELECT * FROM careers_qualifications WHERE position_id = $position_id ORDER BY display_order";
        $qual_result = $conn->query($qual_sql);
        if ($qual_result && $qual_result->num_rows > 0) {
            while ($qual = $qual_result->fetch_assoc()) {
                $qualifications[] = $qual['qualification'];
            }
        }
        $row['qualifications'] = $qualifications;
        $positions[] = $row;
    }
}

// Fetch gallery images grouped by batch
$gallery_batches = [];
$sql = "SELECT * FROM ojt_media_gallery WHERE is_active = 1 ORDER BY batch, uploaded_at DESC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $gallery_batches[$row['batch']][] = $row['image'];
    }
}

// Fetch testimonials
$testimonials = [];
$sql = "SELECT * FROM careers_testimonials WHERE is_active = 1 ORDER BY display_order, name";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $testimonials[] = $row;
    }
}

// Fetch benefits
$benefits = [];
$sql = "SELECT * FROM careers_benefits WHERE is_active = 1 ORDER BY display_order, title";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $benefits[] = $row;
    }
}

// Handle application form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['careers_application'])) {
    $position_id = $_POST['position_id'] ?? '';
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $has_skills = $_POST['hasSkills'] ?? '';
    $hours_required = $_POST['hoursRequired'] ?? '';
    $work_onsite = $_POST['workOnsite'] ?? '';

    // Handle file upload (resume)
    $resume_url = '';
    if (isset($_FILES['resume']) && $_FILES['resume']['error'] == UPLOAD_ERR_OK) {
        $uploads_dir = __DIR__ . '/uploads/resumes/';
        if (!is_dir($uploads_dir)) {
            mkdir($uploads_dir, 0777, true);
        }
        $filename = uniqid() . '_' . basename($_FILES['resume']['name']);
        $target_path = $uploads_dir . $filename;
        if (move_uploaded_file($_FILES['resume']['tmp_name'], $target_path)) {
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https" : "http";
            $domain = $_SERVER['HTTP_HOST'];
            $project_path = '/dashboard/JPMC';
            $resume_url = $protocol . '://' . $domain . $project_path . '/uploads/resumes/' . $filename;
        }
    }

    // Save application to database
    $sql = "INSERT INTO careers_applications (position_id, first_name, last_name, email, phone, has_skills, hours_required, work_onsite, resume_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issssssss", $position_id, $first_name, $last_name, $email, $phone, $has_skills, $hours_required, $work_onsite, $resume_url);

    if ($stmt->execute()) {
        // Get position title for email
        $job_title = '';
        $pos_sql = "SELECT title FROM careers_positions WHERE id = ?";
        $pos_stmt = $conn->prepare($pos_sql);
        $pos_stmt->bind_param("i", $position_id);
        $pos_stmt->execute();
        $pos_result = $pos_stmt->get_result();
        if ($pos_result->num_rows > 0) {
            $pos_row = $pos_result->fetch_assoc();
            $job_title = $pos_row['title'];
        }

        // Send emails using Brevo
        $brevo_api_key = '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';
        $config_brevo = Configuration::getDefaultConfiguration()->setApiKey('api-key', $brevo_api_key);
        $apiInstance = new TransactionalEmailsApi(new GuzzleHttp\Client(), $config_brevo);

        // Admin notification email
        $admin_email_content = "
        <html>
        <body>
            <h2>New Careers Application</h2>
            <p><strong>Position:</strong> $job_title</p>
            <p><strong>Name:</strong> $first_name $last_name</p>
            <p><strong>Email:</strong> $email</p>
            <p><strong>Phone:</strong> $phone</p>
            <p><strong>Has Required Skills:</strong> $has_skills</p>
            <p><strong>Hours Required:</strong> $hours_required</p>
            <p><strong>Willing to Work On-site:</strong> $work_onsite</p>
            " . ($resume_url ? "<p><strong>Resume:</strong> <a href='$resume_url'>Download</a></p>" : "") . "
        </body>
        </html>
        ";

        $admin_sender = ['name' => 'JPMC', 'email' => 'danielrossevia@gmail.com'];

        $admin_to = [
            ['email' => 'danielrossevia@gmail.com', 'name' => 'Daniel Rossevia']
        ];

        $admin_subject = "New Careers Application: $job_title";

        $adminEmail = new SendSmtpEmail([
            'subject' => $admin_subject,
            'sender' => $admin_sender,
            'to' => $admin_to,
            'htmlContent' => $admin_email_content
        ]);

        // Applicant confirmation email
        $applicant_email_content = "
        <html>
        <body>
            <h2>Thank You for Your Application</h2>
            <p>Dear $first_name $last_name,</p>
            <p>Thank you for applying for the <strong>$job_title</strong> position at JPMC. We have received your application and our team will review your submission. If your qualifications match our requirements, we will contact you for the next steps.</p>
            <p>Best regards,<br>JPMC Careers Team</p>
        </body>
        </html>
        ";

        $applicant_sender = ['name' => 'JPMC', 'email' => 'danielrossevia@gmail.com'];
        $applicant_to = [['email' => $email, 'name' => "$first_name $last_name"]];
        $applicant_subject = "Thank you for your application to JPMC";

        $applicantEmail = new SendSmtpEmail([
            'subject' => $applicant_subject,
            'sender' => $applicant_sender,
            'to' => $applicant_to,
            'htmlContent' => $applicant_email_content
        ]);

        try {
            $apiInstance->sendTransacEmail($adminEmail);
            $apiInstance->sendTransacEmail($applicantEmail);
            $success_message = "Thank you for your application! We will review your submission and get back to you soon.";
        } catch (Exception $e) {
            $error_message = "Application submitted successfully, but there was an error sending confirmation emails. Please try again later.";
        }
    } else {
        $error_message = "There was an error submitting your application. Please try again later.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Careers - Join Our Team</title>

    <!--Tab Icon-->
    <link rel="icon" type="image/png" href="assets/img/tab_icon.png">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        .hero-modern {
            background: <?php echo !empty($config['header_bg']) ? "url('" . $config['header_bg'] . "') center/cover no-repeat" : "#0066cc";
                        ?>;
            color: white;
            padding: 8rem 2rem;
            position: relative;
            overflow: hidden;
            text-align: center;
        }

        .hero-modern::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: <?php echo $config['header_overlay'];
                        ?>;
        }

        <?php include 'includes/css/career.css'; ?>
    </style>
</head>

<body class="bg-gray-50" style="background: url('<?php echo $config['main_bg']; ?>') center/cover no-repeat;">
    <?php include 'header.php'; ?>

    <?php if (isset($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show m-4" role="alert">
            <strong>Success!</strong> <?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show m-4" role="alert">
            <strong>Error!</strong> <?php echo $error_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- HERO SECTION (modern, customizable background) -->
    <section
        data-aos="fade-down"
        class="relative bg-blue-400 h-96 flex items-center justify-center bg-cover bg-center drop-shadow-2xl animate__animated animate__fadeIn"
        style="background-image: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('<?php echo !empty($config['header_bg']) ? $config['header_bg'] : 'https://www.james-polymers.com/wp-content/uploads/2021/09/careers-banner.jpg'; ?>')">
        <!-- Inclined overlay image (optional, replace src to customize) -->
        <img
            src="assets/img/banners/careers_banner.jpg"
            alt="Inclined Overlay"
            class="absolute inset-0 w-full h-full object-cover"
            style="mix-blend-mode: multiply; opacity: 1;" />

        <div class="container mx-auto px-4 text-center text-white relative z-10">
            <h1 class="text-4xl md:text-5xl font-bold mb-6"><?php echo $config['header_title']; ?></h1>
            <p class="text-xl md:text-2xl mb-8">Be part of a team that values innovation, learning, and growth. Explore our open positions and internship programs.</p>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="#careers-tabs" class="btn-modern btn-primary">View Opportunities</a>
            </div>
        </div>
    </section>

    <!-- CAREERS TABS SECTION -->
    <section id="careers-tabs" class="section-modern bg-light" data-aos="fade-up">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <span class="text-accent font-bold uppercase tracking-widest">Opportunities</span>
                <h2 class="section-title">Join Our Team</h2>
                <p class="section-subtitle">Explore our full-time positions and internship programs.</p>
            </div>

            <!-- Tabs Navigation -->
            <ul class="nav nav-tabs justify-center mb-8" id="careersTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="fulltime-tab" data-bs-toggle="tab" data-bs-target="#fulltime" type="button" role="tab">Full-Time Positions</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="internship-tab" data-bs-toggle="tab" data-bs-target="#internship" type="button" role="tab">Internship Programs</button>
                </li>
            </ul>

            <!-- Tabs Content -->
            <div class="tab-content" id="careersTabContent">
                <!-- Full-Time Positions Tab -->
                <div class="tab-pane fade show active" id="fulltime" role="tabpanel">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-8">
                        <?php foreach ($positions as $position): ?>
                            <?php if ($position['type'] === 'full-time'): ?>
                                <div class="card-modern bg-white p-8 flex flex-col items-start job-card shadow-md hover:shadow-2xl transition-shadow duration-300 animate__animated animate__fadeInUp">
                                    <?php if ($position['image']): ?>
                                        <img src="<?php echo $position['image']; ?>" alt="<?php echo $position['title']; ?>" class="w-full h-48 object-cover rounded-lg mb-4">
                                    <?php else: ?>
                                        <img src="https://images.unsplash.com/photo-1517694712202-14dd9538aa97?w=500" alt="<?php echo $position['title']; ?>" class="w-full h-48 object-cover rounded-lg mb-4">
                                    <?php endif; ?>
                                    <h3 class="text-2xl font-bold mb-2"><?php echo $position['title']; ?></h3>
                                    <p class="text-gray-600 mb-4"><?php echo ucfirst($position['type']); ?> • <?php echo $position['location']; ?></p>
                                    <p class="mb-6"><?php echo $position['description']; ?></p>
                                    <button class="btn-modern btn-primary mt-auto" onclick="openGoogleForm()">
                                    Apply Now
                                    </button>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Internship Programs Tab -->
                <div class="tab-pane fade" id="internship" role="tabpanel">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-8">
                        <?php foreach ($positions as $position): ?>
                            <?php if ($position['type'] === 'internship'): ?>
                                <div class="card-modern bg-white p-8 flex flex-col items-start job-card">
                                    <?php if ($position['image']): ?>
                                        <img src="<?php echo $position['image']; ?>" alt="<?php echo $position['title']; ?>" class="w-full h-48 object-cover rounded-lg mb-4">
                                    <?php else: ?>
                                        <img src="https://images.unsplash.com/photo-1517694712202-14dd9538aa97?w=500" alt="<?php echo $position['title']; ?>" class="w-full h-48 object-cover rounded-lg mb-4">
                                    <?php endif; ?>
                                    <h3 class="text-2xl font-bold mb-2"><?php echo $position['title']; ?></h3>
                                    <p class="text-gray-600 mb-4"><?php echo ucfirst($position['type']); ?> • <?php echo $position['location']; ?></p>
                                    <p class="mb-6"><?php echo $position['description']; ?></p>
                                    <button class="btn-modern btn-primary mt-auto" onclick="openOjtForm()">
                                    Apply Now
                                    </button>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>

                    <!-- OJT Media Gallery Section -->
                    <section class="section-modern bg-light mt-12" data-aos="fade-up">
                        <div class="container mx-auto px-4">
                            <div class="text-center mb-16">
                                <span class="text-accent font-bold uppercase tracking-widest">Experience</span>
                                <h2 class="section-title">OJT Media Gallery</h2>
                                <p class="section-subtitle">Explore our training program and student experiences through photos.</p>
                            </div>
                            <!-- Batch Filter Buttons -->
                            <div class="flex flex-wrap justify-center gap-4 mb-12">
                                <button class="gallery-filter-btn" data-filter="all">All</button>
                                <button class="gallery-filter-btn" data-filter="Batch 1">Batch 1</button>
                                <button class="gallery-filter-btn" data-filter="Batch 2">Batch 2</button>
                                <button class="gallery-filter-btn" data-filter="Batch 3">Batch 3</button>
                            </div>
                            <div class="gallery-grid grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6" id="ojtGallery">
                                <?php
                                $max_initial = 20;
                                $count = 0;
                                foreach ($gallery_batches as $batch => $images):
                                    foreach ($images as $img):
                                        $count++;
                                ?>
                                        <div class="gallery-item<?php echo $count > $max_initial ? ' hidden' : ''; ?>" data-batch="<?php echo htmlspecialchars($batch); ?>">
                                            <a href="<?php echo htmlspecialchars($img); ?>" class="glightbox" data-gallery="ojt-gallery">
                                                <img src="<?php echo htmlspecialchars($img); ?>" alt="" class="gallery-image rounded-lg shadow-lg w-full h-56 object-cover" />
                                            </a>
                                        </div>
                                <?php
                                    endforeach;
                                endforeach;
                                ?>
                            </div>
                            <div class="text-center mt-8">
                                <button id="loadMoreGalleryBtn" class="bg-modern bt-primary">
                                    <i class="fas fa-plus"></i> Load More
                                </button>
                                <button id="backGalleryBtn" class="bg-modern bt-primary" style="display:none;">
                                    <i class="fas fa-minus"></i> Back
                                </button>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </section>


    <!-- BENEFITS SECTION -->
    <section class="section-modern benefits-section" data-aos="fade-up" style="background-image: url('assets/img/banners/careers_cta.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat; position: relative;">
        <div class="overlay" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.6);"></div>
        <div class="container mx-auto px-4 relative z-10">
            <div class="text-center mb-16">
                <span class="text-accent font-bold uppercase tracking-widest">Why Join Us</span>
                <h2 class="section-title text-white">Our Benefits</h2>
                <p class="section-subtitle text-white">We offer comprehensive benefits that support your professional growth and personal wellbeing.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-8">
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-project-diagram"></i>
                    </div>
                    <h4 class="text-xl font-bold mb-2">Project-based Activities</h4>
                    <p class="text-gray-700">Learning through real-world tasks or projects that apply concepts to practical situations.</p>
                </div>
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-tools"></i>
                    </div>
                    <h4 class="text-xl font-bold mb-2">Hands-on Experience</h4>
                    <p class="text-gray-700">Gaining skills by directly engaging in practical work or experiments.</p>
                </div>
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h4 class="text-xl font-bold mb-2">Collaboration</h4>
                    <p class="text-gray-700">Working together with others to achieve shared goals and exchange ideas.</p>
                </div>
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <h4 class="text-xl font-bold mb-2">Quality Assurance</h4>
                    <p class="text-gray-700">Ensuring that a product or service meets established standards through careful checking and testing.</p>
                </div>
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <h4 class="text-xl font-bold mb-2">Leadership</h4>
                    <p class="text-gray-700">Acquire leadership skills by providing opportunities to collaborate, take responsibility, and make decisions through group projects.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Gallery Modal -->
    <div class="modal fade" id="galleryModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content gallery-modal-content">
                <div class="modal-header gallery-modal-header">
                    <h5 class="modal-title text-xl font-bold" id="galleryModalTitle"></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body gallery-modal-body p-0">
                    <div id="galleryModalContent" class="gallery-modal-media"></div>
                    <div class="gallery-modal-info p-6">
                        <p id="galleryModalDescription" class="text-gray-600 leading-relaxed"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Application Modal -->
    <div class="modal fade" id="applicationModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-xl font-bold">Apply for <span id="jobTitle"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Progress Steps -->
                    <div class="flex justify-between mb-8 relative">
                        <div class="absolute top-1/2 left-0 w-full h-0.5 bg-gray-200 -translate-y-1/2"></div>
                        <div class="step-indicator active">
                            <div class="step-circle">1</div>
                            <span class="text-sm font-medium text-primary">Job Details</span>
                        </div>
                        <div class="step-indicator">
                            <div class="step-circle">2</div>
                            <span class="text-sm font-medium text-gray-600">Initial Questions</span>
                        </div>
                        <div class="step-indicator">
                            <div class="step-circle">3</div>
                            <span class="text-sm font-medium text-gray-600">Personal Details</span>
                        </div>
                    </div>

                    <!-- Step 1: Job Details -->
                    <div class="modal-step active" id="step1">
                        <div class="form-section">
                            <h4>Company Description</h4>
                            <p class="text-gray-700 leading-relaxed">JPMC is a leading polymer manufacturing company committed to innovation and sustainability. We specialize in developing advanced polymer solutions for various industries, focusing on quality, efficiency, and environmental responsibility.</p>
                        </div>
                        <div class="form-section">
                            <h4>Full Job Description</h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-600">Position</p>
                                    <p class="font-medium" id="jobPosition"></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Shift</p>
                                    <p class="font-medium" id="jobShift"></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Schedule</p>
                                    <p class="font-medium" id="jobSchedule"></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Location</p>
                                    <p class="font-medium" id="jobLocation"></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Employment Type</p>
                                    <p class="font-medium" id="jobType"></p>
                                </div>
                            </div>
                            <div class="mt-4">
                                <p class="text-sm text-gray-600 mb-2">Description</p>
                                <p class="text-gray-700" id="jobDescription"></p>
                            </div>
                        </div>
                        <div class="form-section">
                            <h4>Qualifications</h4>
                            <ul class="space-y-2" id="jobQualifications"></ul>
                        </div>
                        <div class="mt-6 text-right">
                            <button type="button" class="btn-modern btn-primary" onclick="nextStep(1)">Proceed to Application</button>
                        </div>
                    </div>

                    <!-- Step 2: Initial Questions -->
                    <div class="modal-step" id="step2">
                        <form id="initialQuestionsForm" class="space-y-6">
                            <div class="form-section">
                                <h4>Do you have the required skills for this position?</h4>
                                <div class="flex gap-6 mt-4">
                                    <label class="flex items-center space-x-3 cursor-pointer">
                                        <input type="radio" name="hasSkills" value="Yes" class="form-radio h-5 w-5 text-primary" required>
                                        <span class="text-gray-700">Yes</span>
                                    </label>
                                    <label class="flex items-center space-x-3 cursor-pointer">
                                        <input type="radio" name="hasSkills" value="No" class="form-radio h-5 w-5 text-primary" required>
                                        <span class="text-gray-700">No</span>
                                    </label>
                                </div>
                            </div>

                            <div class="form-section" id="hoursRequirementSection">
                                <h4>How many hours is your requirement to render?</h4>
                                <input type="number" class="form-control mt-4" name="hoursRequired" min="1" max="1040">
                                <small class="text-gray-500 mt-2 block">Please enter the number of hours (1-1040)</small>
                            </div>

                            <div class="form-section">
                                <h4>Are you willing to work on-site?</h4>
                                <div class="flex gap-6 mt-4">
                                    <label class="flex items-center space-x-3 cursor-pointer">
                                        <input type="radio" name="workOnsite" value="yes" class="form-radio h-5 w-5 text-primary" required>
                                        <span class="text-gray-700">Yes</span>
                                    </label>
                                </div>
                            </div>

                            <div class="flex justify-between mt-8">
                                <button type="button" class="btn-modern btn-secondary" onclick="prevStep(2)">Previous</button>
                                <button type="button" class="btn-modern btn-primary" onclick="nextStep(2)">Next</button>
                            </div>
                        </form>
                    </div>

                    <!-- Step 3: Personal Details -->
                    <div class="modal-step" id="step3">
                        <form id="personalDetailsForm" class="space-y-6" method="POST" enctype="multipart/form-data" action="careers">
                            <input type="hidden" name="careers_application" value="1">
                            <input type="hidden" name="position_id" id="hiddenPositionId">
                            <div class="form-section">
                                <h4>Personal Information</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                                        <input type="text" class="form-control" name="first_name" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                                        <input type="text" class="form-control" name="last_name" required>
                                    </div>
                                </div>
                            </div>
                            <div class="form-section">
                                <h4>Contact Information</h4>
                                <div class="space-y-4 mt-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                        <input type="email" class="form-control" name="email" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                                        <input type="tel" class="form-control" name="phone" required>
                                    </div>
                                </div>
                            </div>
                            <div class="form-section">
                                <h4>Resume/CV Upload</h4>
                                <div class="file-upload mt-4">
                                    <input type="file" class="hidden" accept=".pdf,.doc,.docx,.csv" name="resume" required>
                                    <div class="text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <p class="mt-2 text-sm text-gray-600">Click to upload or drag and drop</p>
                                        <p class="text-xs text-gray-500 mt-1">PDF, DOC, DOCX, or CSV up to 10MB</p>
                                    </div>
                                </div>
                            </div>
                            <div class="flex justify-between mt-8">
                                <button type="button" class="btn-modern btn-secondary" onclick="prevStep(3)">Previous</button>
                                <button type="submit" class="btn-modern btn-primary">Submit Application</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chatbot Integration -->
    <?php include 'chatbot.php'; ?>

    <!-- Footer -->
    <?php include 'footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script src="includes/javascript/careers.js"></script>
    <script>
        // OJT Media Gallery Interactivity
        document.addEventListener('DOMContentLoaded', function() {
            const maxInitial = 20;
            const increment = 20;
            let currentMax = maxInitial;
            const gallery = document.getElementById('ojtGallery');
            const items = Array.from(gallery ? gallery.getElementsByClassName('gallery-item') : []);
            const loadMoreBtn = document.getElementById('loadMoreGalleryBtn');
            const backBtn = document.getElementById('backGalleryBtn');
            const filterBtns = document.querySelectorAll('.gallery-filter-btn');
            let currentFilter = 'all';

            function updateGallery() {
                let visibleCount = 0;
                items.forEach((item, idx) => {
                    const batch = item.getAttribute('data-batch');
                    const matches = (currentFilter === 'all' || batch === currentFilter);
                    if (matches) {
                        visibleCount++;
                        if (visibleCount <= currentMax) {
                            item.classList.remove('hidden');
                        } else {
                            item.classList.add('hidden');
                        }
                    } else {
                        item.classList.add('hidden');
                    }
                });
                // Button visibility
                const totalVisible = items.filter(item => (currentFilter === 'all' || item.getAttribute('data-batch') === currentFilter)).length;
                if (currentMax < totalVisible) {
                    loadMoreBtn.style.display = '';
                    backBtn.style.display = '';
                } else {
                    loadMoreBtn.style.display = 'none';
                    backBtn.style.display = totalVisible > maxInitial ? '' : 'none';
                }
                if (currentMax > maxInitial) {
                    backBtn.style.display = '';
                } else {
                    backBtn.style.display = 'none';
                }
            }

            if (loadMoreBtn && backBtn && items.length) {
                loadMoreBtn.addEventListener('click', function() {
                    const totalVisible = items.filter(item => (currentFilter === 'all' || item.getAttribute('data-batch') === currentFilter)).length;
                    currentMax = Math.min(currentMax + increment, totalVisible);
                    updateGallery();
                });
                backBtn.addEventListener('click', function() {
                    currentMax = maxInitial;
                    updateGallery();
                });
            }

            filterBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    filterBtns.forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    currentFilter = btn.getAttribute('data-filter');
                    currentMax = maxInitial;
                    updateGallery();
                });
            });

            // Initial state
            updateGallery();
        });
        // Full time and Internship Apply Now button functions
        function openGoogleForm() {
            window.open(
                'https://forms.gle/ddrfp3VqGMMk3knf9',
                '_blank'
            );
        }
        // OJT Apply Now button function
        function openOjtForm() {
            window.open(
                'http://forms.gle/R4qkB7Aw6ViFXnVc9',
                '_blank'
            );
        }
    </script>
</body>

</html>