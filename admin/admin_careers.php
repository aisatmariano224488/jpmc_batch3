<?php
// Start session
// session_start();


require_once '../includes/db_connection.php';

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$position_id = $title = $type = $shift = $schedule = $location = $description = $image = "";
$is_active = 1;
$success_message = $error_message = "";
$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        // Get form data
        $title = trim($_POST['title']);
        $type = trim($_POST['type']);
        $shift = trim($_POST['shift']);
        $schedule = trim($_POST['schedule']);
        $location = trim($_POST['location']);
        $description = trim($_POST['description']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        // Validate input
        if (empty($title) || empty($type) || empty($shift) || empty($schedule) || empty($location) || empty($description)) {
            $error_message = "All fields are required";
        } else {
            // Handle file upload
            $target_dir = "../uploads/careers/";
            $image_url = "";

            if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }

                $target_file = $target_dir . basename($_FILES["image"]["name"]);
                $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

                $check = getimagesize($_FILES["image"]["tmp_name"]);
                if ($check === false) {
                    $error_message = "File is not an image.";
                } elseif ($_FILES["image"]["size"] > 5000000) {
                    $error_message = "File is too large. Max size is 5MB.";
                } elseif ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
                    $error_message = "Only JPG, JPEG, PNG & GIF files are allowed.";
                } else {
                    $new_filename = uniqid() . "." . $imageFileType;
                    $target_file = $target_dir . $new_filename;

                    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                        $image_url = "uploads/careers/" . $new_filename;
                    } else {
                        $error_message = "Sorry, there was an error uploading your file.";
                    }
                }
            }

            if (empty($error_message)) {
                if ($_POST['action'] == 'add') {
                    $stmt = $conn->prepare("INSERT INTO careers_positions (title, type, shift, schedule, location, description, image, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                    $stmt->bind_param("sssssssi", $title, $type, $shift, $schedule, $location, $description, $image_url, $is_active);

                    if ($stmt->execute()) {
                        $success_message = "Job position added successfully!";
                    } else {
                        $error_message = "Error: " . $stmt->error;
                    }
                    $stmt->close();
                } elseif ($_POST['action'] == 'edit' && isset($_POST['position_id'])) {
                    $position_id = $_POST['position_id'];

                    if (empty($image_url)) {
                        $stmt = $conn->prepare("UPDATE careers_positions SET title = ?, type = ?, shift = ?, schedule = ?, location = ?, description = ?, is_active = ? WHERE id = ?");
                        $stmt->bind_param("ssssssii", $title, $type, $shift, $schedule, $location, $description, $is_active, $position_id);
                    } else {
                        $stmt = $conn->prepare("UPDATE careers_positions SET title = ?, type = ?, shift = ?, schedule = ?, location = ?, description = ?, image = ?, is_active = ? WHERE id = ?");
                        $stmt->bind_param("sssssssii", $title, $type, $shift, $schedule, $location, $description, $image_url, $is_active, $position_id);
                    }

                    if ($stmt->execute()) {
                        $success_message = "Job position updated successfully!";
                    } else {
                        $error_message = "Error: " . $stmt->error;
                    }
                    $stmt->close();
                }
            }
        }
    }
}


// Handle delete request
if ($action == "delete" && isset($_GET['id'])) {
    $position_id = $_GET['id'];
    
    // Get image path before deleting
    $stmt = $conn->prepare("SELECT image FROM careers_positions WHERE id = ?");
    $stmt->bind_param("i", $position_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $image = $row['image'];
    }
    $stmt->close();
    
    // Delete position
    $stmt = $conn->prepare("DELETE FROM careers_positions WHERE id = ?");
    $stmt->bind_param("i", $position_id);
    
    if ($stmt->execute()) {
        if (!empty($image) && file_exists("../".$image)) {
            unlink("../".$image);
        }
        $success_message = "Job position deleted successfully!";
    } else {
        $error_message = "Error deleting position: " . $stmt->error;
    }
    $stmt->close();
}

// Load position for editing
if ($action == "edit" && isset($_GET['id'])) {
    $position_id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM careers_positions WHERE id = ?");
    $stmt->bind_param("i", $position_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $title = $row['title'];
        $type = $row['type'];
        $shift = $row['shift'];
        $schedule = $row['schedule'];
        $location = $row['location'];
        $description = $row['description'];
        $image = $row['image'];
        $is_active = $row['is_active'];
    } else {
        $error_message = "Position not found";
    }
    $stmt->close();
}

// Get all positions
$positions = array();
$sql = "SELECT * FROM careers_positions ORDER BY created_at DESC";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $positions[] = $row;
    }
}

