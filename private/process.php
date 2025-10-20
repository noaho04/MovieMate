<?php
require_once __DIR__ . "/call.php";

// continue session
session_start();

if (!isset($_SESSION['messages'])) {
    $_SESSION['messages'] = [[
        "role" => "model",
        "content" => "Hei, jeg er MovieMate. Spør meg om hva som helst film-relatert!"
    ]];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $user_message = trim(strip_tags($_POST['message']));

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
        $reply = callAPI($chatlog);
        $_SESSION['messages'][] = ["role" => "model", "content" => $reply];
    }

    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}
?>