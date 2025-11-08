<?php
// session_start();
require_once '../includes/db_connection.php';

// Check if user is logged in
// if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
//     header('Location: admin_login.php');
//     exit();
// }

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $success = false;
        $message = '';
        
        switch ($_POST['action']) {
            case 'update_section':
                $id = $_POST['id'];
                $title = $_POST['title'];
                $subtitle = $_POST['subtitle'];
                $content = $_POST['content'];
                
                $image = $_POST['current_image'];
                if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                    $upload_dir = '../assets/img/about/';
                    $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                    $new_filename = uniqid() . '.' . $file_extension;
                    
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $new_filename)) {
                        $image = $new_filename;
                    }
                }
                
                $stmt = $conn->prepare("UPDATE about_sections SET title = ?, subtitle = ?, content = ?, image = ? WHERE id = ?");
                $stmt->bind_param("ssssi", $title, $subtitle, $content, $image, $id);
                $success = $stmt->execute();
                $message = 'Section updated successfully';
                break;
                
                if ($_POST['section_name'] === 'presidents_message') {
                    $audio = $_POST['current_audio'] ?? '';
                    $position = $_POST['position'] ?? '';
                    if (isset($_FILES['audio']) && $_FILES['audio']['error'] === 0) {
                        $upload_dir = '../assets/audio/';
                        // Create directory if it doesn't exist
                        if (!is_dir($upload_dir)) {
                            mkdir($upload_dir, 0755, true);
                        }
                        $file_extension = strtolower(pathinfo($_FILES['audio']['name'], PATHINFO_EXTENSION));
                        $new_filename = uniqid() . '.' . $file_extension;
                        if (move_uploaded_file($_FILES['audio']['tmp_name'], $upload_dir . $new_filename)) {
                            $audio = $new_filename;
                        }
                    }
                    // Update query for presidents message (with audio and position)
                    $stmt = $conn->prepare("UPDATE about_sections SET title = ?, subtitle = ?, content = ?, position = ?, audio = ? WHERE id = ?");
                    $stmt->bind_param("sssssi", $title, $subtitle, $content, $position, $audio, $id);
                    $success = $stmt->execute();
                    $message = 'Section updated successfully';
                    break;
                } else {
                    // Update query for other sections (without audio and position)
                    $stmt = $conn->prepare("UPDATE about_sections SET title = ?, subtitle = ?, content = ? WHERE id = ?");
                    $stmt->bind_param("sssi", $title, $subtitle, $content, $id);
                    $success = $stmt->execute();
                    $message = 'Section updated successfully';
                }
                break;
                
            case 'update_timeline':
                    $id = $_POST['id'];
                    $title = $_POST['title'];
                    $description = $_POST['description'];
                    $year = $_POST['year'];
                    $photo = $_POST['current_photo'] ?? ''; // Add this hidden input in your form
                
                    // Handle new image upload
                    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                        $upload_dir = '../assets/img/timeline/';
                        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                        $new_filename = uniqid() . '.' . $file_extension;
                
                        if (!is_dir($upload_dir)) {
                            mkdir($upload_dir, 0777, true);
                        }
                
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $new_filename)) {
                            // Delete old image
                            if ($photo && file_exists($upload_dir . $photo)) {
                                unlink($upload_dir . $photo);
                            }
                            $photo = $new_filename;
                        }
                    }
                
                    $stmt = $conn->prepare("UPDATE about_timeline SET title = ?, description = ?, year = ?, photo = ? WHERE id = ?");
                    $stmt->bind_param("ssssi", $title, $description, $year, $photo, $id);
                    $success = $stmt->execute();
                    $message = 'Timeline event updated successfully';
                    break;

            case 'delete_timeline':
                        $id = $_POST['id'];
                    
                        // Fetch current photo
                        $result = $conn->prepare("SELECT photo FROM about_timeline WHERE id = ?");
                        $result->bind_param("i", $id);
                        $result->execute();
                        $result->bind_result($photo);
                        $result->fetch();
                        $result->close();
                    
                        if ($photo && file_exists('../assets/img/timeline/' . $photo)) {
                            unlink('../assets/img/timeline/' . $photo);
                        }
                    
                        $stmt = $conn->prepare("DELETE FROM about_timeline WHERE id = ?");
                        $stmt->bind_param("i", $id);
                        $success = $stmt->execute();
                        $message = 'Timeline event deleted successfully';
                        break;

            case 'add_timeline':
                    $title = $_POST['title'];
                    $description = $_POST['description'];
                    $year = $_POST['year'];
                    $photo = '';
                
                    // Handle image upload
                    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                        $upload_dir = '../assets/img/timeline/';
                        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                        $new_filename = uniqid() . '.' . $file_extension;
                
                        if (!is_dir($upload_dir)) {
                            mkdir($upload_dir, 0777, true);
                        }
                
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $new_filename)) {
                            $photo = $new_filename;
                        }
                    }
                
                    // Get the highest display order
                    $result = $conn->query("SELECT MAX(display_order) as max_order FROM about_timeline");
                    $row = $result->fetch_assoc();
                    $display_order = ($row['max_order'] ?? 0) + 1;
                
                    $stmt = $conn->prepare("INSERT INTO about_timeline (title, description, photo, year, display_order) VALUES (?, ?, ?, ?, ?)");
                    $stmt->bind_param("ssssi", $title, $description, $photo, $year, $display_order);
                    $success = $stmt->execute();
                    $message = 'Timeline event added successfully';
                    break;
                
            case 'update_certification':
                $id = $_POST['id'];
                $title = $_POST['title'];
                
                // Handle image upload if new image is provided
                $image = $_POST['current_image'];
                if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                    $upload_dir = '../assets/img/certifications/';
                    $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                    $new_filename = uniqid() . '.' . $file_extension;
                    
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $new_filename)) {
                        $image = $new_filename;
                    }
                }
                
                $stmt = $conn->prepare("UPDATE about_certifications SET title = ?, image = ? WHERE id = ?");
                $stmt->bind_param("ssi", $title, $image, $id);
                $success = $stmt->execute();
                $message = 'Certification updated successfully';
                break;

            case 'delete_certification':
                $id = $_POST['id'];
                $image = $_POST['current_image'];
                
                // Delete the image file if it exists
                if ($image) {
                    $image_path = '../assets/img/certifications/' . $image;
                    if (file_exists($image_path)) {
                        unlink($image_path);
                    }
                }
                
                $stmt = $conn->prepare("DELETE FROM about_certifications WHERE id = ?");
                $stmt->bind_param("i", $id);
                $success = $stmt->execute();
                $message = 'Certification deleted successfully';
                break;

            case 'add_certification':
                $title = $_POST['title'];
                
                // Handle image upload
                $image = '';
                if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                    $upload_dir = '../assets/img/certifications/';
                    $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                    $new_filename = uniqid() . '.' . $file_extension;
                    
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $new_filename)) {
                        $image = $new_filename;
                    }
                }
                
                // Get the highest display order
                $result = $conn->query("SELECT MAX(display_order) as max_order FROM about_certifications");
                $row = $result->fetch_assoc();
                $display_order = ($row['max_order'] ?? 0) + 1;
                
                $stmt = $conn->prepare("INSERT INTO about_certifications (title, image, display_order) VALUES (?, ?, ?)");
                $stmt->bind_param("ssi", $title, $image, $display_order);
                $success = $stmt->execute();
                $message = 'Certification added successfully';
                break;

