document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('role');
    const facultyFields = document.getElementById('facultyFields');
    const studentFields = document.getElementById('studentFields');
    const submitButton = document.getElementById('submitButton');
    const signupForm = document.querySelector('.signup-form');

    // Get validation settings from backend
    const studentValidation = window.studentValidation || null;
    const employeeValidation = window.employeeValidation || null;

    // Role change handler
    roleSelect.addEventListener('change', function() {
        const selectedRole = this.value;
        
        // Hide all fields first and clear form inputs
        hideAllFields();
        clearFormInputs();
        
        if (selectedRole === 'Faculty') {
            showFacultyFields();
        } else if (selectedRole === 'Student') {
            showStudentFields();
        }
    });

    // Function to hide all role-specific fields
    function hideAllFields() {
        facultyFields.style.display = 'none';
        studentFields.style.display = 'none';
        submitButton.style.display = 'none';
        
        // Disable all form elements in hidden sections
        toggleFieldsDisabled(facultyFields, true);
        toggleFieldsDisabled(studentFields, true);
    }

    // Function to show faculty fields
    function showFacultyFields() {
        facultyFields.style.display = 'block';
        submitButton.style.display = 'block';
        
        // Enable faculty fields and disable student fields
        toggleFieldsDisabled(facultyFields, false);
        toggleFieldsDisabled(studentFields, true);
        
        // Remove name attributes from student fields to prevent conflicts
        removeNameAttributes(studentFields);
        
        setupEmployeeNumberValidation();
    }

    // Function to show student fields
    function showStudentFields() {
        studentFields.style.display = 'block';
        submitButton.style.display = 'block';
        
        // Enable student fields and disable faculty fields
        toggleFieldsDisabled(studentFields, false);
        toggleFieldsDisabled(facultyFields, true);
        
        // Remove name attributes from faculty fields to prevent conflicts
        removeNameAttributes(facultyFields);
        
        setupStudentNumberValidation();
    }

    // Function to enable/disable all form elements within a container
    function toggleFieldsDisabled(container, disabled) {
        if (!container) return;
        
        const formElements = container.querySelectorAll('input, select, textarea');
        formElements.forEach(element => {
            element.disabled = disabled;
            if (disabled) {
                element.removeAttribute('required');
            } else {
                // Re-add required attribute where needed
                if (element.hasAttribute('data-required')) {
                    element.setAttribute('required', '');
                }
            }
        });
    }

    // Function to remove name attributes from hidden fields to prevent form conflicts
    function removeNameAttributes(container) {
        if (!container) return;
        
        const formElements = container.querySelectorAll('input, select, textarea');
        formElements.forEach(element => {
            if (element.name) {
                element.setAttribute('data-original-name', element.name);
                element.removeAttribute('name');
            }
        });
    }

    // Function to restore name attributes
    function restoreNameAttributes(container) {
        if (!container) return;
        
        const formElements = container.querySelectorAll('input, select, textarea');
        formElements.forEach(element => {
            const originalName = element.getAttribute('data-original-name');
            if (originalName) {
                element.setAttribute('name', originalName);
                element.removeAttribute('data-original-name');
            }
        });
    }

    // Function to clear form inputs
    function clearFormInputs() {
        const inputs = signupForm.querySelectorAll('input:not(#email):not(#role), select:not(#role)');
        inputs.forEach(input => {
            if (input.type === 'checkbox' || input.type === 'radio') {
                input.checked = false;
            } else {
                input.value = '';
            }
            // Clear any validation styling
            input.style.borderColor = '';
        });
        
        // Clear any existing error messages
        clearErrorMessages();
    }

    // Setup student number validation
    function setupStudentNumberValidation() {
        const studentNumberInput = document.getElementById('studentNumber');
        if (studentNumberInput && studentValidation) {
            // Set min and max length
            studentNumberInput.setAttribute('minlength', studentValidation.min_digits);
            studentNumberInput.setAttribute('maxlength', studentValidation.max_digits);
            
            // Set pattern based on allowed characters
            let pattern = '';
            let title = '';
            
            if (studentValidation.numbers_only) {
                pattern = '[0-9]+';
                title = `Student number must be ${studentValidation.min_digits}-${studentValidation.max_digits} digits (numbers only)`;
            } else if (studentValidation.letters_only) {
                pattern = '[A-Za-z]+';
                title = `Student number must be ${studentValidation.min_digits}-${studentValidation.max_digits} characters (letters only)`;
            } else {
                pattern = '[A-Za-z0-9\\-_]+';
                title = `Student number must be ${studentValidation.min_digits}-${studentValidation.max_digits} characters (letters, numbers, hyphens, underscores only)`;
            }
            
            studentNumberInput.setAttribute('pattern', pattern);
            studentNumberInput.setAttribute('title', title);
            
            // Remove existing event listeners to prevent duplicates
            studentNumberInput.replaceWith(studentNumberInput.cloneNode(true));
            const newStudentNumberInput = document.getElementById('studentNumber');
            
            // Add real-time validation
            newStudentNumberInput.addEventListener('input', function() {
                validateInput(this, studentValidation);
            });
        }
    }

    // Setup employee number validation
    function setupEmployeeNumberValidation() {
        const employeeNumberInput = document.getElementById('employeeNumber');
        if (employeeNumberInput && employeeValidation) {
            // Set min and max length
            employeeNumberInput.setAttribute('minlength', employeeValidation.min_digits);
            employeeNumberInput.setAttribute('maxlength', employeeValidation.max_digits);
            
            // Set pattern based on allowed characters
            let pattern = '';
            let title = '';
            
            if (employeeValidation.numbers_only) {
                pattern = '[0-9]+';
                title = `Employee number must be ${employeeValidation.min_digits}-${employeeValidation.max_digits} digits (numbers only)`;
            } else if (employeeValidation.letters_only) {
                pattern = '[A-Za-z]+';
                title = `Employee number must be ${employeeValidation.min_digits}-${employeeValidation.max_digits} characters (letters only)`;
            } else {
                pattern = '[A-Za-z0-9\\-_]+';
                title = `Employee number must be ${employeeValidation.min_digits}-${employeeValidation.max_digits} characters (letters, numbers, hyphens, underscores only)`;
            }
            
            employeeNumberInput.setAttribute('pattern', pattern);
            employeeNumberInput.setAttribute('title', title);
            
            // Remove existing event listeners to prevent duplicates
            employeeNumberInput.replaceWith(employeeNumberInput.cloneNode(true));
            const newEmployeeNumberInput = document.getElementById('employeeNumber');
            
            // Add real-time validation
            newEmployeeNumberInput.addEventListener('input', function() {
                validateInput(this, employeeValidation);
            });
        }
    }

    // Real-time input validation
    function validateInput(input, validation) {
        const value = input.value;
        let isValid = true;
        let errorMessage = '';

        // Skip validation if input is empty (let HTML5 required handle it)
        if (value.length === 0) {
            showValidationError(input, true, '');
            return;
        }

        // Check length
        if (value.length < validation.min_digits) {
            isValid = false;
            errorMessage = `Must be at least ${validation.min_digits} characters`;
        } else if (value.length > validation.max_digits) {
            isValid = false;
            errorMessage = `Cannot exceed ${validation.max_digits} characters`;
        }

        // Check character type
        if (isValid) {
            if (validation.numbers_only && !/^[0-9]+$/.test(value)) {
                isValid = false;
                errorMessage = 'Only numbers are allowed';
            } else if (validation.letters_only && !/^[A-Za-z]+$/.test(value)) {
                isValid = false;
                errorMessage = 'Only letters are allowed';
            } else if (validation.letters_symbols_numbers && !/^[A-Za-z0-9\-_]+$/.test(value)) {
                isValid = false;
                errorMessage = 'Only letters, numbers, hyphens and underscores are allowed';
            }
        }

        // Show/hide error message
        showValidationError(input, isValid, errorMessage);
    }

    // Show validation error message
    function showValidationError(input, isValid, errorMessage) {
        // Remove existing error message
        const existingError = input.parentNode.querySelector('.validation-error');
        if (existingError) {
            existingError.remove();
        }

        if (!isValid && errorMessage) {
            // Add error styling
            input.style.borderColor = '#dc3545';
            input.style.boxShadow = '0 0 0 0.2rem rgba(220, 53, 69, 0.25)';
            
            // Create error message element
            const errorDiv = document.createElement('div');
            errorDiv.className = 'validation-error';
            errorDiv.style.color = '#dc3545';
            errorDiv.style.fontSize = '0.875rem';
            errorDiv.style.marginTop = '0.25rem';
            errorDiv.textContent = errorMessage;
            
            // Insert error message after input
            input.parentNode.appendChild(errorDiv);
        } else {
            // Remove error styling
            input.style.borderColor = '';
            input.style.boxShadow = '';
        }
    }

    // Form submission handler
    signupForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Restore name attributes for the active section before submitting
        const selectedRole = roleSelect.value;
        if (selectedRole === 'Faculty') {
            restoreNameAttributes(facultyFields);
        } else if (selectedRole === 'Student') {
            restoreNameAttributes(studentFields);
        }
        
        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        
        // Disable submit button and show loading
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Creating Account...';
        
        // Clear any existing error messages
        clearErrorMessages();
        
        fetch('/register', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.message && !data.errors) {
                // Success
                showSuccessMessage(data.message);
                signupForm.reset();
                hideAllFields();
                roleSelect.value = '';
            } else {
                // Validation errors
                if (data.errors) {
                    showValidationErrors(data.errors);
                }
                if (data.message) {
                    showErrorMessage(data.message);
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showErrorMessage('An error occurred while creating your account. Please try again.');
        })
        .finally(() => {
            // Re-enable submit button
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        });
    });

    // Function to show success message
    function showSuccessMessage(message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-success alert-dismissible fade show';
        alertDiv.style.marginBottom = '1rem';
        alertDiv.innerHTML = `
            <i class="fas fa-check-circle me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        signupForm.insertBefore(alertDiv, signupForm.firstChild);
        
        // Scroll to top to show message
        signupForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
        
        // Auto hide after 8 seconds
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 8000);
    }
    
    // Function to show validation errors
    function showValidationErrors(errors) {
        for (const [field, messages] of Object.entries(errors)) {
            const input = signupForm.querySelector(`[name="${field}"]`);
            if (input && !input.disabled) {
                showValidationError(input, false, messages[0]);
                
                // Focus on first error field
                if (!signupForm.querySelector('.validation-error')) {
                    input.focus();
                }
            }
        }
    }

    // Function to clear error messages
    function clearErrorMessages() {
        const errorMessages = signupForm.querySelectorAll('.validation-error');
        errorMessages.forEach(error => error.remove());
        
        const alerts = signupForm.querySelectorAll('.alert');
        alerts.forEach(alert => alert.remove());
        
        const inputs = signupForm.querySelectorAll('input, select');
        inputs.forEach(input => {
            input.style.borderColor = '';
            input.style.boxShadow = '';
        });
    }

    // Initialize form on page load
    hideAllFields();

    /**
     * Birthdate - Disabled the future dates
     */
    document.addEventListener('DOMContentLoaded', function() {
    // Get today's date in YYYY-MM-DD format
    const today = new Date().toISOString().split('T')[0];
    
    // Set max attribute for both faculty and student birthdate fields
    const facultyBirthdate = document.getElementById('facultyBirthdate');
    const studentBirthdate = document.getElementById('studentBirthdate');
    
    if (facultyBirthdate) {
        facultyBirthdate.setAttribute('max', today);
    }
    
    if (studentBirthdate) {
        studentBirthdate.setAttribute('max', today);
    }
    
    // Optional: Add validation to prevent manual entry of future dates
    function validateBirthdate(input) {
        const selectedDate = new Date(input.value);
        const todayDate = new Date();
        
        if (selectedDate > todayDate) {
            input.setCustomValidity('Birthdate cannot be in the future');
            input.reportValidity();
            input.value = ''; // Clear the invalid date
        } else {
            input.setCustomValidity(''); // Clear any previous error
        }
    }
    
    // Add event listeners for validation
    if (facultyBirthdate) {
        facultyBirthdate.addEventListener('change', function() {
            validateBirthdate(this);
        });
    }
    
    if (studentBirthdate) {
        studentBirthdate.addEventListener('change', function() {
            validateBirthdate(this);
        });
    }
});
});