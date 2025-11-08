<?php
// Start session
// session_start();

// // Check if user is logged in
// if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
//     header("Location: admin_login.php");
//     exit;
// }

require_once '../includes/db_connection.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $page_name = $_POST['page_name'];
    $header_title = $_POST['header_title'];
    $header_overlay = $_POST['header_overlay'];
    
    // Function to handle file upload
    function handleFileUpload($file, $old_path = '', $remove_image = false) {
        if ($remove_image) {
            return ''; // Return empty string to remove image
        }
        
        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            return $old_path; // Keep existing file if no new file uploaded
        }
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("File upload failed with error code: " . $file['error']);
        }
        
        $upload_dir = '../uploads/page_configs/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (!in_array($file_extension, $allowed_extensions)) {
            throw new Exception("Invalid file type. Allowed types: " . implode(', ', $allowed_extensions));
        }
        
        $new_filename = uniqid() . '.' . $file_extension;
        $upload_path = $upload_dir . $new_filename;
        
        if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
            throw new Exception("Failed to move uploaded file");
        }
        
        return 'uploads/page_configs/' . $new_filename;
    }
    
    try {
        // Check if remove flags are set
        $remove_main_bg = isset($_POST['remove_main_bg']);
        $remove_header_bg = isset($_POST['remove_header_bg']);
        $remove_left_badge = isset($_POST['remove_left_badge']);
        $remove_right_badge = isset($_POST['remove_right_badge']);
        $remove_coming_soon = isset($_POST['remove_coming_soon']);
        
        // Handle file uploads
        $main_bg = handleFileUpload($_FILES['main_bg'], $_POST['old_main_bg'] ?? '', $remove_main_bg);
        $header_bg = handleFileUpload($_FILES['header_bg'], $_POST['old_header_bg'] ?? '', $remove_header_bg);
        $left_badge = handleFileUpload($_FILES['left_badge'], $_POST['old_left_badge'] ?? '', $remove_left_badge);
        $right_badge = handleFileUpload($_FILES['right_badge'], $_POST['old_right_badge'] ?? '', $remove_right_badge);
        $coming_soon = handleFileUpload($_FILES['coming_soon'], $_POST['old_coming_soon'] ?? '', $remove_coming_soon);

        // Update the configuration in the database
        $sql = "INSERT INTO page_configs (page_name, main_bg, header_bg, header_title, header_overlay, left_badge, right_badge, coming_soon) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE 
                main_bg = VALUES(main_bg),
                header_bg = VALUES(header_bg),
                header_title = VALUES(header_title),
                header_overlay = VALUES(header_overlay),
                left_badge = VALUES(left_badge),
                right_badge = VALUES(right_badge),
                coming_soon = VALUES(coming_soon)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssss", $page_name, $main_bg, $header_bg, $header_title, $header_overlay, $left_badge, $right_badge, $coming_soon);
        
        if ($stmt->execute()) {
            $success_message = "Configuration updated successfully!";
        } else {
            $error_message = "Error updating configuration: " . $conn->error;
        }
        $stmt->close();
    } catch (Exception $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

// Get all page configurations
$page_configs = array();
$sql = "SELECT * FROM page_configs";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $page_configs[$row['page_name']] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Configuration - JPMC Admin</title>
    
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
</head>
<body class="bg-gray-100">
    <!-- Include Admin Sidebar -->
    <?php include 'includes/adminsidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="admin-content p-4 sm:p-6 lg:p-8 ml-0 lg:ml-64 min-h-screen">
        <!-- Admin Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Page Configuration</h1>
                <p class="text-gray-600">Manage page configurations and layouts</p>
            </div>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $success_message; ?></span>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $error_message; ?></span>
            </div>
        <?php endif; ?>

        <!-- Configuration Form -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <form method="POST" action="" class="space-y-6" enctype="multipart/form-data">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Page Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Page</label>
                        <select name="page_name" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50" required>
                            <option value="">Select a page...</option>
                            <option value="sustainability">Sustainability</option>
                            <option value="videos_promotion">Videos & Promotion</option>
                            <option value="overviewprocess">Overview Process</option>
                            <option value="manufacturingprocess">Manufacturing Process</option>
                            <option value="shop">Shop</option>
                            <option value="news_events">News & Events</option>
                            <option value="careers">Careers</option>
                            <option value="plant_visit">Plant Visit</option>
                            <option value="faq">FAQ</option>
                        </select>
                    </div>

                    <!-- Main Background -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Main Background Image</label>
                        <input type="file" name="main_bg" accept="image/*" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50">
                        <input type="hidden" name="old_main_bg" id="old_main_bg">
                        <div id="main_bg_preview" class="mt-2"></div>
                        <div class="mt-2">
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="remove_main_bg" class="rounded border-gray-300 text-primary shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-red-600">Remove current image</span>
                            </label>
                        </div>
                    </div>

                    <!-- Header Title -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Header Title</label>
                        <input type="text" name="header_title" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50" required>
                    </div>

                    <!-- Header Background -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Header Background Image</label>
                        <input type="file" name="header_bg" accept="image/*" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50">
                        <input type="hidden" name="old_header_bg" id="old_header_bg">
                        <div id="header_bg_preview" class="mt-2"></div>
                        <div class="mt-2">
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="remove_header_bg" class="rounded border-gray-300 text-primary shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-red-600">Remove current image</span>
                            </label>
                        </div>
                    </div>

                    <!-- Header Overlay -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Header Overlay Color</label>
                        <input type="text" name="header_overlay" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50" placeholder="rgba(37,80,200,0.38)" required>
                    </div>

                    <!-- Left Badge -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Left Badge Image</label>
                        <input type="file" name="left_badge" accept="image/*" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50">
                        <input type="hidden" name="old_left_badge" id="old_left_badge">
                        <div id="left_badge_preview" class="mt-2"></div>
                        <div class="mt-2">
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="remove_left_badge" class="rounded border-gray-300 text-primary shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-red-600">Remove current image</span>
                            </label>
                        </div>
                    </div>

                    <!-- Right Badge -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Right Badge Image</label>
                        <input type="file" name="right_badge" accept="image/*" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50">
                        <input type="hidden" name="old_right_badge" id="old_right_badge">
                        <div id="right_badge_preview" class="mt-2"></div>
                        <div class="mt-2">
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="remove_right_badge" class="rounded border-gray-300 text-primary shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-red-600">Remove current image</span>
                            </label>
                        </div>
                    </div>

                    <!-- Coming Soon Image -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Coming Soon Image</label>
                        <input type="file" name="coming_soon" accept="image/*" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50">
                        <input type="hidden" name="old_coming_soon" id="old_coming_soon">
                        <div id="coming_soon_preview" class="mt-2"></div>
                        <div class="mt-2">
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="remove_coming_soon" class="rounded border-gray-300 text-primary shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-red-600">Remove current image</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="bg-primary hover:bg-secondary text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Save Configuration
                    </button>
                </div>
            </form>
        </div>

        <!-- Current Configurations -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Current Configurations</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Page</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Header Title</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Header Background</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($page_configs as $page_name => $config): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?php echo ucwords(str_replace('_', ' ', $page_name)); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo htmlspecialchars($config['header_title']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo htmlspecialchars($config['header_bg']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <button onclick="loadConfig('<?php echo $page_name; ?>')" class="text-primary hover:text-secondary">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        const pageConfigs = <?php echo json_encode($page_configs); ?>;
        let userEditedTitle = false;
        let userEditedOverlay = false;

        // When the page loads, reset edit flags
        document.addEventListener('DOMContentLoaded', function() {
            userEditedTitle = false;
            userEditedOverlay = false;
        });

        // Track if user edits the header title or overlay color
        document.querySelector('input[name="header_title"]').addEventListener('input', function() {
            userEditedTitle = true;
        });
        document.querySelector('input[name="header_overlay"]').addEventListener('input', function() {
            userEditedOverlay = true;
        });

        // When page selection changes, auto-fill fields unless user has edited
        document.querySelector('select[name="page_name"]').addEventListener('change', function() {
            const pageName = this.value;
            const config = pageConfigs[pageName];
            if (config) {
                // Only auto-fill if user hasn't edited
                if (!userEditedTitle) {
                    document.querySelector('input[name="header_title"]').value = config.header_title;
                }
                if (!userEditedOverlay) {
                    document.querySelector('input[name="header_overlay"]').value = config.header_overlay;
                }
                // Set old file paths
                document.getElementById('old_header_bg').value = config.header_bg;
                document.getElementById('old_left_badge').value = config.left_badge;
                document.getElementById('old_right_badge').value = config.right_badge;
                document.getElementById('old_coming_soon').value = config.coming_soon;
                document.getElementById('old_main_bg').value = config.main_bg;
                // Show current image previews
                document.getElementById('header_bg_preview').innerHTML = `<img src="../${config.header_bg}" class="h-20 w-auto mt-2">`;
                document.getElementById('left_badge_preview').innerHTML = `<img src="../${config.left_badge}" class="h-20 w-auto mt-2">`;
                document.getElementById('right_badge_preview').innerHTML = `<img src="../${config.right_badge}" class="h-20 w-auto mt-2">`;
                document.getElementById('coming_soon_preview').innerHTML = `<img src="../${config.coming_soon}" class="h-20 w-auto mt-2">`;
                document.getElementById('main_bg_preview').innerHTML = `<img src="../${config.main_bg}" class="h-20 w-auto mt-2">`;
                
                // Show/hide remove checkboxes based on whether images exist
                toggleRemoveCheckbox('remove_header_bg', config.header_bg);
                toggleRemoveCheckbox('remove_left_badge', config.left_badge);
                toggleRemoveCheckbox('remove_right_badge', config.right_badge);
                toggleRemoveCheckbox('remove_coming_soon', config.coming_soon);
                toggleRemoveCheckbox('remove_main_bg', config.main_bg);
                
                // Scroll to the form
                document.querySelector('form').scrollIntoView({ behavior: 'smooth' });
            } else {
                // Clear fields if no config
                if (!userEditedTitle) document.querySelector('input[name="header_title"]').value = '';
                if (!userEditedOverlay) document.querySelector('input[name="header_overlay"]').value = '';
                document.getElementById('old_header_bg').value = '';
                document.getElementById('old_left_badge').value = '';
                document.getElementById('old_right_badge').value = '';
                document.getElementById('old_coming_soon').value = '';
                document.getElementById('old_main_bg').value = '';
                document.getElementById('header_bg_preview').innerHTML = '';
                document.getElementById('left_badge_preview').innerHTML = '';
                document.getElementById('right_badge_preview').innerHTML = '';
                document.getElementById('coming_soon_preview').innerHTML = '';
                document.getElementById('main_bg_preview').innerHTML = '';
                
                // Hide all remove checkboxes
                hideAllRemoveCheckboxes();
            }
        });

        // Function to toggle remove checkbox visibility
        function toggleRemoveCheckbox(checkboxName, imagePath) {
            const checkbox = document.querySelector(`input[name="${checkboxName}"]`);
            const checkboxContainer = checkbox.closest('.mt-2');
            if (imagePath && imagePath.trim() !== '') {
                checkboxContainer.style.display = 'block';
                checkbox.checked = false;
            } else {
                checkboxContainer.style.display = 'none';
                checkbox.checked = false;
            }
        }

        // Function to hide all remove checkboxes
        function hideAllRemoveCheckboxes() {
            const removeCheckboxes = ['remove_header_bg', 'remove_left_badge', 'remove_right_badge', 'remove_coming_soon', 'remove_main_bg'];
            removeCheckboxes.forEach(checkboxName => {
                const checkbox = document.querySelector(`input[name="${checkboxName}"]`);
                const checkboxContainer = checkbox.closest('.mt-2');
                checkboxContainer.style.display = 'none';
                checkbox.checked = false;
            });
        }

        // Preview images before upload
        document.querySelectorAll('input[type="file"]').forEach(input => {
            input.addEventListener('change', function(e) {
                const preview = document.getElementById(this.name + '_preview');
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.innerHTML = `<img src="${e.target.result}" class="h-20 w-auto mt-2">`;
                    }
                    reader.readAsDataURL(this.files[0]);
                }
            });
        });

        // When clicking Edit, reset edit flags so auto-fill works again
        function loadConfig(pageName) {
            const config = pageConfigs[pageName];
            if (config) {
                document.querySelector('select[name="page_name"]').value = pageName;
                document.querySelector('input[name="header_title"]').value = config.header_title;
                document.querySelector('input[name="header_overlay"]').value = config.header_overlay;
                userEditedTitle = false;
                userEditedOverlay = false;
                document.getElementById('old_header_bg').value = config.header_bg;
                document.getElementById('old_left_badge').value = config.left_badge;
                document.getElementById('old_right_badge').value = config.right_badge;
                document.getElementById('old_coming_soon').value = config.coming_soon;
                document.getElementById('old_main_bg').value = config.main_bg;
                document.getElementById('header_bg_preview').innerHTML = `<img src="../${config.header_bg}" class="h-20 w-auto mt-2">`;
                document.getElementById('left_badge_preview').innerHTML = `<img src="../${config.left_badge}" class="h-20 w-auto mt-2">`;
                document.getElementById('right_badge_preview').innerHTML = `<img src="../${config.right_badge}" class="h-20 w-auto mt-2">`;
                document.getElementById('coming_soon_preview').innerHTML = `<img src="../${config.coming_soon}" class="h-20 w-auto mt-2">`;
                document.getElementById('main_bg_preview').innerHTML = `<img src="../${config.main_bg}" class="h-20 w-auto mt-2">`;
                
                // Show/hide remove checkboxes based on whether images exist
                toggleRemoveCheckbox('remove_header_bg', config.header_bg);
                toggleRemoveCheckbox('remove_left_badge', config.left_badge);
                toggleRemoveCheckbox('remove_right_badge', config.right_badge);
                toggleRemoveCheckbox('remove_coming_soon', config.coming_soon);
                toggleRemoveCheckbox('remove_main_bg', config.main_bg);
                
                document.querySelector('form').scrollIntoView({ behavior: 'smooth' });
            }
        }
    </script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 