// Get all applications
$applications = array();
$sql_applications = "SELECT ca.*, cp.title AS position_title FROM careers_applications ca JOIN careers_positions cp ON ca.position_id = cp.id ORDER BY ca.created_at DESC";
$result_applications = $conn->query($sql_applications);
if ($result_applications && $result_applications->num_rows > 0) {
    while ($row = $result_applications->fetch_assoc()) {
        $applications[] = $row;
    }
}


$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Careers | James Polymers Admin</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        .admin-content { margin-left: 16rem; }
    </style>
</head>
<body class="bg-gray-100">
    <?php include 'includes/adminsidebar.php'; ?>
    
    <div class="admin-content p-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Manage Careers</h1>

        <?php if (!empty($success_message)): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
            <p><?php echo $success_message; ?></p>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($error_message)): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
            <p><?php echo $error_message; ?></p>
        </div>
        <?php endif; ?>

        <?php if ($action == "new_position" || $action == "edit"): ?>
        <!-- Add/Edit Position Form -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">
                <?php echo $action == "new_position" ? "Add New Job Position" : "Edit Job Position"; ?>
            </h2>
            
            <form action="admin_careers.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" value="<?php echo $action == 'new_position' ? 'add' : 'edit'; ?>">
                <?php if ($action == "edit"): ?>
                <input type="hidden" name="position_id" value="<?php echo $position_id; ?>">
                <?php endif; ?>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Job Title</label>
                        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($title); ?>" required
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Job Type</label>
                        <select id="type" name="type" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
                            <option value="full-time" <?php echo $type == 'full-time' ? 'selected' : ''; ?>>Full-time</option>
                            <option value="internship" <?php echo $type == 'internship' ? 'selected' : ''; ?>>Internship</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                     <div>
                        <label for="shift" class="block text-sm font-medium text-gray-700 mb-1">Shift</label>
                        <input type="text" id="shift" name="shift" value="<?php echo htmlspecialchars($shift); ?>" required
                               class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    </div>
                     <div>
                        <label for="schedule" class="block text-sm font-medium text-gray-700 mb-1">Schedule</label>
                        <input type="text" id="schedule" name="schedule" value="<?php echo htmlspecialchars($schedule); ?>" required
                               class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="location" class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                    <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($location); ?>" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2">
                </div>
                
                <div class="mb-4">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea id="description" name="description" rows="5" required
                              class="w-full border border-gray-300 rounded-lg px-3 py-2"><?php echo htmlspecialchars($description); ?></textarea>
                </div>
                
                <div class="mb-6">
                    <label for="image" class="block text-sm font-medium text-gray-700 mb-1">Image</label>
                    <?php if ($action == "edit" && !empty($image)): ?>
                    <div class="mb-2"><img src="../<?php echo htmlspecialchars($image); ?>" alt="Current image" class="h-24 w-auto rounded"></div>
                    <?php endif; ?>
                    <input type="file" id="image" name="image" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                </div>

                <div class="mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" value="1" <?php echo $is_active ? 'checked' : ''; ?> class="form-checkbox h-5 w-5 text-primary rounded">
                        <span class="ml-2 text-sm text-gray-700">Active (Visible on careers page)</span>
                    </label>
                </div>
                
                <div class="flex items-center justify-end">
                    <a href="admin_careers.php" class="bg-gray-300 hover:bg-gray-400 text-gray-800 py-2 px-4 rounded-lg mr-4">Cancel</a>
                    <button type="submit" class="bg-primary hover:bg-secondary text-white py-2 px-6 rounded-lg">
                        <?php echo $action == "new_position" ? "Add Position" : "Update Position"; ?>
                    </button>
                </div>
            </form>
        </div>
        <?php else: ?>
        
        <!-- Tabs -->
        <div class="mb-6">
            <ul class="flex border-b" id="career-tabs">
                <li class="-mb-px mr-1">
                    <a class="bg-white inline-block border-l border-t border-r rounded-t py-2 px-4 text-primary font-semibold" href="#positions">Job Positions</a>
                </li>
                 <li class="mr-1">
                    <a class="bg-gray-200 inline-block py-2 px-4 text-gray-600 hover:text-primary font-semibold" href="#applications">Job Applications</a>
                </li>
                <li class="mr-1">
                    <a class="bg-gray-200 inline-block py-2 px-4 text-gray-600 hover:text-primary font-semibold" href="#testimonials">Testimonials</a>
                </li>
            </ul>
        </div>

        <!-- Job Positions Table -->
        <div id="positions" class="tab-content bg-white rounded-lg shadow overflow-hidden">
            <div class="flex justify-between items-center p-4 border-b">
                <h2 class="text-xl font-bold">Job Positions</h2>
                <a href="admin_careers.php?action=new_position" class="bg-primary hover:bg-secondary text-white py-2 px-4 rounded-lg flex items-center">
                    <i class="fas fa-plus mr-2"></i> Add New Position
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="py-3 px-4 text-left">Title</th>
                            <th class="py-3 px-4 text-left">Type</th>
                            <th class="py-3 px-4 text-left">Location</th>
                            <th class="py-3 px-4 text-left">Status</th>
                            <th class="py-3 px-4 text-left">Date Added</th>
                            <th class="py-3 px-4 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($positions as $position): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 px-4"><?php echo htmlspecialchars($position['title']); ?></td>
                            <td class="py-3 px-4"><?php echo ucfirst(htmlspecialchars($position['type'])); ?></td>
                            <td class="py-3 px-4"><?php echo htmlspecialchars($position['location']); ?></td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-1 text-xs rounded-full <?php echo $position['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                    <?php echo $position['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td class="py-3 px-4"><?php echo date('M d, Y', strtotime($position['created_at'])); ?></td>
                            <td class="py-3 px-4">
                                <a href="admin_careers.php?action=edit&id=<?php echo $position['id']; ?>" class="text-primary hover:text-secondary mr-3"><i class="fas fa-edit"></i> Edit</a>
                                <a href="#" onclick="deletePosition(<?php echo $position['id']; ?>, '<?php echo addslashes($position['title']); ?>')" class="text-red-600 hover:text-red-800"><i class="fas fa-trash"></i> Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($positions)): ?>
                        <tr><td colspan="6" class="p-6 text-center text-gray-500">No job positions found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Job Applications Table (example, to be implemented fully with tabs) -->
        <div id="applications" class="tab-content hidden bg-white rounded-lg shadow overflow-hidden mt-8">
            <div class="p-4 border-b"><h2 class="text-xl font-bold">Job Applications</h2></div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="py-3 px-4 text-left">Applicant</th>
                            <th class="py-3 px-4 text-left">Position</th>
                            <th class="py-3 px-4 text-left">Email</th>
                            <th class="py-3 px-4 text-left">Date Applied</th>
                            <th class="py-3 px-4 text-left">Resume</th>
                            <th class="py-3 px-4 text-left">Status</th>
                            <th class="py-3 px-4 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($applications as $app): ?>
                        <tr>
                            <td class="py-3 px-4"><?php echo htmlspecialchars($app['first_name'] . ' ' . $app['last_name']); ?></td>
                            <td class="py-3 px-4"><?php echo htmlspecialchars($app['position_title']); ?></td>
                            <td class="py-3 px-4"><?php echo htmlspecialchars($app['email']); ?></td>
                            <td class="py-3 px-4"><?php echo date('M d, Y', strtotime($app['created_at'])); ?></td>
                            <?php
                                // Remove BASE_URL from the resume_url to get a relative path
                                $resume_path = str_replace(BASE_URL, '', $app['resume_url']);
                            ?>
                            <td class="py-3 px-4"><a href="download_resume.php?file=<?php echo urlencode($resume_path); ?>" class="text-primary">Download Resume</a></td>
                            <td class="py-3 px-4"><?php echo ucfirst(htmlspecialchars($app['status'])); ?></td>
                            <td class="py-3 px-4">
                            <a href="#" class="text-primary hover:text-secondary"><i class="fas fa-eye"></i> View</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                         <?php if (empty($applications)): ?>
                        <tr><td colspan="7" class="p-6 text-center text-gray-500">No job applications found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Testimonials Management -->
        <div id="testimonials" class="tab-content hidden bg-white rounded-lg shadow overflow-hidden mt-8">
            <div class="flex justify-between items-center p-4 border-b">
                <h2 class="text-xl font-bold">Employee Testimonials</h2>
                <button class="bg-primary hover:bg-secondary text-white py-2 px-4 rounded-lg flex items-center" onclick="showAddTestimonialModal()">
                    <i class="fas fa-plus mr-2"></i> Add New Testimonial
                </button>
            </div>
            
            <!-- Batch Filter -->
            <div class="p-4 border-b bg-gray-50">
                <div class="flex flex-wrap gap-2">
                    <button class="testimonial-batch-filter active bg-primary text-white px-4 py-2 rounded-lg text-sm" data-batch="all">All Batches</button>
                    <button class="testimonial-batch-filter bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm hover:bg-gray-300" data-batch="1">Batch 1</button>
                    <button class="testimonial-batch-filter bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm hover:bg-gray-300" data-batch="2">Batch 2</button>
                    <button class="testimonial-batch-filter bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm hover:bg-gray-300" data-batch="3">Batch 3</button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="py-3 px-4 text-left">Name</th>
                            <th class="py-3 px-4 text-left">Position</th>
                            <th class="py-3 px-4 text-left">Batch</th>
                            <th class="py-3 px-4 text-left">Testimonial</th>
                            <th class="py-3 px-4 text-left">Status</th>
                            <th class="py-3 px-4 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="testimonials-table-body">
                        <!-- Sample testimonials - replace with actual data from database -->
                        <tr class="hover:bg-gray-50" data-batch="1">
                            <td class="py-3 px-4 font-medium">Maria Santos</td>
                            <td class="py-3 px-4">OJT Student - Chemical Engineering</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">Batch 1</span>
                            </td>
                            <td class="py-3 px-4 max-w-xs truncate">My experience at JPMC has been incredible...</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Active</span>
                            </td>
                            <td class="py-3 px-4">
                                <button onclick="editTestimonial(1)" class="text-primary hover:text-secondary mr-3"><i class="fas fa-edit"></i> Edit</button>
                                <button onclick="deleteTestimonial(1)" class="text-red-600 hover:text-red-800"><i class="fas fa-trash"></i> Delete</button>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50" data-batch="2">
                            <td class="py-3 px-4 font-medium">Anna Cruz</td>
                            <td class="py-3 px-4">Former OJT, Now Quality Assurance Specialist</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-1 text-xs rounded-full bg-orange-100 text-orange-800">Batch 2</span>
                            </td>
                            <td class="py-3 px-4 max-w-xs truncate">I started as an OJT student at JPMC...</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Active</span>
                            </td>
                            <td class="py-3 px-4">
                                <button onclick="editTestimonial(2)" class="text-primary hover:text-secondary mr-3"><i class="fas fa-edit"></i> Edit</button>
                                <button onclick="deleteTestimonial(2)" class="text-red-600 hover:text-red-800"><i class="fas fa-trash"></i> Delete</button>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50" data-batch="3">
                            <td class="py-3 px-4 font-medium">Carlos Mendoza</td>
                            <td class="py-3 px-4">Chemical Engineering Graduate</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Batch 3</span>
                            </td>
                            <td class="py-3 px-4 max-w-xs truncate">The 6-month OJT at JPMC was instrumental...</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Active</span>
                            </td>
                            <td class="py-3 px-4">
                                <button onclick="editTestimonial(3)" class="text-primary hover:text-secondary mr-3"><i class="fas fa-edit"></i> Edit</button>
                                <button onclick="deleteTestimonial(3)" class="text-red-600 hover:text-red-800"><i class="fas fa-trash"></i> Delete</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <script>
        function deletePosition(id, name) {
            if (confirm('Are you sure you want to delete the position "' + name + '"?')) {
                window.location.href = 'admin_careers.php?action=delete&id=' + id;
            }
        }

        // Testimonial management functions
        function showAddTestimonialModal() {
            alert('Add testimonial functionality will be implemented. For now, you can add testimonials directly to the database using the SQL script provided.');
        }

        function editTestimonial(id) {
            alert('Edit testimonial functionality will be implemented. Testimonial ID: ' + id);
        }

        function deleteTestimonial(id) {
            if (confirm('Are you sure you want to delete this testimonial?')) {
                alert('Delete testimonial functionality will be implemented. Testimonial ID: ' + id);
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const tabs = document.querySelectorAll('#career-tabs a');
            const tabContents = document.querySelectorAll('.tab-content');

            tabs.forEach(tab => {
                tab.addEventListener('click', function (e) {
                    e.preventDefault();

                    tabs.forEach(item => {
                        item.classList.remove('bg-white', 'border-l', 'border-t', 'border-r', 'text-primary', 'font-semibold');
                        item.classList.add('bg-gray-200', 'text-gray-600');
                    });

                    this.classList.add('bg-white', 'border-l', 'border-t', 'border-r', 'text-primary', 'font-semibold');
                    this.classList.remove('bg-gray-200', 'text-gray-600');

                    tabContents.forEach(content => {
                        content.classList.add('hidden');
                    });

                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.classList.remove('hidden');
                    }
                });
            });

            // Testimonials batch filtering
            const batchFilters = document.querySelectorAll('.testimonial-batch-filter');
            const testimonialRows = document.querySelectorAll('#testimonials-table-body tr');

            batchFilters.forEach(filter => {
                filter.addEventListener('click', function() {
                    const batch = this.dataset.batch;
                    
                    // Update active filter button
                    batchFilters.forEach(btn => {
                        btn.classList.remove('active', 'bg-primary', 'text-white');
                        btn.classList.add('bg-gray-200', 'text-gray-700');
                    });
                    this.classList.add('active', 'bg-primary', 'text-white');
                    this.classList.remove('bg-gray-200', 'text-gray-700');
                    
                    // Filter testimonial rows
                    testimonialRows.forEach(row => {
                        if (batch === 'all' || row.dataset.batch === batch) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                });
            });
        });
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
