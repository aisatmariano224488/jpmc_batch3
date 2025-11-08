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
$solution_id = $solution = $industry_id = "";
$success_message = $error_message = "";
$action = isset($_GET['action']) ? $_GET['action'] : "";

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $solution = trim($_POST['solution']);
    $industry_id = isset($_POST['industry_id']) ? $_POST['industry_id'] : "";
    
    // Validate input
    if (empty($solution) || empty($industry_id)) {
        $error_message = "Solution text and industry selection are required";
    } else {
        // Perform database operations based on action
        if (isset($_POST['action']) && $_POST['action'] == 'add') {
            // Add new solution
            $stmt = $conn->prepare("INSERT INTO industry_solutions (industry_id, solution) VALUES (?, ?)");
            $stmt->bind_param("is", $industry_id, $solution);
            
            if ($stmt->execute()) {
                $success_message = "Solution added successfully!";
                $solution = "";
            } else {
                $error_message = "Error: " . $stmt->error;
            }
            $stmt->close();
        } 
        else if (isset($_POST['action']) && $_POST['action'] == 'edit' && isset($_POST['solution_id'])) {
            $solution_id = $_POST['solution_id'];
            
            // Update solution
            $stmt = $conn->prepare("UPDATE industry_solutions SET industry_id = ?, solution = ? WHERE id = ?");
            $stmt->bind_param("isi", $industry_id, $solution, $solution_id);
            
            if ($stmt->execute()) {
                $success_message = "Solution updated successfully!";
            } else {
                $error_message = "Error: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Handle delete request
if ($action == "delete" && isset($_GET['id'])) {
    $solution_id = $_GET['id'];
    
    // Delete solution
    $stmt = $conn->prepare("DELETE FROM industry_solutions WHERE id = ?");
    $stmt->bind_param("i", $solution_id);
    
    if ($stmt->execute()) {
        $success_message = "Solution deleted successfully!";
    } else {
        $error_message = "Error deleting solution: " . $stmt->error;
    }
    $stmt->close();
}

// Load solution for editing
if ($action == "edit" && isset($_GET['id'])) {
    $solution_id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM industry_solutions WHERE id = ?");
    $stmt->bind_param("i", $solution_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $solution = $row['solution'];
        $industry_id = $row['industry_id'];
    } else {
        $error_message = "Solution not found";
    }
    $stmt->close();
}

// Get all industries for dropdown
$industries = array();
$sql = "SELECT id, name FROM industries ORDER BY name ASC";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $industries[] = $row;
    }
}

// Get all solutions with industry info
$solutions = array();
$sql = "SELECT s.*, i.name as industry_name 
        FROM industry_solutions s 
        JOIN industries i ON s.industry_id = i.id 
        ORDER BY i.name ASC, s.solution ASC";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $solutions[] = $row;
    }
}

// Get filter value if any
$filter_industry = isset($_GET['filter']) ? intval($_GET['filter']) : 0;

