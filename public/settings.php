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
        if (empty($new_username)) {
            $message['type'] = "error";
            $message['text'] = "Brukernavn kan ikke være tomt.";
        } elseif (!validateUsername($new_username)) {
            $message['type'] = "error";
            $message['text'] = "Brukernavn er ikke gyldig.";
        } elseif (isTaken($new_username, "username")) {
            $message['type'] = "error";
            $message['text'] = "Brukernavn er allerede tatt";
        } else {
            $message = updateUsername($new_username, $current_user['id']);
            $current_user = getCurrentUser();
        }

    } else if ($action === 'update_email') {
        $new_email = isset($_POST['new_email']) ? trim($_POST['new_email']) : '';
        if (empty($new_email)) {
            $message['type'] = "error";
            $message['text'] = "E-post kan ikke være tom.";
        } elseif (!validateEmail($new_email)) {
            $message['type'] = "error";
            $message['text'] = "E-post ikke gyldig.";
        } elseif (isTaken($new_email, "email")) {
            $message['type'] = "error";
            $message['text'] = "E-post allerede i bruk.";
        } else {
            $message = updateEmail($new_email, $current_user['id']);
            $current_user = getCurrentUser();
        }

    } else if ($action === 'update_password') {
        $current_password = isset($_POST['current_password']) ? trim($_POST['current_password']) : '';
        $new_password = isset($_POST['new_password']) ? trim($_POST['new_password']) : '';
        $confirm_password = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $message['type'] = "error";
            $message['text'] = "Alle felt må fylles inn.";
        } elseif (!verifyPassword($current_password, $current_user['password'])) {
            $message['type'] = "error";
            $message['text'] = "Gjeldende passord er feil.";
        } elseif (!validatePassword($new_password)) {
            $message['type'] = "error";
            $message['text'] = "Passord må inneholde minst 10 tegn og ett spesialtegn, tall og stor bokstav.";
        } elseif ($new_password !== $confirm_password) {
            $message['type'] = "error";
            $message['text'] = "Nye passord matcher ikke.";
        } else {
            $message = updatePassword($new_password, $current_user['id']);
            $current_user = getCurrentUser();
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
    <link href="site/style/global.css" rel="stylesheet">
    <link href="site/style/auth-popup.css" rel="stylesheet">
    <link href="site/style/settings-page.css" rel="stylesheet">
    <script src="site/js/theme.js" defer></script>
    <script src="site/js/auth.js" defer></script>
</head>
<body>
    <!-- Theme Toggle -->
    <button id="themeToggle" class="theme-toggle">🌙</button>

    <!-- User Menu -->
    <?php if ($current_user): ?>
    <div class="user-menu-container">
        <button id="userMenuBtn" class="user-menu-btn" onclick="toggleUserMenu()">
            Hei, <?php echo htmlspecialchars($current_user['username']); ?>!
        </button>
        <div id="userMenu" class="user-menu hidden">
            <a href="profile.php" class="menu-item">Profil</a>
            <a href="settings.php" class="menu-item">Innstillinger</a>
            <button onclick="logout()" class="menu-item logout-btn">Logg ut</button>
        </div>
    </div>
    <?php endif; ?>

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

            <?php if (isset($message) && !empty($message)): ?>
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
                    <input type="text" id="new_email" name="new_email" value="<?php echo htmlspecialchars($current_user['email']); ?>" required>
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
        </div>
    </div>

    <!-- Navigation Footer -->
    <div class="page-footer">
        <button class="user-menu-btn" onclick="window.location.href='index.php';">Tilbake til chat</button>
    </div>
</body>
</html>
