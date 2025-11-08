<!-- <?php
// Start session if not already started
// if (session_status() === PHP_SESSION_NONE) {
//     session_start();
// }

// // Check if user is logged in
// if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
//     header("Location: admin_login.php");
//     exit;
// }
// ?> -->

<!-- Admin Sidebar -->
<div class="admin-sidebar bg-gray-900 text-white h-screen fixed left-0 top-0 w-64 overflow-y-auto transition-all duration-300 z-50" id="adminSidebar">
    <!-- Sidebar Header -->
    <div class="p-4 border-b border-gray-800 flex items-center justify-between">
        <div class="flex items-center">
            <img src="../assets/img/logo.jpg" alt="JPMC Logo" class="h-10 w-auto mr-3">
            <h1 class="text-xl font-bold">Admin Panel</h1>
        </div>
        <button id="toggleSidebar" class="text-white focus:outline-none lg:hidden">
            <i class="fas fa-times"></i>
        </button>
    </div>
    
    <!-- Admin Info -->
    <div class="p-4 border-b border-gray-800">
        <div class="flex items-center mb-3">
            <div class="w-10 h-10 rounded-full bg-primary text-white flex items-center justify-center text-xl font-semibold mr-3">
                <i class="fas fa-user"></i>
            </div>
            <div>
                <p class="font-medium text-sm" id="adminName">
                    <?php 
                    if(isset($_SESSION['admin_name'])) {
                        echo $_SESSION['admin_name'];
                    } else {
                        echo 'Administrator';
                    }
                    ?>
                </p>
                <p class="text-xs text-gray-400">Admin</p>
            </div>
        </div>
    </div>
    
    <!-- Navigation Menu -->
    <nav class="mt-4">
        <p class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Main</p>
        <ul>
            <li>
                <a href="admin.php" class="flex items-center px-4 py-3 text-gray-300 hover:bg-primary hover:text-white transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'admin.php' ? 'bg-primary text-white' : ''; ?>">
                    <i class="fas fa-tachometer-alt w-5 mr-3"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="./admin_products.php" class="flex items-center px-4 py-3 text-gray-300 hover:bg-primary hover:text-white transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'admin_products.php' ? 'bg-primary text-white' : ''; ?>">
                    <i class="fas fa-box w-5 mr-3"></i>
                    <span>Products</span>
                </a>
            </li>
            <li class="relative">
                <button class="w-full flex items-center justify-between px-4 py-3 text-gray-300 hover:bg-primary hover:text-white transition-colors <?php echo in_array(basename($_SERVER['PHP_SELF']), ['admin_industries.php', 'admin_industry_categories.php']) ? 'bg-primary text-white' : ''; ?>">
                    <div class="flex items-center">
                        <i class="fas fa-industry w-5 mr-3"></i>
                        <span>Industries</span>
                    </div>
                    <i class="fas fa-chevron-down text-sm transition-transform duration-200"></i>
                </button>
                <ul class="hidden bg-gray-800 border-l-2 border-primary">
                    <li>
                        <a href="./admin_industries.php" class="flex items-center px-4 py-2 text-gray-300 hover:bg-primary hover:text-white transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'admin_industries.php' ? 'bg-primary text-white' : ''; ?>">
                            <i class="fas fa-list w-4 mr-3"></i>
                            <span>Manage Industries</span>
                        </a>
                    </li>
                    <li>
                        <a href="./admin_industry_solutions.php" class="flex items-center px-4 py-2 text-gray-300 hover:bg-primary hover:text-white transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'admin_industry_categories.php' ? 'bg-primary text-white' : ''; ?>">
                            <i class="fas fa-tags w-4 mr-3"></i>
                            <span>Industry Solutions</span>
                        </a>
                    </li>
                </ul>
            </li>
            <li>
                <a href="admin_services.php" class="flex items-center px-4 py-3 text-gray-300 hover:bg-primary hover:text-white transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'admin_services.php' ? 'bg-primary text-white' : ''; ?>">
                    <i class="fas fa-cogs w-5 mr-3"></i>
                    <span>Services</span>
                </a>
            </li>
            <li>
                <a href="./admin_awards.php" class="flex items-center px-4 py-3 text-gray-300 hover:bg-primary hover:text-white transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'admin_awards.php' ? 'bg-primary text-white' : ''; ?>">
                    <i class="fas fa-trophy w-5 mr-3"></i>
                    <span>Awards & Timeline</span>
                </a>
            </li>
        </ul>
        
        <p class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider mt-6 mb-2">Content</p>
        <ul>
                <li class="relative">
                <button class="w-full flex items-center justify-between px-4 py-3 text-gray-300 hover:bg-primary hover:text-white transition-colors <?php echo in_array(basename($_SERVER['PHP_SELF']), ['admin_industries.php', 'admin_industry_categories.php']) ? 'bg-primary text-white' : ''; ?>">
                    <div class="flex items-center">
                        <i class="fas fa-industry w-5 mr-3"></i>
                        <span>Pages</span>
                    </div>
                    <i class="fas fa-chevron-down text-sm transition-transform duration-200"></i>
                </button>
                <ul class="hidden bg-gray-800 border-l-2 border-primary">
              <li>
                        <a href="admin_home.php" class="flex items-center px-4 py-2 text-gray-300 hover:bg-primary hover:text-white transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'admin_industries.php' ? 'bg-primary text-white' : ''; ?>">
                          <i class="fas fa-home w-4 mr-3"></i> <!-- Changed icon to home -->
                         <span>Home</span>
                        </a>
                    </li>
                <li>
    <a href="admin_about.php" class="flex items-center px-4 py-2 text-gray-300 hover:bg-primary hover:text-white transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'admin_industry_categories.php' ? 'bg-primary text-white' : ''; ?>">
        <i class="fas fa-info-circle w-4 mr-3"></i> <!-- Changed icon to info-circle -->
        <span>About</span>
    </a>
