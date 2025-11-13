<?php
// // Start session
// session_start();

// // Check if user is logged in
// if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
//     header("Location: admin_login.php");
//     exit;
// }

require_once '../includes/db_connection.php';

// Initialize variables
$industry_id = $industry_name = $industry_description = $industry_icon = $industry_image = "";
$coming_soon = 0;
$success_message = $error_message = "";
$action = isset($_GET['action']) ? $_GET['action'] : "";

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $industry_name = trim($_POST['industry_name']);
    $industry_description = trim($_POST['industry_description']);
    $industry_icon = trim($_POST['industry_icon']);
    $coming_soon = isset($_POST['coming_soon']) ? 1 : 0;
    
    // Validate input
    if (empty($industry_name)) {
        $error_message = "Industry name is required";
    } else {
        // Handle file upload
        $target_dir = "../uploads/industries/";
        $industry_image = "";
        
        if (isset($_FILES["industry_image"]) && $_FILES["industry_image"]["error"] == 0) {
            // Create directory if it doesn't exist
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $target_file = $target_dir . basename($_FILES["industry_image"]["name"]);
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            
            // Check if image file is an actual image
            $check = getimagesize($_FILES["industry_image"]["tmp_name"]);
            if($check === false) {
                $error_message = "File is not an image.";
            }
            // Check file size (limit to 5MB)
            elseif ($_FILES["industry_image"]["size"] > 5000000) {
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
                
                if (move_uploaded_file($_FILES["industry_image"]["tmp_name"], $target_file)) {
                    // Store the path relative to the root directory for front-end access
                    $industry_image = "uploads/industries/" . $new_filename;
                } else {
                    $error_message = "Sorry, there was an error uploading your file.";
                }
            }
        } else if (isset($_POST['existing_image']) && !empty($_POST['existing_image'])) {
            $industry_image = $_POST['existing_image'];
        }
        
        if (empty($error_message)) {
            // Perform database operations based on action
            if (isset($_POST['action']) && $_POST['action'] == 'add') {
                // Add new industry
                $stmt = $conn->prepare("INSERT INTO industries (name, description, image_url, icon_class, coming_soon) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssi", $industry_name, $industry_description, $industry_image, $industry_icon, $coming_soon);
                
                if ($stmt->execute()) {
                    $success_message = "Industry added successfully!";
                    $industry_name = $industry_description = $industry_icon = $industry_image = "";
                    $coming_soon = 0;
                } else {
                    $error_message = "Error: " . $stmt->error;
                }
                $stmt->close();
            } 
            else if (isset($_POST['action']) && $_POST['action'] == 'edit' && isset($_POST['industry_id'])) {
                $industry_id = $_POST['industry_id'];
                
                // If no new image uploaded, keep existing image
                if (empty($industry_image)) {
                    $stmt = $conn->prepare("UPDATE industries SET name = ?, description = ?, icon_class = ?, coming_soon = ? WHERE id = ?");
                    $stmt->bind_param("sssii", $industry_name, $industry_description, $industry_icon, $coming_soon, $industry_id);
                } else {
                    $stmt = $conn->prepare("UPDATE industries SET name = ?, description = ?, image_url = ?, icon_class = ?, coming_soon = ? WHERE id = ?");
                    $stmt->bind_param("ssssii", $industry_name, $industry_description, $industry_image, $industry_icon, $coming_soon, $industry_id);
                }
                
                if ($stmt->execute()) {
                    $success_message = "Industry updated successfully!";
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
    $industry_id = $_GET['id'];
    
    // Get industry image path before deleting
    $stmt = $conn->prepare("SELECT image_url FROM industries WHERE id = ?");
    $stmt->bind_param("i", $industry_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $industry_image = $row['image_url'];
    }
    $stmt->close();
    
    // First, delete related solutions
    $stmt = $conn->prepare("DELETE FROM industry_solutions WHERE industry_id = ?");
    $stmt->bind_param("i", $industry_id);
    $stmt->execute();
    $stmt->close();

    // Then delete the industry
    $stmt = $conn->prepare("DELETE FROM industries WHERE id = ?");
    $stmt->bind_param("i", $industry_id);

    
    if ($stmt->execute()) {
        // Delete image file if exists and is a local file (not a URL)
        if (!empty($industry_image) && file_exists($industry_image) && strpos($industry_image, 'http') !== 0) {
            unlink($industry_image);
        }
        $success_message = "Industry deleted successfully!";
    } else {
        $error_message = "Error deleting industry: " . $stmt->error;
    }
    $stmt->close();
}

// Load industry for editing
if ($action == "edit" && isset($_GET['id'])) {
    $industry_id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM industries WHERE id = ?");
    $stmt->bind_param("i", $industry_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $industry_name = $row['name'];
        $industry_description = $row['description'];
        $industry_icon = $row['icon_class'];
        $industry_image = $row['image_url'];
        $coming_soon = $row['coming_soon'];
    } else {
        $error_message = "Industry not found";
    }
    $stmt->close();
}

// Get all industries
$industries = array();
$sql = "SELECT * FROM industries ORDER BY name ASC";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $industries[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Industries | James Polymers Admin</title>
    <link rel="icon" type="image/png" href="../assets/img/tab_icon.png">
    
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
            padding: 1rem;
        }
        
        @media (min-width: 768px) {
            .admin-content {
                padding: 1.5rem;
            }
        }
        
        @media (min-width: 1024px) {
            .admin-content {
                margin-left: 16rem;
                padding: 2rem;
            }
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
        <div class="mb-6">
            <div class="flex flex-col gap-4">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Manage Industries</h1>
                    <p class="text-sm sm:text-base text-gray-600">Add, edit or delete industries we serve</p>
                </div>
                <div class="w-full sm:w-auto">
                    <?php if ($action != "new" && $action != "edit"): ?>
                    <a href="admin_industries.php?action=new" class="bg-primary hover:bg-secondary text-white py-2 px-4 rounded-lg transition-all flex items-center justify-center w-full sm:w-auto">
                        <i class="fas fa-plus mr-2"></i> Add New Industry
                    </a>
                    <?php else: ?>
                    <a href="admin_industries.php" class="bg-gray-600 hover:bg-gray-700 text-white py-2 px-4 rounded-lg transition-all flex items-center justify-center w-full sm:w-auto">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Industries
                    </a>
                    <?php endif; ?>
                </div>
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
        <!-- Add/Edit Industry Form -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">
                <?php echo $action == "new" ? "Add New Industry" : "Edit Industry"; ?>
            </h2>
            
            <form action="admin_industries.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" value="<?php echo $action == 'new' ? 'add' : 'edit'; ?>">
                <?php if ($action == "edit"): ?>
                <input type="hidden" name="industry_id" value="<?php echo $industry_id; ?>">
                <input type="hidden" name="existing_image" value="<?php echo $industry_image; ?>">
                <?php endif; ?>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="industry_name" class="block text-sm font-medium text-gray-700 mb-1">Industry Name</label>
                        <input type="text" id="industry_name" name="industry_name" value="<?php echo htmlspecialchars($industry_name); ?>" required
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                    
                    <div>
                        <label for="industry_icon" class="block text-sm font-medium text-gray-700 mb-1">
                            Font Awesome Icon Class
                            <a href="https://fontawesome.com/icons" target="_blank" class="text-primary hover:underline ml-1">
                                <i class="fas fa-external-link-alt text-xs"></i> Browse Icons
                            </a>
                        </label>
                        <input type="text" id="industry_icon" name="industry_icon" value="<?php echo htmlspecialchars($industry_icon); ?>"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary"
                               placeholder="e.g. fas fa-industry">
                        <div class="icon-preview mt-2">
                            <i id="icon-preview-element" class="<?php echo htmlspecialchars($industry_icon); ?>"></i>
                            <small class="text-gray-500 ml-2" id="icon-preview-text">Icon preview</small>
                        </div>
                    </div>
                </div>
                
                <div class="mb-6">
                    <label for="industry_description" class="block text-sm font-medium text-gray-700 mb-1">Industry Description</label>
                    <textarea id="industry_description" name="industry_description" rows="4"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary"><?php echo htmlspecialchars($industry_description); ?></textarea>
                </div>
                
                <div class="mb-6">
                    <label for="industry_image" class="block text-sm font-medium text-gray-700 mb-1">Industry Image</label>
                    
                    <?php if (!empty($industry_image)): ?>
                    <div class="mb-3 flex items-center">
                        <img src="../<?php echo htmlspecialchars($industry_image); ?>" alt="Current industry image" class="h-20 w-auto border rounded-md">
                        <span class="ml-3 text-sm text-gray-600">Current Image</span>
                    </div>
                    <?php endif; ?>
                    
                    <input type="file" id="industry_image" name="industry_image" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary">
                    <p class="text-xs text-gray-500 mt-1">
                        <?php echo $action == "edit" ? "Upload a new image only if you want to change the current one." : "Please upload an image for the industry."; ?>
                        Recommended size: 800x600px.
                    </p>
                </div>
                
                <div class="mb-6">
                    <div class="flex items-center">
                        <input type="checkbox" id="coming_soon" name="coming_soon" <?php echo $coming_soon ? 'checked' : ''; ?> class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                        <label for="coming_soon" class="ml-2 block text-sm text-gray-700">Mark as "Coming Soon"</label>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Check this if this industry service is in development but not yet available.</p>
                </div>
                
                <div class="flex items-center justify-end">
                    <button type="button" onclick="window.location.href='admin_industries.php'" class="bg-gray-300 hover:bg-gray-400 text-gray-800 py-2 px-4 rounded-lg mr-4 transition-all">
                        Cancel
                    </button>
                    <button type="submit" class="bg-primary hover:bg-secondary text-white py-2 px-6 rounded-lg transition-all">
                        <?php echo $action == "new" ? "Add Industry" : "Update Industry"; ?>
                    </button>
                </div>
            </form>
        </div>
        <?php else: ?>
        <!-- Industries Grid/List -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
            <?php foreach ($industries as $industry): ?>
            <div class="bg-white rounded-lg shadow overflow-hidden hover:shadow-lg transition-shadow">
                <!-- FIXED: -->
                <div class="h-40 bg-cover bg-center relative" 
                    style="background-image: url('../<?php echo !empty($industry['image_url']) ? htmlspecialchars($industry['image_url']) : 'https://via.placeholder.com/800x600?text=No+Image'; ?>');">
                    
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                    
                    <?php if ($industry['coming_soon']): ?>
                    <div class="absolute top-2 left-2">
                        <span class="bg-yellow-500 text-white text-xs px-2 py-1 rounded-full font-medium">Coming Soon</span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="absolute bottom-0 left-0 p-4 text-white">
                        <h3 class="font-bold text-lg"><?php echo htmlspecialchars($industry['name']); ?></h3>
                    </div>
                    
                    <div class="absolute top-2 right-2 flex space-x-1">
                        <a href="admin_industries.php?action=edit&id=<?php echo $industry['id']; ?>" class="bg-white p-2 rounded-full shadow hover:bg-gray-100 transition-colors">
                            <i class="fas fa-edit text-primary"></i>
                        </a>
                        <a href="#" onclick="deleteIndustry(<?php echo $industry['id']; ?>, '<?php echo addslashes($industry['name']); ?>')" class="bg-white p-2 rounded-full shadow hover:bg-gray-100 transition-colors">
                            <i class="fas fa-trash text-red-500"></i>
                        </a>
                    </div>
                </div>
                
                <div class="p-4">
                    <div class="flex items-center mb-3">
                        <?php if (!empty($industry['icon_class'])): ?>
                        <div class="bg-primary bg-opacity-10 p-2 rounded-full mr-3">
                            <i class="<?php echo htmlspecialchars($industry['icon_class']); ?> text-primary"></i>
                        </div>
                        <?php endif; ?>
                        <h3 class="font-bold text-gray-800"><?php echo htmlspecialchars($industry['name']); ?></h3>
                    </div>
                    
                    <?php if (!empty($industry['description'])): ?>
                    <p class="text-gray-600 text-sm">
                        <?php echo htmlspecialchars(substr($industry['description'], 0, 100)); ?>
                        <?php if (strlen($industry['description']) > 100): ?>...<?php endif; ?>
                    </p>
                    <?php else: ?>
                    <p class="text-gray-500 text-sm italic">No description available</p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
            
            <?php if (empty($industries)): ?>
            <div class="col-span-full bg-white rounded-lg shadow p-8 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-4">
                    <i class="fas fa-industry text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-800 mb-2">No Industries Found</h3>
                <p class="text-gray-600 mb-4">You haven't added any industries yet.</p>
                <a href="admin_industries.php?action=new" class="inline-flex items-center text-primary hover:text-secondary">
                    <i class="fas fa-plus mr-2"></i> Add Your First Industry
                </a>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <script>
        // For icon preview
        document.addEventListener('DOMContentLoaded', function() {
            const iconInput = document.getElementById('industry_icon');
            const iconPreview = document.getElementById('icon-preview-element');
            const iconText = document.getElementById('icon-preview-text');
            
            if (iconInput && iconPreview) {
                iconInput.addEventListener('input', function() {
                    iconPreview.className = this.value.trim();
                    if (this.value.trim() === '') {
                        iconText.textContent = 'Enter icon class to see preview';
                    } else {
                        iconText.textContent = 'Icon preview';
                    }
                });
            }
        });
        
        function deleteIndustry(id, name) {
            if (confirm('Are you sure you want to delete the industry "' + name + '"?')) {
                window.location.href = 'admin_industries.php?action=delete&id=' + id;
            }
        }
    </script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>