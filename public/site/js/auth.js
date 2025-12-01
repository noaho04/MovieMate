function openAuthModal() {
    document.getElementById('authModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden'; // Prevent scrolling
}

function closeAuthModal() {
    document.getElementById('authModal').classList.add('hidden');
    document.body.style.overflow = 'auto'; // Re-enable scrolling
    clearAuthError();
}

function toggleAuthForm() {
    const loginForm = document.getElementById('loginForm');
    const signupForm = document.getElementById('signupForm');

    loginForm.classList.toggle('active');
    loginForm.classList.toggle('hidden');

    signupForm.classList.toggle('active');
    signupForm.classList.toggle('hidden');

    clearAuthError();
}

function toggleUserMenu() {
    document.getElementById('userMenu').classList.toggle('hidden');
}

function showAuthError(message) {
    const errorDiv = document.getElementById('authError');
    errorDiv.textContent = message;
    errorDiv.classList.remove('hidden');
}

function clearAuthError() {
    document.getElementById('authError').classList.add('hidden');
    document.getElementById('authError').textContent = '';
}

function getCsrfToken(formSelector) {
    if (formSelector) {
        const el = document.querySelector(formSelector + ' input[name="csrf_token"]');
        if (el) return el.value;
    }
    const el = document.querySelector('input[name="csrf_token"]');
    return el ? el.value : '';
}

//Handle login form submission
document.getElementById('loginFormElement')?.addEventListener('submit', async function(e) {
    e.preventDefault();

    const username = document.getElementById('loginUsername').value;
    const password = document.getElementById('loginPassword').value;

    try {
        const body = 'action=login'
            + '&username=' + encodeURIComponent(username)
            + '&password=' + encodeURIComponent(password)

        const response = await fetch('site/wrapper/auth-wrapper.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: body
        });

        const data = await response.json();

        if (data.success) {
            // Redirect to index.php to refresh and show logged in state
            window.location.href = 'index.php';
        } else {
            showAuthError(data.message || 'Innlogging feilet.');
        }
    } catch (error) {
        showAuthError('En feil oppstod. Prøv igjen.');
        console.error('Login error:', error);
    }
});

// Handle signup
document.getElementById('signupFormElement')?.addEventListener('submit', async function(e) {
    e.preventDefault();

    const username = document.getElementById('signupUsername').value;
    const email = document.getElementById('signupEmail').value;
    const password = document.getElementById('signupPassword').value;


    try {
        const body = 'action=signup'
            + '&username=' + encodeURIComponent(username)
            + '&email=' + encodeURIComponent(email)
            + '&password=' + encodeURIComponent(password)

        const response = await fetch('site/wrapper/auth-wrapper.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: body
        });

        const data = await response.json();

        if (data.success) {
            alert('Bruker registrert! Logg inn nå.');
            toggleAuthForm();
        } else {
            showAuthError(data.message || 'Registrering feilet.');
        }
    } catch (error) {
        showAuthError('En feil oppstod. Prøv igjen.');
        console.error('Signup error:', error);
    }
});

/**
 * Handle logout
 */
function logout() {
    if (confirm('Er du sikker på at du vil logge ut?')) {
        // Create a form and submit it as POST to prevent URL manipulation
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'site/wrapper/auth-wrapper.php';

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'action';
        input.value = 'logout';
        form.appendChild(input);

        document.body.appendChild(form);
        form.submit();
    }
}

// Close modal when clicking outside of it
window.addEventListener('click', function(event) {
    const modal = document.getElementById('authModal');
    if (event.target === modal) {
        closeAuthModal();
    }
});
