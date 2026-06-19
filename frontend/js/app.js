document.getElementById('signupForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    // Clear DOM state validation states
    clearErrors();
    
    const API_ENDPOINT = 'https://php-signup-app.onrender.com/index.php';
    const submitBtn = document.getElementById('submitBtn');
    
    const formData = {
        full_name: document.getElementById('fullName').value.trim(),
        email: document.getElementById('email').value.trim(),
        phone_number: document.getElementById('phone').value.trim(),
        password: document.getElementById('password').value
    };

    // Fast Client-Side Validations
    let hasError = false;
    if (!formData.full_name) {
        showFieldError('nameError', 'Full name required.');
        hasError = true;
    }
    if (!formData.email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) {
        showFieldError('emailError', 'Provide a valid email address.');
        hasError = true;
    }
    
    if (hasError) return;

    submitBtn.disabled = true;
    submitBtn.innerText = 'Processing...';

    try {
        const response = await fetch(API_ENDPOINT, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'FetchApp' // CSRF Header check validation validation helper
            },
            body: JSON.stringify(formData)
        });

        // 1. Check if the server returned an empty success status code first
        if (response.status === 201 || response.status === 200) {
            // Handle cases where the body might be empty cleanly
            let result = {};
            try { result = await response.json(); } catch(e) {}
            
            showAlert('alert-success', result.success || 'Signup finalized successfully.');
            document.getElementById('signupForm').reset();
            return;
        }

        // 2. Parse errors for non-200 status codes
        const result = await response.json();

        if (response.status === 422 && result.errors) {
            for (const [key, msg] of Object.entries(result.errors)) {
                if (key === 'full_name') showFieldError('nameError', msg);
                if (key === 'email') showFieldError('emailError', msg);
                if (key === 'phone_number') showFieldError('phoneError', msg);
            }
            showAlert('alert-danger', 'Validation errors occurred.');
        } else {
            showAlert('alert-danger', result.error || 'Server error occurred.');
        }
    } catch (err) {
        console.error("Frontend Parse Error details:", err);
        showAlert('alert-danger', 'Network latency failure or invalid server response.');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerText = 'Register';
    }
});

function clearErrors() {
    document.querySelectorAll('.error-msg').forEach(el => el.innerText = '');
    const msgBox = document.getElementById('messageBox');
    msgBox.className = 'alert hidden';
}

function showFieldError(id, msg) {
    document.getElementById(id).innerText = msg;
}

function showAlert(type, msg) {
    const msgBox = document.getElementById('messageBox');
    msgBox.className = `alert ${type}`;
    msgBox.innerText = msg;
}