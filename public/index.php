<?php
require "../private/process.php"
?>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="site/style/style.css" rel="stylesheet">
        <script src="site/js/page.js" defer></script>
    </head>
    <body>
        <button id="themeToggle" class="theme-toggle">🌙</button>
        <div class="chat-container">
            <div class="chat-header">
                <img src="site/img/MovieMate_logo.png" alt="MovieMate logo" class="chat-logo">
                <h1 class="chat-title">MovieMate</h1>
            </div>
            <div class="chat-messages">
                <!--TODO message separation by . to avoid overly long messages from AI-->
                <?php
                // Iterate over messages and create visual representation by sender
                foreach ($_SESSION['messages'] as $message) {
                    echo '<div class="message ' . $message['role'] . '">' . htmlspecialchars($message["content"]) . '</div>';
                }
                ?>
            </div>
            <form method="post" id="chatForm" autocomplete="off">
                <input type="text" placeholder="Spør i vei!" required autofocus>
                <button type="submit">Send</button>
            </form>
        </div>
    </body>
</html>