<?php
require "../src/call.php";

// Start a session for chat persistence
session_start();

// Initialize the messages with a welcome if no messages are found
if (!isset($_SESSION['messages'])) {
    $_SESSION['messages'] = [[
        "role" => "model",
        "content" => "Hei, jeg er MovieMate. Spør meg om hva som helst film-relatert!"
    ]];
}
//TODO Loading stage? display user message and a text bubble with ... from AI
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['message'])) {
    // Further sanitization later?
    $userMessage = trim(strip_tags($_POST['message']));

    $_SESSION['messages'][] = [
        "role" => "user",
        "content" => $userMessage
    ];

    $chatlog = [];
    // Pass messages to chatlog excepting first welcome message
    foreach (array_slice($_SESSION['messages'], 1) as $message) {
        $chatlog[] = [
            "role" => $message["role"],
            "parts" => [
                [
                    "text" => $message["content"]
                ]
            ]
        ];
    }
    // Add a new message and call API to get response
    $_SESSION['messages'][] = [
        "role" => "model",
        "content" => call_api($chatlog)
    ];
}
?>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="style.css" rel="stylesheet">
    </head>
    <script>
        // When content is loaded scroll chat to bottom
        document.addEventListener("DOMContentLoaded", function() {
            chatMessages = document.querySelector(".chat-messages");
            chatMessages.scrollTop = chatMessages.scrollHeight;
        });
    </script>
    <body>
        <div class="chat-container">
            <div class="chat-messages">
                <!--TODO message separation by . to avoid overly long messages from AI-->
                <?php
                // Iterate over messages and create visual representation by sender
                foreach ($_SESSION['messages'] as $message) {
                    echo '<div class="message ' . $message['role'] . '">' . htmlspecialchars($message["content"]) . '</div>';
                }
                ?>
            </div>
            <!--TODO fix double submit?-->
            <form method="post" class="p-4 border-t flex" autocomplete="off">
                <input type="text" name="message" placeholder="Spør i vei!" class="" required>
                <button type="submit" class="">Send</button>
            </form>
        </div>
    </body>
</html>