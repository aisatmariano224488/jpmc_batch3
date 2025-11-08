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
$product_id = $product_name = $product_description = $product_category = $product_image = "";
$success_message = $error_message = "";
$action = isset($_GET['action']) ? $_GET['action'] : "";

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $product_name = trim($_POST['product_name']);
    $product_description = trim($_POST['product_description']);
    $product_category = trim($_POST['product_category']);
    
    // Validate input
    if (empty($product_name) || empty($product_description) || empty($product_category)) {
        $error_message = "All fields are required";
    } else {
        // Handle file upload
        $target_dir = "../uploads/products/";
        $product_image = "";
        
        if (isset($_FILES["product_image"]) && $_FILES["product_image"]["error"] == 0) {
            // Create directory if it doesn't exist
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $target_file = $target_dir . basename($_FILES["product_image"]["name"]);
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            
            // Check if image file is an actual image
            $check = getimagesize($_FILES["product_image"]["tmp_name"]);
            if($check === false) {
                $error_message = "File is not an image.";
            }
            // Check file size (limit to 5MB)
            elseif ($_FILES["product_image"]["size"] > 5000000) {
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
                
                if (move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file)) {
                    // Store the path relative to the root directory for front-end access
                    $product_image = "uploads/products/" . $new_filename;
                } else {
                    $error_message = "Sorry, there was an error uploading your file.";
                }
            }
        }
        
        if (empty($error_message)) {
            // Perform database operations based on action
            if (isset($_POST['action']) && $_POST['action'] == 'add') {
                // Add new product
                $stmt = $conn->prepare("INSERT INTO products (name, description, category, image_url, created_at) VALUES (?, ?, ?, ?, NOW())");
                $stmt->bind_param("ssss", $product_name, $product_description, $product_category, $product_image);
                
                if ($stmt->execute()) {
                    $success_message = "Product added successfully!";
                    $product_name = $product_description = $product_category = $product_image = "";
                } else {
                    $error_message = "Error: " . $stmt->error;
                }
                $stmt->close();
            } 
            else if (isset($_POST['action']) && $_POST['action'] == 'edit' && isset($_POST['product_id'])) {
                $product_id = $_POST['product_id'];
                
                // If no new image uploaded, keep existing image
                if (empty($product_image)) {
                    $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, category = ? WHERE id = ?");
                    $stmt->bind_param("sssi", $product_name, $product_description, $product_category, $product_id);
                } else {
                    $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, category = ?, image_url = ? WHERE id = ?");
                    $stmt->bind_param("ssssi", $product_name, $product_description, $product_category, $product_image, $product_id);
                }
                
                if ($stmt->execute()) {
                    $success_message = "Product updated successfully!";
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
    $product_id = $_GET['id'];
    
    // Get product image path before deleting
    $stmt = $conn->prepare("SELECT image_url FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $product_image = $row['image_url'];
    }
    $stmt->close();
    
    // Delete product
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    
    if ($stmt->execute()) {
        // Delete image file if exists
        if (!empty($product_image) && file_exists($product_image)) {
            unlink($product_image);
        }
        $success_message = "Product deleted successfully!";
    } else {
        $error_message = "Error deleting product: " . $stmt->error;
    }
    $stmt->close();
}

// Load product for editing
if ($action == "edit" && isset($_GET['id'])) {
    $product_id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $product_name = $row['name'];
        $product_description = $row['description'];
        $product_category = $row['category'];
        $product_image = $row['image_url'];
    } else {
        $error_message = "Product not found";
    }
    $stmt->close();
}

// Get all products
$products = array();
$sql = "SELECT * FROM products ORDER BY created_at DESC";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products | James Polymers Admin</title>
    
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
                <h1 class="text-2xl font-bold text-gray-800">Manage Products</h1>
                <p class="text-gray-600">Add, edit or delete products</p>
            </div>
            <div>
                <?php if ($action != "new" && $action != "edit"): ?>
                <a href="admin_products.php?action=new" class="bg-primary hover:bg-secondary text-white py-2 px-4 rounded-lg transition-all flex items-center">
                    <i class="fas fa-plus mr-2"></i> Add New Product
                </a>
                <?php else: ?>
                <a href="admin_products.php" class="bg-gray-600 hover:bg-gray-700 text-white py-2 px-4 rounded-lg transition-all flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Products
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
        <!-- Add/Edit Product Form -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">
                <?php echo $action == "new" ? "Add New Product" : "Edit Product"; ?>
            </h2>
            
            <form action="admin_products.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" value="<?php echo $action == 'new' ? 'add' : 'edit'; ?>">
                <?php if ($action == "edit"): ?>
                <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                <?php endif; ?>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="product_name" class="block text-sm font-medium text-gray-700 mb-1">Product Name</label>
                        <input type="text" id="product_name" name="product_name" value="<?php echo htmlspecialchars($product_name); ?>" required
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                    
                    <div>
                        <label for="product_category" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                        <select id="product_category" name="product_category" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary">
                            <option value="">Select Category</option>
                            <option value="thermoplastic" <?php echo $product_category == 'thermoplastic' ? 'selected' : ''; ?>>Thermoplastic Elastomers</option>
                            <option value="engineering" <?php echo $product_category == 'engineering' ? 'selected' : ''; ?>>Engineering Plastics</option>
                            <option value="custom" <?php echo $product_category == 'custom' ? 'selected' : ''; ?>>Custom Compounds</option>
                            <option value="appliance" <?php echo $product_category == 'appliance' ? 'selected' : ''; ?>>Appliance</option>
                            <option value="automotive" <?php echo $product_category == 'automotive' ? 'selected' : ''; ?>>Automotive</option>
                            <option value="industrial" <?php echo $product_category == 'industrial' ? 'selected' : ''; ?>>Industrial</option>
                        </select>
                    </div>
                </div>
                
                <div class="mb-6">
                    <label for="product_description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea id="product_description" name="product_description" rows="4" required
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary"><?php echo htmlspecialchars($product_description); ?></textarea>
                </div>
                
                <div class="mb-6">
                    <label for="product_image" class="block text-sm font-medium text-gray-700 mb-1">Product Image</label>
                    
                    <?php if (!empty($product_image) && $action == "edit"): ?>
                    <div class="mb-3 flex items-center">
                        <img src="<?php echo htmlspecialchars($product_image); ?>" alt="Current product image" class="h-20 w-auto border rounded-md">
                        <span class="ml-3 text-sm text-gray-600">Current Image</span>
                    </div>
                    <?php endif; ?>
                    
                    <input type="file" id="product_image" name="product_image" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary">
                    <p class="mt-1 text-sm text-gray-500">
                        <?php echo $action == "edit" ? "Upload a new image only if you want to change the current one." : "Please upload an image for the product."; ?>
                        Supported formats: JPG, PNG, GIF.
                    </p>
                </div>
                
                <div class="flex items-center justify-end">
                    <button type="button" onclick="window.location.href='admin_products.php'" class="bg-gray-300 hover:bg-gray-400 text-gray-800 py-2 px-4 rounded-lg mr-4 transition-all">
                        Cancel
                    </button>
                    <button type="submit" class="bg-primary hover:bg-secondary text-white py-2 px-6 rounded-lg transition-all">
                        <?php echo $action == "new" ? "Add Product" : "Update Product"; ?>
                    </button>
                </div>
            </form>
        </div>
        <?php else: ?>
        <!-- Products Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Image
                            </th>
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Name
                            </th>
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Category
                            </th>
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Date Added
                            </th>
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($products as $product): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 px-4 whitespace-nowrap">
                                <?php if (!empty($product['image_url']) && file_exists($product['image_url'])): ?>
                                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="h-12 w-auto object-cover">
                                <?php else: ?>
                                <div class="h-12 w-12 bg-gray-200 flex items-center justify-center rounded">
                                    <i class="fas fa-image text-gray-400"></i>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($product['name']); ?></div>
                                <div class="text-sm text-gray-500 truncate max-w-xs"><?php echo htmlspecialchars(substr($product['description'], 0, 100)) . '...'; ?></div>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs rounded-full <?php 
                                    switch($product['category']) {
                                        case 'thermoplastic': echo 'bg-blue-100 text-blue-800'; break;
                                        case 'engineering': echo 'bg-green-100 text-green-800'; break;
                                        case 'custom': echo 'bg-purple-100 text-purple-800'; break;
                                        case 'appliance': echo 'bg-yellow-100 text-yellow-800'; break;
                                        case 'automotive': echo 'bg-red-100 text-red-800'; break;
                                        case 'industrial': echo 'bg-indigo-100 text-indigo-800'; break;
                                        default: echo 'bg-gray-100 text-gray-800';
                                    }
                                ?>">
                                    <?php echo ucfirst($product['category']); ?>
                                </span>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo date('M d, Y', strtotime($product['created_at'])); ?>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap text-sm font-medium">
                                <a href="admin_products.php?action=edit&id=<?php echo $product['id']; ?>" class="text-primary hover:text-secondary mr-3">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="#" onclick="deleteProduct(<?php echo $product['id']; ?>, '<?php echo addslashes($product['name']); ?>')" class="text-red-600 hover:text-red-800">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="5" class="py-6 px-4 text-center text-gray-500">
                                <i class="fas fa-box-open text-gray-400 text-4xl mb-3"></i>
                                <p>No products found. Click "Add New Product" to create one.</p>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <script>
        function deleteProduct(id, name) {
            if (confirm('Are you sure you want to delete the product "' + name + '"?')) {
                window.location.href = 'admin_products.php?action=delete&id=' + id;
            }
        }
    </script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>