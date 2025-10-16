<?php
require "../src/call.php";

// Start a session for chat persistence
session_start();

if (!isset($_SESSION['messages'])) {
    $_SESSION['messages'] = [[
        "role" => "model",
        "content" => "Hi, I’m MovieMate. Ask me anything about movies!"
    ]];
}

$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'fetch';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $userMessage = trim(strip_tags($_POST['message']));

    if ($userMessage !== '') {
        // save user message
        $_SESSION['messages'][] = ["role" => "user", "content" => $userMessage];

        // build chatlog (skip greeting)
        $chatlog = [];
        foreach (array_slice($_SESSION['messages'], 1) as $m) {
            $chatlog[] = [
                "role"  => $m["role"],
                "parts" => [["text" => $m["content"]]]
            ];
        }

        // get model reply + save
        $reply = call_api($chatlog);
        $_SESSION['messages'][] = ["role" => "model", "content" => $reply];
    } else {
        $reply = '';
    }

    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => true, 'reply' => $reply]);
        exit;
    } else {
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }
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
        <button id="themeToggle" class="theme-toggle">🌙</button>
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
            <form id="chatForm" method="post" class="p-4 border-t flex" autocomplete="off">
                <input type="text" name="message" placeholder="Spør i vei!" required>
                <button type="submit">Send</button>
            </form>
        </div>
        <script>
            // Handle chat form submission with AJAX (fetch)
            const form = document.getElementById('chatForm');
            const input = form.querySelector('input[name="message"]');
            const chatBox = document.querySelector('.chat-messages');

            function addBubble(text, role) {
                const div = document.createElement('div');
                div.className = 'message ' + role;
                div.textContent = text;
                chatBox.appendChild(div);
                chatBox.scrollTop = chatBox.scrollHeight;
                return div;
            }

            document.addEventListener('DOMContentLoaded', () => {
                chatBox.scrollTop = chatBox.scrollHeight;
            });

            form.addEventListener('submit', async (e) => {
                e.preventDefault(); // stop full page reload

                const text = (input.value || '').trim();
                if (!text) return;

                // 1) show user message instantly
                addBubble(text, 'user');
                input.value = '';

                // 2) show thinking bubble
                const thinking = addBubble('Tenker...', 'model');
                thinking.classList.add('thinking');

                try {
                // 3) post with fetch to the SAME URL, mark it as AJAX
                const body = new URLSearchParams();
                body.append('message', text);

                const res = await fetch(window.location.href, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'fetch' }, // <-- tells PHP to return JSON
                    body
                });

                const data = await res.json();

                // 4) replace thinking with reply
                thinking.remove();
                addBubble((data && data.reply) ? data.reply : 'Sorry, something went wrong.', 'model');
                } catch (err) {
                thinking.remove();
                addBubble('Network error. Please try again.', 'model');
                console.error(err);
                }
            });

            // Theme toggle dark/light mode
            const toggleBtn = document.getElementById("themeToggle");
            const root = document.documentElement;

            // Load saved theme from localStorage if present
            if (localStorage.getItem("theme") === "dark") {
                root.classList.add("dark");
                toggleBtn.textContent = "☀️";
            }

            toggleBtn.addEventListener("click", () => {
                const isDark = root.classList.toggle("dark");
                toggleBtn.textContent = isDark ? "☀️" : "🌙";
                localStorage.setItem("theme", isDark ? "dark" : "light");
            });
        </script>
    </body>
</html>