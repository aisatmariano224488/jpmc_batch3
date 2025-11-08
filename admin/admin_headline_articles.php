<?php
// session_start();
// if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
//     header("Location: admin_login.php");
//     exit;
// }

// Database credentials
$servername = "localhost"; 
$username = "root"; 
$password = ""; 
$dbname = "jpmc";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize message variables
$success_message = '';
$error_message = '';

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $title = $_POST['title'];
                $description = $_POST['description'];
                $date = $_POST['date'];
                $is_active = isset($_POST['is_active']) ? 1 : 0;
                
                // Handle image upload
                $image_path = '';
                if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                    $upload_dir = '../assets/img/headlines/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                    $file_name = 'headline_' . time() . '.' . $file_extension;
                    $upload_path = $upload_dir . $file_name;
                    
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                        $image_path = 'assets/img/headlines/' . $file_name;
                    } else {
                        $_SESSION['error_message'] = "Error uploading image";
                        header("Location: " . $_SERVER['PHP_SELF']);
                        exit;
                    }
                }
                
                // If no new image uploaded, use the provided image path
                if (empty($image_path) && !empty($_POST['image_path'])) {
                    $image_path = $_POST['image_path'];
                }
                
                // If active, deactivate other headlines
                if ($is_active == 1) {
                    $conn->query("UPDATE headline_articles SET is_active = 0");
                }
                
                $sql = "INSERT INTO headline_articles (title, description, image_path, date, is_active) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssi", $title, $description, $image_path, $date, $is_active);
                
                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "Headline article added successfully";
                } else {
                    $_SESSION['error_message'] = "Error adding headline article";
                }
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
                break;
                
            case 'edit':
                $id = $_POST['id'];
                $title = $_POST['title'];
                $description = $_POST['description'];
                $date = $_POST['date'];
                $is_active = isset($_POST['is_active']) ? 1 : 0;
                
                // Handle image upload
                $image_path = $_POST['current_image_path'];
                if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                    $upload_dir = '../assets/img/headlines/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                    $file_name = 'headline_' . time() . '.' . $file_extension;
                    $upload_path = $upload_dir . $file_name;
                    
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                        $image_path = 'assets/img/headlines/' . $file_name;
                    } else {
                        $_SESSION['error_message'] = "Error uploading image";
                        header("Location: " . $_SERVER['PHP_SELF']);
                        exit;
                    }
                }
                
                // If active, deactivate other headlines
                if ($is_active == 1) {
                    $conn->query("UPDATE headline_articles SET is_active = 0 WHERE id != " . intval($id));
                }
                
                $sql = "UPDATE headline_articles SET title = ?, description = ?, image_path = ?, date = ?, is_active = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssii", $title, $description, $image_path, $date, $is_active, $id);
                
                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "Headline article updated successfully";
                } else {
                    $_SESSION['error_message'] = "Error updating headline article";
                }
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
                break;
                
            case 'delete':
                $id = $_POST['id'];
                
                // Get image path before deletion
                $sql = "SELECT image_path FROM headline_articles WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result && $result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $image_path = $row['image_path'];
                    
                    // Delete image file if it exists and is in headlines directory
                    if (!empty($image_path) && strpos($image_path, 'assets/img/headlines/') === 0) {
                        $full_path = '../' . $image_path;
                        if (file_exists($full_path)) {
                            unlink($full_path);
                        }
                    }
                }
                
                // Delete from database
                $sql = "DELETE FROM headline_articles WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $id);
                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "Headline article deleted successfully";
                } else {
                    $_SESSION['error_message'] = "Error deleting headline article";
                }
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
                break;
        }
    }
}

// Get messages from session and clear them
$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
unset($_SESSION['success_message'], $_SESSION['error_message']);

