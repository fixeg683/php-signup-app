document.getElementById('signupForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    // Clear DOM state validation states
    clearErrors();
    
    const API_ENDPOINT = 'https://api.yourdomain.com/index.php'; // Map to your decoupled PHP live server endpoint
    const submitBtn = document.getElementById('submitBtn');
    
    const formData = {
        full_name: document.getElementById('fullName').value.trim(),
        email: document.getElementById('email').value.trim(),
        phone_number: document.getElementById('phone').value.trim()
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

        const result = await response.json();

        if (response.status === 201) {
            showAlert('alert-success', result.success);
            document.getElementById('signupForm').reset();
        } else if (response.status === 422 && result.errors) {
            // Apply targeted backend field validation mapping directly on DOM fields
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
        showAlert('alert-danger', 'Network latency failure. Try again later.');
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