// Apply filter if selected
if ($filter_industry > 0) {
    $filtered_solutions = array();
    foreach ($solutions as $solution) {
        if ($solution['industry_id'] == $filter_industry) {
            $filtered_solutions[] = $solution;
        }
    }
    $solutions = $filtered_solutions;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Industry Solutions | James Polymers Admin</title>
    <link rel="icon" type="image/png" href="assets/img/tab_icon.png">
    
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
                <h1 class="text-2xl font-bold text-gray-800">Industry Solutions</h1>
                <p class="text-gray-600">Manage solutions for each industry</p>
            </div>
            <div>
                <?php if ($action != "new" && $action != "edit"): ?>
                <a href="admin_industry_solutions.php?action=new" class="bg-primary hover:bg-secondary text-white py-2 px-4 rounded-lg transition-all flex items-center">
                    <i class="fas fa-plus mr-2"></i> Add New Solution
                </a>
                <?php else: ?>
                <a href="admin_industry_solutions.php" class="bg-gray-600 hover:bg-gray-700 text-white py-2 px-4 rounded-lg transition-all flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Solutions
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
        <!-- Add/Edit Solution Form -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">
                <?php echo $action == "new" ? "Add New Solution" : "Edit Solution"; ?>
            </h2>
            
            <form action="admin_industry_solutions.php" method="post">
                <input type="hidden" name="action" value="<?php echo $action == 'new' ? 'add' : 'edit'; ?>">
                <?php if ($action == "edit"): ?>
                <input type="hidden" name="solution_id" value="<?php echo $solution_id; ?>">
                <?php endif; ?>
                
                <div class="mb-6">
                    <label for="industry_id" class="block text-sm font-medium text-gray-700 mb-1">Industry</label>
                    <select id="industry_id" name="industry_id" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary">
                        <option value="">Select Industry</option>
                        <?php foreach ($industries as $industry): ?>
                        <option value="<?php echo $industry['id']; ?>" <?php echo $industry_id == $industry['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($industry['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="mb-6">
                    <label for="solution" class="block text-sm font-medium text-gray-700 mb-1">Solution</label>
                    <textarea id="solution" name="solution" rows="3" required
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary"
                              placeholder="Enter the industry solution"><?php echo htmlspecialchars($solution); ?></textarea>
                    <p class="text-xs text-gray-500 mt-1">Describe the solution or product used in this industry.</p>
                </div>
                
                <div class="flex items-center justify-end">
                    <button type="button" onclick="window.location.href='admin_industry_solutions.php'" class="bg-gray-300 hover:bg-gray-400 text-gray-800 py-2 px-4 rounded-lg mr-4 transition-all">
                        Cancel
                    </button>
                    <button type="submit" class="bg-primary hover:bg-secondary text-white py-2 px-6 rounded-lg transition-all">
                        <?php echo $action == "new" ? "Add Solution" : "Update Solution"; ?>
                    </button>
                </div>
            </form>
        </div>
        <?php else: ?>
        <!-- Solutions List -->
        
        <!-- Filter Options -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <form action="admin_industry_solutions.php" method="get" class="flex flex-wrap items-center gap-4">
                <div class="flex-grow">
                    <label for="filter" class="block text-sm font-medium text-gray-700 mb-1">Filter by Industry</label>
                    <select id="filter" name="filter" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary" onchange="this.form.submit()">
                        <option value="0">All Industries</option>
                        <?php foreach ($industries as $industry): ?>
                        <option value="<?php echo $industry['id']; ?>" <?php echo $filter_industry == $industry['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($industry['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded-lg transition-all">
                        <i class="fas fa-filter mr-2"></i> Filter
                    </button>
                    <?php if ($filter_industry > 0): ?>
                    <a href="admin_industry_solutions.php" class="bg-gray-300 hover:bg-gray-400 text-gray-800 py-2 px-4 rounded-lg ml-2 transition-all">
                        <i class="fas fa-times mr-2"></i> Clear
                    </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Industry
                            </th>
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Solution
                            </th>
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($solutions as $item): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 px-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <i class="fas fa-industry text-primary mr-2"></i>
                                    <span class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($item['industry_name']); ?></span>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($item['solution']); ?></div>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap text-sm font-medium">
                                <a href="admin_industry_solutions.php?action=edit&id=<?php echo $item['id']; ?>" class="text-primary hover:text-secondary mr-3">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="#" onclick="deleteConfirm(<?php echo $item['id']; ?>, '<?php echo addslashes($item['solution']); ?>')" class="text-red-600 hover:text-red-800">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($solutions)): ?>
                        <tr>
                            <td colspan="3" class="py-6 px-4 text-center text-gray-500">
                                <i class="fas fa-info-circle text-gray-400 text-4xl mb-3"></i>
                                <?php if ($filter_industry > 0): ?>
                                <p>No solutions found for this industry. Click "Add New Solution" to create one.</p>
                                <?php else: ?>
                                <p>No solutions found. Click "Add New Solution" to create one.</p>
                                <?php endif; ?>
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
        function deleteConfirm(id, solution) {
            if (confirm('Are you sure you want to delete the solution "' + solution + '"?')) {
                window.location.href = 'admin_industry_solutions.php?action=delete&id=' + id;
            }
        }
    </script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>