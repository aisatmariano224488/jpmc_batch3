<?php
include 'page_config.php';
$config = get_page_config('shop');
?>
<!DOCTYPE html>
<!-- <html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop | James Polymers - High Performance Polymer Solutions</title>
        
    <!--Tab Icon-->
<!-- <link rel="icon" type="image/png" href="assets/img/tab_icon.png">
    
        <!-- Bootstrap CSS -->
<!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Tailwind CSS -->
<!-- <script src="https://cdn.tailwindcss.com"></script>
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
<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        .dropdown:hover .dropdown-menu {
            display: block;
        }
        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .industry-card:hover {
            transform: scale(1.05);
        }
        body {
            position: relative;
            background: none !important;
        }
        body::before {
            content: "";
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            z-index: -1;
            background: url('<?php echo isset(
                                    $config["main_bg"]
                                ) && $config["main_bg"] ? $config["main_bg"] : "images/sustainability/jpmclogo.png"; ?>') center center / contain no-repeat fixed;
            opacity: 0.4;
            pointer-events: none;
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>   

<!-- Header Section -->
<!-- <div class="w-full py-6 flex flex-col items-center justify-center relative" style="background: url('images/sustainability/header.png') center center / cover no-repeat; min-height: 130px;">
    <div class="absolute inset-0" style="background: rgba(37,80,200,0.38);"></div>
    <h1 class="relative text-white text-4xl font-bold tracking-wider z-10" style="letter-spacing:2px; text-shadow: 1px 1px 4px #222;">SHOP</h1>
    <div class="absolute left-0 bottom-0 h-1 bg-red-500 rounded-full z-10" style="width: 300px;"></div>
</div>

<!-- Main Content: Side by Side Badges and Coming Soon -->
<!-- <div class="relative z-10 flex flex-row justify-center items-center py-16 gap-12" style="min-height: 60vh;">
    <!-- Left Badge -->
<!-- <img 
        src="images/sustainability/beslogo.png" 
        alt="Best Award Badge" 
        class="w-72 h-72 object-cover rounded-full" 
    />
    <!-- Coming Soon -->
<!-- <div class="flex items-center justify-center">
        <img src="images/sustainability/comingsoon.jfif" alt="Coming Soon" class="w-72 h-72 object-contain opacity-90" />
    </div>
    <!-- Right Badge -->
<!-- <img 
        src="images/sustainability/beslogo.png" 
        alt="Best Award Badge" 
        class="w-72 h-72 object-cover rounded-full" 
    />
</div>

<!-- Floating Chat Icon -->
<!-- <a href="#" class="fixed bottom-6 right-6 z-50 bg-white rounded-full shadow-lg p-2 border border-gray-200 hover:bg-gray-100 transition-all">
    <img src="assets/img/csr4.png" alt="Chat Support" class="w-14 h-14 rounded-full object-cover" />
</a>

<?php include 'footer.php'; ?>
</body>
</html>