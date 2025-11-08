<?php
// Start session
// session_start();

// Enable error logging for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);
$debug_log = fopen("../logs/email_debug.log", "a");

// Include database connection
require_once '../includes/db_connection.php';

// Add Composer autoload for Brevo SDK
require_once __DIR__ . '/../vendor/autoload.php';

use Brevo\Client\Api\TransactionalEmailsApi;
use Brevo\Client\Configuration;
use Brevo\Client\Model\SendSmtpEmail;

// Handle email reply
if (isset($_POST['send_reply']) && isset($_POST['inquiry_id']) && isset($_POST['email']) && isset($_POST['subject']) && isset($_POST['message'])) {
    $inquiry_id = intval($_POST['inquiry_id']);
    $to_email = $conn->real_escape_string($_POST['email']);
    $subject = $conn->real_escape_string($_POST['subject']);
    $message_body = $conn->real_escape_string($_POST['message']);

    // Get recipient's name from the database
    $name_query = "SELECT name FROM inquiries WHERE id = $inquiry_id";
    $name_result = $conn->query($name_query);
    $recipient_name = '';
    if ($name_result && $name_result->num_rows > 0) {
        $recipient_name = $name_result->fetch_assoc()['name'];
    }
    
    // Build a dynamic and robust logo URL
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https" : "http";
    $domain = $_SERVER['HTTP_HOST'];
    $project_path = '/'; // Adjust if your project is not in a subdirectory
    $logo_url = $protocol . '://' . $domain . $project_path . '../assets/img/logo-whitebg.png';

    // Create email content using a template
    $email_content = "
    <html>
    <head>
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap');
            body { font-family: 'Roboto', sans-serif; line-height: 1.6; color: #333; background-color: #f9f9f9; margin: 0; padding: 20px; }
            .email-container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
            .email-header { background-color: #0066cc; padding: 20px; text-align: center; }
            .logo {width: 180px;height: 180px; /* Make height equal to width for a perfect circle */margin-bottom: 15px;
                    border-radius: 50%; /* Makes it round */ object-fit: cover;  /* Ensures the image fills the container nicely */}

            .email-content { padding: 30px; }
            h2 { color: #0066cc; margin-top: 0; font-size: 24px; font-weight: 600; }
            .message-box { background-color: #f5f5f5; padding: 20px; border-radius: 6px; border-left: 4px solid #0066cc; margin: 20px 0; }
            .email-footer { background-color: #f5f5f5; padding: 15px; text-align: center; font-size: 13px; color: #777; border-top: 1px solid #e5e5e5; }
        </style>
    </head>
    <body>
        <div class='email-container'>
            <div class='email-header'>
                <img src='" . $logo_url . "' alt='James Polymers Logo' class='logo' />
                <h2 style='color: white; margin: 0;'>Response to Your Inquiry</h2>
            </div>
            <div class='email-content'>
                <p>Dear " . htmlspecialchars($recipient_name) . ",</p>
                <p>Thank you for contacting James Polymers Manufacturing Corporation. Here is the response to your recent inquiry:</p>
                <div class='message-box'>
                    " . nl2br(htmlspecialchars($message_body)) . "
                </div>
                <p>If you have any further questions, please do not hesitate to reply to this email.</p>
                <p>Best regards,<br>The James Polymers Team</p>
            </div>
            <div class='email-footer'>
                <p>&copy; " . date('Y') . " James Polymers. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";

    // Prepare and send email with Brevo
    $brevo_api_key = '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';
    $config = Configuration::getDefaultConfiguration()->setApiKey('api-key', $brevo_api_key);
    $apiInstance = new TransactionalEmailsApi(new GuzzleHttp\Client(), $config);

    $sendSmtpEmail = new SendSmtpEmail([
        'subject' => $subject,
        'sender' => ['name' => 'JPMC', 'email' => 'danielrossevia@gmail.com'],
        'to' => [['email' => $to_email, 'name' => $recipient_name]],
        'htmlContent' => $email_content
    ]);

    try {
        $apiInstance->sendTransacEmail($sendSmtpEmail);
        $_SESSION['success_message'] = "Reply sent successfully.";

        // Update inquiry status to in-progress if it's new
        $status_query = "SELECT status FROM inquiries WHERE id = $inquiry_id";
        $status_result = $conn->query($status_query);
        if ($status_result && $status_result->num_rows > 0) {
            $status_row = $status_result->fetch_assoc();
            if ($status_row['status'] == 'new') {
                $update_status_query = "UPDATE inquiries SET status = 'in-progress' WHERE id = $inquiry_id";
                $conn->query($update_status_query);
            }
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Error sending email: " . $e->getMessage();
        fwrite($debug_log, date('Y-m-d H:i:s') . " - Brevo API Error: " . $e->getMessage() . "\n");
    }
    
    // Redirect to remove POST data
    header("Location: " . $_SERVER['PHP_SELF'] . (isset($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : ''));
    exit;
}

// Handle status update
if (isset($_POST['update_status']) && isset($_POST['inquiry_id']) && isset($_POST['status'])) {
    $inquiry_id = intval($_POST['inquiry_id']);
    $status = $conn->real_escape_string($_POST['status']);
    
    $update_query = "UPDATE inquiries SET status = '$status' WHERE id = $inquiry_id";
    if ($conn->query($update_query)) {
        $_SESSION['success_message'] = "Inquiry status updated successfully.";
    } else {
        $_SESSION['error_message'] = "Error updating status: " . $conn->error;
    }
    
    // Redirect to remove POST data
    header("Location: " . $_SERVER['PHP_SELF'] . (isset($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : ''));
    exit;
}

// Handle inquiry deletion
if (isset($_POST['delete_inquiry']) && isset($_POST['inquiry_id'])) {
    $inquiry_id = intval($_POST['inquiry_id']);
    
    $delete_query = "DELETE FROM inquiries WHERE id = $inquiry_id";
    if ($conn->query($delete_query)) {
        $_SESSION['success_message'] = "Inquiry deleted successfully.";
    } else {
        $_SESSION['error_message'] = "Error deleting inquiry: " . $conn->error;
    }
    
    // Redirect to remove POST data
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Pagination settings
$items_per_page = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $items_per_page;

// Build query based on filters
$where_clauses = array();
$params = array();

// Status filter
if (isset($_GET['status']) && !empty($_GET['status'])) {
    $status = $conn->real_escape_string($_GET['status']);
    $where_clauses[] = "status = '$status'";
    $params[] = "status=" . urlencode($_GET['status']);
}

// Date range filter
if (isset($_GET['date_range']) && !empty($_GET['date_range'])) {
    $date_range = $conn->real_escape_string($_GET['date_range']);
    if ($date_range == 'today') {
        $where_clauses[] = "DATE(date_submitted) = CURDATE()";
    } elseif ($date_range == 'week') {
        $where_clauses[] = "YEARWEEK(date_submitted, 1) = YEARWEEK(CURDATE(), 1)";
    } elseif ($date_range == 'month') {
        $where_clauses[] = "YEAR(date_submitted) = YEAR(CURDATE()) AND MONTH(date_submitted) = MONTH(CURDATE())";
    }
    $params[] = "date_range=" . urlencode($_GET['date_range']);
}

// Search filter
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $where_clauses[] = "(name LIKE '%$search%' OR email LIKE '%$search%' OR subject LIKE '%$search%' OR message LIKE '%$search%')";
    $params[] = "search=" . urlencode($_GET['search']);
}

// Combine where clauses
$where_sql = "";
if (!empty($where_clauses)) {
    $where_sql = "WHERE " . implode(" AND ", $where_clauses);
}

// Get total count for pagination
$count_sql = "SELECT COUNT(*) as total FROM inquiries $where_sql";
$count_result = $conn->query($count_sql);
$total_count = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_count / $items_per_page);
$total_inquiries = $total_count;

// Get inquiries with pagination
$sql = "SELECT * FROM inquiries $where_sql ORDER BY date_submitted DESC LIMIT $offset, $items_per_page";
$result = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Inquiries | Admin Dashboard</title>
    
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
        .inquiry-card {
            transition: all 0.3s ease;
        }
        .inquiry-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .status-new {
            background-color: #EFF6FF;
            border-left: 4px solid #3B82F6;
        }
        .status-in-progress {
            background-color: #FFFBEB;
            border-left: 4px solid #F59E0B;
        }
        .status-resolved {
            background-color: #ECFDF5;
            border-left: 4px solid #10B981;
        }
        .status-closed {
            background-color: #F3F4F6;
            border-left: 4px solid #6B7280;
        }
    </style>
</head>
<body class="bg-gray-50 flex">
    <?php include 'includes/adminsidebar.php'; ?>

    <div class="flex-1 ml-64">
        <div class="container mx-auto px-6 py-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-gray-800">Customer Inquiries</h1>
                <div class="flex space-x-2">
                <a href="admin_dashboard.php" class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded-lg transition">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
                </a>                <a href="admin_export_inquiries.php<?php
                    $export_params = array();
                    if (isset($_GET['status']) && !empty($_GET['status'])) {
                        $export_params[] = "status=" . urlencode($_GET['status']);
                    }
                    if (isset($_GET['date_range']) && !empty($_GET['date_range'])) {
                        $export_params[] = "date_range=" . urlencode($_GET['date_range']);
                    }
                    if (isset($_GET['search']) && !empty($_GET['search'])) {
                        $export_params[] = "search=" . urlencode($_GET['search']);
                    }
                    echo !empty($export_params) ? "?" . implode("&", $export_params) : "";
                ?>" class="bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-lg transition">
                    <i class="fas fa-file-export mr-2"></i> Export CSV
                </a>
            </div>
        </div>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div id="success-alert" class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                <p><?php echo $_SESSION['success_message']; ?></p>
                <?php unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                <p><?php echo $_SESSION['error_message']; ?></p>
                <?php unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>
        
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <?php
            // Get count of inquiries by status
            $status_counts = [];
            $status_query = "SELECT status, COUNT(*) as count FROM inquiries GROUP BY status";
            $status_result = $conn->query($status_query);
            while ($row = $status_result->fetch_assoc()) {
                $status_counts[$row['status']] = $row['count'];
            }
            
            $new_count = isset($status_counts['new']) ? $status_counts['new'] : 0;
            $in_progress_count = isset($status_counts['in-progress']) ? $status_counts['in-progress'] : 0;
            $resolved_count = isset($status_counts['resolved']) ? $status_counts['resolved'] : 0;
            $closed_count = isset($status_counts['closed']) ? $status_counts['closed'] : 0;
            ?>
            
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm text-gray-500 uppercase">New Inquiries</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo $new_count; ?></p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-inbox text-blue-500 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-yellow-500">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm text-gray-500 uppercase">In Progress</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo $in_progress_count; ?></p>
                    </div>
                    <div class="bg-yellow-100 p-3 rounded-full">
                        <i class="fas fa-spinner text-yellow-500 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm text-gray-500 uppercase">Resolved</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo $resolved_count; ?></p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <i class="fas fa-check-circle text-green-500 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-gray-500">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm text-gray-500 uppercase">Closed</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo $closed_count; ?></p>
                    </div>
                    <div class="bg-gray-100 p-3 rounded-full">
                        <i class="fas fa-archive text-gray-500 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Filter Inquiries</h2>
            <form action="admin_inquiries.php" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="status_filter" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select id="status_filter" name="status" class="w-full rounded-lg border-gray-300 p-2 border focus:ring-primary focus:border-primary">
                        <option value="">All Statuses</option>
                        <option value="new" <?php echo (isset($_GET['status']) && $_GET['status'] == 'new') ? 'selected' : ''; ?>>New</option>
                        <option value="in-progress" <?php echo (isset($_GET['status']) && $_GET['status'] == 'in-progress') ? 'selected' : ''; ?>>In Progress</option>
                        <option value="resolved" <?php echo (isset($_GET['status']) && $_GET['status'] == 'resolved') ? 'selected' : ''; ?>>Resolved</option>
                        <option value="closed" <?php echo (isset($_GET['status']) && $_GET['status'] == 'closed') ? 'selected' : ''; ?>>Closed</option>
                    </select>
                </div>
                <div>
                    <label for="date_filter" class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
                    <select id="date_filter" name="date_range" class="w-full rounded-lg border-gray-300 p-2 border focus:ring-primary focus:border-primary">
                        <option value="">All Time</option>
                        <option value="today" <?php echo (isset($_GET['date_range']) && $_GET['date_range'] == 'today') ? 'selected' : ''; ?>>Today</option>
                        <option value="week" <?php echo (isset($_GET['date_range']) && $_GET['date_range'] == 'week') ? 'selected' : ''; ?>>This Week</option>
                        <option value="month" <?php echo (isset($_GET['date_range']) && $_GET['date_range'] == 'month') ? 'selected' : ''; ?>>This Month</option>
                    </select>
                </div>
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" id="search" name="search" placeholder="Search by name, email or subject..." class="w-full rounded-lg border-gray-300 p-2 border focus:ring-primary focus:border-primary" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="bg-primary hover:bg-secondary text-white py-2 px-4 rounded-lg transition w-full">
                        <i class="fas fa-filter mr-2"></i> Apply Filters
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Inquiries List -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-800">Inquiries (<?php echo $total_inquiries; ?>)</h2>
            </div>
            
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): 
                    $status_class = '';
                    switch($row['status']) {
                        case 'new':
                            $status_class = 'status-new';
                            break;
                        case 'in-progress':
                            $status_class = 'status-in-progress';
                            break;
                        case 'resolved':
                            $status_class = 'status-resolved';
                            break;
                        case 'closed':
                            $status_class = 'status-closed';
                            break;
                        default:
                            $status_class = 'status-new';
                    }
                ?>
                    <div class="inquiry-card p-6 border-b border-gray-200 <?php echo $status_class; ?>">
                        <div class="flex flex-col md:flex-row justify-between">
                            <div class="flex-1">
                                <div class="flex items-center mb-2">
                                    <h3 class="text-lg font-semibold text-gray-800"><?php echo htmlspecialchars($row['name']); ?></h3>
                                    <span class="ml-3 text-sm text-gray-500"><?php echo htmlspecialchars($row['email']); ?></span>
                                    <?php if ($row['phone']): ?>
                                    <span class="ml-3 text-sm text-gray-500">
                                        <i class="fas fa-phone-alt text-gray-400 mr-1"></i> <?php echo htmlspecialchars($row['phone']); ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="flex items-center mb-2">
                                    <span class="inline-block bg-blue-100 text-blue-800 text-xs font-semibold px-2 py-1 rounded-full mr-2">
                                        <?php echo htmlspecialchars(ucfirst($row['subject'])); ?>
                                    </span>
                                    <span class="text-sm text-gray-500">
                                        <i class="far fa-clock text-gray-400 mr-1"></i> 
                                        <?php echo date('M j, Y g:i A', strtotime($row['date_submitted'])); ?>
                                    </span>
                                    <?php if ($row['company']): ?>
                                    <span class="ml-3 text-sm text-gray-500">
                                        <i class="far fa-building text-gray-400 mr-1"></i> 
                                        <?php echo htmlspecialchars($row['company']); ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="text-gray-600 mt-2">
                                    <?php 
                                    // Show truncated message with "Read more" option
                                    $message = htmlspecialchars($row['message']);
                                    $truncated = strlen($message) > 150;
                                    echo '<div class="message-preview">' . ($truncated ? substr($message, 0, 150) . '...' : $message) . '</div>';
                                    if ($truncated): 
                                    ?>
                                    <div class="message-full hidden"><?php echo nl2br($message); ?></div>
                                    <button class="toggle-message text-primary hover:text-secondary text-sm mt-1" data-action="expand">
                                        Read more <i class="fas fa-chevron-down ml-1"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="flex items-center mt-4 md:mt-0 space-x-2">
                                <form method="POST" class="inline">
                                    <input type="hidden" name="inquiry_id" value="<?php echo $row['id']; ?>">
                                    <select name="status" onchange="this.form.submit()" class="rounded-lg border-gray-300 p-2 border text-sm focus:ring-primary focus:border-primary">
                                        <option value="new" <?php echo $row['status'] == 'new' ? 'selected' : ''; ?>>New</option>
                                        <option value="in-progress" <?php echo $row['status'] == 'in-progress' ? 'selected' : ''; ?>>In Progress</option>
                                        <option value="resolved" <?php echo $row['status'] == 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                        <option value="closed" <?php echo $row['status'] == 'closed' ? 'selected' : ''; ?>>Closed</option>
                                    </select>
                                    <input type="hidden" name="update_status" value="1">
                                </form>
                                
                                <button class="reply-btn bg-primary hover:bg-secondary text-white py-1 px-3 rounded-lg transition text-sm">
                                    <i class="fas fa-reply mr-1"></i> Reply
                                </button>
                                
                                <form method="POST" class="inline delete-form">
                                    <input type="hidden" name="inquiry_id" value="<?php echo $row['id']; ?>">
                                    <input type="hidden" name="delete_inquiry" value="1">
                                    <button type="button" class="delete-btn bg-red-500 hover:bg-red-600 text-white py-1 px-3 rounded-lg transition text-sm">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="p-6 flex justify-center">
                    <nav class="flex space-x-2">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?page=<?php echo $i; ?><?php echo isset($_GET['status']) ? '&status=' . htmlspecialchars($_GET['status']) : ''; ?><?php echo isset($_GET['date_range']) ? '&date_range=' . htmlspecialchars($_GET['date_range']) : ''; ?><?php echo isset($_GET['search']) ? '&search=' . htmlspecialchars($_GET['search']) : ''; ?>" 
                               class="px-3 py-1 rounded-md <?php echo ($i == $page) ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                    </nav>
                </div>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="p-8 text-center">
                    <img src="assets/img/empty-inbox.svg" alt="No inquiries" class="w-32 h-32 mx-auto mb-4 opacity-50">
                    <p class="text-lg text-gray-500">No inquiries found.</p>
                    <?php if (isset($_GET['status']) || isset($_GET['date_range']) || isset($_GET['search'])): ?>
                        <a href="admin_inquiries.php" class="text-primary hover:text-secondary mt-2 inline-block">
                            <i class="fas fa-times-circle mr-1"></i> Clear filters
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>    </div>
</div>
    
    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg p-6 max-w-md w-full">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Confirm Deletion</h3>
            <p class="text-gray-600 mb-6">Are you sure you want to delete this inquiry? This action cannot be undone.</p>
            <div class="flex justify-end space-x-3">
                <button id="cancelDelete" class="bg-gray-300 hover:bg-gray-400 text-gray-800 py-2 px-4 rounded-lg transition">Cancel</button>
                <button id="confirmDelete" class="bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded-lg transition">Delete</button>
            </div>
        </div>
    </div>
    
    <!-- Reply Modal -->
    <div id="replyModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg p-6 max-w-2xl w-full">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Reply to Inquiry</h3>
            <form id="replyForm" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?><?php echo isset($_SERVER['QUERY_STRING']) ? '?' . htmlspecialchars($_SERVER['QUERY_STRING']) : ''; ?>">
                <input type="hidden" id="inquiry_id" name="inquiry_id">
                <div class="mb-4">
                    <label for="reply_to" class="block text-sm font-medium text-gray-700 mb-1">To</label>
                    <input type="email" id="reply_to" name="email" class="w-full rounded-lg border-gray-300 p-2 border focus:ring-primary focus:border-primary" readonly>
                </div>
                <div class="mb-4">
                    <label for="reply_subject" class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                    <input type="text" id="reply_subject" name="subject" class="w-full rounded-lg border-gray-300 p-2 border focus:ring-primary focus:border-primary" required>
                </div>
                <div class="mb-4">
                    <label for="reply_message" class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                    <textarea id="reply_message" name="message" rows="6" class="w-full rounded-lg border-gray-300 p-2 border focus:ring-primary focus:border-primary" required></textarea>
                </div>
                <input type="hidden" name="send_reply" value="1">
                <div class="flex justify-end space-x-3">
                    <button type="button" id="cancelReply" class="bg-gray-300 hover:bg-gray-400 text-gray-800 py-2 px-4 rounded-lg transition">Cancel</button>
                    <button type="submit" id="sendReply" class="bg-primary hover:bg-secondary text-white py-2 px-4 rounded-lg transition">
                        <i class="fas fa-paper-plane mr-2"></i> Send Reply
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-hide success message
            const successAlert = document.getElementById('success-alert');
            if (successAlert) {
                setTimeout(() => {
                    successAlert.style.transition = 'opacity 0.5s ease-out';
                    successAlert.style.opacity = '0';
                    setTimeout(() => {
                        successAlert.remove();
                    }, 500); // wait for fade-out to finish
                }, 3000);
            }

            // Toggle message expandability
            const toggleButtons = document.querySelectorAll('.toggle-message');
            toggleButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const card = this.closest('.inquiry-card');
                    const preview = card.querySelector('.message-preview');
                    const full = card.querySelector('.message-full');
                    const action = this.getAttribute('data-action');
                    
                    if (action === 'expand') {
                        preview.classList.add('hidden');
                        full.classList.remove('hidden');
                        this.innerHTML = 'Show less <i class="fas fa-chevron-up ml-1"></i>';
                        this.setAttribute('data-action', 'collapse');
                    } else {
                        preview.classList.remove('hidden');
                        full.classList.add('hidden');
                        this.innerHTML = 'Read more <i class="fas fa-chevron-down ml-1"></i>';
                        this.setAttribute('data-action', 'expand');
                    }
                });
            });
            
            // Handle delete confirmation
            const deleteModal = document.getElementById('deleteModal');
            const deleteButtons = document.querySelectorAll('.delete-btn');
            const cancelDelete = document.getElementById('cancelDelete');
            const confirmDelete = document.getElementById('confirmDelete');
            let activeDeleteForm = null;
            
            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    activeDeleteForm = this.closest('.delete-form');
                    deleteModal.classList.remove('hidden');
                });
            });
            
            cancelDelete.addEventListener('click', function() {
                deleteModal.classList.add('hidden');
                activeDeleteForm = null;
            });
            
            confirmDelete.addEventListener('click', function() {
                if (activeDeleteForm) {
                    activeDeleteForm.submit();
                }
            });
              // Handle reply modal
            const replyModal = document.getElementById('replyModal');
            const replyButtons = document.querySelectorAll('.reply-btn');
            const cancelReply = document.getElementById('cancelReply');
            const sendReply = document.getElementById('sendReply');
            
            replyButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const card = this.closest('.inquiry-card');
                    // Get the email correctly by finding the first email element in the card
                    const nameEmailSection = card.querySelector('.flex.items-center.mb-2');
                    const email = nameEmailSection.querySelector('.text-sm.text-gray-500').textContent.trim();
                    const subject = 'Re: ' + card.querySelector('.bg-blue-100').textContent.trim();
                    
                    // Clear any previous values
                    document.getElementById('reply_message').value = '';
                    
                    // Set new values
                    document.getElementById('reply_to').value = email;
                    document.getElementById('reply_subject').value = subject;
                    
                    // Set the inquiry ID value directly in the hidden input field
                    const inquiryId = card.querySelector('input[name="inquiry_id"]').value;
                    document.getElementById('inquiry_id').value = inquiryId;
                    
                    // Show the modal
                    replyModal.classList.remove('hidden');
                });
            });
            
            cancelReply.addEventListener('click', function() {
                replyModal.classList.add('hidden');
            });
            
            // Close modals when clicking outside
            window.addEventListener('click', function(e) {
                if (e.target === deleteModal) {
                    deleteModal.classList.add('hidden');
                    activeDeleteForm = null;
                }
                
                if (e.target === replyModal) {
                    replyModal.classList.add('hidden');
                }
            });
        });
    </script>
</body>
</html>