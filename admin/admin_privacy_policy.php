<?php
// session_start();

// if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
//     header("Location: admin_login.php");
//     exit;
// }

$conn = new mysqli("sql102.infinityfree.com", "if0_39268761", "KlHiP075oQ7fV4T", "if0_39268761_jpmc");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$editing = false;
$edit_data = [
    'id' => '',
    'section_title' => '',
    'section_content' => '',
    'display_order' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = $_POST['id'] ?? null;

    if ($action === 'add') {
        $title = $_POST['section_title'] ?? '';
        $content = $_POST['section_content'] ?? '';
        $order = $_POST['display_order'] ?? 0;

        $stmt = $conn->prepare("INSERT INTO privacy_policy (section_title, section_content, display_order) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $title, $content, $order);
        $stmt->execute();
        header("Location: admin_privacy_policy.php");
        exit;

    } elseif ($action === 'edit' && $id) {
        $title = $_POST['section_title'] ?? '';
        $content = $_POST['section_content'] ?? '';
        $order = $_POST['display_order'] ?? 0;

        $stmt = $conn->prepare("UPDATE privacy_policy SET section_title = ?, section_content = ?, display_order = ? WHERE id = ?");
        $stmt->bind_param("ssii", $title, $content, $order, $id);
        $stmt->execute();
        header("Location: admin_privacy_policy.php");
        exit;

    } elseif ($action === 'delete' && $id) {
        $stmt = $conn->prepare("DELETE FROM privacy_policy WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        header("Location: admin_privacy_policy.php");
        exit;

    } elseif ($action === 'load' && $id) {
        $stmt = $conn->prepare("SELECT * FROM privacy_policy WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $edit_data = $result->fetch_assoc();
            $editing = true;
        }
    }
}

$policies = [];
$result = $conn->query("SELECT * FROM privacy_policy ORDER BY display_order ASC");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $policies[] = $row;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Privacy Policy Management | Admin</title>
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
    <!-- Add this in <head> of admin_privacy_policy.php -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

</head>
<body class="bg-gray-100">

    <?php include 'includes/adminsidebar.php'; ?>

    <div class="my-5 ml-64 px-4 max-w-full">
        <h2 class="text-2xl font-bold mb-4">Manage Privacy Policy Sections</h2>

        <form method="POST" class="mb-6">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($edit_data['id']); ?>">
            <input type="hidden" name="action" value="<?php echo $editing ? 'edit' : 'add'; ?>">

            <div class="mb-3">
                <label class="form-label">Section Title</label>
                <input type="text" name="section_title" class="form-control" required value="<?php echo htmlspecialchars($edit_data['section_title']); ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Content</label>
                <textarea name="section_content" class="form-control" rows="5" required><?php echo htmlspecialchars($edit_data['section_content']); ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Display Order</label>
                <input type="number" name="display_order" class="form-control" required value="<?php echo htmlspecialchars($edit_data['display_order']); ?>">
            </div>

            <button type="submit" class="btn btn-primary"><?php echo $editing ? 'Update' : 'Add'; ?> Section</button>
            <?php if ($editing): ?>
                <a href="admin_privacy_policy.php" class="btn btn-secondary ms-2">Cancel</a>
            <?php endif; ?>
        </form>

        <table class="table table-bordered">
            <thead>
            <tr>
                <th>#</th>
                <th>Title</th>
                <th>Order</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($policies as $policy): ?>
                <tr>
                    <td><?php echo $policy['id']; ?></td>
                    <td><?php echo htmlspecialchars($policy['section_title']); ?></td>
                    <td><?php echo $policy['display_order']; ?></td>
                    <td>
                        <form method="POST" style="display:inline">
                            <input type="hidden" name="id" value="<?php echo $policy['id']; ?>">
                            <input type="hidden" name="action" value="load">
                            <button type="submit" class="btn btn-sm btn-info">Edit</button>
                        </form>
                        <form method="POST" style="display:inline" onsubmit="return confirm('Are you sure?')">
                            <input type="hidden" name="id" value="<?php echo $policy['id']; ?>">
                            <input type="hidden" name="action" value="delete">
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
