<?php
// Start session
session_start();

// Check if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: admin.php");
    exit;
}

// Initialize variables
$error = '';
$success = '';

// Process form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once '../includes/db_connection.php';
    
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    // Check user credentials
    $stmt = $conn->prepare("SELECT id, name, email, password, is_active FROM admin_users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        if ($row['is_active'] != 1) {
            $error = "Account is inactive. Please contact the administrator.";
        } elseif (password_verify($password, $row['password'])) {
            // Login successful
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $row['id'];
            $_SESSION['admin_name'] = $row['name'];
            $_SESSION['admin_email'] = $row['email'];
            
            // Update last login
            $update_sql = "UPDATE admin_users SET last_login = NOW() WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("i", $row['id']);
            $update_stmt->execute();
            $update_stmt->close();
            
            header("Location: admin.php");
            exit;
        } else {
            $error = "Invalid email or password.";
        }
    } else {
        $error = "Invalid email or password.";
    }
    
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | James Polymers</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#1976d2',
                        secondary: '#d32f2f',
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
        /* Loading Screen Styles */
        #loadingScreen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: #000;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            transition: opacity 0.5s ease-out;
        }
        
        #loadingScreen.fade-out {
            opacity: 0;
            pointer-events: none;
        }
        
        .loading-video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            object-fit: cover;
            z-index: 10000;
        }
        
        .skip-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50px;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            z-index: 10001;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }
        
        .skip-btn:hover {
            background: rgba(0, 0, 0, 0.9);
            border-color: rgba(255, 255, 255, 0.6);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }

        .main-content {
            opacity: 0;
            transition: opacity 0.5s ease-in;
        }
        
        .main-content.show {
            opacity: 1;
        }

        html, body { height: 100%; }
        body { 
            min-height: 100vh; 
            background: #f5f7fa;
            overflow-x: hidden;
        }
        
        .login-split {
            min-height: 100vh;
            display: flex;
            flex-direction: row;
            position: relative;
        }
        
        .login-left {
            flex: 1;
            color: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3rem 2rem;
            background: linear-gradient(135deg, #1976d2 0%, #d32f2f 100%);
            position: relative;
            overflow: hidden;
        }
        
        .login-left::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%);
            animation: rotate 20s linear infinite;
        }
        
        .login-left .logo {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            box-shadow: 0 8px 32px rgba(25, 118, 210, 0.15);
            animation: floatLogo 3s ease-in-out infinite;
            position: relative;
            z-index: 2;
            transition: transform 0.3s ease;
        }
        
        .login-left .logo:hover {
            transform: scale(1.1) rotate(5deg);
        }
        
        .login-right {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            min-width: 350px;
            animation: fadeInRight 1.2s;
        }
        
        .login-card {
            width: 100%;
            max-width: 400px;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 10px 30px rgba(25, 118, 210, 0.10);
            padding: 2.5rem 2rem;
            animation: fadeIn 1.5s;
        }
        
        .login-card h1 {
            color: #1976d2;
            font-weight: 800;
        }
        
        .login-btn {
            background: linear-gradient(90deg, #1976d2 60%, #d32f2f 100%);
            color: #fff;
            font-weight: 700;
            border: none;
            border-radius: 8px;
            height: 48px;
            transition: all 0.3s ease;
        }
        
        .login-btn:hover {
            box-shadow: 0 5px 15px rgba(25, 118, 210, 0.25);
            transform: translateY(-2px) scale(1.03);
            background: linear-gradient(90deg, #d32f2f 0%, #1976d2 100%);
        }
        
        .form-input {
            position: relative;
            margin-bottom: 22px;
        }
        
        .form-input i {
            position: absolute;
            top: 50%;
            left: 16px;
            transform: translateY(-50%);
            color: #1976d2;
            z-index: 2;
            font-size: 1.1rem;
        }
        
        .form-input input {
            padding-left: 45px;
            padding-right: 40px;
            height: 48px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .form-input input:focus {
            box-shadow: 0 0 0 3px rgba(25, 118, 210, 0.1);
        }
        
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        @keyframes floatLogo {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes fadeInRight {
            from { opacity: 0; transform: translateX(50px); }
            to { opacity: 1; transform: translateX(0); }
        }
        
        .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
            margin-right: 8px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        @media (max-width: 768px) {
            .login-split {
                flex-direction: column;
            }
            .login-left {
                min-height: 40vh;
                padding: 2rem 1rem;
            }
            .login-right {
                min-height: 60vh;
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Loading Screen -->
    <div id="loadingScreen">
        <video class="loading-video" autoplay muted loop playsinline>
            <source src="../assets/video/logo_animation.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>
        <button id="skipButton" class="skip-btn" onclick="skipLoadingScreen()">
            <i class="fas fa-forward"></i>
            <span>Skip</span>
        </button>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="login-split">
            <!-- Left Side -->
            <div class="login-left">
                <div class="logo shadow-lg">
                    <img src="../assets/img/logo-whitebg.png" alt="James Polymers Logo" style="width: 180px; height: 180px; border-radius: 50%; object-fit: contain;">
                </div>
                <div class="welcome text-center mt-2">
                    <h2 class="text-3xl font-extrabold mb-2">Welcome Back!</h2>
                    <p class="text-lg font-medium mb-4">James Polymers Admin Portal</p>
                    <p class="text-base text-blue-100">Empowering Excellence in Polymer Solutions</p>
                </div>
            </div>
            
            <!-- Right Side -->
            <div class="login-right">
                <div class="login-card">
                    <h1 class="text-2xl font-bold mb-2">Admin Login</h1>
                    <p class="text-gray-500 mb-4">Enter your credentials to access the admin panel</p>
                    
                    <?php if (!empty($error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <?php echo $error; ?>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="loginForm" onsubmit="return validateLogin(event)">
                        <div class="form-input">
                            <i class="fas fa-user"></i>
                            <input type="email" name="username" id="username" placeholder="Email" class="w-full border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary" required>
                        </div>
                        
                        <div class="form-input">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="password" id="password" placeholder="Password" class="w-full border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary" required>
                            <span class="show-hide" onclick="togglePassword()" style="right: 16px; top: 50%; transform: translateY(-50%); position: absolute; cursor: pointer; z-index: 2;">
                                <i class="fas fa-eye" id="toggleIcon"></i>
                            </span>
                        </div>
                        
                        <div class="flex items-center justify-between mb-6">
                            <label class="flex items-center">
                                <input type="checkbox" class="form-checkbox h-4 w-4 text-primary">
                                <span class="ml-2 text-sm text-gray-600">Remember me</span>
                            </label>
                        </div>
                        
                        <button type="submit" class="login-btn w-full" id="loginBtn">
                            <span id="loginBtnText">Login</span>
                        </button>
                    </form>
                    
                    <div class="text-center mt-6">
                        <a href="../index.php" class="text-sm text-gray-600 hover:text-primary">
                            <i class="fas fa-arrow-left mr-2"></i> Back to Website
                        </a>
                    </div>
                    
                    <div class="text-center mt-8 text-gray-500">
                        <p class="italic text-primary mb-2">"Quality is never an accident; it is always the result of intelligent effort."</p>
                        &copy; <?php echo date('Y'); ?> James Polymers. All rights reserved.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function skipLoadingScreen() {
            const loadingScreen = document.getElementById('loadingScreen');
            const mainContent = document.querySelector('.main-content');
            loadingScreen.classList.add('fade-out');
            mainContent.classList.add('show');
            setTimeout(() => loadingScreen.style.display = 'none', 500);
        }

        document.addEventListener('DOMContentLoaded', function() {
            const loadingScreen = document.getElementById('loadingScreen');
            const mainContent = document.querySelector('.main-content');
            
            const urlParams = new URLSearchParams(window.location.search);
            const fromLogout = urlParams.get('from') === 'logout';
            
            if (fromLogout) {
                loadingScreen.style.display = 'none';
                mainContent.classList.add('show');
            } else {
                setTimeout(function() {
                    loadingScreen.classList.add('fade-out');
                    mainContent.classList.add('show');
                    setTimeout(() => loadingScreen.style.display = 'none', 500);
                }, 8000);
            }
        });

        function togglePassword() {
            const pwd = document.getElementById('password');
            const icon = document.getElementById('toggleIcon');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                pwd.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        function validateLogin(e) {
            const email = document.getElementById('username').value.trim();
            const pwd = document.getElementById('password').value;
            
            if (!email || !pwd) {
                alert('Please fill in all fields');
                return false;
            }
            
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(email)) {
                alert('Please enter a valid email address');
                return false;
            }
            
            const btn = document.getElementById('loginBtn');
            const btnText = document.getElementById('loginBtnText');
            btn.disabled = true;
            btnText.innerHTML = '<span class="spinner"></span>Logging in...';
            
            return true;
        }
    </script>
</body>
</html>