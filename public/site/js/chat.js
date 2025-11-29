// Chat functionality, only runs on index.php where chatForm exists
const form = document.getElementById("chatForm");
if (form) {
    const input = form.querySelector('input[name="message"]');
    const button = form.querySelector('button');
    const chatBox = document.querySelector(".chat-messages");

    // scroll to bottom
    document.addEventListener('DOMContentLoaded', () => {
        chatBox.scrollTop = chatBox.scrollHeight;
    });

    form.addEventListener("submit", (e) => {
        // get the usertext for temporary display
        const userText = input.value.trim();
        if (userText){
            // create user message
            const userDiv = document.createElement("div");
            userDiv.className = "message user";
            userDiv.textContent = userText;
            chatBox.appendChild(userDiv);

            // add model thinking bubble
            const thinkDiv = document.createElement("div");
            thinkDiv.className = "message model thinking";
            thinkDiv.textContent = "Tenker...";
            chatBox.appendChild(thinkDiv);

            // ensure chatbox is at bottom
            chatBox.scrollTop = chatBox.scrollHeight;

            // lock visible input
            input.readOnly = true;
            button.disabled = true;
        } else {
            e.preventDefault();
        }
    });
}