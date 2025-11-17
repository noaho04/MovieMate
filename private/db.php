<?php
// Database configuration and helper functions for authentication

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection details
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "moviemate";

// Create connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8");

// Hash password using PHP's built-in password_hash function
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

// Verify password against hashed password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Register a new user with SQL injection protection
 * Returns array with success status and message
 */
function registerUser($username, $email, $password) {
    global $conn;

    // Validate inputs
    if (empty($username) || empty($email) || empty($password)) {
        return ["success" => false, "message" => "Alle felt må fylles inn"];
    }

    if (strlen($password) < 6) {
        return ["success" => false, "message" => "Passordet må være minst 6 tegn"];
    }

    // Check if username already exists using prepared statement
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $stmt->close();
        return ["success" => false, "message" => "Brukernavn er allerede tatt"];
    }
    $stmt->close();

    // Check if email already exists using prepared statement
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $stmt->close();
        return ["success" => false, "message" => "E-postadressen er allerede registrert"];
    }
    $stmt->close();

    // Hash password
    $hashed_password = hashPassword($password);

    // Insert new user with prepared statement
    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $hashed_password);

    if ($stmt->execute()) {
        $stmt->close();
        return ["success" => true, "message" => "Bruker registrert. Logg inn nå"];
    } else {
        $stmt->close();
        return ["success" => false, "message" => "Registrering feilet. Prøv igjen"];
    }
}

/**
 * Login user with SQL injection protection
 * Returns array with success status and message
 */
function loginUser($username, $password) {
    global $conn;

    // Validate inputs
    if (empty($username) || empty($password)) {
        return ["success" => false, "message" => "Brukernavn og passord er påkrevd"];
    }

    // Get user from database using prepared statement
    $stmt = $conn->prepare("SELECT id, username, password, is_admin FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $stmt->close();
        return ["success" => false, "message" => "Brukernavn eller passord er feil"];
    }

    $user = $result->fetch_assoc();
    $stmt->close();

    // Verify password
    if (!verifyPassword($password, $user['password'])) {
        return ["success" => false, "message" => "Brukernavn eller passord er feil"];
    }

    // Set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['is_admin'] = $user['is_admin'];

    return ["success" => true, "message" => "Innlogging vellykket"];
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if logged in user is admin
function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

// Get current user's info
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }

    global $conn;
    $stmt = $conn->prepare("SELECT id, username, email, preferred_genre, is_admin FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    return $user;
}

// Logout user
function logoutUser() {
    session_destroy();
    header("Location: ../public/index.php");
    exit;
}
?>