// ADD CSR
            case 'add_csr':
                $title = $_POST['title'];
                $subtitle = $_POST['subtitle'];
                $author= $_POST['author_credit'];
                $image = '';

                if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                    $upload_dir = '../assets/img/about/';
                    $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                    $new_filename = uniqid() . '.' . $file_extension;

                    if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $new_filename)) {
                        $image = $new_filename;
                    }
                }

                $result = $conn->query("SELECT MAX(display_order) as max_order FROM about_csr");
                $row = $result->fetch_assoc();
                $display_order = ($row['max_order'] ?? 0) + 1;

                $stmt = $conn->prepare("INSERT INTO about_csr (title, subtitle, author_credit, image, display_order) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssi", $title, $subtitle, $author, $image, $display_order);
                $success = $stmt->execute();
                $message = 'CSR item added successfully';   
                break;


            // UPDATE CSR
            case 'update_csr':
                $id = $_POST['id'];
                $title = $_POST['title'];
                $subtitle = $_POST['subtitle'];
                $author = $_POST['author_credit'];
                $image = $_POST['current_image'];
                $display_order = (int)$_POST['display_order'];

                if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                    $upload_dir = '../assets/img/about/';
                    $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                    $new_filename = uniqid() . '.' . $file_extension;

                    if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $new_filename)) {
                        if ($image && file_exists($upload_dir . $image)) {
                            unlink($upload_dir . $image);
                        }
                        $image = $new_filename;
                    }
                }

                $stmt = $conn->prepare("UPDATE about_csr SET title = ?, subtitle = ?, author_credit = ?, image = ?, display_order = ? WHERE id = ?");
                $stmt->bind_param("ssssii", $title, $subtitle, $author, $image, $display_order, $id);
                $success = $stmt->execute();
                $message = 'CSR item updated successfully';
                break;

            // DELETE CSR
            case 'delete_csr':
                $id = $_POST['id'];
                $image = $_POST['current_image'];

                if ($image && file_exists('../assets/img/about/' . $image)) {
                    unlink('../assets/img/about/' . $image);
                }

                $stmt = $conn->prepare("DELETE FROM about_csr WHERE id = ?");
                $stmt->bind_param("i", $id);
                $success = $stmt->execute();
                $message = 'CSR item deleted successfully';
                break;
        
            
        }

        // Set success message in session and redirect
        if ($success) {
            $_SESSION['success_message'] = $message;
        } else {
            $_SESSION['error_message'] = 'Operation failed. Please try again.';
        }
        
        // Redirect to prevent form resubmission
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Fetch all sections
$sections = $conn->query("SELECT * FROM about_sections ORDER BY display_order");
$timeline = $conn->query("SELECT * FROM about_timeline ORDER BY display_order");
$certifications = $conn->query("SELECT * FROM about_certifications ORDER BY display_order");

