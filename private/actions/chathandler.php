<?php
// Handle POST for chats in index.php

function handle_chat_post($current_user) {
    // Ensure callAPI is available
    require_once __DIR__ . '/../api/call.php';
    
    // Get posted token
    $submit_token = $_POST['submit_token'] ?? '';
    // Attempt to match token from post with session token
    if ($submit_token === '' || !isset($_SESSION['submit_token']) || !hash_equals($_SESSION['submit_token'], $submit_token)) {
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }

    // Spend token
    $_SESSION['submit_token'] = '';

    // Handle message, max len 100
    $raw = $_POST['message'] ?? '';
    $user_message = trim(strip_tags($raw));
    if (mb_strlen($user_message, 'UTF-8') > 100 || $user_message === '') {
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }

    // Pass the message with role context for model
    $_SESSION['messages'][] = ["role" => "user", "content" => $user_message];

    // Build the chatlog, using specified format
    $chatlog = [];
    foreach (array_slice($_SESSION['messages'], 0) as $m) {
        $chatlog[] = [
            "role"  => $m["role"],
            "parts" => [["text" => $m["content"]]]
        ];
    }

    // Get user's preferred genre or set to empty
    $genre = $current_user['preferred_genre'] ?? '';
    $reply = callAPI($chatlog, $genre);
    $_SESSION['messages'][] = ["role" => "model", "content" => $reply];

    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}
?>