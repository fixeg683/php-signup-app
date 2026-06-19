// Wake up Render on page load to avoid cold start timeout
fetch('https://php-signup-app.onrender.com/index.php', { method: 'OPTIONS' })
  .catch(() => {}); // silently ignore — just warming the server

document.getElementById('loginForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const msgBox = document.getElementById('messageBox');
    msgBox.className = 'alert hidden';

    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;

    try {
        const response = await fetch('https://php-signup-app.onrender.com/index.php?route=login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'FetchApp'
            },
            body: JSON.stringify({ email, password })
        });

        const result = await response.json();

        if (response.ok) {
            msgBox.className = 'alert alert-success';
            msgBox.innerText = 'Login successful! Redirecting...';
            localStorage.setItem('user_session', JSON.stringify(result.user));
        } else {
            msgBox.className = 'alert alert-danger';
            msgBox.innerText = result.error || 'Invalid credentials.';
        }
    } catch (err) {
        msgBox.className = 'alert alert-danger';
        msgBox.innerText = err.name === 'TypeError'
            ? 'Server is waking up, please wait 30 seconds and try again.'
            : 'Network connection failure.';
    }
});
