// Theme toggle dark/light mode
document.addEventListener('DOMContentLoaded', () => {
    const toggleBtn = document.getElementById("themeToggle");
    const root = document.documentElement;

    // Load saved theme from localStorage if present
    if (localStorage.getItem("theme") === "dark") {
        root.classList.add("dark");
        if (toggleBtn) toggleBtn.textContent = "☀️";
    }

    // Setup toggle button if it exists
    if (toggleBtn) {
        toggleBtn.addEventListener("click", () => {
            const isDark = root.classList.toggle("dark");
            toggleBtn.textContent = isDark ? "☀️" : "🌙";
            localStorage.setItem("theme", isDark ? "dark" : "light");
        });
    }
});
