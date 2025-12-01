<?php
require "../private/db/db.php";
require_once "../private/actions/genreupdater.php";


// Check if user is logged in
if (!isLoggedIn()) {
    header("Location: index.php");
    exit;
}

// Create token if not POSTing
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['submit_token'] = bin2hex(random_bytes(16));
}

$current_user = getCurrentUser();
$genres = getGenres();

// Handle the genre post to db if a post is done and a genre is received
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['preferred_genre'])) {
    handle_genre_post($current_user, $genres);
}

// Get message and then unset the session message to avoid conflict
if (isset($_SESSION['status_message'])) {
    $message = $_SESSION['status_message'];
    unset($_SESSION['status_message']);
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil</title>
    <link href="site/style/global.css" rel="stylesheet">
    <link href="site/style/auth-popup.css" rel="stylesheet">
    <link href="site/style/profile-page.css" rel="stylesheet">
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
            <h1>Profil</h1>
        </div>
    </div>

    <!-- Main Content -->
    <div class="page-container">
        <div class="page-content">
            <!-- Profile Info -->
            <div class="info-section">
                <h2>Brukerinformasjon</h2>
                <div class="info-item">
                    <label>Brukernavn:</label>
                    <span><?php echo htmlspecialchars($current_user['username']); ?></span>
                </div>
                <div class="info-item">
                    <label>E-post:</label>
                    <span><?php echo htmlspecialchars($current_user['email']); ?></span>
                </div>

                <?php if ($current_user['is_admin']): ?>
                <div class="info-item admin-badge">
                    <span>Administratorbruker</span>
                </div>
                <?php endif; ?>
            </div>

            <!-- Genre Preference Section -->
            <div class="form-section">
                <h2>Foretrukket sjanger</h2>
                <p class="section-description">Velg din foretrukne sjanger slik at MovieMate kan gi bedre anbefalinger</p>
                <!-- Display message if found -->
                <?php if (isset($message) && !empty($message)): ?>
                <div class="status message <?php echo $message['type']; ?>">
                    <?php echo htmlspecialchars($message['text']); ?>
                </div>
                <?php endif; ?>
                <form method="post">
                    <input type="hidden" name="submit_token" value="<?php echo htmlspecialchars($_SESSION['submit_token'] ?? ''); ?>">
                    <select name="preferred_genre" class="form-select">
                        <option value="">Velg en sjanger...</option>
                        <?php foreach ($genres as $genre): ?>
                        <option value="<?php echo htmlspecialchars($genre); ?>"
                            <?php echo ($current_user['preferred_genre'] === $genre) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($genre); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="auth-btn">Lagre sjanger</button>
                </form>
            </div>

            <!-- Admin Section -->
            <?php if ($current_user['is_admin']): ?>
            <div class="form-section">
                <h2>Administratorpanel</h2>
                <a href="admin.php" class="auth-btn">Se alle brukere</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <!-- Navigation Footer -->
    <div class="page-footer">
        <button class="user-menu-btn" onclick="window.location.href='index.php';">Tilbake til chat</button>
    </div>
</body>
</html>