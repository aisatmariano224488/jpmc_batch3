<?php
// // Start session
// session_start();

// // Check if user is logged in
// if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
//     header("Location: admin_login.php");
//     exit;
// }

require_once '../includes/db_connection.php';

// Get stats for dashboard
$productCount = 0;
$industryCount = 0;
$serviceCount = 0;
$inquiryCount = 0;

// Count products
$sql = "SELECT COUNT(*) as count FROM products";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $productCount = $row['count'];
}

// Count industries
$sql = "SELECT COUNT(*) as count FROM industries";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $industryCount = $row['count'];
}

// Count services
$sql = "SELECT COUNT(*) as count FROM services";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $serviceCount = $row['count'];
}

// Recent products
$recentProducts = array();
$sql = "SELECT * FROM products ORDER BY created_at DESC LIMIT 5";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $recentProducts[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | James Polymers</title>
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
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        .admin-content {
            transition: margin-left 0.3s ease;
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
        <!-- Admin Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
                <p class="text-gray-600">Welcome back, Admin</p>
            </div>
        </div>
        
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="card-stats bg-white rounded-lg shadow p-6 flex items-center">
                <div class="rounded-full bg-blue-100 p-3 mr-4">
                    <i class="fas fa-box text-blue-500 text-xl"></i>
                </div>
                <div>
                    <p class="text-gray-600 text-sm">Total Products</p>
                    <h2 class="text-3xl font-bold text-gray-800"><?php echo $productCount; ?></h2>
                </div>
            </div>
            
            <div class="card-stats bg-white rounded-lg shadow p-6 flex items-center">
                <div class="rounded-full bg-green-100 p-3 mr-4">
                    <i class="fas fa-industry text-green-500 text-xl"></i>
                </div>
                <div>
                    <p class="text-gray-600 text-sm">Industries</p>
                    <h2 class="text-3xl font-bold text-gray-800"><?php echo $industryCount; ?></h2>
                </div>
            </div>
            
            <div class="card-stats bg-white rounded-lg shadow p-6 flex items-center">
                <div class="rounded-full bg-purple-100 p-3 mr-4">
                    <i class="fas fa-cogs text-purple-500 text-xl"></i>
                </div>
                <div>
                    <p class="text-gray-600 text-sm">Services</p>
                    <h2 class="text-3xl font-bold text-gray-800"><?php echo $serviceCount; ?></h2>
                </div>
            </div>
            
            <div class="card-stats bg-white rounded-lg shadow p-6 flex items-center">
                <div class="rounded-full bg-yellow-100 p-3 mr-4">
                    <i class="fas fa-users text-yellow-500 text-xl"></i>
                </div>
                <div>
                    <p class="text-gray-600 text-sm">Visitors (Monthly)</p>
                    <h2 class="text-3xl font-bold text-gray-800">2,461</h2>
                </div>
            </div>
        </div>
        
        <!-- Charts & Tables Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Chart -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Website Traffic</h3>
                <div class="h-80">
                    <canvas id="trafficChart"></canvas>
                </div>
            </div>
            
            <!-- Recent Products -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Recent Products</h3>
                    <a href="admin_products.php" class="text-primary hover:text-secondary text-sm font-medium">View All</a>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="bg-gray-50 text-gray-600 text-sm leading-normal">
                                <th class="py-3 px-4 text-left">Name</th>
                                <th class="py-3 px-4 text-left">Category</th>
                                <th class="py-3 px-4 text-left">Date Added</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 text-sm">
                            <?php foreach ($recentProducts as $product): ?>
                            <tr class="border-b border-gray-200 hover:bg-gray-50">
                                <td class="py-3 px-4"><?php echo htmlspecialchars($product['name']); ?></td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-1 rounded-full text-xs <?php 
                                        switch($product['category']) {
                                            case 'appliance': echo 'bg-blue-100 text-blue-800'; break;
                                            case 'automotive': echo 'bg-green-100 text-green-800'; break;
                                            case 'industrial': echo 'bg-purple-100 text-purple-800'; break;
                                            default: echo 'bg-gray-100 text-gray-800';
                                        }
                                    ?>">
                                        <?php echo ucfirst($product['category']); ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <?php echo date('M d, Y', strtotime($product['created_at'])); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <?php if (empty($recentProducts)): ?>
                            <tr>
                                <td colspan="3" class="py-4 text-center text-gray-500">No products found</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Quick Actions</h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                <a href="admin_products.php?action=new" class="bg-blue-50 hover:bg-blue-100 p-4 rounded-lg flex items-center transition duration-300">
                    <div class="rounded-full bg-blue-100 p-3 mr-3">
                        <i class="fas fa-plus text-blue-500"></i>
                    </div>
                    <span class="text-blue-800 font-medium">Add Product</span>
                </a>
                
                <a href="admin_industries.php?action=new" class="bg-green-50 hover:bg-green-100 p-4 rounded-lg flex items-center transition duration-300">
                    <div class="rounded-full bg-green-100 p-3 mr-3">
                        <i class="fas fa-plus text-green-500"></i>
                    </div>
                    <span class="text-green-800 font-medium">Add Industry</span>
                </a>
                
                <a href="admin_services.php?action=new" class="bg-purple-50 hover:bg-purple-100 p-4 rounded-lg flex items-center transition duration-300">
                    <div class="rounded-full bg-purple-100 p-3 mr-3">
                        <i class="fas fa-plus text-purple-500"></i>
                    </div>
                    <span class="text-purple-800 font-medium">Add Service</span>
                </a>
                
                <a href="admin_news.php?action=new" class="bg-yellow-50 hover:bg-yellow-100 p-4 rounded-lg flex items-center transition duration-300">
                    <div class="rounded-full bg-yellow-100 p-3 mr-3">
                        <i class="fas fa-plus text-yellow-500"></i>
                    </div>
                    <span class="text-yellow-800 font-medium">Add News</span>
                </a>
            </div>
        </div>
    </div>
    
    <!-- JavaScript for Charts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Traffic Chart
            const trafficCtx = document.getElementById('trafficChart').getContext('2d');
            const trafficChart = new Chart(trafficCtx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    datasets: [{
                        label: 'Monthly Visitors',
                        data: [1500, 1800, 2200, 1900, 2400, 2600, 2300, 2500, 2800, 3000, 2900, 3100],
                        backgroundColor: 'rgba(0, 102, 204, 0.1)',
                        borderColor: 'rgba(0, 102, 204, 1)',
                        borderWidth: 2,
                        tension: 0.4,
                        pointBackgroundColor: 'rgba(0, 102, 204, 1)',
                        pointRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                drawBorder: false
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        });
    </script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>