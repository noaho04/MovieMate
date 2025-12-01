<?php
require_once "../private/db/db.php";
require_once "../private/actions/chathandler.php";

// Set a submit token when not POSTing
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['submit_token'] = bin2hex(random_bytes(16));
}

// Set init message
if (!isset($_SESSION['messages']) || !is_array($_SESSION['messages'])) {
    $_SESSION['messages'] = [[
        "role" => "model",
        "content" => "Hei, jeg er MovieMate. Spør meg om hva som helst film-relatert!"
    ]];
}

// Get current user if logged in
$current_user = getCurrentUser();

// Handle incoming chat message if a post is happening
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    handle_chat_post($current_user);
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="site/style/global.css" rel="stylesheet">
    <link href="site/style/chat-page.css" rel="stylesheet">
    <link href="site/style/auth-popup.css" rel="stylesheet">
    <script src="site/js/theme.js" defer></script>
    <script src="site/js/chat.js" defer></script>
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
                    <input type="text" id="loginUsername" placeholder="Brukernavn">
                    <input type="password" id="loginPassword" placeholder="Passord">
                    <button type="submit" class="auth-btn">Logg inn</button>
                </form>
                <p class="auth-toggle">Ingen konto? <a href="#" onclick="toggleAuthForm()">Registrer deg</a></p>
            </div>

            <!-- Signup Form -->
            <div id="signupForm" class="auth-form hidden">
                <h2>Registrer deg</h2>
                <form id="signupFormElement">
                    <input type="text" id="signupUsername" placeholder="Brukernavn">
                    <input type="email" id="signupEmail" placeholder="E-post">
                    <input type="password" id="signupPassword" placeholder="Passord (Minst 10 tegn, inkl. tall, stor bokstav og spesialtegn)">
                    <button type="submit" class="auth-btn">Registrer</button>
                </form>
                <p class="auth-toggle">Har du konto? <a href="#" onclick="toggleAuthForm()">Logg inn</a></p>
            </div>

            <!-- Error Message -->
            <div id="authError" class="auth-error hidden"></div>
        </div>
    </div>

    <!-- User Menu -->
    <div>
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
    </div>

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
                if ($message === end($_SESSION['messages'])) {
                    $display_message = '<div class="message ' . htmlspecialchars($message['role']) . ' latest">' . htmlspecialchars($message["content"]) . '</div>';
                } else {
                    $display_message = '<div class="message ' . htmlspecialchars($message['role']) . '">' . htmlspecialchars($message["content"]) . '</div>';
                }
                echo $display_message;
            }
            ?>
        </div>
        <form method="post" id="chatForm" autocomplete="off" action="">
            <input type="hidden" name="submit_token" value="<?php echo htmlspecialchars($_SESSION['submit_token'] ?? ''); ?>">
            <input type="text" name="message" placeholder="Spør i vei!" maxlength="100" required autofocus>
            <button type="submit" id="chatSendBtn">Send</button>
        </form>
    </div>
</body>
</html>