<?php

require_once "../private/db/db.php";
require_once "../private/api/call.php";

// Get current user if logged in
$current_user = getCurrentUser();

if (!isset($_SESSION['messages'])) {
    $_SESSION['messages'] = [[
        "role" => "model",
        "content" => "Hei, jeg er MovieMate. Spør meg om hva som helst film-relatert!"
    ]];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    // Token setup for prevention of double submission
    $submit_token = filter_input(INPUT_POST, 'submit_token', FILTER_UNSAFE_RAW) ?? '';
    // require non-empty token and exact match with session token
    if ($submit_token === '' || !isset($_SESSION['submit_token']) || !hash_equals($_SESSION['submit_token'], $submit_token)) {
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }
    // consume token
    $_SESSION['submit_token'] = '';
    // Sanitize raw
    $raw = filter_input(INPUT_POST, 'message', FILTER_UNSAFE_RAW) ?? '';
    // Remove HTML tags, trim whitespace
    $user_message = trim(strip_tags($raw));
    if (mb_strlen($user_message, 'UTF-8') > 100) {
        exit;
    }

    if ($user_message !== '') {
        // Save user message
        $_SESSION['messages'][] = ["role" => "user", "content" => $user_message];

        // Build chatlog (skip greeting)
        $chatlog = [];
        foreach (array_slice($_SESSION['messages'], 1) as $m) {
            $chatlog[] = [
                "role"  => $m["role"],
                "parts" => [["text" => $m["content"]]]
            ];
        }

        // Get model reply + save
        if(isset($current_user['preferred_genre'])) {
            $reply = callAPI($chatlog, $current_user['preferred_genre']);
        } else {
            $reply = callAPI($chatlog, '');
        }
        $_SESSION['messages'][] = ["role" => "model", "content" => $reply];
    }

    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}

$_SESSION['submit_token'] = bin2hex(random_bytes(16));
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
                    <input type="password" id="signupPassword" placeholder="Passord (minst 6 tegn)">
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
        <form method="post" id="chatForm" autocomplete="off" action="">
            <input type="hidden" name="submit_token" value="<?php echo htmlspecialchars($_SESSION['submit_token'] ?? ''); ?>">
            <input type="text" name="message" placeholder="Spør i vei!" maxlength="100" required autofocus>
            <button type="submit" id="chatSendBtn">Send</button>
        </form>
    </div>
</body>
</html>
