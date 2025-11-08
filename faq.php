<?php
require_once 'includes/db_connection.php';

require_once 'page_config.php';
$config = get_page_config('faq');


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch FAQs
$faqs = [];
$result = $conn->query("SELECT * FROM faqs ORDER BY created_at DESC");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $faqs[] = $row;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ | James Polymers - High Performance Polymer Solutions</title>

    <!--Tab Icon-->
    <link rel="icon" type="image/png" href="assets/img/tab_icon.png">

    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        <?php include 'includes/css/faq.css'; ?>
    </style>
</head>

<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 min-h-screen industrial-bg">
    <?php include 'header.php'; ?>

    <!-- chatbot.php -->
    <?php include 'chatbot.php'; ?>

    <!-- Hero Section -->
    <div class="hero-gradient relative overflow-hidden">
        <div class="absolute inset-0 bg-black opacity-30"></div>
        <div class="absolute inset-0" style="background: url('<?php echo $config['header_bg']; ?>') center center / cover no-repeat;"></div>
        <div class="relative z-10 container mx-auto px-4 py-24">
            <div class="text-center max-w-4xl mx-auto">
                <div class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm rounded-full px-6 py-2 mb-6">
                    <i class="fas fa-cogs text-blue-300"></i>
                    <span class="text-blue-100 font-medium">Manufacturing Excellence</span>
                </div>
                <h1 class="text-5xl md:text-7xl font-bold text-white mb-8 leading-tight" style="text-shadow: 0 4px 12px rgba(0,0,0,0.4);">
                    <?php echo $config['header_title']; ?>
                </h1>
                <p class="text-xl md:text-2xl text-blue-100 mb-10 leading-relaxed max-w-3xl mx-auto">
                    Expert answers to your questions about our advanced plastic and rubber manufacturing processes, quality standards, and industry solutions
                </p>
                <div class="flex justify-center mb-8">
                    <div class="w-32 h-1 bg-gradient-to-r from-blue-400 via-purple-400 to-cyan-400 rounded-full"></div>
                </div>
                <div class="flex flex-wrap justify-center gap-4 text-blue-100">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-shield-alt"></i>
                        <span>ISO Certified</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fas fa-clock"></i>
                        <span>24/7 Support</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fas fa-award"></i>
                        <span>Industry Leader</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Section Removed for the mean time -->
    <!-- <div class="container mx-auto px-4 -mt-12 relative z-20">
        <div class="search-container p-8 max-w-3xl mx-auto">
            <form id="faq-search-form" autocomplete="off">
                <div class="flex items-center gap-6">
                    <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-purple-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-search text-white text-xl"></i>
                    </div>
                    <input type="text" id="faqSearch" placeholder="Search for manufacturing questions, quality standards, or technical specifications..." class="search-input flex-1 text-lg">
                    <button type="submit" class="btn-primary text-white px-8 py-3 rounded-xl font-semibold text-lg">
                        <i class="fas fa-search mr-2"></i>
                        Search
                    </button>
                </div>
            </form>
        </div>
    </div> -->

    <!-- Stats Section -->
    <div class="container mx-auto px-4 py-12">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto mb-16">
            <div class="stats-card p-8 text-center animate-fade-in" style="animation-delay: 0.1s;">
                <div class="w-16 h-16 bg-gradient-to-r from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-question-circle text-2xl text-white"></i>
                </div>
                <div class="text-4xl font-bold text-blue-600 mb-2"><?php echo count($faqs); ?></div>
                <div class="text-gray-700 font-semibold text-lg">Expert Answers</div>
                <div class="text-gray-500 text-sm mt-2">Comprehensive knowledge base</div>
            </div>
            <div class="stats-card p-8 text-center animate-fade-in" style="animation-delay: 0.2s;">
                <div class="w-16 h-16 bg-gradient-to-r from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-headset text-2xl text-white"></i>
                </div>
                <div class="text-4xl font-bold text-purple-600 mb-2">24/7</div>
                <div class="text-gray-700 font-semibold text-lg">Technical Support</div>
                <div class="text-gray-500 text-sm mt-2">Round-the-clock assistance</div>
            </div>
            <div class="stats-card p-8 text-center animate-fade-in" style="animation-delay: 0.3s;">
                <div class="w-16 h-16 bg-gradient-to-r from-green-500 to-green-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-star text-2xl text-white"></i>
                </div>
                <div class="text-4xl font-bold text-green-600 mb-2">100%</div>
                <div class="text-gray-700 font-semibold text-lg">Quality Assurance</div>
                <div class="text-gray-500 text-sm mt-2">ISO certified processes</div>
            </div>
        </div>
    </div>

    <!-- Main Content: FAQs -->
    <div class="container mx-auto px-4 py-16">
        <div class="faq-container p-10 max-w-5xl mx-auto">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">Frequently Asked Questions</h2>
                <p class="text-gray-600 text-lg max-w-2xl mx-auto">
                    Get expert insights into our manufacturing processes, quality standards, and industry solutions
                </p>
            </div>
            <div id="faq-list">
                <?php if (count($faqs) > 0): ?>
                    <div class="space-y-6">
                        <?php foreach ($faqs as $index => $faq): ?>
                            <div class="faq-item animate-fade-in" data-question="<?php echo htmlspecialchars(strtolower($faq['question'])); ?>" data-answer="<?php echo htmlspecialchars(strtolower($faq['answer'])); ?>" style="animation-delay: <?php echo $index * 0.1; ?>s;">
                                <div class="faq-question">
                                    <div class="faq-icon">
                                        <i class="fa-solid fa-question"></i>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="text-lg font-semibold text-gray-800 leading-relaxed">
                                            <?php echo htmlspecialchars($faq['question']); ?>
                                        </h3>
                                    </div>
                                    <div class="faq-chevron">
                                        <i class="fas fa-chevron-down"></i>
                                    </div>
                                </div>
                                <div class="faq-answer">
                                    <div class="faq-answer-content">
                                        <div class="prose prose-lg max-w-none">
                                            <?php echo nl2br(htmlspecialchars($faq['answer'])); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-20">
                        <div class="w-32 h-32 bg-gradient-to-r from-blue-100 to-purple-100 rounded-full flex items-center justify-center mx-auto mb-8">
                            <i class="fas fa-industry text-6xl text-blue-500"></i>
                        </div>
                        <h3 class="text-3xl font-bold text-gray-800 mb-4">Knowledge Base Under Construction</h3>
                        <p class="text-gray-600 text-xl mb-8">We're building a comprehensive FAQ section with insights into our manufacturing processes.</p>
                        <div class="flex flex-col sm:flex-row gap-4 justify-center">
                            <a href="contact.php" class="btn-primary text-white px-6 py-3 rounded-lg font-semibold inline-flex items-center gap-2">
                                <i class="fas fa-envelope"></i>
                                Contact Us
                            </a>
                            <a href="tel:+1234567890" class="btn-secondary text-white px-6 py-3 rounded-lg font-semibold inline-flex items-center gap-2">
                                <i class="fas fa-phone"></i>
                                Call Now
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <script>
                // FAQ client-side search/filter
                document.addEventListener('DOMContentLoaded', function() {
                    // Wrap search bar in a form if not already
                    var searchContainer = document.querySelector('.search-container');
                    if (searchContainer && !searchContainer.querySelector('form')) {
                        var form = document.createElement('form');
                        form.id = 'faq-search-form';
                        form.autocomplete = 'off';
                        var flexDiv = searchContainer.querySelector('.flex');
                        if (flexDiv) {
                            searchContainer.replaceChild(form, flexDiv);
                            form.appendChild(flexDiv);
                        }
                    }
                    var form = document.getElementById('faq-search-form');
                    var input = document.getElementById('faqSearch');
                    var faqList = document.getElementById('faq-list');
                    if (form && input && faqList) {
                        function filterFaqs(query) {
                            var items = faqList.querySelectorAll('.faq-item');
                            var q = query.trim().toLowerCase();
                            var anyVisible = false;
                            items.forEach(function(item) {
                                var question = item.getAttribute('data-question') || '';
                                var answer = item.getAttribute('data-answer') || '';
                                if (!q || question.includes(q) || answer.includes(q)) {
                                    item.style.display = '';
                                    anyVisible = true;
                                } else {
                                    item.style.display = 'none';
                                }
                            });
                            // Optionally show a message if nothing matches
                            var noResultId = 'faq-no-result';
                            var noResult = document.getElementById(noResultId);
                            if (!anyVisible) {
                                if (!noResult) {
                                    noResult = document.createElement('div');
                                    noResult.id = noResultId;
                                    noResult.className = 'text-center text-gray-500 text-xl py-8';
                                    noResult.textContent = 'No FAQs matched your search.';
                                    faqList.appendChild(noResult);
                                }
                            } else {
                                if (noResult) noResult.remove();
                            }
                        }
                        form.addEventListener('submit', function(e) {
                            e.preventDefault();
                            filterFaqs(input.value);
                        });
                        input.addEventListener('input', function() {
                            filterFaqs(input.value);
                        });
                    }
                });
            </script>
        </div>
    </div>

    <!-- Contact CTA Section -->
    <div class="cta-gradient py-20 mt-20 relative">
        <div class="relative z-10 container mx-auto px-4 text-center">
            <div class="max-w-4xl mx-auto">
                <div class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm rounded-full px-6 py-2 mb-6">
                    <i class="fas fa-cogs text-blue-300"></i>
                    <span class="text-blue-100 font-medium">Technical Support</span>
                </div>
                <h2 class="text-4xl md:text-5xl font-bold text-white mb-8">Need Technical Support?</h2>
                <p class="text-xl md:text-2xl text-blue-100 mb-12 max-w-3xl mx-auto leading-relaxed">
                    Our manufacturing team is ready to provide detailed technical support and answer your specific questions about our processes and products.
                </p>
                <div class="flex flex-col sm:flex-row gap-6 justify-center">
                    <a href="contact.php" class="btn-primary text-white px-10 py-4 rounded-xl font-semibold text-lg inline-flex items-center gap-3">
                        <i class="fas fa-envelope"></i>
                        Contact Us
                    </a>
                    <a href="tel:+1234567890" class="btn-secondary text-white px-10 py-4 rounded-xl font-semibold text-lg inline-flex items-center gap-3">
                        <i class="fas fa-phone"></i>
                        Call Technical Support
                    </a>
                </div>
                <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-8 text-blue-100">
                    <div class="flex items-center justify-center gap-3">
                        <i class="fas fa-certificate text-2xl"></i>
                        <span class="font-semibold">ISO Certified</span>
                    </div>
                    <div class="flex items-center justify-center gap-3">
                        <i class="fas fa-clock text-2xl"></i>
                        <span class="font-semibold">24/7 Availability</span>
                    </div>
                    <div class="flex items-center justify-center gap-3">
                        <i class="fas fa-users text-2xl"></i>
                        <span class="font-semibold">Professional Team</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'footer.php'; ?>

    <script src="includes/javascript/faq.js" defer></script>
</body>

</html>