<?php
// session_start();
require_once '../includes/db_connection.php';

// Check if user is logged in
// if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
//     header('Location: admin_login.php');
//     exit();
// }

// Handle customer section management
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Debug information
    error_log("POST request received");
    error_log("POST data: " . print_r($_POST, true));
    error_log("FILES data: " . print_r($_FILES, true));

    if (isset($_POST['action'])) {
        error_log("Action detected: " . $_POST['action']);
        if ($_POST['action'] === 'add_customer') {
            // Get the next available ID
            $id_query = "SELECT COALESCE(MAX(id), 0) + 1 as next_id FROM home_sections";
            $id_result = $conn->query($id_query);
            if (!$id_result) {
                $_SESSION['error_message'] = "Error getting next ID: " . $conn->error;
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit();
            }
            $id_row = $id_result->fetch_assoc();
            $next_id = $id_row['next_id'];

            // Get the next customer number
            $query = "SELECT COALESCE(MAX(CAST(SUBSTRING(field_name, 9) AS UNSIGNED)), 0) as max_num FROM home_sections WHERE field_name LIKE 'customer%_logo'";
            $result = $conn->query($query);
            if (!$result) {
                $_SESSION['error_message'] = "Error getting next customer number: " . $conn->error;
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit();
            }
            $row = $result->fetch_assoc();
            $next_num = $row['max_num'] + 1;
            
            // Get the highest display order for customers section
            $order_query = "SELECT COALESCE(MAX(display_order), 0) as max_order FROM home_sections WHERE section_name = 'customers' AND field_name LIKE 'customer%_logo'";
            $order_result = $conn->query($order_query);
            if (!$order_result) {
                $_SESSION['error_message'] = "Error getting max display order: " . $conn->error;
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit();
            }
            $order_row = $order_result->fetch_assoc();
            $max_order = $order_row['max_order'];
            
            // Insert new customer section with explicit ID
            $stmt = $conn->prepare("INSERT INTO home_sections (id, section_name, field_name, field_type, label, value, display_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
            if (!$stmt) {
                $_SESSION['error_message'] = "Error preparing statement: " . $conn->error;
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit();
            }
            
            $section_name = 'customers';
            $field_name = "customer{$next_num}_logo";
            $field_type = 'image';
            $label = "Customer {$next_num}";
            $value = '';
            $display_order = $max_order + 1; // Set to max_order + 1 to place at bottom
            
            $stmt->bind_param("isssssi", $next_id, $section_name, $field_name, $field_type, $label, $value, $display_order);
            
            if ($stmt->execute()) {
                // Also add the heading and subheading if they don't exist
                $check_heading = $conn->prepare("SELECT id FROM home_sections WHERE section_name = 'customers' AND field_name = 'heading'");
                $check_heading->execute();
                if ($check_heading->get_result()->num_rows === 0) {
                    // Get next ID for heading
                    $id_result = $conn->query($id_query);
                    $id_row = $id_result->fetch_assoc();
                    $heading_id = $id_row['next_id'];

                    $stmt = $conn->prepare("INSERT INTO home_sections (id, section_name, field_name, field_type, label, value, display_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $field_name = 'heading';
                    $field_type = 'text';
                    $label = 'Customers Section Heading';
                    $value = 'Our Valued Customers';
                    $display_order = 0;
                    $stmt->bind_param("isssssi", $heading_id, $section_name, $field_name, $field_type, $label, $value, $display_order);
                    $stmt->execute();
                }

                $check_subheading = $conn->prepare("SELECT id FROM home_sections WHERE section_name = 'customers' AND field_name = 'subheading'");
                $check_subheading->execute();
                if ($check_subheading->get_result()->num_rows === 0) {
                    $stmt = $conn->prepare("INSERT INTO home_sections (section_name, field_name, field_type, label, value, display_order) VALUES (?, ?, ?, ?, ?, ?)");
                    $field_name = 'subheading';
                    $field_type = 'text';
                    $label = 'Customers Section Subheading';
                    $value = "We're proud to partner with industry leaders across various sectors, providing them with high-performance polymer solutions.";
                    $display_order = 1;
                    $stmt->bind_param("sssssi", $section_name, $field_name, $field_type, $label, $value, $display_order);
                    $stmt->execute();
                }

                $_SESSION['success_message'] = "New customer added successfully to the carousel";
            } else {
                $_SESSION['error_message'] = "Error adding customer: " . $conn->error;
            }
        } elseif ($_POST['action'] === 'delete_customer' && isset($_POST['customer_id'])) {
            $customer_id = $_POST['customer_id'];   
            
            // Get the current image before deleting
            $stmt = $conn->prepare("SELECT value, field_name FROM home_sections WHERE id = ?");
            $stmt->bind_param("i", $customer_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            // Delete the customer section
            $stmt = $conn->prepare("DELETE FROM home_sections WHERE id = ?");
            $stmt->bind_param("i", $customer_id);
            
            if ($stmt->execute()) {
                // Delete the associated image file if it exists
                if ($row && $row['value'] && !filter_var($row['value'], FILTER_VALIDATE_URL)) {
                    // Check both possible locations
                    $image_paths = [
                        '../assets/home/' . $row['value'],
                        '../assets/img/' . $row['value']
                    ];
                    
                    foreach ($image_paths as $image_path) {
                        if (file_exists($image_path)) {
                            unlink($image_path);
                            break;
                        }
                    }
                }

                // Reorder remaining customer sections
                $stmt = $conn->prepare("SELECT id, field_name FROM home_sections WHERE section_name = 'customers' AND field_name LIKE 'customer%_logo' ORDER BY display_order");
                $stmt->execute();
                $result = $stmt->get_result();
                $order = 1;
                while ($row = $result->fetch_assoc()) {
                    $update = $conn->prepare("UPDATE home_sections SET display_order = ? WHERE id = ?");
                    $update->bind_param("ii", $order, $row['id']);
                    $update->execute();
                    $order++;
                }

                $_SESSION['success_message'] = "Customer removed from carousel successfully";
            } else {
                $_SESSION['error_message'] = "Error deleting customer: " . $conn->error;
            }
        } elseif ($_POST['action'] === 'reorder_customers' && isset($_POST['order'])) {
            // Handle reordering of customers
            $order_data = json_decode($_POST['order'], true);
            if (is_array($order_data)) {
                foreach ($order_data as $index => $customer_id) {
                    $stmt = $conn->prepare("UPDATE home_sections SET display_order = ? WHERE id = ?");
                    $display_order = $index + 1;
                    $stmt->bind_param("ii", $display_order, $customer_id);
                    $stmt->execute();
                }
                $_SESSION['success_message'] = "Customer order updated successfully";
            }
        }
        
        // Redirect to prevent form resubmission
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    } elseif (isset($_POST['section_name'])) {
        error_log("Section update detected for section: " . $_POST['section_name']);
        $section_name = $_POST['section_name'];
        $success = false;
        $message = '';
        
        // Process text fields
        if (isset($_POST['content_fields'])) {
            error_log("Content fields found: " . print_r($_POST['content_fields'], true));
            foreach ($_POST['content_fields'] as $field => $data) {
                $field_id = $data['id'];
                $value = $data['value'];
                
                error_log("Updating field ID: " . $field_id . " with value: " . $value);
                
                $stmt = $conn->prepare("UPDATE home_sections SET value = ? WHERE id = ?");
                $stmt->bind_param("si", $value, $field_id);
                if ($stmt->execute()) {
                    $success = true;
                    error_log("Field update successful");
                } else {
                    $success = false;
                    $message = "Error updating {$field}: " . $conn->error;
                    error_log("Field update failed: " . $conn->error);
                    break;
                }
            }
            
            if ($success) {
                $message = "Section updated successfully";
                error_log("All fields updated successfully");
            }
        } else {
            error_log("No content fields found in POST data");
        }
        
        // Process image uploads - support multiple images
        if ($success && isset($_FILES) && !empty($_FILES)) {
            error_log("Processing image uploads");
            foreach($_FILES as $field_name => $file_info) {
                error_log("Processing file: " . $field_name);
                // Skip any files that weren't uploaded
                if ($file_info['error'] !== 0 || empty($file_info['tmp_name'])) {
                    error_log("Skipping file due to error or empty tmp_name");
                    continue;
                }
                
                // Get the associated image field name from the post data
                $image_field = isset($_POST['image_fields'][$field_name]) ? $_POST['image_fields'][$field_name] : '';
                if (empty($image_field)) {
                    error_log("No image field found for: " . $field_name);
                    continue;
                }
                
                // Use assets/home/ for customer logos, assets/img/ for others
                $upload_dir = ($section_name === 'customers') ? '../assets/home/' : '../assets/img/';
                
                // Create directory if it doesn't exist
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                    error_log("Created upload directory: " . $upload_dir);
                }
                
                $file_extension = strtolower(pathinfo($file_info['name'], PATHINFO_EXTENSION));
                $new_filename = uniqid() . '.' . $file_extension;
                $upload_path = $upload_dir . $new_filename;
                
                error_log("Attempting to move uploaded file to: " . $upload_path);
                
                if (move_uploaded_file($file_info['tmp_name'], $upload_path)) {
                    error_log("File moved successfully");
                    // Get current image to delete and field ID
                    $field_id = $_POST['image_fields_id'][$field_name];
                    
                    $stmt = $conn->prepare("SELECT value FROM home_sections WHERE id = ?");
                    $stmt->bind_param("i", $field_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($row = $result->fetch_assoc()) {
                        $old_image = $row['value'];
                        // Delete old image if it exists and isn't a URL
                        if ($old_image && !filter_var($old_image, FILTER_VALIDATE_URL)) {
                            $old_image_path = $upload_dir . $old_image;
                            if (file_exists($old_image_path)) {
                                unlink($old_image_path);
                                error_log("Deleted old image: " . $old_image);
                            }
                        }
                    }
                    
                    // Update image in database
                    $stmt = $conn->prepare("UPDATE home_sections SET value = ? WHERE id = ?");
                    $stmt->bind_param("si", $new_filename, $field_id);
                    if ($stmt->execute()) {
                        $success = true;
                        $message = "Customer logo updated successfully in carousel";
                        error_log("Image updated in database successfully");
                    } else {
                        $success = false;
                        $message = "Error updating image: " . $conn->error;
                        error_log("Failed to update image in database: " . $conn->error);
                    }
                } else {
                    $success = false;
                    $message = "Error uploading image: " . error_get_last()['message'];
                    error_log("Failed to move uploaded file: " . error_get_last()['message']);
                }
            }
        } else {
            error_log("No files to process or previous step failed");
        }
        
        // Set success message in session
        if ($success) {
            $_SESSION['success_message'] = $message;
            error_log("Setting success message: " . $message);
        } else {
            $_SESSION['error_message'] = $message ?: 'Operation failed. Please try again.';
            error_log("Setting error message: " . $message);
        }
        
        // Redirect to prevent form resubmission
        error_log("Redirecting to: " . $_SERVER['PHP_SELF']);
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    } else {
        error_log("No action or section_name found in POST data");
    }
}

// Fetch ONLY customers section
$query = "SELECT id, section_name, field_name, field_type, label, value FROM home_sections WHERE section_name = 'customers' ORDER BY display_order";
$result = $conn->query($query);

$sections = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $section_name = $row['section_name'];
        if (!isset($sections[$section_name])) {
            $sections[$section_name] = [
                'fields' => []
            ];
        }
        $sections[$section_name]['fields'][$row['field_name']] = [
            'id' => $row['id'],
            'type' => $row['field_type'],
            'label' => $row['label'],
            'value' => $row['value']
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Customers | Admin Panel</title>
    <link rel="icon" type="image/png" href="/assets/img/tab_icon.png">
    
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
    
    <!-- SortableJS for drag-and-drop -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    
    <style>
        textarea.content-editor {
            min-height: 200px;
            font-family: -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif;
            font-size: 14px;
            line-height: 1.5;
            padding: 10px;
        }
        
        .sortable-ghost {
            opacity: 0.4;
            background: #f0f0f0;
        }
        
        .sortable-drag {
            opacity: 1;
        }
        
        .customer-item {
            cursor: move;
            transition: all 0.3s ease;
        }
        
        .customer-item:hover {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body class="bg-gray-50">
    <?php include 'includes/adminsidebar.php'; ?>
    
    <div class="lg:ml-64 p-8">
        <div class="max-w-7xl mx-auto">
            <h1 class="text-3xl font-bold text-gray-800 mb-8">Manage Customers Content</h1>
            
            <?php if (isset($_SESSION['success_message'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $_SESSION['success_message']; ?></span>
                <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <?php unset($_SESSION['success_message']); endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $_SESSION['error_message']; ?></span>
                <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <?php unset($_SESSION['error_message']); endif; ?>
            
            <!-- Customers Section Only -->
            <div class="space-y-8">
                <?php 
                foreach ($sections as $section_name => $section): 
                    $section_fields = $section['fields'];
                ?>
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Customer Carousel Management</h2>
                    <div class="mb-4 flex gap-2">
                        <form method="POST" class="inline">
                            <input type="hidden" name="action" value="add_customer">
                            <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600">
                                <i class="fas fa-plus mr-2"></i> Add New Customer to Carousel
                            </button>
                        </form>
                        <button type="button" id="saveOrderBtn" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 hidden">
                            <i class="fas fa-save mr-2"></i> Save Order
                        </button>
                    </div>
                    <p class="text-sm text-gray-600 mb-4">
                        <i class="fas fa-info-circle mr-1"></i> Drag and drop to reorder customers in the carousel
                    </p>
                    <form method="POST" enctype="multipart/form-data" class="space-y-4 section-update-form">
                        <input type="hidden" name="section_name" value="<?php echo $section_name; ?>">
                        
                        <?php 
                        // Separate image fields from text fields
                        $image_fields = [];
                        foreach ($section_fields as $field_name => $field) {
                            if ($field['type'] === 'image') {
                                $image_fields[$field_name] = $field;
                            } else {
                                // Text fields (heading and subheading)
                        ?>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo $field['label']; ?></label>
                            <?php if ($field['type'] === 'textarea'): ?>
                            <textarea name="content_fields[<?php echo $field_name; ?>][value]" class="content-editor mt-1 block w-full rounded-md border-gray-300 shadow-sm px-4 py-2 focus:ring-primary focus:border-primary" rows="10"><?php echo htmlspecialchars($field['value']); ?></textarea>
                            <input type="hidden" name="content_fields[<?php echo $field_name; ?>][id]" value="<?php echo $field['id']; ?>">
                            <p class="text-sm text-gray-500 mt-1">You can use HTML tags for formatting (e.g., &lt;p&gt;, &lt;strong&gt;, &lt;em&gt;, &lt;br&gt;)</p>
                            <?php else: ?>
                            <input type="text" name="content_fields[<?php echo $field_name; ?>][value]" value="<?php echo htmlspecialchars($field['value']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm px-4 py-2 focus:ring-primary focus:border-primary">
                            <input type="hidden" name="content_fields[<?php echo $field_name; ?>][id]" value="<?php echo $field['id']; ?>">
                            <?php endif; ?>
                        </div>
                        <?php 
                                }
                            }
                            
                            // Now process image fields (customer logos)
                            if (!empty($image_fields)):
                        ?>
                        <div id="customersList" class="space-y-4">
                            <?php
                            $image_counter = 0;
                            foreach ($image_fields as $field_name => $field):
                                $image_counter++;
                                // Extract just the number from the label (remove " Logo" suffix)
                                $label_text = preg_replace('/\s+logo$/i', '', $field['label']);
                            ?>
                            <div class="customer-item border rounded-lg p-4" data-customer-id="<?php echo $field['id']; ?>">
                                <div class="flex items-center gap-3">
                                    <div class="drag-handle cursor-move text-gray-400 hover:text-gray-600">
                                        <i class="fas fa-grip-vertical"></i>
                                    </div>
                                    <div class="flex-1">
                                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo $label_text; ?></label>
                                        
                                        <!-- Hidden fields for form submission -->
                                        <input type="hidden" name="image_fields[image_<?php echo $image_counter; ?>]" value="<?php echo $field_name; ?>">
                                        <input type="hidden" name="image_fields_id[image_<?php echo $image_counter; ?>]" value="<?php echo $field['id']; ?>">
                                        
                                        <!-- File upload input -->
                                        <input type="file" name="image_<?php echo $image_counter; ?>" accept="image/*" class="mt-1 block w-full">
                                        <p class="text-sm text-gray-500 mt-1">Recommended size: 400x300 pixels</p>
                                    </div>
                                    <div>
                                        <button type="button" onclick="deleteCustomer(<?php echo $field['id']; ?>)" class="text-red-500 hover:text-red-700 px-3 py-2">
                                            <i class="fas fa-trash mr-1"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        
                        <div>
                            <button type="submit" class="bg-primary text-white px-4 py-2 rounded-md hover:bg-secondary">Update Section</button>
                        </div>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- Delete Customer Form (Hidden) -->
    <form id="deleteCustomerForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete_customer">
        <input type="hidden" name="customer_id" id="deleteCustomerId">
    </form>
    
    <!-- Reorder Form (Hidden) -->
    <form id="reorderForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="reorder_customers">
        <input type="hidden" name="order" id="customerOrder">
    </form>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('[role="alert"]');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.remove();
                }, 5000);
            });
            
            // Initialize Sortable for customers list
            const customersList = document.getElementById('customersList');
            if (customersList) {
                const sortable = Sortable.create(customersList, {
                    handle: '.drag-handle',
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    dragClass: 'sortable-drag',
                    onEnd: function(evt) {
                        // Show save button when order changes
                        document.getElementById('saveOrderBtn').classList.remove('hidden');
                    }
                });
                
                // Handle save order button
                document.getElementById('saveOrderBtn').addEventListener('click', function() {
                    const items = customersList.querySelectorAll('.customer-item');
                    const order = Array.from(items).map(item => item.dataset.customerId);
                    
                    document.getElementById('customerOrder').value = JSON.stringify(order);
                    document.getElementById('reorderForm').submit();
                });
            }
        });

        // Function to handle customer deletion
        function deleteCustomer(customerId) {
            if (confirm('Are you sure you want to delete this customer from the carousel?')) {
                document.getElementById('deleteCustomerId').value = customerId;
                document.getElementById('deleteCustomerForm').submit();
            }
        }
    </script>
</body>
</html>