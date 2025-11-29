<?php
function handle_genre_post($current_user, $genres) {
    // token check
    $token = $_POST['submit_token'] ?? '';
    if (!isset($_SESSION['submit_token']) || $token === '' || !hash_equals($_SESSION['submit_token'], $token)) {
        $_SESSION['status_message'] = ['type'=>'error','text'=>'Ugyldig eller manglende token.'];
        header('Location: profile.php');
        exit;
    }
    // consume token
    unset($_SESSION['submit_token']);

    $preferred_genre = trim($_POST['preferred_genre'] ?? '');

    // Validate genre (allow empty to clear preference)
    if (!($preferred_genre === '' || in_array($preferred_genre, $genres, true))) {
        $_SESSION['status_message'] = ['type'=>'error','text'=>'Ugyldig sjanger valgt.'];
        header('Location: profile.php');
        exit;
    }

    // Update, use current_user id
    $userId = $current_user['id'] ?? null;
    if ($userId === null || !updateGenre($userId, $preferred_genre)) {
        $_SESSION['status_message'] = ['type'=>'error','text'=>'Kunne ikke lagre sjanger. Prøv igjen.'];
    } else {
        $_SESSION['status_message'] = ['type'=>'success','text'=>'Sjanger lagret!'];
    }

    // Back to page
    header('Location: profile.php');
    exit;
}
?>