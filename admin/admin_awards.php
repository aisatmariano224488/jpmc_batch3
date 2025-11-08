<?php
// Start session
// session_start();

// Check if user is logged in
// if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
//     header("Location: admin_login.php");
//     exit;
// }

require_once '../includes/db_connection.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_award':
                $title = $conn->real_escape_string($_POST['title']);
                $description = $conn->real_escape_string($_POST['description']);
                $year = $conn->real_escape_string($_POST['year']);
                $icon = $conn->real_escape_string($_POST['icon']);
                
                // Handle image upload
                $image = '';
                if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                    $target_dir = "../assets/img/awards/";
                    if (!file_exists($target_dir)) {
                        mkdir($target_dir, 0777, true);
                    }
                    $image = time() . '_' . basename($_FILES['image']['name']);
                    $target_file = $target_dir . $image;
                    move_uploaded_file($_FILES['image']['tmp_name'], $target_file);
                }
                
                $sql = "INSERT INTO awards (title, description, image, year, icon) VALUES ('$title', '$description', '$image', '$year', '$icon')";
                if ($conn->query($sql)) {
                    $_SESSION['success_message'] = "Award added successfully!";
                } else {
                    $_SESSION['error_message'] = "Error adding award: " . $conn->error;
                }
                break;
                
            case 'edit_award':
                $id = (int)$_POST['id'];
                $title = $conn->real_escape_string($_POST['title']);
                $description = $conn->real_escape_string($_POST['description']);
                $year = $conn->real_escape_string($_POST['year']);
                $icon = $conn->real_escape_string($_POST['icon']);
                
                // Handle image upload
                $image_sql = '';
                if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                    $target_dir = "../assets/img/awards/";
                    if (!file_exists($target_dir)) {
                        mkdir($target_dir, 0777, true);
                    }
                    $image = time() . '_' . basename($_FILES['image']['name']);
                    $target_file = $target_dir . $image;
                    move_uploaded_file($_FILES['image']['tmp_name'], $target_file);
                    $image_sql = ", image = '$image'";
                }
                
                $sql = "UPDATE awards SET title = '$title', description = '$description', year = '$year', icon = '$icon' $image_sql WHERE id = $id";
                if ($conn->query($sql)) {
                    $_SESSION['success_message'] = "Award updated successfully!";
                } else {
                    $_SESSION['error_message'] = "Error updating award: " . $conn->error;
                }
                break;
                
            case 'delete_award':
                $id = (int)$_POST['id'];
                $sql = "DELETE FROM awards WHERE id = $id";
                if ($conn->query($sql)) {
                    $_SESSION['success_message'] = "Award deleted successfully!";
                } else {
                    $_SESSION['error_message'] = "Error deleting award: " . $conn->error;
                }
                break;

            case 'add_timeline':
                $title = $conn->real_escape_string($_POST['title']);
                $description = $conn->real_escape_string($_POST['description']);
                $date = $conn->real_escape_string($_POST['date']);
                $icon = $conn->real_escape_string($_POST['icon']);
                
                $sql = "INSERT INTO award_timeline (title, description, date, icon) VALUES ('$title', '$description', '$date', '$icon')";
                if ($conn->query($sql)) {
                    $_SESSION['success_message'] = "Timeline item added successfully!";
                } else {
                    $_SESSION['error_message'] = "Error adding timeline item: " . $conn->error;
                }
                break;
                
            case 'edit_timeline':
                $id = (int)$_POST['id'];
                $title = $conn->real_escape_string($_POST['title']);
                $description = $conn->real_escape_string($_POST['description']);
                $date = $conn->real_escape_string($_POST['date']);
                $icon = $conn->real_escape_string($_POST['icon']);
                
                $sql = "UPDATE award_timeline SET title = '$title', description = '$description', date = '$date', icon = '$icon' WHERE id = $id";
                if ($conn->query($sql)) {
                    $_SESSION['success_message'] = "Timeline item updated successfully!";
                } else {
                    $_SESSION['error_message'] = "Error updating timeline item: " . $conn->error;
                }
                break;
                
            case 'delete_timeline':
                $id = (int)$_POST['id'];
                $sql = "DELETE FROM award_timeline WHERE id = $id";
                if ($conn->query($sql)) {
                    $_SESSION['success_message'] = "Timeline item deleted successfully!";
                } else {
                    $_SESSION['error_message'] = "Error deleting timeline item: " . $conn->error;
                }
                break;
        }
    }
}

