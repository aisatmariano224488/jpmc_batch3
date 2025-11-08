<?php
// Start session
// session_start();

// // Check if user is logged in
// if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
//     header("Location: admin_login.php");
//     exit;
// }

require_once '../includes/db_connection.php';

// Initialize variables
$service_id = $service_name = $service_description = $service_image_url = "";
$success_message = $error_message = "";
$action = isset($_GET['action']) ? $_GET['action'] : "";

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $service_name = trim($_POST['service_name']);
    $service_description = trim($_POST['service_description']);
    
    // Validate input
    if (empty($service_name)) {
        $error_message = "Service name is required";
    } else {
        // Handle file upload
        $target_dir = "../uploads/services/";
        $service_image = "";
        
        if (isset($_FILES["service_image"]) && $_FILES["service_image"]["error"] == 0) {
            // Create directory if it doesn't exist
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $target_file = $target_dir . basename($_FILES["service_image"]["name"]);
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            
            // Check if image file is an actual image
            $check = getimagesize($_FILES["service_image"]["tmp_name"]);
            if($check === false) {
                $error_message = "File is not an image.";
            }
            // Check file size (limit to 5MB)
            elseif ($_FILES["service_image"]["size"] > 5000000) {
                $error_message = "File is too large. Max size is 5MB.";
            }
            // Allow certain file formats
            elseif($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
                $error_message = "Only JPG, JPEG, PNG & GIF files are allowed.";
            }
            // If everything is ok, try to upload file
            else {
                $new_filename = uniqid() . "." . $imageFileType;
                $target_file = $target_dir . $new_filename;
                
                if (move_uploaded_file($_FILES["service_image"]["tmp_name"], $target_file)) {
                    // Store the path relative to the root directory for front-end access
                    $service_image = "uploads/services/" . $new_filename;
                } else {
                    $error_message = "Sorry, there was an error uploading your file.";
                }
            }
        }
          if (empty($error_message)) {
            // Perform database operations based on action
            if (isset($_POST['action']) && $_POST['action'] == 'add') {
                // Add new service
                $stmt = $conn->prepare("INSERT INTO services (name, description, image_url) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $service_name, $service_description, $service_image);
                
                if ($stmt->execute()) {
                    $success_message = "Service added successfully!";
                    $service_name = $service_description = $service_image_url = "";
                } else {
                    $error_message = "Error: " . $stmt->error;
                }
                $stmt->close();
            }            else if (isset($_POST['action']) && $_POST['action'] == 'edit' && isset($_POST['service_id'])) {
                $service_id = $_POST['service_id'];
                
                // If no new image uploaded, keep existing image
                if (empty($service_image)) {
                    $stmt = $conn->prepare("UPDATE services SET name = ?, description = ? WHERE id = ?");
                    $stmt->bind_param("ssi", $service_name, $service_description, $service_id);
                } else {
                    $stmt = $conn->prepare("UPDATE services SET name = ?, description = ?, image_url = ? WHERE id = ?");
                    $stmt->bind_param("sssi", $service_name, $service_description, $service_image, $service_id);
                }
                
                if ($stmt->execute()) {
                    $success_message = "Service updated successfully!";
                } else {
                    $error_message = "Error: " . $stmt->error;
                }
                $stmt->close();
            }
        }
    }
}

// Handle delete request
if ($action == "delete" && isset($_GET['id'])) {
    $service_id = $_GET['id'];
    
    // Get service image path before deleting
    $stmt = $conn->prepare("SELECT image_url FROM services WHERE id = ?");
    $stmt->bind_param("i", $service_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $service_image = $row['image_url'];
    }
    $stmt->close();
    
    // Delete service
    $stmt = $conn->prepare("DELETE FROM services WHERE id = ?");
    $stmt->bind_param("i", $service_id);
    
    if ($stmt->execute()) {
        // Delete image file if exists
        if (!empty($service_image) && file_exists($service_image)) {
            unlink($service_image);
        }
        $success_message = "Service deleted successfully!";
    } else {
        $error_message = "Error deleting service: " . $stmt->error;
    }
    $stmt->close();
}

// Load service for editing
if ($action == "edit" && isset($_GET['id'])) {
    $service_id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->bind_param("i", $service_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $service_name = $row['name'];
        $service_description = $row['description'];
        $service_image_url = $row['image_url'];
    } else {
        $error_message = "Service not found";
    }
    $stmt->close();
}

