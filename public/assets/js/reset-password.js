// Listen to the password input field and validate the password requirements
document.getElementById('password').addEventListener('input', function () {
    const password = this.value;
    const errorElement = document.getElementById('password-error');
    
    // Regex for password validation (at least 8 characters, one uppercase, one lowercase, one special character)
    const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\W).{8,}$/;

    // Check if the password matches the requirements
    if (!passwordRegex.test(password)) {
        errorElement.textContent = 'Password must be at least 8 characters long, contain at least one uppercase letter, one lowercase letter, and one special character.';
    } else {
        errorElement.textContent = '';
    }
});

// Listen to the confirm password input field and validate if it matches the password
document.getElementById('password_confirmation').addEventListener('input', function () {
    const password = document.getElementById('password').value;
    const passwordConfirmation = this.value;
    const errorElement = document.getElementById('password-confirmation-error');

    if (password !== passwordConfirmation) {
        errorElement.textContent = 'Passwords do not match.';
    } else {
        errorElement.textContent = '';
    }
});

// Disable the submit button if there are errors
document.getElementById('reset-password-btn').addEventListener('click', function (e) {
    const passwordError = document.getElementById('password-error').textContent;
    const passwordConfirmationError = document.getElementById('password-confirmation-error').textContent;

    // If there's any error message, prevent form submission
    if (passwordError || passwordConfirmationError) {
        e.preventDefault();
    }
});