$csrList = $conn->query("SELECT * FROM about_csr ORDER BY display_order");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage About Page | Admin Panel</title>
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
</head>
<body class="bg-gray-50">
    <?php include 'includes/adminsidebar.php'; ?>
    
    <div class="lg:ml-64 p-8">
        <div class="max-w-7xl mx-auto">
            <h1 class="text-3xl font-bold text-gray-800 mb-8">Manage About Page</h1>
            
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
            
            <!-- Sections Management -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Page Sections</h2>
                <div class="space-y-6">
                    <?php while ($section = $sections->fetch_assoc()): ?>
                    <div class="border rounded-lg p-4">
                        <form method="POST" enctype="multipart/form-data" class="space-y-4">
                            <input type="hidden" name="action" value="update_section">
                            <input type="hidden" name="id" value="<?php echo $section['id']; ?>">
                            <input type="hidden" name="section_name" value="<?php echo $section['section_name']; ?>">
                            <?php if ($section['section_name'] === 'presidents_message'): ?>
                            <input type="hidden" name="current_audio" value="<?php echo $section['audio']; ?>">
                            <?php endif; ?>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Section Name</label>
                                <input type="text" value="<?php echo htmlspecialchars($section['section_name']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm px-4 py-2 focus:ring-primary focus:border-primary" disabled>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                                <input type="text" name="title" value="<?php echo htmlspecialchars($section['title']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm px-4 py-2 focus:ring-primary focus:border-primary" required>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Subtitle</label>
                                <textarea name="subtitle" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm px-4 py-2 focus:ring-primary focus:border-primary" rows="3"><?php echo htmlspecialchars($section['subtitle']); ?></textarea>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Content</label>
                                <textarea name="content" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm px-4 py-2 focus:ring-primary focus:border-primary" rows="6"><?php echo htmlspecialchars($section['content']); ?></textarea>
                            </div>
                            
                            <?php if ($section['section_name'] === 'presidents_message'): ?>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Position/Title</label>
                                <input type="text" name="position" value="<?php echo htmlspecialchars($section['position']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm px-4 py-2 focus:ring-primary focus:border-primary" placeholder="e.g., President & CEO">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Current Audio</label>
                                <?php if (!empty($section['audio'])): ?>
                                    <audio controls class="mb-2">
                                        <source src="../assets/audio/<?php echo htmlspecialchars($section['audio']); ?>" type="audio/mpeg">
                                        Your browser does not support the audio element.
                                    </audio>
                                <?php else: ?>
                                    <div class="text-gray-500 mb-2">No audio uploaded.</div>
                                <?php endif; ?>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Upload New Audio (MP3)</label>
                                <input type="file" name="audio" accept="audio/mp3,audio/mpeg" class="mt-1 block w-full">
                            </div>
                            <?php endif; ?>
                            
                            <div>
                                <button type="submit" class="bg-primary text-white px-4 py-2 rounded-md hover:bg-primary-dark">Update Section</button>
                            </div>
                        </form>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>

                        <!-- CSR Management -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold text-gray-800">CSR Sections</h2>
                    <button type="button" onclick="toggleCSRForm()" class="bg-primary text-white px-4 py-2 rounded-md hover:bg-primary-dark">
                        <i class="fas fa-plus mr-2"></i>Add New CSR
                    </button>
                </div>

                <!-- ADD CSR FORM -->
                <div id="newCSRForm" class="hidden border rounded-lg p-4 mb-6">
                    <form method="POST" enctype="multipart/form-data" class="space-y-4">
                        <input type="hidden" name="action" value="add_csr">

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Title</label>
                            <input type="text" name="title" class="block w-full rounded-md border-gray-300 px-4 py-2 shadow-sm" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Subtitle</label>
                            <input type="text" name="subtitle" class="block w-full rounded-md border-gray-300 shadow-sm px-4 py-2 focus:ring-primary focus:border-primary">
                        </div>

                        <!--If using stock images-->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Author</label>
                            <input type="text" name="author_credit" class="block w-full rounded-md border-gray-300 shadow-sm px-4 py-2 focus:ring-primary focus:border-primary" placeholder="Enter author credit">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Image</label>
                            <input type="file" name="image" accept="image/*" class="block w-full" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Display Order</label>
                            <input type="number" name="display_order" value="0" class="block w-full rounded-md border-gray-300 px-4 py-2">
                        </div>

                        <div class="flex gap-2">
                            <button type="submit" class="bg-primary text-white px-4 py-2 rounded-md hover:bg-primary-dark">Add</button>
                            <button type="button" onclick="toggleCSRForm()" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">Cancel</button>
                        </div>
                    </form>
                </div>

                <!-- EXISTING CSR RECORDS -->
                <div class="space-y-6">
                    <?php while ($csr = $csrList->fetch_assoc()): ?>
                    <div class="border rounded-lg p-4">
                        <form method="POST" enctype="multipart/form-data" class="space-y-4">
                            <input type="hidden" name="action" value="update_csr">
                            <input type="hidden" name="id" value="<?php echo $csr['id']; ?>">
                            <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($csr['image']); ?>">

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Title</label>
                                <input type="text" name="title" value="<?php echo htmlspecialchars($csr['title']); ?>" class="block w-full rounded-md border-gray-300 px-4 py-2 shadow-sm" required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Subtitle</label>
                                <textarea name="subtitle" class="block w-full rounded-md border-gray-300 px-4 py-2 shadow-sm" rows="3"><?php echo htmlspecialchars($csr['subtitle']); ?></textarea>
                            </div>

                            <!--If using stock images-->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Author</label>
                                <input type="text" name="author_credit" class="block w-full rounded-md border-gray-300 shadow-sm px-4 py-2 focus:ring-primary focus:border-primary" value="<?php echo htmlspecialchars($csr['author_credit']); ?>">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Current Image</label><br>
                                <img src="../assets/img/about/<?php echo htmlspecialchars($csr['image']); ?>" alt="CSR" class="mt-2 h-32 rounded">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Replace Image</label>
                                <input type="file" name="image" accept="image/*" class="block w-full">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Display Order</label>
                                <input type="number" name="display_order" value="<?php echo (int)$csr['display_order']; ?>" class="block w-full rounded-md border-gray-300 px-4 py-2">
                            </div>

                            <div class="flex gap-2">
                                <button type="submit" class="bg-primary text-white px-4 py-2 rounded-md hover:bg-primary-dark">Update</button>
                                <button type="button" onclick="confirmDelete('csr', <?php echo $csr['id']; ?>)" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600">Delete CSR</button>
                            </div>
                        </form>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>

            

            
            
            <!-- Timeline Management -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold text-gray-800">Timeline Events</h2>
                    <button type="button" onclick="toggleTimelineForm()" class="bg-primary text-white px-4 py-2 rounded-md hover:bg-primary-dark">
                        <i class="fas fa-plus mr-2"></i>Add New Event
                    </button>
                </div>

                <!-- Add New Timeline Event Form -->
                <div id="newTimelineForm" class="hidden border rounded-lg p-4 mb-6">
                    <form method="POST" class="space-y-4">
                        <input type="hidden" name="action" value="add_timeline">
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                            <input type="text" name="title" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm px-4 py-2 focus:ring-primary focus:border-primary" required>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea name="description" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm px-4 py-2 focus:ring-primary focus:border-primary" rows="3" required></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Image (optional)</label>
                            <input type="file" name="image" accept="image/*" class="mt-1 block w-full">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Year</label>
                            <input type="text" name="year" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm px-4 py-2 focus:ring-primary focus:border-primary" required>
                        </div>
                        
                        <div>
                            <button type="submit" class="bg-primary text-white px-4 py-2 rounded-md hover:bg-primary-dark">Add Event</button>
                            <button type="button" onclick="toggleTimelineForm()" class="ml-2 bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">Cancel</button>
                        </div>
                    </form>
                </div>

                <div class="space-y-6">
                    <?php while ($event = $timeline->fetch_assoc()): ?>
                    <div class="border rounded-lg p-4">
                        <form method="POST" enctype="multipart/form-data" class="space-y-4">
                            
                            <?php if (!empty($event['photo'])): ?>
                            <div class="mb-4">
                                <img src="../assets/img/timeline/<?php echo htmlspecialchars($event['photo']); ?>" alt="Timeline Photo" class="w-full max-w-xs rounded-md shadow-md">
                            </div>
                            <?php endif; ?>

                            <input type="hidden" name="action" value="update_timeline">
                            <input type="hidden" name="id" value="<?php echo $event['id']; ?>">
                            <input type="hidden" name="current_photo" value="<?php echo htmlspecialchars($event['photo']); ?>">

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                                <input type="text" name="title" value="<?php echo htmlspecialchars($event['title']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm px-4 py-2 focus:ring-primary focus:border-primary" required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                <textarea name="description" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm px-4 py-2 focus:ring-primary focus:border-primary" rows="3" required><?php echo htmlspecialchars($event['description']); ?></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Year</label>
                                <input type="text" name="year" value="<?php echo htmlspecialchars($event['year']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm px-4 py-2 focus:ring-primary focus:border-primary" required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Replace Image (optional)</label>
                                <input type="file" name="image" accept="image/*" class="mt-1 block w-full">
                            </div>

                            <div class="flex gap-2">
                                <button type="submit" class="bg-primary text-white px-4 py-2 rounded-md hover:bg-primary-dark">Update Event</button>
                                <button type="button" onclick="confirmDelete('timeline', <?php echo $event['id']; ?>)" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600">Delete Event</button>
                            </div>
                        </form>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
            
            <!-- Certifications Management -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold text-gray-800">Certifications</h2>
                    <button type="button" onclick="toggleCertificationForm()" class="bg-primary text-white px-4 py-2 rounded-md hover:bg-primary-dark">
                        <i class="fas fa-plus mr-2"></i>Add New Certification
                    </button>
                </div>

                <!-- Add New Certification Form -->
                <div id="newCertificationForm" class="hidden border rounded-lg p-4 mb-6">
                    <form method="POST" enctype="multipart/form-data" class="space-y-4">
                        <input type="hidden" name="action" value="add_certification">
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                            <input type="text" name="title" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm px-4 py-2 focus:ring-primary focus:border-primary" required>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Image</label>
                            <input type="file" name="image" accept="image/*" class="mt-1 block w-full" required>
                        </div>
                        
                        <div>
                            <button type="submit" class="bg-primary text-white px-4 py-2 rounded-md hover:bg-primary-dark">Add Certification</button>
                            <button type="button" onclick="toggleCertificationForm()" class="ml-2 bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">Cancel</button>
                        </div>
                    </form>
                </div>

                <div class="space-y-6">
                    <?php while ($cert = $certifications->fetch_assoc()): ?>
                    <div class="border rounded-lg p-4">
                        <form method="POST" enctype="multipart/form-data" class="space-y-4">
                            <input type="hidden" name="action" value="update_certification">
                            <input type="hidden" name="id" value="<?php echo $cert['id']; ?>">
                            <input type="hidden" name="current_image" value="<?php echo $cert['image']; ?>">
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                                <input type="text" name="title" value="<?php echo htmlspecialchars($cert['title']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm px-4 py-2 focus:ring-primary focus:border-primary" required>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Current Image</label>
                                <img src="../assets/img/certifications/<?php echo $cert['image']; ?>" alt="Current certification" class="mt-2 h-32 w-auto">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">New Image</label>
                                <input type="file" name="image" accept="image/*" class="mt-1 block w-full">
                            </div>
                            
                            <div class="flex gap-2">
                                <button type="submit" class="bg-primary text-white px-4 py-2 rounded-md hover:bg-primary-dark">Update Certification</button>
                                <button type="button" onclick="confirmDelete('certification', <?php echo $cert['id']; ?>, '<?php echo $cert['image']; ?>')" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600">Delete Certification</button>
                            </div>
                        </form>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function toggleTimelineForm() {
            const form = document.getElementById('newTimelineForm');
            form.classList.toggle('hidden');
        }

        function toggleCSRForm() {
            const form = document.getElementById('newCSRForm');
            form.classList.toggle('hidden');
        }

        function toggleCertificationForm() {
            const form = document.getElementById('newCertificationForm');
            form.classList.toggle('hidden');
        }

        function confirmDelete(type, id, image = null) {
            if (confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_${type}">
                    <input type="hidden" name="id" value="${id}">
                    ${image ? `<input type="hidden" name="current_image" value="${image}">` : ''}
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('[role="alert"]');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.remove();
                }, 5000);
            });
        });
    </script>
</body>
</html> 