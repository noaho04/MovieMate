<?php
require "../private/db.php";

// Check if user is logged in
if (!isLoggedIn()) {
    header("Location: index.php");
    exit;
}

$current_user = getCurrentUser();

// Handle genre preference update
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['preferred_genre'])) {
    $preferred_genre = isset($_POST['preferred_genre']) ? trim($_POST['preferred_genre']) : '';

    // Update genre preference using prepared statement
    $stmt = $conn->prepare("UPDATE users SET preferred_genre = ? WHERE id = ?");
    $stmt->bind_param("si", $preferred_genre, $_SESSION['user_id']);

    if ($stmt->execute()) {
        $message = "Sjanger lagret!";
        $message_type = "success";
        // Refresh user data
        $current_user = getCurrentUser();
    } else {
        $message = "Feil ved lagring av sjanger";
        $message_type = "error";
    }
    $stmt->close();
}

// List of available genres
$genres = [
    "Action",
    "Comedy",
    "Drama",
    "Horror",
    "Romance",
    "Sci-Fi",
    "Thriller",
    "Animation",
    "Adventure",
    "Crime"
];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - MovieMate</title>
    <link href="site/style/style.css" rel="stylesheet">
    <link href="site/style/auth.css" rel="stylesheet">
    <link href="site/style/pages.css" rel="stylesheet">
</head>
<body>
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

                <?php if ($message): ?>
                <div class="message message-<?php echo $message_type; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
                <?php endif; ?>

                <form method="post">
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
            <div class="admin-section">
                <h2>Administratorpanel</h2>
                <a href="admin.php" class="auth-btn">Se alle brukere</a>
            </div>
            <?php endif; ?>

            <!-- Go to Settings -->
            <div class="action-section">
                <a href="settings.php" class="link-btn">Gå til innstillinger →</a>
            </div>
        </div>
    </div>

    <!-- Navigation Footer -->
    <div class="page-footer">
        <button class="user-menu-btn" onclick="window.location.href='index.php';">Tilbake til chat</button>
    </div>
</body>
</html>
