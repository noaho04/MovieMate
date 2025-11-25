<?php
require "../private/db/db.php";

// Check if user is logged in
if (!isLoggedIn()) {
    header("Location: index.php");
    exit;
}

$current_user = getCurrentUser();
$message = '';
$message_type = '';

// Handle settings updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? trim($_POST['action']) : '';

    if ($action === 'update_username') {
        $new_username = isset($_POST['new_username']) ? trim($_POST['new_username']) : '';

        if (empty($new_username)) {
            $message = "Brukernavn kan ikke være tomt";
            $message_type = "error";
        } else {
            // Check if username is already taken using prepared statement
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
            $stmt->bind_param("si", $new_username, $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $message = "Brukernavn er allerede tatt";
                $message_type = "error";
            } else {
                // Update username
                $stmt = $conn->prepare("UPDATE users SET username = ? WHERE id = ?");
                $stmt->bind_param("si", $new_username, $_SESSION['user_id']);

                if ($stmt->execute()) {
                    $_SESSION['username'] = $new_username;
                    $message = "Brukernavn oppdatert!";
                    $message_type = "success";
                    $current_user = getCurrentUser();
                } else {
                    $message = "Feil ved oppdatering av brukernavn";
                    $message_type = "error";
                }
            }
            $stmt->close();
        }

    } else if ($action === 'update_email') {
        $new_email = isset($_POST['new_email']) ? trim($_POST['new_email']) : '';

        if (empty($new_email)) {
            $message = "E-post kan ikke være tom";
            $message_type = "error";
        } else {
            // Check if email is already taken using prepared statement
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->bind_param("si", $new_email, $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $message = "E-postadressen er allerede registrert";
                $message_type = "error";
            } else {
                // Update email
                $stmt = $conn->prepare("UPDATE users SET email = ? WHERE id = ?");
                $stmt->bind_param("si", $new_email, $_SESSION['user_id']);

                if ($stmt->execute()) {
                    $message = "E-postadresse oppdatert!";
                    $message_type = "success";
                    $current_user = getCurrentUser();
                } else {
                    $message = "Feil ved oppdatering av e-postadresse";
                    $message_type = "error";
                }
            }
            $stmt->close();
        }

    } else if ($action === 'update_password') {
        $current_password = isset($_POST['current_password']) ? trim($_POST['current_password']) : '';
        $new_password = isset($_POST['new_password']) ? trim($_POST['new_password']) : '';
        $confirm_password = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';

        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $message = "Alle passordfelter må fylles inn";
            $message_type = "error";
        } else if ($new_password !== $confirm_password) {
            $message = "Nye passord matcher ikke";
            $message_type = "error";
        } else if (strlen($new_password) < 6) {
            $message = "Nytt passord må være minst 6 tegn";
            $message_type = "error";
        } else {
            // Verify current password
            $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();

            if (!verifyPassword($current_password, $user['password'])) {
                $message = "Gjeldende passord er feil";
                $message_type = "error";
            } else {
                // Update password
                $hashed_password = hashPassword($new_password);
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $hashed_password, $_SESSION['user_id']);

                if ($stmt->execute()) {
                    $message = "Passord oppdatert!";
                    $message_type = "success";
                } else {
                    $message = "Feil ved oppdatering av passord";
                    $message_type = "error";
                }
                $stmt->close();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Innstillinger - MovieMate</title>
    <link href="site/style/style.css" rel="stylesheet">
    <link href="site/style/auth.css" rel="stylesheet">
    <link href="site/style/pages.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <div class="page-header">
        <div class="header-content">
            <a href="index.php" class="back-btn">← Tilbake</a>
            <h1>Innstillinger</h1>
        </div>
    </div>

    <!-- Main Content -->
    <div class="page-container">
        <div class="page-content">

            <?php if ($message): ?>
            <div class="message message-<?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
            <?php endif; ?>

            <!-- Update Username -->
            <div class="form-section">
                <h2>Endre brukernavn</h2>
                <form method="post">
                    <input type="hidden" name="action" value="update_username">
                    <label for="new_username">Nytt brukernavn</label>
                    <input type="text" id="new_username" name="new_username" value="<?php echo htmlspecialchars($current_user['username']); ?>" required>
                    <button type="submit" class="auth-btn">Lagre brukernavn</button>
                </form>
            </div>

            <!-- Update Email -->
            <div class="form-section">
                <h2>Endre e-postadresse</h2>
                <form method="post">
                    <input type="hidden" name="action" value="update_email">
                    <label for="new_email">Ny e-postadresse</label>
                    <input type="email" id="new_email" name="new_email" value="<?php echo htmlspecialchars($current_user['email']); ?>" required>
                    <button type="submit" class="auth-btn">Lagre e-postadresse</button>
                </form>
            </div>

            <!-- Update Password -->
            <div class="form-section">
                <h2>Endre passord</h2>
                <form method="post">
                    <input type="hidden" name="action" value="update_password">
                    <label for="current_password">Gjeldende passord</label>
                    <input type="password" id="current_password" name="current_password" required>

                    <label for="new_password">Nytt passord</label>
                    <input type="password" id="new_password" name="new_password" placeholder="Minst 6 tegn" required>

                    <label for="confirm_password">Bekreft passord</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>

                    <button type="submit" class="auth-btn">Lagre passord</button>
                </form>
            </div>

            <!-- Go to Profile -->
            <div class="action-section">
                <a href="profile.php" class="link-btn">Gå til profil →</a>
            </div>
        </div>
    </div>

    <!-- Navigation Footer -->
    <div class="page-footer">
        <button class="user-menu-btn" onclick="window.location.href='index.php';">Tilbake til chat</button>
    </div>
</body>
</html>