// Get all services
$services = array();
$sql = "SELECT * FROM services ORDER BY name ASC";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
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
    <title>Manage Services | James Polymers Admin</title>
    
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
        .admin-content {
            transition: margin-left 0.3s ease;
        }
        
        @media (min-width: 1024px) {
            .admin-content {
                margin-left: 16rem; /* w-64 = 16rem */
            }
        }
        
        .service-card {
            transition: all 0.2s ease-in-out;
        }
        
        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }
        
        .icon-preview {
            font-size: 24px;
            margin-top: 10px;
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Include Admin Sidebar -->
    <?php include 'includes/adminsidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="admin-content p-4 sm:p-6 lg:p-8">
        <!-- Admin Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Manage Services</h1>
                <p class="text-gray-600">Add, edit or delete company services</p>
            </div>
            <div>
                <?php if ($action != "new" && $action != "edit"): ?>
                <a href="admin_services.php?action=new" class="bg-primary hover:bg-secondary text-white py-2 px-4 rounded-lg transition-all flex items-center">
                    <i class="fas fa-plus mr-2"></i> Add New Service
                </a>
                <?php else: ?>
                <a href="admin_services.php" class="bg-gray-600 hover:bg-gray-700 text-white py-2 px-4 rounded-lg transition-all flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Services
                </a>
                <?php endif; ?>
            </div>
        </div>
        
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
        
        <?php if ($action == "new" || $action == "edit"): ?>
        <!-- Add/Edit Service Form -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">
                <?php echo $action == "new" ? "Add New Service" : "Edit Service"; ?>
            </h2>
            
            <form action="admin_services.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" value="<?php echo $action == 'new' ? 'add' : 'edit'; ?>">
                <?php if ($action == "edit"): ?>
                <input type="hidden" name="service_id" value="<?php echo $service_id; ?>">
                <?php endif; ?>
                  <div class="mb-6">
                    <label for="service_name" class="block text-sm font-medium text-gray-700 mb-1">Service Name</label>
                    <input type="text" id="service_name" name="service_name" value="<?php echo htmlspecialchars($service_name); ?>" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary">
                </div>
                
                <div class="mb-6">
                    <label for="service_description" class="block text-sm font-medium text-gray-700 mb-1">Service Description</label>
                    <textarea id="service_description" name="service_description" rows="5"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary"><?php echo htmlspecialchars($service_description); ?></textarea>
                    <p class="text-xs text-gray-500 mt-1">Detailed description of the service.</p>
                </div>
                
                <div class="mb-6">
                    <label for="service_image" class="block text-sm font-medium text-gray-700 mb-1">Service Image</label>
                    
                    <?php if (!empty($service_image_url)): ?>
                    <div class="mb-3 flex items-center">
                        <img src="<?php echo htmlspecialchars($service_image_url); ?>" alt="Current service image" class="h-20 w-auto border rounded-md">
                        <span class="ml-3 text-sm text-gray-600">Current Image</span>
                    </div>
                    <?php endif; ?>
                    
                    <input type="file" id="service_image" name="service_image" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary">
                    <p class="text-xs text-gray-500 mt-1">
                        <?php echo $action == "edit" ? "Upload a new image only if you want to change the current one." : "Please upload an image for the service."; ?>
                        Recommended size: 800x600px.
                    </p>
                </div>
                
                <div class="flex items-center justify-end">
                    <button type="button" onclick="window.location.href='admin_services.php'" class="bg-gray-300 hover:bg-gray-400 text-gray-800 py-2 px-4 rounded-lg mr-4 transition-all">
                        Cancel
                    </button>
                    <button type="submit" class="bg-primary hover:bg-secondary text-white py-2 px-6 rounded-lg transition-all">
                        <?php echo $action == "new" ? "Add Service" : "Update Service"; ?>
                    </button>
                </div>
            </form>
        </div>
        <?php else: ?>
        <!-- Services Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
            <?php foreach ($services as $service): ?>            <div class="service-card bg-white rounded-lg shadow overflow-hidden">
                <div class="h-40 bg-cover bg-center relative" 
                     style="background-image: url('<?php echo !empty($service['image_url']) ? htmlspecialchars($service['image_url']) : 'https://via.placeholder.com/800x600?text=No+Image'; ?>');">
                    
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                    
                    <div class="absolute bottom-0 left-0 p-4 text-white">
                        <h3 class="font-bold text-lg"><?php echo htmlspecialchars($service['name']); ?></h3>
                    </div>
                    
                    <div class="absolute top-2 right-2 flex space-x-1">
                        <a href="admin_services.php?action=edit&id=<?php echo $service['id']; ?>" class="bg-white p-2 rounded-full shadow hover:bg-gray-100 transition-colors">
                            <i class="fas fa-edit text-primary"></i>
                        </a>
                        <a href="#" onclick="deleteService(<?php echo $service['id']; ?>, '<?php echo addslashes($service['name']); ?>')" class="bg-white p-2 rounded-full shadow hover:bg-gray-100 transition-colors">
                            <i class="fas fa-trash text-red-500"></i>
                        </a>
                    </div>
                </div>
                
                <div class="p-4">
                    <h3 class="font-bold text-gray-800 mb-3"><?php echo htmlspecialchars($service['name']); ?></h3>
                    
                    <p class="text-gray-600 text-sm mb-4"><?php echo htmlspecialchars(substr($service['description'], 0, 150)); ?>
                        <?php if (strlen($service['description']) > 150): ?>...<?php endif; ?>
                    </p>
                    
                    <div class="text-xs text-gray-500 mt-3">
                        Added: <?php echo date('M d, Y', strtotime($service['created_at'])); ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            
            <?php if (empty($services)): ?>
            <div class="col-span-full bg-white rounded-lg shadow p-8 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-4">
                    <i class="fas fa-cogs text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-800 mb-2">No Services Found</h3>
                <p class="text-gray-600 mb-4">You haven't added any services yet.</p>
                <a href="admin_services.php?action=new" class="inline-flex items-center text-primary hover:text-secondary">
                    <i class="fas fa-plus mr-2"></i> Add Your First Service
                </a>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
      <script>
        function deleteService(id, name) {
            if (confirm('Are you sure you want to delete the service "' + name + '"?')) {
                window.location.href = 'admin_services.php?action=delete&id=' + id;
            }
        }
    </script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>