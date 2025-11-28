// scroll to bottom
document.addEventListener('DOMContentLoaded', () => {
    chatBox.scrollTop = chatBox.scrollHeight;
});

// define constants for message manipulation
const form = document.getElementById("chatForm");
const input = form.querySelector('input[name="message"]');
const chatBox = document.querySelector(".chat-messages");

form.addEventListener("submit", () => {
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

        // clear and lock visible input
        input.value = "";
        input.readOnly = true;
        button.disabled = true;
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