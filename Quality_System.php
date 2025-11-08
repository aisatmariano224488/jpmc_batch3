<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quality System - JPMC</title>
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
        .dropdown:hover .dropdown-menu {
            display: block;
        }

        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
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
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: -1;
            background: url('images/quality/background.png') center center / cover no-repeat fixed;
            opacity: 0.4;
            pointer-events: none;
        }
    </style>
</head>

<body>
    <?php include 'header.php'; ?>

    <!-- Chatbot Integration -->
    <?php include 'chatbot.php'; ?>

    <!-- Header Section -->
    <div class="w-full py-6 flex flex-col items-center justify-center relative" style="background: url('images/quality/header.png') center center / cover no-repeat; min-height: 130px;">
        <div class="absolute inset-0" style="background: rgba(37,80,200,0.38);"></div>
        <h1 class="relative text-white text-4xl font-bold tracking-wider z-10" style="letter-spacing:2px; text-shadow: 1px 1px 4px #222;" data-aos="zoom-in">QUALITY SYSTEM</h1>
        <div class="absolute left-0 bottom-0 h-1 bg-red-500 rounded-full z-10" style="width: 300px;"></div>
    </div>

    <!-- Main Content: Quality System Overview -->
    <div class="container mx-auto px-4 py-16">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
            <!-- Left Column: Quality Certifications -->
            <div class="bg-white rounded-lg shadow-lg p-8" data-aos="fade-up">
                <h2 class="text-2xl font-bold mb-6 text-primary">Our Certifications</h2>
                <div class="space-y-6">
                    <div class="flex items-start space-x-4">
                        <i class="fas fa-certificate text-3xl text-primary mt-1"></i>
                        <div>
                            <h3 class="text-xl font-semibold mb-2">ISO 9001:2015</h3>
                            <p class="text-gray-600">Certified for Quality Management Systems, demonstrating our commitment to consistent quality and continuous improvement.</p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-4">
                        <i class="fas fa-award text-3xl text-primary mt-1"></i>
                        <div>
                            <h3 class="text-xl font-semibold mb-2">ISO 14001:2015</h3>
                            <p class="text-gray-600">Environmental Management System certification, showcasing our dedication to environmental responsibility.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Quality Policy -->
            <div class="bg-white rounded-lg shadow-lg p-8" data-aos="fade-up">
                <h2 class="text-2xl font-bold mb-6 text-primary">Quality Policy</h2>
                <div class="space-y-4">
                    <p class="text-gray-600">We are committed to delivering excellence in every aspect of our operations through:</p>
                    <ul class="list-disc list-inside space-y-2 text-gray-600">
                        <li>Continuous improvement of our quality management system</li>
                        <li>Meeting and exceeding customer requirements</li>
                        <li>Compliance with applicable regulations and standards</li>
                        <li>Employee training and development</li>
                        <li>Regular monitoring and measurement of quality objectives</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Quality Metrics Section -->
        <div class="mt-16">
            <h2 class="text-3xl font-bold text-center mb-12" data-aos="fade-up">Quality Performance Metrics</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8" data-aos="fade-up">
                <div class="bg-white rounded-lg shadow-lg p-6 text-center">
                    <i class="fas fa-chart-line text-4xl text-primary mb-4"></i>
                    <h3 class="text-xl font-bold mb-2">Customer Satisfaction</h3>
                    <p class="text-3xl font-bold text-primary">98%</p>
                    <p class="text-gray-600 mt-2">Based on customer feedback surveys</p>
                </div>
                <div class="bg-white rounded-lg shadow-lg p-6 text-center">
                    <i class="fas fa-check-circle text-4xl text-primary mb-4"></i>
                    <h3 class="text-xl font-bold mb-2">Quality Compliance</h3>
                    <p class="text-3xl font-bold text-primary">99.9%</p>
                    <p class="text-gray-600 mt-2">Regulatory compliance rate</p>
                </div>
                <div class="bg-white rounded-lg shadow-lg p-6 text-center">
                    <i class="fas fa-clock text-4xl text-primary mb-4"></i>
                    <h3 class="text-xl font-bold mb-2">Response Time</h3>
                    <p class="text-3xl font-bold text-primary">24h</p>
                    <p class="text-gray-600 mt-2">Average response to quality issues</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'footer.php'; ?>
</body>

</html>