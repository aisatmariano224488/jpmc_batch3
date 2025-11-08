<?php
// Start session
// session_start();

// // Check if user is logged in
// if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
//     header("Location: admin_login.php");
//     exit;
// }

require_once '../includes/db_connection.php';

// Handle Add FAQ
if (isset($_POST['add_faq'])) {
    $question = $conn->real_escape_string($_POST['question']);
    $answer = $conn->real_escape_string($_POST['answer']);
    $conn->query("INSERT INTO faqs (question, answer) VALUES ('$question', '$answer')");
    $_SESSION['faq_success'] = "FAQ added successfully!";
    header("Location: admin_faq.php");
    exit;
}

// Handle Edit FAQ
if (isset($_POST['edit_faq'])) {
    $id = intval($_POST['id']);
    $question = $conn->real_escape_string($_POST['question']);
    $answer = $conn->real_escape_string($_POST['answer']);
    $conn->query("UPDATE faqs SET question='$question', answer='$answer' WHERE id=$id");
    $_SESSION['faq_success'] = "FAQ updated successfully!";
    header("Location: admin_faq.php");
    exit;
}

// Handle Delete FAQ
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM faqs WHERE id=$id");
    $_SESSION['faq_success'] = "FAQ deleted successfully!";
    header("Location: admin_faq.php");
    exit;
}

// Fetch FAQs
$faqs = [];
$result = $conn->query("SELECT * FROM faqs ORDER BY created_at DESC");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $faqs[] = $row;
    }
}

