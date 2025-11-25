<?php
require "../private/db/db.php";

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    header("Location: index.php");
    exit;
}

$message = '';
$message_type = '';

// Handle user deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_user') {
    $user_id_to_delete = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;

    // Prevent admin from deleting themselves
    if ($user_id_to_delete === $_SESSION['user_id']) {
        $message = "Du kan ikke slette din egen bruker";
        $message_type = "error";
    } else if ($user_id_to_delete > 0) {
        // Delete user using prepared statement
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id_to_delete);

        if ($stmt->execute()) {
            $message = "Bruker slettet!";
            $message_type = "success";
        } else {
            $message = "Feil ved sletting av bruker";
            $message_type = "error";
        }
        $stmt->close();
    }
}

// Get all users
$users_result = $conn->query("SELECT id, username, email, preferred_genre, is_admin FROM users ORDER BY username ASC");
$users = [];
if ($users_result) {
    while ($row = $users_result->fetch_assoc()) {
        $users[] = $row;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administratorpanel - MovieMate</title>
    <link href="site/style/style.css" rel="stylesheet">
    <link href="site/style/auth.css" rel="stylesheet">
    <link href="site/style/pages.css" rel="stylesheet">
    <link href="site/style/admin.css" rel="stylesheet">
</head>
<body>
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

            <?php if ($message): ?>
            <div class="message message-<?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
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
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
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

            <!-- Go back to Profile -->
            <div class="action-section">
                <a href="profile.php" class="link-btn">Tilbake til profil →</a>
            </div>
        </div>
    </div>

    <!-- Navigation Footer -->
    <div class="page-footer">
        <button class="user-menu-btn" onclick="window.location.href='index.php';">Tilbake til chat</button>
    </div>
</body>
</html>
