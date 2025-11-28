<?php
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/validation.php';

// Database configuration and helper functions for authentication
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    ensureCsrfToken();
}

// Database connection details
$db_host = getenv("MYSQL_HOST");
$db_user = getenv("MYSQL_USER");
$db_pass = getenv("MYSQL_PASSWORD");
$db_name = getenv("MYSQL_DATABASE");

// Create connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8");

// Get current user's info
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }

    global $conn;
    $stmt = $conn->prepare("SELECT id, username, email, preferred_genre, is_admin, password FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    return $user;
}

function getAllUsers() {
    global $conn;
    $users_result = $conn->query("SELECT id, username, email, preferred_genre, is_admin FROM users ORDER BY username ASC");
    $users = [];
    if ($users_result) {
        while ($row = $users_result->fetch_assoc()) {
            $users[] = $row;
        }
    }
    return $users;
}

function getGenres() {
    global $conn;
    $stmt = $conn->prepare("SELECT genre_name FROM genres");

    if (!$stmt->execute()) {
        $stmt->close();
        return ["Feil under henting av sjangere"];
    }
    
    $result = $stmt->get_result();
    $stmt->close();
    // Return list of genres
    return array_column($result->fetch_all(MYSQLI_ASSOC), 'genre_name');
}

function updateGenre($user_id, $preferred_genre) {
    global $conn;
    // Update genre preference using prepared statement
    $stmt = $conn->prepare("UPDATE users SET preferred_genre = ? WHERE id = ?");
    $stmt->bind_param("si", $preferred_genre, $user_id);

    if (!$stmt->execute()) {
        $stmt->close();
        return False;
    }
    $stmt->close();
    return True;
}

function isTaken($needle, $haystack) {
    $valid_columns = ['email', 'username']; // Example of valid columns
    if (!in_array($haystack, $valid_columns)) {
        return False;
    }

    global $conn;
    $query = "SELECT id FROM users WHERE $haystack = ?";
    $stmt = $conn->prepare("SELECT id FROM users WHERE $haystack = ?");
    $stmt->bind_param("s", $needle);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if ($result->num_rows > 0) {
        return True;
    }
    
    return False;
}

