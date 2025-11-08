<?php
// Start session
// session_start();

// // Check if user is logged in
// if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
//     header("Location: admin_login.php");
//     exit;
// }

require_once '../includes/db_connection.php';

// Create pages table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS page_content (
    id INT AUTO_INCREMENT PRIMARY KEY,
    page_name VARCHAR(50) NOT NULL,
    section_name VARCHAR(100) NOT NULL,
    content_key VARCHAR(100) NOT NULL,
    content_value TEXT,
    content_type ENUM('text', 'html', 'image', 'video') DEFAULT 'text',
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_content (page_name, section_name, content_key)
)";

if (!$conn->query($sql)) {
    die("Error creating table: " . $conn->error);
}

// Initialize variables
$success_message = $error_message = "";
$action = isset($_GET['action']) ? $_GET['action'] : "";
$page_name = isset($_GET['page']) ? $_GET['page'] : "index";
$edit_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// All available pages
$available_pages = [
    'index' => 'Homepage',
    'about' => 'About Us',
    'contact' => 'Contact Us',
    'sustainability' => 'Sustainability',
    'careers' => 'Careers',
    'overviewprocess' => 'Overview Process',
    'manufacturingprocess' => 'Manufacturing Process',
    'shop' => 'Shop'
];

// Handle form submission for updating page content
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_content':
                $id = (int)$_POST['content_id'];
                $content_value = $conn->real_escape_string($_POST['content_value']);
                $content_type = $conn->real_escape_string($_POST['content_type']);
                
                // Handle image upload if content type is image
                if ($content_type === 'image' && isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                    $target_dir = "../uploads/pages/";
                    
                    // Create directory if it doesn't exist
                    if (!file_exists($target_dir)) {
                        mkdir($target_dir, 0777, true);
                    }
                    
                    $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                    $new_filename = uniqid() . '.' . $file_extension;
                    $target_file = $target_dir . $new_filename;
                    
                    // Check if image file is an actual image
                    $check = getimagesize($_FILES['image']['tmp_name']);
                    if($check === false) {
                        $error_message = "File is not an image.";
                    } else {
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                            $content_value = 'uploads/pages/' . $new_filename;
                        } else {
                            $error_message = "Sorry, there was an error uploading your file.";
                        }
                    }
                }
                
                if (empty($error_message)) {
                    $sql = "UPDATE page_content SET content_value = ? WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("si", $content_value, $id);
                    
                    if ($stmt->execute()) {
                        $success_message = "Content updated successfully!";
                    } else {
                        $error_message = "Error updating content: " . $conn->error;
                    }
                }
                break;
                
            case 'add_content':
                $page_name = $conn->real_escape_string($_POST['page_name']);
                $section_name = $conn->real_escape_string($_POST['section_name']);
                $content_key = $conn->real_escape_string($_POST['content_key']);
                $content_value = $conn->real_escape_string($_POST['content_value']);
                $content_type = $conn->real_escape_string($_POST['content_type']);
                
                // Handle image upload if content type is image
                if ($content_type === 'image' && isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                    $target_dir = "../uploads/pages/";
                    
                    // Create directory if it doesn't exist
                    if (!file_exists($target_dir)) {
                        mkdir($target_dir, 0777, true);
                    }
                    
                    $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                    $new_filename = uniqid() . '.' . $file_extension;
                    $target_file = $target_dir . $new_filename;
                    
                    // Check if image file is an actual image
                    $check = getimagesize($_FILES['image']['tmp_name']);
                    if($check === false) {
                        $error_message = "File is not an image.";
                    } else {
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                            $content_value = 'uploads/pages/' . $new_filename;
                        } else {
                            $error_message = "Sorry, there was an error uploading your file.";
                        }
                    }
                }
                
                if (empty($error_message)) {
                    // Check if the content already exists
                    $check_sql = "SELECT id FROM page_content WHERE page_name = ? AND section_name = ? AND content_key = ?";
                    $check_stmt = $conn->prepare($check_sql);
                    $check_stmt->bind_param("sss", $page_name, $section_name, $content_key);
                    $check_stmt->execute();
                    $check_result = $check_stmt->get_result();
                    
                    if ($check_result->num_rows > 0) {
                        // Update existing content
                        $row = $check_result->fetch_assoc();
                        $content_id = $row['id'];
                        
                        $sql = "UPDATE page_content SET content_value = ?, content_type = ? WHERE id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("ssi", $content_value, $content_type, $content_id);
                    } else {
                        // Insert new content
                        $sql = "INSERT INTO page_content (page_name, section_name, content_key, content_value, content_type) VALUES (?, ?, ?, ?, ?)";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("sssss", $page_name, $section_name, $content_key, $content_value, $content_type);
                    }
                    
                    if ($stmt->execute()) {
                        $success_message = "Content added successfully!";
                    } else {
                        $error_message = "Error adding content: " . $conn->error;
                    }
                }
                break;
                
            case 'delete_content':
                $id = (int)$_POST['content_id'];
                
                // Get content information before deleting
                $sql = "SELECT content_value, content_type FROM page_content WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($row = $result->fetch_assoc()) {
                    // If it's an image, delete the file
                    if ($row['content_type'] === 'image' && !empty($row['content_value'])) {
                        $file_path = '../' . $row['content_value'];
                        if (file_exists($file_path)) {
                            unlink($file_path);
                        }
                    }
                }
                
                $sql = "DELETE FROM page_content WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $id);
                
                if ($stmt->execute()) {
                    $success_message = "Content deleted successfully!";
                } else {
                    $error_message = "Error deleting content: " . $conn->error;
                }
                break;
        }
    }
}