// Fetch all awards
$awards = array();
$sql = "SELECT * FROM awards ORDER BY year DESC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $awards[] = $row;
    }
}

// Fetch all timeline items
$timeline = array();
$sql = "SELECT * FROM award_timeline ORDER BY date DESC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $timeline[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Awards & Timeline | James Polymers</title>
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
    
    <style>
        .admin-content {
            transition: margin-left 0.3s ease;
            margin-left: 0;
        }
        
        @media (min-width: 1024px) {
            .admin-content {
                margin-left: 16rem; /* w-64 = 16rem */
            }
        }
        
        .card-stats {
            transition: all 0.3s ease;
        }
        
        .card-stats:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Include Admin Sidebar -->
    <?php include 'includes/adminsidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="admin-content p-4 sm:p-6 lg:p-8">
        <!-- Notifications -->
        <?php if (isset($_SESSION['success_message'])): ?>
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline"><?php echo $_SESSION['success_message']; ?></span>
            <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <?php unset($_SESSION['success_message']); endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline"><?php echo $_SESSION['error_message']; ?></span>
            <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <?php unset($_SESSION['error_message']); endif; ?>

        <!-- Admin Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Manage Awards & Timeline</h1>
                <p class="text-gray-600">Add, edit, or remove awards and timeline items</p>
            </div>
            <div class="flex gap-2">
                <button class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-secondary transition duration-300" onclick="openAddModal('award')">
                    <i class="fas fa-plus mr-2"></i> Add New Award
                </button>
                <button class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-secondary transition duration-300" onclick="openAddModal('timeline')">
                    <i class="fas fa-plus mr-2"></i> Add Timeline Item
                </button>
            </div>
        </div>
        
        <!-- Tabs -->
        <div class="mb-6">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8">
                    <button onclick="showTab('awards')" class="tab-button border-primary text-primary whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Awards
                    </button>
                    <button onclick="showTab('timeline')" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Timeline
                    </button>
                </nav>
            </div>
        </div>
        
        <!-- Awards Section -->
        <div id="awards-section" class="bg-white rounded-lg shadow-md p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600 text-sm leading-normal">
                            <th class="py-3 px-4 text-left">Image</th>
                            <th class="py-3 px-4 text-left">Title</th>
                            <th class="py-3 px-4 text-left">Year</th>
                            <th class="py-3 px-4 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 text-sm">
                        <?php foreach ($awards as $award): ?>
                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                            <td class="py-3 px-4">
                                <?php if ($award['image']): ?>
                                <img src="../assets/img/awards/<?php echo htmlspecialchars($award['image']); ?>" alt="<?php echo htmlspecialchars($award['title']); ?>" class="h-12 w-12 object-cover rounded">
                                <?php else: ?>
                                <div class="h-12 w-12 bg-gray-200 rounded flex items-center justify-center">
                                    <i class="fas <?php echo htmlspecialchars($award['icon']); ?> text-gray-400"></i>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4"><?php echo htmlspecialchars($award['title']); ?></td>
                            <td class="py-3 px-4"><?php echo htmlspecialchars($award['year']); ?></td>
                            <td class="py-3 px-4">
                                <button class="text-blue-500 hover:text-blue-700 mr-3" onclick="openEditModal('award', <?php echo htmlspecialchars(json_encode($award)); ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="text-red-500 hover:text-red-700" onclick="deleteItem('award', <?php echo $award['id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Timeline Section -->
        <div id="timeline-section" class="bg-white rounded-lg shadow-md p-6 hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600 text-sm leading-normal">
                            <th class="py-3 px-4 text-left">Icon</th>
                            <th class="py-3 px-4 text-left">Title</th>
                            <th class="py-3 px-4 text-left">Date</th>
                            <th class="py-3 px-4 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 text-sm">
                        <?php foreach ($timeline as $item): ?>
                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                            <td class="py-3 px-4">
                                <div class="h-12 w-12 bg-gray-200 rounded-full flex items-center justify-center">
                                    <i class="fas <?php echo htmlspecialchars($item['icon']); ?> text-gray-400"></i>
                                </div>
                            </td>
                            <td class="py-3 px-4"><?php echo htmlspecialchars($item['title']); ?></td>
                            <td class="py-3 px-4"><?php echo date('F Y', strtotime($item['date'])); ?></td>
                            <td class="py-3 px-4">
                                <button class="text-blue-500 hover:text-blue-700 mr-3" onclick="openEditModal('timeline', <?php echo htmlspecialchars(json_encode($item)); ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="text-red-500 hover:text-red-700" onclick="deleteItem('timeline', <?php echo $item['id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Add/Edit Modal -->
    <div id="itemModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-2xl mx-4">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold" id="modalTitle">Add New Item</h2>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="itemForm" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" id="formAction" value="add_award">
                <input type="hidden" name="id" id="itemId">
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="title">Title</label>
                    <input type="text" id="title" name="title" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-primary">
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="description">Description</label>
                    <textarea id="description" name="description" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-primary" rows="3"></textarea>
                </div>
                
                <div class="mb-4" id="yearField">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="year">Year</label>
                    <input type="text" id="year" name="year" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-primary">
                </div>
                
                <div class="mb-4" id="dateField" style="display: none;">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="date">Date</label>
                    <input type="date" id="date" name="date" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-primary">
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="icon">Icon (Font Awesome class)</label>
                    <input type="text" id="icon" name="icon" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-primary" value="fa-trophy">
                </div>
                
                <div class="mb-4" id="imageField">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="image">Image</label>
                    <input type="file" id="image" name="image" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-primary" accept="image/*">
                </div>
                
                <div class="flex justify-end">
                    <button type="button" onclick="closeModal()" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 mr-2">Cancel</button>
                    <button type="submit" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-secondary">Save</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function showTab(tab) {
            // Hide all sections
            document.getElementById('awards-section').classList.add('hidden');
            document.getElementById('timeline-section').classList.add('hidden');
            
            // Show selected section
            document.getElementById(tab + '-section').classList.remove('hidden');
            
            // Update tab styles
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('border-primary', 'text-primary');
                button.classList.add('border-transparent', 'text-gray-500');
            });
            
            // Style active tab
            event.target.classList.remove('border-transparent', 'text-gray-500');
            event.target.classList.add('border-primary', 'text-primary');
        }
        
        function openAddModal(type) {
            document.getElementById('modalTitle').textContent = 'Add New ' + (type === 'award' ? 'Award' : 'Timeline Item');
            document.getElementById('formAction').value = 'add_' + type;
            document.getElementById('itemForm').reset();
            document.getElementById('itemId').value = '';
            
            // Show/hide relevant fields
            document.getElementById('yearField').style.display = type === 'award' ? 'block' : 'none';
            document.getElementById('dateField').style.display = type === 'timeline' ? 'block' : 'none';
            document.getElementById('imageField').style.display = type === 'award' ? 'block' : 'none';
            
            document.getElementById('itemModal').classList.remove('hidden');
            document.getElementById('itemModal').classList.add('flex');
        }
        
        function openEditModal(type, item) {
            document.getElementById('modalTitle').textContent = 'Edit ' + (type === 'award' ? 'Award' : 'Timeline Item');
            document.getElementById('formAction').value = 'edit_' + type;
            document.getElementById('itemId').value = item.id;
            document.getElementById('title').value = item.title;
            document.getElementById('description').value = item.description;
            document.getElementById('icon').value = item.icon;
            
            // Show/hide relevant fields
            document.getElementById('yearField').style.display = type === 'award' ? 'block' : 'none';
            document.getElementById('dateField').style.display = type === 'timeline' ? 'block' : 'none';
            document.getElementById('imageField').style.display = type === 'award' ? 'block' : 'none';
            
            if (type === 'award') {
                document.getElementById('year').value = item.year;
            } else {
                document.getElementById('date').value = item.date;
            }
            
            document.getElementById('itemModal').classList.remove('hidden');
            document.getElementById('itemModal').classList.add('flex');
        }
        
        function closeModal() {
            document.getElementById('itemModal').classList.add('hidden');
            document.getElementById('itemModal').classList.remove('flex');
        }
        
        function deleteItem(type, id) {
            if (confirm('Are you sure you want to delete this item?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_${type}">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>
<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "JPMC";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_award':
                $title = $conn->real_escape_string($_POST['title']);
                $description = $conn->real_escape_string($_POST['description']);
                $year = $conn->real_escape_string($_POST['year']);
                $icon = $conn->real_escape_string($_POST['icon']);
                
                // Handle image upload
                $image = '';
                if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                    $target_dir = "../assets/img/awards/";
                    if (!file_exists($target_dir)) {
                        mkdir($target_dir, 0777, true);
                    }
                    $image = time() . '_' . basename($_FILES['image']['name']);
                    $target_file = $target_dir . $image;
                    move_uploaded_file($_FILES['image']['tmp_name'], $target_file);
                }
                
                $sql = "INSERT INTO awards (title, description, image, year, icon) VALUES ('$title', '$description', '$image', '$year', '$icon')";
                if ($conn->query($sql)) {
                    $_SESSION['success_message'] = "Award added successfully!";
                } else {
                    $_SESSION['error_message'] = "Error adding award: " . $conn->error;
                }
                break;
                
            case 'edit_award':
                $id = (int)$_POST['id'];
                $title = $conn->real_escape_string($_POST['title']);
                $description = $conn->real_escape_string($_POST['description']);
                $year = $conn->real_escape_string($_POST['year']);
                $icon = $conn->real_escape_string($_POST['icon']);
                
                // Handle image upload
                $image_sql = '';
                if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                    $target_dir = "../assets/img/awards/";
                    if (!file_exists($target_dir)) {
                        mkdir($target_dir, 0777, true);
                    }
                    $image = time() . '_' . basename($_FILES['image']['name']);
                    $target_file = $target_dir . $image;
                    move_uploaded_file($_FILES['image']['tmp_name'], $target_file);
                    $image_sql = ", image = '$image'";
                }
                
                $sql = "UPDATE awards SET title = '$title', description = '$description', year = '$year', icon = '$icon' $image_sql WHERE id = $id";
                if ($conn->query($sql)) {
                    $_SESSION['success_message'] = "Award updated successfully!";
                } else {
                    $_SESSION['error_message'] = "Error updating award: " . $conn->error;
                }
                break;
                
            case 'delete_award':
                $id = (int)$_POST['id'];
                $sql = "DELETE FROM awards WHERE id = $id";
                if ($conn->query($sql)) {
                    $_SESSION['success_message'] = "Award deleted successfully!";
                } else {
                    $_SESSION['error_message'] = "Error deleting award: " . $conn->error;
                }
                break;

            case 'add_timeline':
                $title = $conn->real_escape_string($_POST['title']);
                $description = $conn->real_escape_string($_POST['description']);
                $date = $conn->real_escape_string($_POST['date']);
                $icon = $conn->real_escape_string($_POST['icon']);
                
                $sql = "INSERT INTO award_timeline (title, description, date, icon) VALUES ('$title', '$description', '$date', '$icon')";
                if ($conn->query($sql)) {
                    $_SESSION['success_message'] = "Timeline item added successfully!";
                } else {
                    $_SESSION['error_message'] = "Error adding timeline item: " . $conn->error;
                }
                break;
                
            case 'edit_timeline':
                $id = (int)$_POST['id'];
                $title = $conn->real_escape_string($_POST['title']);
                $description = $conn->real_escape_string($_POST['description']);
                $date = $conn->real_escape_string($_POST['date']);
                $icon = $conn->real_escape_string($_POST['icon']);
                
                $sql = "UPDATE award_timeline SET title = '$title', description = '$description', date = '$date', icon = '$icon' WHERE id = $id";
                if ($conn->query($sql)) {
                    $_SESSION['success_message'] = "Timeline item updated successfully!";
                } else {
                    $_SESSION['error_message'] = "Error updating timeline item: " . $conn->error;
                }
                break;
                
            case 'delete_timeline':
                $id = (int)$_POST['id'];
                $sql = "DELETE FROM award_timeline WHERE id = $id";
                if ($conn->query($sql)) {
                    $_SESSION['success_message'] = "Timeline item deleted successfully!";
                } else {
                    $_SESSION['error_message'] = "Error deleting timeline item: " . $conn->error;
                }
                break;
        }
    }
}

// Fetch all awards
$awards = array();
$sql = "SELECT * FROM awards ORDER BY year DESC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $awards[] = $row;
    }
}

// Fetch all timeline items
$timeline = array();
$sql = "SELECT * FROM award_timeline ORDER BY date DESC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $timeline[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Awards & Timeline | James Polymers</title>
    
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
            margin-left: 0;
        }
        
        @media (min-width: 1024px) {
            .admin-content {
                margin-left: 16rem; /* w-64 = 16rem */
            }
        }
        
        .card-stats {
            transition: all 0.3s ease;
        }
        
        .card-stats:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Include Admin Sidebar -->
    <?php include 'includes/adminsidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="admin-content p-4 sm:p-6 lg:p-8">
        <!-- Notifications -->
        <?php if (isset($_SESSION['success_message'])): ?>
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline"><?php echo $_SESSION['success_message']; ?></span>
            <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <?php unset($_SESSION['success_message']); endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline"><?php echo $_SESSION['error_message']; ?></span>
            <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <?php unset($_SESSION['error_message']); endif; ?>

        <!-- Admin Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Manage Awards & Timeline</h1>
                <p class="text-gray-600">Add, edit, or remove awards and timeline items</p>
            </div>
            <div class="flex gap-2">
                <button class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-secondary transition duration-300" onclick="openAddModal('award')">
                    <i class="fas fa-plus mr-2"></i> Add New Award
                </button>
                <button class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-secondary transition duration-300" onclick="openAddModal('timeline')">
                    <i class="fas fa-plus mr-2"></i> Add Timeline Item
                </button>
            </div>
        </div>
        
        <!-- Tabs -->
        <div class="mb-6">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8">
                    <button onclick="showTab('awards')" class="tab-button border-primary text-primary whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Awards
                    </button>
                    <button onclick="showTab('timeline')" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Timeline
                    </button>
                </nav>
            </div>
        </div>
        
        <!-- Awards Section -->
        <div id="awards-section" class="bg-white rounded-lg shadow-md p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600 text-sm leading-normal">
                            <th class="py-3 px-4 text-left">Image</th>
                            <th class="py-3 px-4 text-left">Title</th>
                            <th class="py-3 px-4 text-left">Year</th>
                            <th class="py-3 px-4 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 text-sm">
                        <?php foreach ($awards as $award): ?>
                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                            <td class="py-3 px-4">
                                <?php if ($award['image']): ?>
                                <img src="../assets/img/awards/<?php echo htmlspecialchars($award['image']); ?>" alt="<?php echo htmlspecialchars($award['title']); ?>" class="h-12 w-12 object-cover rounded">
                                <?php else: ?>
                                <div class="h-12 w-12 bg-gray-200 rounded flex items-center justify-center">
                                    <i class="fas <?php echo htmlspecialchars($award['icon']); ?> text-gray-400"></i>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4"><?php echo htmlspecialchars($award['title']); ?></td>
                            <td class="py-3 px-4"><?php echo htmlspecialchars($award['year']); ?></td>
                            <td class="py-3 px-4">
                                <button class="text-blue-500 hover:text-blue-700 mr-3" onclick="openEditModal('award', <?php echo htmlspecialchars(json_encode($award)); ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="text-red-500 hover:text-red-700" onclick="deleteItem('award', <?php echo $award['id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Timeline Section -->
        <div id="timeline-section" class="bg-white rounded-lg shadow-md p-6 hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600 text-sm leading-normal">
                            <th class="py-3 px-4 text-left">Icon</th>
                            <th class="py-3 px-4 text-left">Title</th>
                            <th class="py-3 px-4 text-left">Date</th>
                            <th class="py-3 px-4 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 text-sm">
                        <?php foreach ($timeline as $item): ?>
                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                            <td class="py-3 px-4">
                                <div class="h-12 w-12 bg-gray-200 rounded-full flex items-center justify-center">
                                    <i class="fas <?php echo htmlspecialchars($item['icon']); ?> text-gray-400"></i>
                                </div>
                            </td>
                            <td class="py-3 px-4"><?php echo htmlspecialchars($item['title']); ?></td>
                            <td class="py-3 px-4"><?php echo date('F Y', strtotime($item['date'])); ?></td>
                            <td class="py-3 px-4">
                                <button class="text-blue-500 hover:text-blue-700 mr-3" onclick="openEditModal('timeline', <?php echo htmlspecialchars(json_encode($item)); ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="text-red-500 hover:text-red-700" onclick="deleteItem('timeline', <?php echo $item['id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Add/Edit Modal -->
    <div id="itemModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-2xl mx-4">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold" id="modalTitle">Add New Item</h2>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="itemForm" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" id="formAction" value="add_award">
                <input type="hidden" name="id" id="itemId">
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="title">Title</label>
                    <input type="text" id="title" name="title" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-primary">
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="description">Description</label>
                    <textarea id="description" name="description" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-primary" rows="3"></textarea>
                </div>
                
                <div class="mb-4" id="yearField">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="year">Year</label>
                    <input type="text" id="year" name="year" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-primary">
                </div>
                
                <div class="mb-4" id="dateField" style="display: none;">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="date">Date</label>
                    <input type="date" id="date" name="date" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-primary">
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="icon">Icon (Font Awesome class)</label>
                    <input type="text" id="icon" name="icon" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-primary" value="fa-trophy">
                </div>
                
                <div class="mb-4" id="imageField">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="image">Image</label>
                    <input type="file" id="image" name="image" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-primary" accept="image/*">
                </div>
                
                <div class="flex justify-end">
                    <button type="button" onclick="closeModal()" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 mr-2">Cancel</button>
                    <button type="submit" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-secondary">Save</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function showTab(tab) {
            // Hide all sections
            document.getElementById('awards-section').classList.add('hidden');
            document.getElementById('timeline-section').classList.add('hidden');
            
            // Show selected section
            document.getElementById(tab + '-section').classList.remove('hidden');
            
            // Update tab styles
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('border-primary', 'text-primary');
                button.classList.add('border-transparent', 'text-gray-500');
            });
            
            // Style active tab
            event.target.classList.remove('border-transparent', 'text-gray-500');
            event.target.classList.add('border-primary', 'text-primary');
        }
        
        function openAddModal(type) {
            document.getElementById('modalTitle').textContent = 'Add New ' + (type === 'award' ? 'Award' : 'Timeline Item');
            document.getElementById('formAction').value = 'add_' + type;
            document.getElementById('itemForm').reset();
            document.getElementById('itemId').value = '';
            
            // Show/hide relevant fields
            document.getElementById('yearField').style.display = type === 'award' ? 'block' : 'none';
            document.getElementById('dateField').style.display = type === 'timeline' ? 'block' : 'none';
            document.getElementById('imageField').style.display = type === 'award' ? 'block' : 'none';
            
            document.getElementById('itemModal').classList.remove('hidden');
            document.getElementById('itemModal').classList.add('flex');
        }
        
        function openEditModal(type, item) {
            document.getElementById('modalTitle').textContent = 'Edit ' + (type === 'award' ? 'Award' : 'Timeline Item');
            document.getElementById('formAction').value = 'edit_' + type;
            document.getElementById('itemId').value = item.id;
            document.getElementById('title').value = item.title;
            document.getElementById('description').value = item.description;
            document.getElementById('icon').value = item.icon;
            
            // Show/hide relevant fields
            document.getElementById('yearField').style.display = type === 'award' ? 'block' : 'none';
            document.getElementById('dateField').style.display = type === 'timeline' ? 'block' : 'none';
            document.getElementById('imageField').style.display = type === 'award' ? 'block' : 'none';
            
            if (type === 'award') {
                document.getElementById('year').value = item.year;
            } else {
                document.getElementById('date').value = item.date;
            }
            
            document.getElementById('itemModal').classList.remove('hidden');
            document.getElementById('itemModal').classList.add('flex');
        }
        
        function closeModal() {
            document.getElementById('itemModal').classList.add('hidden');
            document.getElementById('itemModal').classList.remove('flex');
        }
        
        function deleteItem(type, id) {
            if (confirm('Are you sure you want to delete this item?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_${type}">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>
