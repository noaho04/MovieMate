<?php
require "../src/call.php";
session_start();
if (!isset($_SESSION['messages'])) {
    $_SESSION['messages'] = [[
        "role" => "model",
        "content" => "Hei, jeg er MovieMate. Spør meg om hva som helst film-relatert!",
        "display_only" => true
    ]];
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['message'])) {
    // do sanitization later?
    $userMessage = trim(strip_tags($_POST['message']));

    $_SESSION['messages'][] = [
        "role" => "user",
        "content" => $userMessage
    ];

    $chatlog = [];
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
    <body>
        <div class="container">
            <h1>MovieMate</h1>
        </div>
        <div>
            <div>
                <!--TODO message separation by . to avoid overly long messages from AI-->
                <?php 
                foreach ($_SESSION['messages'] as $message) {
                    echo '<div>'.$message["content"].'</div>';
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