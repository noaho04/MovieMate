<?php
require "../private/api/process.php";
require "../private/db/db.php";

// Get current user if logged in
$current_user = getCurrentUser();

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="site/style/style.css" rel="stylesheet">
    <link href="site/style/auth.css" rel="stylesheet">
    <script src="site/js/page.js" defer></script>
    <script src="site/js/auth.js" defer></script>
</head>
<body>
    <!-- Auth Modal -->
    <div id="authModal" class="modal hidden">
        <div class="modal-content">
            <button class="modal-close" onclick="closeAuthModal()">&times;</button>

            <!-- Login Form -->
            <div id="loginForm" class="auth-form active">
                <h2>Logg inn</h2>
                <form id="loginFormElement">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <input type="text" id="loginUsername" placeholder="Brukernavn" required>
                    <input type="password" id="loginPassword" placeholder="Passord" required>
                    <button type="submit" class="auth-btn">Logg inn</button>
                </form>
                <p class="auth-toggle">Ingen konto? <a href="#" onclick="toggleAuthForm()">Registrer deg</a></p>
            </div>

            <!-- Signup Form -->
            <div id="signupForm" class="auth-form hidden">
                <h2>Registrer deg</h2>
                <form id="signupFormElement">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <input type="text" id="signupUsername" placeholder="Brukernavn" required>
                    <input type="email" id="signupEmail" placeholder="E-post" required>
                    <input type="password" id="signupPassword" placeholder="Passord (minst 6 tegn)" required>
                    <button type="submit" class="auth-btn">Registrer</button>
                </form>
                <p class="auth-toggle">Har du konto? <a href="#" onclick="toggleAuthForm()">Logg inn</a></p>
            </div>

            <!-- Error Message -->
            <div id="authError" class="auth-error hidden"></div>
        </div>
    </div>

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
    <?php else: ?>
    <button class="auth-btn-header" onclick="openAuthModal()">Logg inn / Registrer</button>
    <?php endif; ?>

    <!-- Theme Toggle -->
    <button id="themeToggle" class="theme-toggle">🌙</button>

    <!-- Chat Container -->
    <div class="chat-container">
        <div class="chat-header">
            <img src="site/img/MovieMate_logo.png" alt="MovieMate logo" class="chat-logo">
            <h1 class="chat-title">MovieMate</h1>
        </div>
        <div class="chat-messages">
            <?php
            // Display chat messages
            foreach ($_SESSION['messages'] as $message) {
                echo '<div class="message ' . htmlspecialchars($message['role']) . '">' . htmlspecialchars($message["content"]) . '</div>';
            }
            ?>
        </div>
        <form method="post" id="chatForm" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <input type="text" placeholder="Spør i vei!" required autofocus>
            <button type="submit">Send</button>
        </form>
    </div>
</body>
</html>
