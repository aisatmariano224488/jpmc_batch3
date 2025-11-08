<?php
// Start session
// session_start();

// // Check if user is logged in
// if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
//     header("Location: admin_login.php");
//     exit;
// }

require_once '../includes/db_connection.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_timeline':
                $title = $conn->real_escape_string($_POST['title']);
                $description = $conn->real_escape_string($_POST['description']);
                $date = $conn->real_escape_string($_POST['date']);
                $icon = $conn->real_escape_string($_POST['icon']);
                
                $sql = "INSERT INTO award_timeline (title, description, date, icon) VALUES ('$title', '$description', '$date', '$icon')";
                $conn->query($sql);
                break;
                
            case 'edit_timeline':
                $id = (int)$_POST['id'];
                $title = $conn->real_escape_string($_POST['title']);
                $description = $conn->real_escape_string($_POST['description']);
                $date = $conn->real_escape_string($_POST['date']);
                $icon = $conn->real_escape_string($_POST['icon']);
                
                $sql = "UPDATE award_timeline SET title = '$title', description = '$description', date = '$date', icon = '$icon' WHERE id = $id";
                $conn->query($sql);
                break;
                
            case 'delete_timeline':
                $id = (int)$_POST['id'];
                $sql = "DELETE FROM award_timeline WHERE id = $id";
                $conn->query($sql);
                break;
        }
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
    <title>Manage Timeline | James Polymers</title>
    
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
                <h1 class="text-2xl font-bold text-gray-800">Manage Timeline</h1>
                <p class="text-gray-600">Add, edit, or remove timeline items</p>
            </div>
            <button class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-secondary transition duration-300" onclick="openAddModal()">
                <i class="fas fa-plus mr-2"></i> Add New Timeline Item
            </button>
        </div>
        
        <!-- Timeline List -->
        <div class="bg-white rounded-lg shadow-md p-6">
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
                                <button class="text-blue-500 hover:text-blue-700 mr-3" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($item)); ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="text-red-500 hover:text-red-700" onclick="deleteTimeline(<?php echo $item['id']; ?>)">
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
    <div id="timelineModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-2xl mx-4">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold" id="modalTitle">Add New Timeline Item</h2>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="timelineForm" method="POST">
                <input type="hidden" name="action" id="formAction" value="add_timeline">
                <input type="hidden" name="id" id="timelineId">
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="title">Title</label>
                    <input type="text" id="title" name="title" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-primary" required>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="description">Description</label>
                    <textarea id="description" name="description" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-primary" rows="3" required></textarea>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="date">Date</label>
                    <input type="date" id="date" name="date" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-primary" required>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="icon">Icon (Font Awesome class)</label>
                    <input type="text" id="icon" name="icon" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-primary" value="fa-trophy" required>
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
        function openAddModal() {
            document.getElementById('modalTitle').textContent = 'Add New Timeline Item';
            document.getElementById('formAction').value = 'add_timeline';
            document.getElementById('timelineForm').reset();
            document.getElementById('timelineId').value = '';
            document.getElementById('timelineModal').classList.remove('hidden');
            document.getElementById('timelineModal').classList.add('flex');
        }
        
        function openEditModal(item) {
            document.getElementById('modalTitle').textContent = 'Edit Timeline Item';
            document.getElementById('formAction').value = 'edit_timeline';
            document.getElementById('timelineId').value = item.id;
            document.getElementById('title').value = item.title;
            document.getElementById('description').value = item.description;
            document.getElementById('date').value = item.date;
            document.getElementById('icon').value = item.icon;
            document.getElementById('timelineModal').classList.remove('hidden');
            document.getElementById('timelineModal').classList.add('flex');
        }
        
        function closeModal() {
            document.getElementById('timelineModal').classList.add('hidden');
            document.getElementById('timelineModal').classList.remove('flex');
        }
        
        function deleteTimeline(id) {
            if (confirm('Are you sure you want to delete this timeline item?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_timeline">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html> 