</li>
            <li>
                <a href="admin_news_events.php" class="flex items-center px-4 py-3 text-gray-300 hover:bg-primary hover:text-white transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'admin_news.php' ? 'bg-primary text-white' : ''; ?>">
                    <i class="fas fa-newspaper w-5 mr-3"></i>
                    <span>News & Events</span>
                </a>
            </li>
            <li>
                <a href="admin_headline_articles.php" class="flex items-center px-4 py-3 text-gray-300 hover:bg-primary hover:text-white transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'admin_headline_articles.php' ? 'bg-primary text-white' : ''; ?>">
                    <i class="fas fa-heading w-5 mr-3"></i>
                    <span>Headline Articles</span>
                </a>
            </li>
            <li>
                <a href="admin_faq.php" class="flex items-center px-4 py-3 text-gray-300 hover:bg-primary hover:text-white transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'admin_faq.php' ? 'bg-primary text-white' : ''; ?>">
                    <i class="fas fa-question-circle w-5 mr-3"></i>
                    <span>FAQ</span>
                </a>
            </li>
            <li>
                <a href="admin_videos_promotion.php" class="flex items-center px-4 py-3 text-gray-300 hover:bg-primary hover:text-white transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'admin_videos_promotion.php' ? 'bg-primary text-white' : ''; ?>">
                    <i class="fas fa-video w-5 mr-3"></i>
                    <span>Videos & Promotions</span>
                </a>
            </li>
              <li>
                <a href="admin_plant_visit.php" class="flex items-center px-4 py-3 text-gray-300 hover:bg-primary hover:text-white transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'admin_plant_visit.php' ? 'bg-primary text-white' : ''; ?>">
                    <i class="fas fa-industry w-5 mr-3"></i>
                    <span>Plant Visit</span>
                </a>
            </li>
            <li>
                <a href="admin_overview_process.php" class="flex items-center px-4 py-3 text-gray-300 hover:bg-primary hover:text-white transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'admin_overview_process.php' ? 'bg-primary text-white' : ''; ?>">
                    <i class="fas fa-diagram-project w-5 mr-3"></i>
                    <span>Overview Process</span>
                </a>
            </li>
            <li>
                <a href="admin_privacy_policy.php" class="flex items-center px-4 py-3 text-gray-300 hover:bg-primary hover:text-white transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'admin_privacy_policy.php' ? 'bg-primary text-white' : ''; ?>">
                    <i class="fas fa-shield-alt w-5 mr-3"></i>
                    <span>Privacy Policy</span>
                </a>
            </li>
            <li>
                <a href="admin_careers.php" class="flex items-center px-4 py-3 text-gray-300 hover:bg-primary hover:text-white transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'admin_careers.phpp' ? 'bg-primary text-white' : ''; ?>">
                    <i class="fas fa-shield-alt w-5 mr-3"></i>
                    <span>Careers</span>
                </a>
            </li>
        </ul>
        
        <p class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider mt-6 mb-2">Users</p>
        <ul>
            <li>
                <a href="admin_users.php" class="flex items-center px-4 py-3 text-gray-300 hover:bg-primary hover:text-white transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'admin_users.php' ? 'bg-primary text-white' : ''; ?>">
                    <i class="fas fa-users w-5 mr-3"></i>
                    <span>Admin Users</span>
                </a>
            </li>
            <li>
                <a href="admin_inquiries.php" class="flex items-center px-4 py-3 text-gray-300 hover:bg-primary hover:text-white transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'admin_inquiries.php' ? 'bg-primary text-white' : ''; ?>">
                    <i class="fas fa-envelope w-5 mr-3"></i>
                    <span>Inquiries</span>
                </a>
            </li>
        </ul>
        
        <p class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider mt-6 mb-2">System</p>
        <ul>
            <li>
                <a href="admin_settings.php" class="flex items-center px-4 py-3 text-gray-300 hover:bg-primary hover:text-white transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'admin_settings.php' ? 'bg-primary text-white' : ''; ?>">
                    <i class="fas fa-cog w-5 mr-3"></i>
                    <span>Settings</span>
                </a>
                </li>
