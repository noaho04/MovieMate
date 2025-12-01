<?php
// Handle POST for settings page

function handle_settings_post($current_user) {
    // token check
    $token = $_POST['submit_token'] ?? '';
    if (!isset($_SESSION['submit_token']) || $token === '' || !hash_equals($_SESSION['submit_token'], $token)) {
        $_SESSION['status_message'] = ['type' => 'error', 'text' => 'Ugyldig eller manglende token.'];
        header('Location: settings.php');
        exit;
    }

    // consume token to prevent double submit
    unset($_SESSION['submit_token']);

    // Get action and trim or set as empty
    $action = isset($_POST['action']) ? trim($_POST['action']) : '';
    $result_msg = null;
    
    if ($action === 'update_username') {
        $new_username = isset($_POST['new_username']) ? trim($_POST['new_username']) : '';
        if (empty($new_username)) {
            $result_msg = ['type'=>'error','text'=>'Brukernavn kan ikke være tomt.'];
        } elseif (!validateUsername($new_username)) {
            $result_msg = ['type'=>'error','text'=>'Brukernavn er ikke gyldig.'];
        } elseif (isTaken($new_username, "username")) {
            $result_msg = ['type'=>'error','text'=>'Brukernavn er allerede i bruk.'];
        } else {
            $result_msg = updateUsername($new_username, $current_user['id']);
        }

    } elseif ($action === 'update_email') {
        $new_email = isset($_POST['new_email']) ? trim($_POST['new_email']) : '';
        if (empty($new_email)) {
            $result_msg = ['type'=>'error','text'=>'E-post kan ikke være tom.'];
        } elseif (!validateEmail($new_email)) {
            $result_msg = ['type'=>'error','text'=>'E-post ikke gyldig.'];
        } elseif (isTaken($new_email, "email")) {
            $result_msg = ['type'=>'error','text'=>'E-post allerede i bruk.'];
        } else {
            $result_msg = updateEmail($new_email, $current_user['id']);
        }

    } elseif ($action === 'update_password') {
        $current_password = isset($_POST['current_password']) ? trim($_POST['current_password']) : '';
        $new_password = isset($_POST['new_password']) ? trim($_POST['new_password']) : '';
        $confirm_password = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';

        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $result_msg = ['type'=>'error','text'=>'Alle felt må fylles inn.'];
        } elseif (!verifyPassword($current_password, $current_user['password'])) {
            $result_msg = ['type'=>'error','text'=>'Gjeldende passord er feil.'];
        } elseif (!validatePassword($new_password)) {
            $result_msg = ['type'=>'error','text'=>'Passord må inneholde minst 10 tegn og ett spesialtegn, tall og stor bokstav.'];
        } elseif ($new_password !== $confirm_password) {
            $result_msg = ['type'=>'error','text'=>'Nye passord matcher ikke.'];
        } else {
            $result_msg = updatePassword($new_password, $current_user['id']);
        }
        
    } else {
        $result_msg = ['type'=>'error','text'=>'Ukjent handling.'];
    }

    // Set message and redirect
    $_SESSION['status_message'] = $result_msg;
    header('Location: settings.php');
    exit;
}
?>