// Get content for the selected page
$contents = array();
$sql = "SELECT * FROM page_content WHERE page_name = ? ORDER BY section_name, content_key";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $page_name);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        if (!isset($contents[$row['section_name']])) {
            $contents[$row['section_name']] = [];
        }
        $contents[$row['section_name']][] = $row;
    }
}

// Get specific content for editing
$edit_content = null;
if ($action === 'edit' && $edit_id > 0) {
    $sql = "SELECT * FROM page_content WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $edit_content = $result->fetch_assoc();
        $page_name = $edit_content['page_name']; // Update page name to match the content being edited
    }
}

// Page-specific section templates
$page_sections = [
    'index' => [
        'hero_section' => 'Hero Section',
        'mission_vision' => 'Mission & Vision',
        'products_services' => 'Products & Services',
        'industries' => 'Industries Section',
        'awards' => 'Awards Section',
        'customers' => 'Customers Section'
    ],
    'about' => [
        'banner' => 'Page Banner',
        'company_profile' => 'Company Profile',
        'mission_vision' => 'Mission & Vision',
        'quality_policy' => 'Quality Policy',
        'environmental_policy' => 'Environmental Policy',
        'history' => 'Company History',
        'cta' => 'Call to Action'
    ],
    'contact' => [
        'banner' => 'Page Banner',
        'contact_info' => 'Contact Information',
        'form' => 'Contact Form',
        'map' => 'Map Section',
        'cta' => 'Call to Action'
    ],
    'sustainability' => [
        'banner' => 'Page Banner',
        'content' => 'Page Content',
        'images' => 'Page Images'
    ],
    'careers' => [
        'banner' => 'Page Banner',
        'content' => 'Page Content',
        'positions' => 'Open Positions',
        'benefits' => 'Benefits Section'
    ],
    'overviewprocess' => [
        'banner' => 'Page Banner',
        'intro' => 'Introduction',
        'steps' => 'Process Steps',
        'conclusion' => 'Conclusion'
    ],
    'manufacturingprocess' => [
        'banner' => 'Page Banner',
        'intro' => 'Introduction',
        'processes' => 'Manufacturing Processes',
        'equipment' => 'Equipment & Facilities',
        'quality' => 'Quality Control'
    ],
    'shop' => [
        'banner' => 'Page Banner',
        'categories' => 'Product Categories',
        'featured' => 'Featured Products',
        'cta' => 'Call to Action'
    ]
];