<ul>
    <li>
        <a href="admin_page_config.php" class="flex items-center px-4 py-3 text-gray-300 hover:bg-primary hover:text-white transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'admin_page_config.php' ? 'bg-primary text-white' : ''; ?>">
            <i class="fas fa-cogs w-5 mr-3"></i>
            <span>Page Configuration</span>
        </a>
    </li>
</ul>
            <li>
                <a href="admin_logout.php" class="flex items-center px-4 py-3 text-gray-300 hover:bg-red-500 hover:text-white transition-colors">
                    <i class="fas fa-sign-out-alt w-5 mr-3"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </nav>
</div>

<!-- Mobile Toggle Button -->
<div class="fixed left-0 top-4 z-50 lg:hidden ml-4" id="sidebarToggle">
    <button class="bg-primary p-2 rounded-md text-white focus:outline-none">
        <i class="fas fa-bars"></i>
    </button>
</div>







<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('adminSidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const toggleSidebar = document.getElementById('toggleSidebar');
    
    // Initialize sidebar state based on screen size
    function initSidebar() {
        if (window.innerWidth < 1024) {
            sidebar.classList.add('-translate-x-full');
        } else {
            sidebar.classList.remove('-translate-x-full');
        }
    }

    // Handle dropdown menus
    const dropdownButtons = document.querySelectorAll('button[class*="flex items-center justify-between"]');
    dropdownButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.stopPropagation();
            const dropdown = this.nextElementSibling;
            const icon = this.querySelector('.fa-chevron-down');
            
            // Toggle dropdown
            dropdown.classList.toggle('hidden');
            icon.classList.toggle('rotate-180');
            
            // Close other dropdowns
            dropdownButtons.forEach(otherButton => {
                if (otherButton !== button) {
                    const otherDropdown = otherButton.nextElementSibling;
                    const otherIcon = otherButton.querySelector('.fa-chevron-down');
                    otherDropdown.classList.add('hidden');
                    otherIcon.classList.remove('rotate-180');
                }
            });
        });
    });
    
    // Toggle sidebar on mobile
    sidebarToggle.addEventListener('click', function() {
        sidebar.classList.toggle('-translate-x-full');
    });
    
    // Close sidebar using X button
    toggleSidebar.addEventListener('click', function() {
        sidebar.classList.add('-translate-x-full');
    });
    
    // Handle window resize
    window.addEventListener('resize', function() {
        initSidebar();
    });
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        if (window.innerWidth < 1024 &&
            !sidebar.contains(e.target) && 
            !sidebarToggle.contains(e.target) &&
            !sidebar.classList.contains('-translate-x-full')) {
            sidebar.classList.add('-translate-x-full');
        }
    });
    
    // Initialize sidebar on page load
    initSidebar();
});
</script>