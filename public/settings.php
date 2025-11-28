<?php
require "../private/db/db.php";

// Check if user is logged in
if (!isLoggedIn()) {
    header("Location: index.php");
    exit;
}

$current_user = getCurrentUser();

// Handle settings updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? trim($_POST['action']) : '';

    if ($action === 'update_username') {
        $new_username = isset($_POST['new_username']) ? trim($_POST['new_username']) : '';

        $message = updateUsername($new_username);
        if ($message['type'] !== "error") {
            $current_user = getCurrentUser();
        }

    } else if ($action === 'update_email') {
        $new_email = isset($_POST['new_email']) ? trim($_POST['new_email']) : '';

        $message = updateEmail($new_email);
        if ($message['type'] !== "error") {
            $current_user = getCurrentUser();
        }

    } else if ($action === 'update_password') {
        $current_password = isset($_POST['current_password']) ? trim($_POST['current_password']) : '';
        $new_password = isset($_POST['new_password']) ? trim($_POST['new_password']) : '';
        $confirm_password = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';
        if (!verifyPassword($current_password, $current_user['password'])) {
            $message['type'] = "error";
            $message['text'] = "Gjeldende passord er feil.";
        } else {
            $message = updatePassword($new_password);
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

            <?php if (isset($message)): ?>
            <div class="message message-<?php echo $message['type']; ?>">
                <?php echo htmlspecialchars($message['text']); ?>
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
                    <input type="password" id="new_password" name="new_password" placeholder="Minst 10 tegn, inkl. tall, stor bokstav og spesialtegn" required>

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