$content_types = [
    'text' => 'Plain Text',
    'html' => 'HTML Content',
    'image' => 'Image'
];

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Pages | James Polymers Admin</title>
    
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
    
    <!-- Summernote WYSIWYG Editor -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
    
    <style>
        .admin-content {
            transition: margin-left 0.3s ease;
        }
        
        @media (min-width: 1024px) {
            .admin-content {
                margin-left: 16rem; /* w-64 = 16rem */
            }
        }
        
        .section-card {
            transition: all 0.3s ease;
            border-left: 4px solid #0066cc;
        }
        
        .section-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        /* Override Summernote styles to match admin theme */
        .note-editor {
            border-radius: 0.5rem !important;
        }
        .note-toolbar {
            background-color: #f8f9fa !important;
            border-radius: 0.5rem 0.5rem 0 0 !important;
        }
        .note-statusbar {
            border-radius: 0 0 0.5rem 0.5rem !important;
        }
        .content-preview img {
            max-width: 100%;
            height: auto;
            border-radius: 0.375rem;
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
                <h1 class="text-2xl font-bold text-gray-800">Manage Website Pages</h1>
                <p class="text-gray-600">Edit content for various pages of the website</p>
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
        
        <!-- Page Selection & Content Management -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Left Sidebar: Page Selection -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-4 mb-4">
                    <h3 class="text-lg font-semibold mb-4 text-gray-800 border-b pb-2">Select Page</h3>
                    <ul class="space-y-2">
                        <?php foreach($available_pages as $key => $label): ?>
                        <li>
                            <a href="?page=<?php echo $key; ?>" 
                               class="flex items-center px-3 py-2 rounded-lg <?php echo ($page_name == $key) ? 'bg-primary text-white' : 'hover:bg-gray-100 text-gray-700'; ?>">
                                <i class="fas fa-file-alt mr-3"></i>
                                <span><?php echo $label; ?></span>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow-md p-4">
                    <h3 class="text-lg font-semibold mb-4 text-gray-800 border-b pb-2">Quick Actions</h3>
                    <div class="space-y-2">
                        <button onclick="openAddContentModal()" class="w-full flex items-center px-4 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg transition">
                            <i class="fas fa-plus-circle mr-2"></i>
                            <span>Add New Content</span>
                        </button>
                        
                        <a href="../<?php echo $page_name; ?>.php" target="_blank" class="w-full flex items-center px-4 py-2 bg-green-50 hover:bg-green-100 text-green-700 rounded-lg transition">
                            <i class="fas fa-external-link-alt mr-2"></i>
                            <span>View Page</span>
                        </a>
                        
                        <button onclick="clearPageCache()" class="w-full flex items-center px-4 py-2 bg-yellow-50 hover:bg-yellow-100 text-yellow-700 rounded-lg transition">
                            <i class="fas fa-sync-alt mr-2"></i>
                            <span>Clear Cache</span>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Right Side: Content Management -->
            <div class="lg:col-span-3">
                <?php if ($action === 'edit' && $edit_content): ?>
                <!-- Edit Content Form -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-bold text-gray-800">
                            Edit Content: <?php echo htmlspecialchars($edit_content['content_key']); ?>
                        </h2>
                        <a href="?page=<?php echo $edit_content['page_name']; ?>" class="text-gray-500 hover:text-gray-700">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                    
                    <form action="" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="update_content">
                        <input type="hidden" name="content_id" value="<?php echo $edit_content['id']; ?>">
                        <input type="hidden" name="content_type" value="<?php echo $edit_content['content_type']; ?>">
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Page</label>
                                <input type="text" value="<?php echo $available_pages[$edit_content['page_name']]; ?>" class="w-full bg-gray-100 border border-gray-300 rounded-lg px-3 py-2" readonly>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Section</label>
                                <input type="text" value="<?php echo htmlspecialchars($edit_content['section_name']); ?>" class="w-full bg-gray-100 border border-gray-300 rounded-lg px-3 py-2" readonly>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Content Key</label>
                                <input type="text" value="<?php echo htmlspecialchars($edit_content['content_key']); ?>" class="w-full bg-gray-100 border border-gray-300 rounded-lg px-3 py-2" readonly>
                            </div>
                        </div>
                        
                        <?php if ($edit_content['content_type'] === 'text'): ?>
                        <div class="mb-6">
                            <label for="content_value" class="block text-sm font-medium text-gray-700 mb-1">Content</label>
                            <textarea id="content_value" name="content_value" rows="4" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"><?php echo htmlspecialchars($edit_content['content_value']); ?></textarea>
                        </div>
                        
                        <?php elseif ($edit_content['content_type'] === 'html'): ?>
                        <div class="mb-6">
                            <label for="content_value" class="block text-sm font-medium text-gray-700 mb-1">HTML Content</label>
                            <textarea id="summernote" name="content_value" class="summernote"><?php echo htmlspecialchars($edit_content['content_value']); ?></textarea>
                        </div>
                        
                        <?php elseif ($edit_content['content_type'] === 'image'): ?>
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Current Image</label>
                            <?php if (!empty($edit_content['content_value'])): ?>
                            <img src="../<?php echo htmlspecialchars($edit_content['content_value']); ?>" alt="Current Image" class="h-48 object-cover rounded-lg mb-2">
                            <input type="hidden" name="content_value" value="<?php echo htmlspecialchars($edit_content['content_value']); ?>">
                            <?php else: ?>
                            <p class="text-gray-500">No image currently set</p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-6">
                            <label for="image" class="block text-sm font-medium text-gray-700 mb-1">Upload New Image</label>
                            <input type="file" id="image" name="image" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                            <p class="text-xs text-gray-500 mt-1">Upload a new image to replace the current one. Leave empty to keep the current image.</p>
                        </div>
                        <?php endif; ?>
                        
                        <div class="flex items-center justify-end">
                            <a href="?page=<?php echo $edit_content['page_name']; ?>" class="bg-gray-300 hover:bg-gray-400 text-gray-800 py-2 px-4 rounded-lg mr-4 transition-all">
                                Cancel
                            </a>
                            <button type="submit" class="bg-primary hover:bg-secondary text-white py-2 px-6 rounded-lg transition-all">
                                Update Content
                            </button>
                        </div>
                    </form>
                </div>
                
                <?php else: ?>
                <!-- Page Content Overview -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-bold text-gray-800">
                            Content for: <?php echo $available_pages[$page_name]; ?>
                        </h2>
                        <button onclick="openAddContentModal()" class="bg-primary hover:bg-secondary text-white py-2 px-4 rounded-lg transition flex items-center">
                            <i class="fas fa-plus mr-2"></i> Add Content
                        </button>
                    </div>
                    
                    <?php if (empty($contents)): ?>
                    <div class="text-center py-12">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-50 rounded-full mb-4">
                            <i class="fas fa-file-alt text-blue-500 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-800 mb-2">No Content Found</h3>
                        <p class="text-gray-600 mb-4">This page doesn't have any content blocks defined yet.</p>
                        <button onclick="openAddContentModal()" class="inline-flex items-center text-primary hover:text-secondary">
                            <i class="fas fa-plus mr-1"></i> Add your first content block
                        </button>
                    </div>
                    <?php else: ?>
                    
                    <!-- Section Accordion -->
                    <div class="space-y-4">
                        <?php foreach ($contents as $section_name => $section_contents): ?>
                        <div class="section-card bg-white border rounded-lg overflow-hidden">
                            <div class="bg-gray-50 px-6 py-4 cursor-pointer flex items-center justify-between" onclick="toggleSection('<?php echo $section_name; ?>')">
                                <h3 class="font-semibold text-gray-800"><?php echo htmlspecialchars($section_name); ?></h3>
                                <i class="fas fa-chevron-down text-gray-600 section-toggle" id="toggle-<?php echo $section_name; ?>"></i>
                            </div>
                            
                            <div class="p-6 border-t section-content" id="section-<?php echo $section_name; ?>">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full">
                                        <thead>
                                            <tr class="bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                                <th class="px-4 py-3">Content Key</th>
                                                <th class="px-4 py-3">Type</th>
                                                <th class="px-4 py-3">Preview</th>
                                                <th class="px-4 py-3">Last Updated</th>
                                                <th class="px-4 py-3">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200">
                                            <?php foreach ($section_contents as $content): ?>
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-4 py-3">
                                                    <span class="font-medium text-gray-900"><?php echo htmlspecialchars($content['content_key']); ?></span>
                                                </td>
                                                <td class="px-4 py-3">
                                                    <?php 
                                                    $type_badges = [
                                                        'text' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"><i class="fas fa-align-left mr-1"></i> Text</span>',
                                                        'html' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800"><i class="fas fa-code mr-1"></i> HTML</span>',
                                                        'image' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"><i class="fas fa-image mr-1"></i> Image</span>'
                                                    ];
                                                    echo $type_badges[$content['content_type']];
                                                    ?>
                                                </td>
                                                <td class="px-4 py-3">
                                                    <div class="content-preview max-w-xs truncate">
                                                        <?php if ($content['content_type'] === 'image' && !empty($content['content_value'])): ?>
                                                            <img src="../<?php echo htmlspecialchars($content['content_value']); ?>" alt="Preview" class="h-10 w-auto">
                                                        <?php else: ?>
                                                            <?php echo htmlspecialchars(substr($content['content_value'], 0, 100)) . (strlen($content['content_value']) > 100 ? '...' : ''); ?>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3 text-sm text-gray-500">
                                                    <?php echo date('M j, Y g:i A', strtotime($content['last_updated'])); ?>
                                                </td>
                                                <td class="px-4 py-3 text-right">
                                                    <div class="flex items-center space-x-3">
                                                        <a href="?action=edit&id=<?php echo $content['id']; ?>" class="text-blue-500 hover:text-blue-700">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <button onclick="confirmDelete(<?php echo $content['id']; ?>, '<?php echo addslashes($content['content_key']); ?>')" class="text-red-500 hover:text-red-700">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Page Information -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Page Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Page File</p>
                            <p class="font-medium"><?php echo $page_name; ?>.php</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Page Title</p>
                            <p class="font-medium"><?php echo $available_pages[$page_name]; ?></p>
                        </div>
                        <div class="md:col-span-2">
                            <p class="text-sm text-gray-500 mb-1">Available Sections</p>
                            <div class="flex flex-wrap gap-2 mt-1">
                                <?php foreach(($page_sections[$page_name] ?? []) as $section_key => $section_label): ?>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    <?php echo $section_label; ?>
                                </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Add Content Modal -->
    <div id="addContentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-2xl mx-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-900">Add New Content</h3>
                <button onclick="closeAddContentModal()" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="addContentForm" action="" method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add_content">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="page_name" class="block text-sm font-medium text-gray-700 mb-1">Page</label>
                        <select id="page_name" name="page_name" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" required>
                            <?php foreach($available_pages as $key => $label): ?>
                            <option value="<?php echo $key; ?>" <?php echo ($page_name === $key) ? 'selected' : ''; ?>><?php echo $label; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label for="section_name" class="block text-sm font-medium text-gray-700 mb-1">Section</label>
                        <select id="section_name" name="section_name" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" required>
                            <?php 
                            $current_sections = $page_sections[$page_name] ?? [];
                            foreach($current_sections as $key => $label): 
                            ?>
                            <option value="<?php echo $key; ?>"><?php echo $label; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="content_key" class="block text-sm font-medium text-gray-700 mb-1">Content Key</label>
                    <input type="text" id="content_key" name="content_key" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" required>
                    <p class="text-xs text-gray-500 mt-1">A unique identifier for this content (e.g. heading, subheading, main_image)</p>
                </div>
                
                <div class="mb-4">
                    <label for="content_type" class="block text-sm font-medium text-gray-700 mb-1">Content Type</label>
                    <select id="content_type" name="content_type" onchange="toggleContentTypeFields()" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" required>
                        <?php foreach($content_types as $key => $label): ?>
                        <option value="<?php echo $key; ?>"><?php echo $label; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div id="text_field" class="content-type-field mb-4">
                    <label for="content_value_text" class="block text-sm font-medium text-gray-700 mb-1">Text Content</label>
                    <textarea id="content_value_text" name="content_value" rows="4" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
                </div>
                
                <div id="html_field" class="content-type-field mb-4 hidden">
                    <label for="content_value_html" class="block text-sm font-medium text-gray-700 mb-1">HTML Content</label>
                    <textarea id="summernote_add" name="content_value_html" class="summernote"></textarea>
                </div>
                
                <div id="image_field" class="content-type-field mb-4 hidden">
                    <label for="image" class="block text-sm font-medium text-gray-700 mb-1">Image</label>
                    <input type="file" id="image" name="image" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                </div>
                
                <div class="flex items-center justify-end mt-6">
                    <button type="button" onclick="closeAddContentModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 py-2 px-4 rounded-lg mr-4 transition-all">
                        Cancel
                    </button>
                    <button type="submit" class="bg-primary hover:bg-secondary text-white py-2 px-6 rounded-lg transition-all">
                        Add Content
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md mx-4">
            <div class="flex items-center justify-center text-red-500 mb-4">
                <i class="fas fa-exclamation-circle text-5xl"></i>
            </div>
            <h3 class="text-lg font-bold text-center text-gray-900 mb-2">Confirm Deletion</h3>
            <p class="text-center text-gray-700 mb-6">Are you sure you want to delete <span id="deleteItemName" class="font-semibold"></span>? This action cannot be undone.</p>
            
            <form id="deleteForm" method="post" class="flex items-center justify-center">
                <input type="hidden" name="action" value="delete_content">
                <input type="hidden" id="deleteContentId" name="content_id" value="">
                
                <button type="button" onclick="closeDeleteModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 py-2 px-4 rounded-lg mr-4 transition-all">
                    Cancel
                </button>
                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white py-2 px-6 rounded-lg transition-all">
                    Delete
                </button>
            </form>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Initialize Summernote WYSIWYG editor
        $(document).ready(function() {
            $('.summernote').summernote({
                placeholder: 'Enter your content here...',
                tabsize: 2,
                height: 200,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture']],
                    ['view', ['fullscreen', 'help']]
                ]
            });

            // Update hidden field with summernote content on form submit
            $('#addContentForm').submit(function() {
                if ($('#content_type').val() === 'html') {
                    $('textarea[name="content_value"]').val($('#summernote_add').summernote('code'));
                }
            });

            // Handle page change in add content modal
            $('#page_name').on('change', function() {
                updateSectionOptions($(this).val());
            });

            // Initialize sections as expanded
            $('.section-content').show();
        });
        
        // Toggle content type fields based on selection
        function toggleContentTypeFields() {
            const selectedType = document.getElementById('content_type').value;
            
            // Hide all fields first
            document.querySelectorAll('.content-type-field').forEach(field => {
                field.classList.add('hidden');
            });
            
            // Show the selected field
            document.getElementById(selectedType + '_field').classList.remove('hidden');
            
            // Handle special case for HTML content
            if (selectedType === 'html') {
                $('#summernote_add').summernote('reset');
            }
        }
        
        // Toggle section expand/collapse
        function toggleSection(sectionName) {
            const content = document.getElementById('section-' + sectionName);
            const toggle = document.getElementById('toggle-' + sectionName);
            
            if (content.style.display === 'none') {
                content.style.display = 'block';
                toggle.classList.remove('transform', 'rotate-180');
            } else {
                content.style.display = 'none';
                toggle.classList.add('transform', 'rotate-180');
            }
        }
        
        // Add Content Modal
        function openAddContentModal() {
            document.getElementById('addContentModal').classList.remove('hidden');
            document.getElementById('addContentModal').classList.add('flex');
            
            // Reset form
            document.getElementById('addContentForm').reset();
            
            // Reset Summernote
            $('#summernote_add').summernote('reset');
            
            // Show the right content type field
            toggleContentTypeFields();
        }
        
        function closeAddContentModal() {
            document.getElementById('addContentModal').classList.add('hidden');
            document.getElementById('addContentModal').classList.remove('flex');
        }
        
        // Delete Confirmation Modal
        function confirmDelete(id, name) {
            document.getElementById('deleteContentId').value = id;
            document.getElementById('deleteItemName').textContent = name;
            document.getElementById('deleteModal').classList.remove('hidden');
            document.getElementById('deleteModal').classList.add('flex');
        }
        
        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
            document.getElementById('deleteModal').classList.remove('flex');
        }
        
        // Update section dropdown options when page changes
        function updateSectionOptions(pageName) {
            // Define sections by page
            const pageSections = <?php echo json_encode($page_sections); ?>;
            const sections = pageSections[pageName] || {};
            
            // Clear the dropdown
            const sectionSelect = document.getElementById('section_name');
            sectionSelect.innerHTML = '';
            
            // Add new options
            for (const [key, label] of Object.entries(sections)) {
                const option = document.createElement('option');
                option.value = key;
                option.textContent = label;
                sectionSelect.appendChild(option);
            }
        }
        
        // Clear page cache
        function clearPageCache() {
            // This is a placeholder - in a real implementation, you would call an endpoint
            // that clears the cache for the website
            alert('Page cache cleared successfully!');
        }
    </script>
</body>
</html>