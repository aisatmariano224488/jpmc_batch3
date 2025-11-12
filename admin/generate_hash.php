<?php
// Save this as generate_hash.php in your admin folder
// Access it once to generate password hash, then DELETE IT for security

// Set your desired password here
$plain_password = "123";

// Generate hash
$hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);

echo "<h2>Password Hash Generator</h2>";
/* echo "<p><strong>Plain Password:</strong> " . htmlspecialchars($plain_password) . "</p>"; */
echo "<p><strong>Hashed Password:</strong></p>";
echo "<textarea style='width:100%; height:100px; font-family:monospace;'>" . $hashed_password . "</textarea>";
echo "<hr>";
echo "<h3>SQL Query to Insert Admin:</h3>";
echo "<textarea style='width:100%; height:150px; font-family:monospace;'>";
echo "INSERT INTO admin_users (name, email, password, is_active, created_at) \n";
echo "VALUES (\n";
echo "    'Admin User',\n";
echo "    'admin@jamespolymers.com',\n";
echo "    '" . $hashed_password . "',\n";
echo "    1,\n";
echo "    NOW()\n";
echo ");";
echo "</textarea>";
echo "<hr>";
echo "<p style='color:red;'><strong>IMPORTANT: Delete this file after using it!</strong></p>";
?>