// Fetch existing headline articles
$sql = "SELECT * FROM headline_articles ORDER BY date DESC, created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Headline Articles - JPMC Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .preview-image {
            max-width: 200px;
            max-height: 150px;
            object-fit: cover;
            border-radius: 8px;
        }
        .headline-card {
            transition: transform 0.2s;
        }
        .headline-card:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'includes/adminsidebar.php'; ?>
            
            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Manage Headline Articles</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#headlineModal" onclick="resetForm()">
                        <i class="fas fa-plus"></i> Add New Headline
                    </button>
                </div>

                <!-- Messages -->
                <?php if ($success_message): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $success_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($error_message): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $error_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Headline Articles Grid -->
                <div class="row">
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card headline-card h-100">
                                    <div class="position-relative">
                                        <?php if (!empty($row['image_path'])): ?>
                                            <img src="../<?php echo htmlspecialchars($row['image_path']); ?>" 
                                                 alt="<?php echo htmlspecialchars($row['title']); ?>" 
                                                 class="card-img-top" style="height: 200px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center" style="height: 200px;">
                                                <i class="fas fa-image text-white fa-3x"></i>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($row['is_active']): ?>
                                            <span class="position-absolute top-0 start-0 badge bg-success m-2">Active</span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="card-body d-flex flex-column">
                                        <h5 class="card-title"><?php echo htmlspecialchars($row['title']); ?></h5>
                                        <p class="card-text text-muted small">
                                            <?php echo substr(htmlspecialchars($row['description']), 0, 100) . '...'; ?>
                                        </p>
                                        <p class="card-text">
                                            <small class="text-muted">
                                                <i class="fas fa-calendar"></i> 
                                                <?php echo date('F j, Y', strtotime($row['date'])); ?>
                                            </small>
                                        </p>
                                        
                                        <div class="mt-auto">
                                            <button class="btn btn-sm btn-outline-primary me-2" 
                                                    onclick="editHeadline(<?php echo htmlspecialchars(json_encode($row)); ?>)">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" 
                                                    onclick="deleteHeadline(<?php echo $row['id']; ?>)">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="text-center py-5">
                                <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
                                <h4 class="text-muted">No headline articles found</h4>
                                <p class="text-muted">Click "Add New Headline" to create your first headline article.</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    <div class="modal fade" id="headlineModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add New Headline Article</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="headlineForm" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="id" id="edit_id">
                        <input type="hidden" name="current_image_path" id="current_image_path">
                        
                        <div class="mb-3">
                            <label for="title" class="form-label">Title *</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description *</label>
                            <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="date" class="form-label">Date *</label>
                            <input type="date" class="form-control" id="date" name="date" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="image" class="form-label">Image</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <div class="form-text">Leave empty to keep current image (when editing)</div>
                        </div>
                        
                        <div class="mb-3" id="currentImageDiv" style="display: none;">
                            <label class="form-label">Current Image</label>
                            <div>
                                <img id="currentImage" class="preview-image" src="" alt="Current image">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                                <label class="form-check-label" for="is_active">
                                    Set as active headline (only one can be active at a time)
                                </label>
                            </div>
                        </div>
                        
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Headline</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this headline article? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="delete_id">
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function resetForm() {
            document.getElementById('headlineForm').reset();
            document.getElementById('modalTitle').textContent = 'Add New Headline Article';
            document.getElementById('edit_id').value = '';
            document.getElementById('current_image_path').value = '';
            document.getElementById('currentImageDiv').style.display = 'none';
            document.querySelector('input[name="action"]').value = 'add';
        }

        function editHeadline(headline) {
            document.getElementById('modalTitle').textContent = 'Edit Headline Article';
            document.getElementById('edit_id').value = headline.id;
            document.getElementById('title').value = headline.title;
            document.getElementById('description').value = headline.description;
            document.getElementById('date').value = headline.date;
            document.getElementById('is_active').checked = headline.is_active == 1;
            document.getElementById('current_image_path').value = headline.image_path;
            document.querySelector('input[name="action"]').value = 'edit';
            
            if (headline.image_path) {
                document.getElementById('currentImage').src = '../' + headline.image_path;
                document.getElementById('currentImageDiv').style.display = 'block';
            } else {
                document.getElementById('currentImageDiv').style.display = 'none';
            }
            
            new bootstrap.Modal(document.getElementById('headlineModal')).show();
        }

        function deleteHeadline(id) {
            document.getElementById('delete_id').value = id;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
    </script>
</body>
</html>
