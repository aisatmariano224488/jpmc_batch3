<?php
// Include functions if they're needed
include_once 'includes/functions.php';

// Current page
$page = '404';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found | James Polymers</title>
    <base href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/'; ?>">

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
    <?php include_once 'header.php'; ?>

    <section class="py-20 bg-white">
        <div class="container mx-auto px-4 text-center">
            <div class="mb-10">
                <i class="fas fa-exclamation-circle text-primary text-8xl mb-6"></i>
                <h1 class="text-5xl font-bold text-gray-800 mb-4">404</h1>
                <h2 class="text-2xl font-bold text-gray-700 mb-6">Page Not Found</h2>
                <p class="text-gray-600 max-w-xl mx-auto mb-8">
                    Sorry, the page you are looking for doesn't exist or has been moved.
                </p>
                <div class="flex justify-center gap-4">
                    <a href="./index.php" class="bg-primary hover:bg-secondary text-white font-bold py-3 px-8 rounded-lg transition duration-300">
                        Return to Home
                    </a>
                    <a href="./contact.php" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-3 px-8 rounded-lg transition duration-300">
                        Contact Us
                    </a>
                </div>
            </div>
        </div>
    </section>

    <?php include_once 'footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>