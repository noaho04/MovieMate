<?php
require_once "../private/db/db.php";
require_once "../private/actions/userdeleter.php";

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    header("Location: index.php");
    exit;
}

// Get all users
$users = getAllUsers();

// Perform user delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_user') {
    handle_user_deletion();
}

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
    <title>Administratorpanel - MovieMate</title>
    <link href="site/style/global.css" rel="stylesheet">
    <link href="site/style/auth-popup.css" rel="stylesheet">
    <link href="site/style/admin-page.css" rel="stylesheet">
    <script src="site/js/theme.js" defer></script>
    <script src="site/js/auth.js" defer></script>
</head>
<body>
    <!-- Theme Toggle -->
    <button id="themeToggle" class="theme-toggle">🌙</button>

    <!-- User Menu -->
    <?php
    $current_user = getCurrentUser();
    if ($current_user): ?>
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
            <a href="profile.php" class="back-btn">← Tilbake</a>
            <h1>Administratorpanel</h1>
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

            <!-- Users Table -->
            <div class="admin-section">
                <h2>Alle brukere (<?php echo count($users); ?>)</h2>

                <?php if (count($users) > 0): ?>
                <div class="users-table-wrapper">
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th>Brukernavn</th>
                                <th>E-post</th>
                                <th>Sjanger</th>
                                <th>Type</th>
                                <th>Handling</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr <?php echo ($user['id'] === $_SESSION['user_id']) ? 'class="current-user"' : ''; ?>>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['preferred_genre'] ?: '-'); ?></td>
                                <td>
                                    <?php if ($user['is_admin']): ?>
                                        <span class="badge-admin">Admin</span>
                                    <?php else: ?>
                                        <span class="badge-user">Bruker</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                    <form method="post" style="display: inline;">
                                        
                                        <input type="hidden" name="action" value="delete_user">
                                        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['id']); ?>">
                                        <button type="submit" class="delete-btn" onclick="return confirm('Er du sikker på at du vil slette denne brukeren?');">Slett</button>
                                    </form>
                                    <?php else: ?>
                                    <span class="current-badge">Du</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p style="text-align: center; color: #666;">Ingen brukere funnet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Navigation Footer -->
    <div class="page-footer">
        <button class="user-menu-btn" onclick="window.location.href='index.php';">Tilbake til chat</button>
    </div>
</body>
</html>
