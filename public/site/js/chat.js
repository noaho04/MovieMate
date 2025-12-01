// Chat functionality, only runs on index.php where chatForm exists
const form = document.getElementById("chatForm");
if (form) {
    const input = form.querySelector('input[name="message"]');
    const button = form.querySelector('button');
    const chatBox = document.querySelector(".chat-messages");

    // Scroll to bottom
    document.addEventListener('DOMContentLoaded', () => {
        chatBox.scrollTop = chatBox.scrollHeight;
    });

    form.addEventListener("submit", (e) => {
        // Get the usertext for temporary display
        const userText = input.value.trim();
        if (userText){
            // Create user message
            const userDiv = document.createElement("div");
            userDiv.className = "message user latest";
            userDiv.textContent = userText;
            chatBox.appendChild(userDiv);

            // Ensure chatbox is at bottom
            chatBox.scrollTop = chatBox.scrollHeight;

            // Add model thinking bubble after wait
            setTimeout(() => {
                const thinkDiv = document.createElement("div");
                thinkDiv.className = "message model latest thinking";
                thinkDiv.textContent = "Tenker...";
                chatBox.appendChild(thinkDiv);
            }, 200);

            // Ensure chatbox is at bottom
            chatBox.scrollTop = chatBox.scrollHeight;

            // Lock visible input
            input.readOnly = true;
            button.disabled = true;
        } else {
            e.preventDefault();
        }
    });
}