// Fetch FAQ for editing
$edit_faq = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $result = $conn->query("SELECT * FROM faqs WHERE id=$id LIMIT 1");
    if ($result && $result->num_rows > 0) {
        $edit_faq = $result->fetch_assoc();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin FAQ | James Polymers</title>
    <link rel="icon" type="image/png" href="/assets/img/tab_icon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .faq-form {
            max-width: 600px;
            margin: 0 auto;
        }
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
<?php include 'includes/adminsidebar.php'; ?>
<div class="admin-content min-h-screen flex flex-col items-center bg-gray-100 py-10">
    <div class="w-full max-w-3xl bg-white shadow-2xl rounded-2xl overflow-hidden">
        <?php if (isset($_SESSION['faq_success'])): ?>
            <div id="faq-success-message" class="w-full flex justify-center z-10">
                <div class="max-w-2xl w-full bg-green-100 border border-green-300 text-green-800 px-6 py-4 rounded-lg shadow text-center text-base font-semibold mt-6 mb-2 transition-opacity duration-500">
                    <?php echo $_SESSION['faq_success']; unset($_SESSION['faq_success']); ?>
                </div>
            </div>
        <?php endif; ?>
        <!-- Tabs -->
        <div class="flex border-b border-gray-200 bg-gray-50">
            <button id="tab-faqs" class="tab-btn flex-1 flex items-center justify-center gap-2 py-4 px-6 text-center font-semibold text-gray-600 hover:bg-gray-100 focus:outline-none border-b-2 border-transparent transition-all text-lg" onclick="showTab('faqs')">
                <i class="fa-solid fa-list-ul text-blue-500"></i>
                <span>Manage FAQs</span>
            </button>
            <button id="tab-form" class="tab-btn flex-1 flex items-center justify-center gap-2 py-4 px-6 text-center font-semibold text-gray-600 hover:bg-gray-100 focus:outline-none border-b-2 border-transparent transition-all text-lg" onclick="showTab('form')">
                <i class="fa-solid fa-plus-circle text-blue-500"></i>
                <span>Add/Edit FAQ</span>
            </button>
        </div>
        <!-- Tab Contents -->
        <div id="tab-content-faqs" class="tab-content p-8 bg-white">
            <h2 class="text-2xl font-bold mb-6 text-gray-800 flex items-center gap-2"><i class="fa-solid fa-list-ul text-blue-500"></i> All FAQs</h2>
            <div class="overflow-x-auto rounded-xl shadow-sm">
                <table class="min-w-full table-auto border-collapse text-base">
                    <thead>
                        <tr class="bg-gray-100 text-gray-700">
                            <th class="px-4 py-3 text-left font-semibold">#</th>
                            <th class="px-4 py-3 text-left font-semibold">Question</th>
                            <th class="px-4 py-3 text-left font-semibold">Answer</th>
                            <th class="px-4 py-3 text-left font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (count($faqs) > 0): ?>
                        <?php foreach ($faqs as $i => $faq): ?>
                            <tr class="hover:bg-blue-50 transition">
                                <td class="px-4 py-3 align-top text-gray-600"><?php echo $i+1; ?></td>
                                <td class="px-4 py-3 align-top text-gray-800 font-medium"><?php echo htmlspecialchars($faq['question']); ?></td>
                                <td class="px-4 py-3 align-top text-gray-700"><?php echo nl2br(htmlspecialchars($faq['answer'])); ?></td>
                                <td class="px-4 py-3 align-top flex gap-2">
                                    <a href="admin_faq.php?edit=<?php echo $faq['id']; ?>" class="inline-flex items-center gap-1 bg-yellow-400 hover:bg-yellow-500 text-white px-3 py-1 rounded-lg transition text-sm font-semibold shadow-sm"><i class="fa-solid fa-pen"></i> Edit</a>
                                    <a href="admin_faq.php?delete=<?php echo $faq['id']; ?>" class="inline-flex items-center gap-1 bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-lg transition text-sm font-semibold shadow-sm" onclick="return confirm('Delete this FAQ?');"><i class="fa-solid fa-trash"></i> Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center text-gray-400 py-8">No FAQs found.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div id="tab-content-form" class="tab-content p-8 bg-white hidden">
            <h2 class="text-2xl font-bold mb-6 text-gray-800 flex items-center gap-2"><i class="fa-solid fa-plus-circle text-blue-500"></i> <?php echo $edit_faq ? 'Edit FAQ' : 'Add New FAQ'; ?></h2>
            <form method="post" class="space-y-6">
                <?php if ($edit_faq): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_faq['id']; ?>">
                <?php endif; ?>
                <div>
                    <label class="block text-base font-medium text-gray-700 mb-2">Question</label>
                    <input type="text" name="question" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:outline-none text-gray-800 bg-gray-50" required value="<?php echo $edit_faq ? htmlspecialchars($edit_faq['question']) : ''; ?>">
                </div>
                <div>
                    <label class="block text-base font-medium text-gray-700 mb-2">Answer</label>
                    <textarea name="answer" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:outline-none text-gray-800 bg-gray-50" rows="4" required><?php echo $edit_faq ? htmlspecialchars($edit_faq['answer']) : ''; ?></textarea>
                </div>
                <div class="flex items-center gap-3">
                    <button type="submit" name="<?php echo $edit_faq ? 'edit_faq' : 'add_faq'; ?>" class="inline-flex items-center gap-2 bg-primary hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-lg shadow transition text-base">
                        <i class="fa-solid fa-save"></i> <?php echo $edit_faq ? 'Update FAQ' : 'Add FAQ'; ?>
                    </button>
                    <?php if ($edit_faq): ?>
                        <a href="admin_faq.php" class="inline-flex items-center gap-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold px-4 py-2 rounded-lg transition text-base"><i class="fa-solid fa-xmark"></i> Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
// Tab switching logic
function showTab(tab) {
    document.getElementById('tab-content-faqs').classList.add('hidden');
    document.getElementById('tab-content-form').classList.add('hidden');
    document.getElementById('tab-faqs').classList.remove('border-blue-500', 'text-blue-600', 'bg-white');
    document.getElementById('tab-form').classList.remove('border-blue-500', 'text-blue-600', 'bg-white');
    if(tab === 'faqs') {
        document.getElementById('tab-content-faqs').classList.remove('hidden');
        document.getElementById('tab-faqs').classList.add('border-blue-500', 'text-blue-600', 'bg-white');
    } else {
        document.getElementById('tab-content-form').classList.remove('hidden');
        document.getElementById('tab-form').classList.add('border-blue-500', 'text-blue-600', 'bg-white');
    }
}
// Set default tab
showTab('<?php echo isset($edit_faq) && $edit_faq ? 'form' : 'faqs'; ?>');

// Auto-dismiss success message
window.addEventListener('DOMContentLoaded', function() {
    var msg = document.getElementById('faq-success-message');
    if (msg) {
        setTimeout(function() {
            msg.querySelector('div').classList.add('opacity-0');
            setTimeout(function() { msg.remove(); }, 500);
        }, 3000);
    }
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
