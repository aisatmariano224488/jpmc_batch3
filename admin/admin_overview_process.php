<?php
// session_start();



// Redirect if not logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

// DB Connection
$conn = new mysqli("sql102.infinityfree.com", "if0_39268761", "KlHiP075oQ7fV4T", "if0_39268761_jpmc");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize edit state
$editing = false;
$edit_data = [
    'id' => '',
    'process_type' => '',
    'title' => '',
    'description' => '',
    'image' => ''
];

// Fetch overview_process_info
$overview_info = [
    'heading' => '',
    'description' => ''
];
$result = $conn->query("SELECT * FROM overview_process_info LIMIT 1");
if ($result && $result->num_rows > 0) {
    $overview_info = $result->fetch_assoc();
}

// Handle form actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = $_POST['id'] ?? null;

    if ($action === 'update_overview_info') {
        $heading = $_POST['overview_heading'] ?? '';
        $desc = $_POST['overview_description'] ?? '';
        $stmt = $conn->prepare("UPDATE overview_process_info SET heading = ?, description = ? WHERE id = 1");
        $stmt->bind_param("ss", $heading, $desc);
        $stmt->execute();
        header("Location: admin_overview_process.php");
        exit;
    }

    if ($action === 'add_overview' || $action === 'edit_overview') {
        $type = $_POST['process_type'] ?? '';
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';

        $image = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $target_dir = "../images/overview_process/";
            if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
            $image = time() . '_' . basename($_FILES['image']['name']);
            move_uploaded_file($_FILES['image']['tmp_name'], $target_dir . $image);
        }
    }

    if ($action === 'add_overview') {
        $stmt = $conn->prepare("INSERT INTO overview_processes (process_type, title, description, image) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $type, $title, $description, $image);
        $stmt->execute();
        header("Location: admin_overview_process.php");
        exit;

    } elseif ($action === 'edit_overview' && $id) {
        if ($image) {
            $stmt = $conn->prepare("UPDATE overview_processes SET process_type = ?, title = ?, description = ?, image = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $type, $title, $description, $image, $id);
        } else {
            $stmt = $conn->prepare("UPDATE overview_processes SET process_type = ?, title = ?, description = ? WHERE id = ?");
            $stmt->bind_param("sssi", $type, $title, $description, $id);
        }
        $stmt->execute();
        header("Location: admin_overview_process.php");
        exit;

    } elseif ($action === 'delete_overview' && $id) {
        $stmt = $conn->prepare("DELETE FROM overview_processes WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        header("Location: admin_overview_process.php");
        exit;

    } elseif ($action === 'edit_load' && $id) {
        $stmt = $conn->prepare("SELECT * FROM overview_processes WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $edit_data = $result->fetch_assoc();
            $editing = true;
        }
    }
}

// Fetch all overview processes
$overview_processes = [];
$result = $conn->query("SELECT * FROM overview_processes ORDER BY created_at DESC");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $overview_processes[] = $row;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Overview Process | James Polymers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#0066cc',
                        secondary: '#004d99'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100">
<?php include 'includes/adminsidebar.php'; ?>

<div class="admin-content p-6 lg:ml-64">
    <!-- Overview Info -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Edit Overview Section</h1>
        <p class="text-gray-600">Edit the main heading and description that appears above the process steps.</p>
    </div>
    <div class="bg-white shadow-md rounded-lg p-6 mb-8">
        <form method="POST">
            <input type="hidden" name="action" value="update_overview_info">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Main Heading</label>
                <input type="text" name="overview_heading" class="w-full p-2 border rounded" value="<?= htmlspecialchars($overview_info['heading']) ?>" required>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Main Description</label>
                <textarea name="overview_description" rows="4" class="w-full p-2 border rounded" required><?= htmlspecialchars($overview_info['description']) ?></textarea>
            </div>
            <div class="text-end">
                <button type="submit" class="bg-primary text-white px-4 py-2 rounded hover:bg-blue-700 transition">Update Info</button>
            </div>
        </form>
    </div>

    <!-- Process Form -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Manage Process Steps</h1>
        <p class="text-gray-600">Add or edit steps and visuals for plastic or rubber molding processes.</p>
    </div>
    <div class="bg-white shadow-md rounded-lg p-6 mb-10">
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="<?= $editing ? 'edit_overview' : 'add_overview' ?>">
            <input type="hidden" name="id" value="<?= htmlspecialchars($edit_data['id']) ?>">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Process Type</label>
                <select name="process_type" class="w-full p-2 border rounded" required>
                    <option value="plastic" <?= $edit_data['process_type'] === 'plastic' ? 'selected' : '' ?>>Plastic Injection</option>
                    <option value="rubber" <?= $edit_data['process_type'] === 'rubber' ? 'selected' : '' ?>>Rubber Molding</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                <input type="text" name="title" class="w-full p-2 border rounded" value="<?= htmlspecialchars($edit_data['title']) ?>" required>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" rows="4" class="w-full p-2 border rounded" required><?= htmlspecialchars($edit_data['description']) ?></textarea>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Image (optional)</label>
                <input type="file" name="image" class="w-full">
                <?php if (!empty($edit_data['image'])): ?>
                    <div class="mt-2">
                        <img src="../images/overview_process/<?= htmlspecialchars($edit_data['image']) ?>" class="w-40 rounded border">
                    </div>
                <?php endif; ?>
            </div>
            <div class="text-end">
                <button type="submit" class="bg-primary text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                    <?= $editing ? 'Update' : 'Save' ?>
                </button>
            </div>
        </form>
    </div>

    <!-- Records -->
    <div class="grid gap-4">
        <?php foreach ($overview_processes as $item): ?>
            <div class="bg-white p-4 rounded shadow">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-lg font-semibold"><?= htmlspecialchars($item['title']) ?></h3>
                        <p class="text-sm text-gray-600"><?= htmlspecialchars($item['description']) ?></p>
                        <span class="inline-block mt-2 px-3 py-1 text-xs rounded-full bg-gray-200 text-gray-800">
                            <?= ucfirst($item['process_type']) ?>
                        </span>
                        <?php if (!empty($item['image'])): ?>
                            <div class="mt-3">
                                <img src="../images/overview_process/<?= htmlspecialchars($item['image']) ?>" class="w-40 rounded border">
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="flex gap-2">
                        <form method="POST" action="admin_overview_process.php">
                            <input type="hidden" name="action" value="edit_load">
                            <input type="hidden" name="id" value="<?= $item['id'] ?>">
                            <button type="submit" class="text-blue-600 hover:text-blue-800">
                                <i class="fas fa-edit"></i>
                            </button>
                        </form>
                        <form method="POST" onsubmit="return confirm('Delete this item?')">
                            <input type="hidden" name="action" value="delete_overview">
                            <input type="hidden" name="id" value="<?= $item['id'] ?>">
                            <button type="submit" class="text-red-600 hover:text-red-800">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Font Awesome -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</body>
</html>