// Register new user, output array with state and message
function registerUser($username, $email, $password) {
    global $conn;
    $errors = array();

    // Validate inputs
    if (empty($username) || empty($email) || empty($password)) {
        $errors[] = "Alle felt må fylles inn";
    }
    
    if (!validateUsername($username)) {
        $errors[] = "Brukernavn kan kun bestå av bokstaver og tall (max. 50 tegn).";
    }
    
    if (!validateEmail($email)) {
        $errors[] = "E-post ikke gyldig.";
    }

    if (!validatePassword($password)) {
        $errors[] = "Passord må inneholde minst 10 tegn og ett spesialtegn, tall og stor bokstav.";
    }

    if (!(empty($errors))) {
        return ["success" => false, "message" => implode("\n",$errors)];
    }

    if (isTaken($username, "username")) {
        return ["success" => false, "message" => "Brukernavn er allerede tatt"];
    }

    if (isTaken($email, "email")) {
        return ["success" => false, "message" => "E-postadressen er allerede registrert"];
    }

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

/* Check if user account is locked due to failed login attempts
Returns array with locked status and remaining time */
function checkLoginLock($username) {
    global $conn;

    $stmt = $conn->prepare("SELECT login_attempts, locked_until FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $stmt->close();
        return ["locked" => false, "message" => ""];
    }

    $user = $result->fetch_assoc();
    $stmt->close();

    // Check if account is locked and if lockout period has expired
    if ($user['locked_until']) {
        $lockout_time = new DateTime($user['locked_until']);
        $now = new DateTime();

        if ($now < $lockout_time) {
            $remaining = $lockout_time->diff($now);
            $minutes = $remaining->i;
            $seconds = $remaining->s;
            return [
                "locked" => true,
                "message" => "Kontoen er låst. Prøv igjen om {$minutes} minutter og {$seconds} sekunder."
            ];
        } else {
            // Lockout period has expired, reset attempts
            $reset_stmt = $conn->prepare("UPDATE users SET login_attempts = 0, locked_until = NULL WHERE username = ?");
            $reset_stmt->bind_param("s", $username);
            $reset_stmt->execute();
            $reset_stmt->close();
        }
    }

    return ["locked" => false, "message" => ""];
}

// Record failed login attempt
function recordFailedAttempt($username) {
    global $conn;

    // Debug
    error_log("Recording failed attempt for: " . $username);

    // Increment failed attempts
    $stmt = $conn->prepare("UPDATE users SET login_attempts = login_attempts + 1 WHERE username = ?");
    $stmt->bind_param("s", $username);
    if (!$stmt->execute()) {
        error_log("Error incrementing attempts: " . $stmt->error);
    }
    $stmt->close();

    // Check current attempt count
    $check_stmt = $conn->prepare("SELECT login_attempts FROM users WHERE username = ?");
    $check_stmt->bind_param("s", $username);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $user = $result->fetch_assoc();
    $check_stmt->close();

    error_log("Current attempt count: " . $user['login_attempts']);

    // If 3 failed attempts, lock the account for 1 hour
    if ($user['login_attempts'] >= 3) {
        $lockout_time = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $lock_stmt = $conn->prepare("UPDATE users SET locked_until = ? WHERE username = ?");
        $lock_stmt->bind_param("ss", $lockout_time, $username);
        if (!$lock_stmt->execute()) {
            error_log("Error setting lockout: " . $lock_stmt->error);
        }
        $lock_stmt->close();

        error_log("Account locked until: " . $lockout_time);
        return ["attempts" => 3, "locked" => true];
    }

    return ["attempts" => $user['login_attempts'], "locked" => false];
}

// Reset login attempts on successful login
function resetLoginAttempts($username) {
    global $conn;

    $stmt = $conn->prepare("UPDATE users SET login_attempts = 0, locked_until = NULL WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->close();
}

/* Login user with SQL injection protection
Returns array with success status and message */
function loginUser($username, $password) {
    global $conn;

    // Validate inputs
    if (empty($username) || empty($password)) {
        return ["success" => false, "message" => "Brukernavn og passord er påkrevd"];
    }

    // Check if account is locked
    $lock_check = checkLoginLock($username);
    if ($lock_check['locked']) {
        return ["success" => false, "message" => $lock_check['message']];
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
        // Record failed attempt
        $attempt = recordFailedAttempt($username);
        if ($attempt['locked']) {
            return ["success" => false, "message" => "Kontoen er låst etter 3 mislykkede innloggingsforsøk. Prøv igjen om 1 time."];
        } else {
            $remaining = 3 - $attempt['attempts'];
            return ["success" => false, "message" => "Brukernavn eller passord er feil. Du har {$remaining} forsøk igjen."];
        }
    }

    // Reset login attempts on successful login
    resetLoginAttempts($username);

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

// Logout user
function logoutUser() {
    session_destroy();
    // Redirect to the public index using an absolute path so the URL resolves
    header("Location: /index.php");
    exit;
}

function updateUsername($new_username, $user_id) {
    if (empty($new_username)) {
        $message['type'] = "error";
        $message['text'] = "Brukernavn kan ikke være tomt.";
        return $message;
    }
    if (!validateUsername($new_username)) {
        $message['type'] = "error";
        $message['text'] = "Brukernavn er ikke gyldig.";
        return $message;
    }
    if (isTaken($new_username, "username")) {
        $message['type'] = "error";
        $message['text'] = "Brukernavn er allerede tatt";
        return $message;
    }
    global $conn;
    // Update username
    $stmt = $conn->prepare("UPDATE users SET username = ? WHERE id = ?");
    $stmt->bind_param("si", $new_username, $user_id);

    if ($stmt->execute()) {
        $message['type'] = "success";
        $message['text'] = "Brukernavn oppdatert!";
    } else {
        $message['type'] = "error";
        $message['text'] = "Feil ved oppdatering av brukernavn.";
    }

    $stmt->close();
    return $message;
}

function updateEmail($new_email, $user_id) {
    if (empty($new_email)) {
        $message['type'] = "error";
        $message['text'] = "E-post kan ikke være tom.";
        return $message;
    }
    if (!validateEmail($new_email)) {
        $message['type'] = "error";
        $message['text'] = "E-post ikke gyldig.";
        return $message;
    }
    if (isTaken($new_email, "email")) {
        $message['type'] = "error";
        $message['text'] = "E-post allerede i bruk.";
        return $message;
    }
    global $conn;
    // Update email
    $stmt = $conn->prepare("UPDATE users SET email = ? WHERE id = ?");
    $stmt->bind_param("si", $new_email, $user_id);

    if ($stmt->execute()) {
        $message['type'] = "success";
        $message['text'] = "E-postadresse oppdatert!";
    } else {
        $message['type'] = "error";
        $message['text'] = "Feil ved oppdatering av e-post.";
    }

    $stmt->close();
    return $message;
}

function updatePassword($new_password, $user_id) {
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $message['type'] = "error";
        $message['text'] = "Alle felt må fylles inn.";
        return $message;
    }
    if ($new_password !== $confirm_password) {
        $message['type'] = "error";
        $message['text'] = "Nye passord matcher ikke.";
        return $message;
    } 
    if (!validatePassword($new_password)) {
        $message['type'] = "error";
        $message['text'] = "Passord må inneholde minst 10 tegn og ett spesialtegn, tall og stor bokstav.";
        return $message;
    } 
    global $conn;
    
    // Update password
    $hashed_password = hashPassword($new_password);
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hashed_password, $user_id);

    if ($stmt->execute()) {
        $message['type'] = "success";
        $message['text'] = "Passord oppdatert!";
    } else {
        $message['type'] = "error";
        $message['text'] = "Feil ved oppdatering av passord";
    }

    $stmt->close();
    return $message;
}

function deleteUser($user_id) {
    global $conn;
    // Delete user using prepared statement
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id_to_delete);

    if ($stmt->execute()) {
        $message['type'] = "success";
        $message['text'] = "Bruker slettet!";
    } else {
        $message['type'] = "error";
        $message['text'] = "Feil ved sletting av bruker.";
    }

    $stmt->close();
    return $message;
}
?>