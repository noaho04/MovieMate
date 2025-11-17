<?php
/**
 * Admin Setup Script
 * Run this once to create the admin user in the database
 * Access via browser: http://localhost/MovieMate/private/setup_admin.php
 */

require_once "db.php";

// Default admin credentials
$admin_username = "admin";
$admin_email = "admin@moviemate.local";
$admin_password = "Password123!";

echo "<h1>MovieMate - Admin Setup</h1>";

// Check if admin already exists
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $admin_username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<p style='color: orange;'><strong>Admin user already exists!</strong></p>";
    echo "<p>The admin account has already been set up. You can log in with:</p>";
    echo "<ul>";
    echo "<li><strong>Username:</strong> " . htmlspecialchars($admin_username) . "</li>";
    echo "<li><strong>Password:</strong> " . htmlspecialchars($admin_password) . "</li>";
    echo "</ul>";
    echo "<p><a href='../public/index.php'>Go back to login</a></p>";
    $stmt->close();
    exit;
}

$stmt->close();

// Create admin user
$hashed_password = hashPassword($admin_password);

$stmt = $conn->prepare("INSERT INTO users (username, email, password, is_admin) VALUES (?, ?, ?, 1)");
$stmt->bind_param("sss", $admin_username, $admin_email, $hashed_password);

if ($stmt->execute()) {
    echo "<p style='color: green;'><strong>Admin user created successfully!</strong></p>";
    echo "<p>You can now log in with the following credentials:</p>";
    echo "<ul>";
    echo "<li><strong>Username:</strong> " . htmlspecialchars($admin_username) . "</li>";
    echo "<li><strong>Password:</strong> " . htmlspecialchars($admin_password) . "</li>";
    echo "</ul>";
    echo "<p><strong>IMPORTANT:</strong> Delete this setup file (setup_admin.php) from your server after creating the admin account for security.</p>";
    echo "<p><a href='../public/index.php'>Go to login</a></p>";
} else {
    echo "<p style='color: red;'><strong>Error creating admin user:</strong> " . $conn->error . "</p>";
}

$stmt->close();
?>
