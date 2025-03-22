document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('role');
    const facultyFields = document.getElementById('facultyFields');
    const studentFields = document.getElementById('studentFields');
    const submitButton = document.getElementById('submitButton');
    const form = document.querySelector('.signup-form');
    
    // Show/hide fields based on role selection
    roleSelect.addEventListener('change', function() {
        if (this.value === 'Faculty') {
            facultyFields.style.display = 'block';
            studentFields.style.display = 'none';
            submitButton.style.display = 'block';
            
            // Disable student fields to prevent them from being submitted
            toggleFieldsDisabled(studentFields, true);
            toggleFieldsDisabled(facultyFields, false);
        } else if (this.value === 'Student') {
            facultyFields.style.display = 'none';
            studentFields.style.display = 'block';
            submitButton.style.display = 'block';
            
            // Disable faculty fields to prevent them from being submitted
            toggleFieldsDisabled(facultyFields, true);
            toggleFieldsDisabled(studentFields, false);
        } else {
            facultyFields.style.display = 'none';
            studentFields.style.display = 'none';
            submitButton.style.display = 'none';
            
            // Disable all fields
            toggleFieldsDisabled(facultyFields, true);
            toggleFieldsDisabled(studentFields, true);
        }
    });
    
    // Form validation
    form.addEventListener('submit', function(event) {
        event.preventDefault();
        
        // Reset previous validation
        clearValidationErrors();
        
        let isValid = true;
        const role = roleSelect.value;
        
        // Common validations
        isValid = validateField('email', /^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$/, 'Please enter a valid email address') && isValid;
        
        // Role-specific validations
        if (role === 'Faculty') {
            isValid = validateField('facultyFirstName', /^[A-Za-zÀ-ÖØ-öø-ÿ\s\-'.]{2,50}$/, 'Please enter a valid first name (2-50 characters)') && isValid;
            isValid = validateField('facultyMiddleName', null, null, false) && isValid; // Optional
            isValid = validateField('facultyLastName', /^[A-Za-zÀ-ÖØ-öø-ÿ\s\-'.]{2,50}$/, 'Please enter a valid last name (2-50 characters)') && isValid;
            isValid = validateField('facultyPhoneNumber', /^[0-9]{10,11}$/, 'Please enter a valid 10-11 digit phone number') && isValid;
            isValid = validateField('employeeNumber', null, 'Please enter your employee number') && isValid;
            isValid = validateField('department', null, 'Please select your department') && isValid;
        } else if (role === 'Student') {
            isValid = validateField('studentFirstName', /^[A-Za-zÀ-ÖØ-öø-ÿ\s\-'.]{2,50}$/, 'Please enter a valid first name (2-50 characters)') && isValid;
            isValid = validateField('studentMiddleName', null, null, false) && isValid; // Optional
            isValid = validateField('studentLastName', /^[A-Za-zÀ-ÖØ-öø-ÿ\s\-'.]{2,50}$/, 'Please enter a valid last name (2-50 characters)') && isValid;
            isValid = validateField('studentNumber', null, 'Please enter your student number') && isValid;
            isValid = validateField('program', null, 'Please select your program/course') && isValid;
            isValid = validateField('year', null, 'Please select your year') && isValid;
            isValid = validateField('section', null, 'Please select your section') && isValid;
            
            // Birthdate validation (cannot be today or future date)
            const birthdateInput = document.getElementById('birthdate');
            if (!birthdateInput.value) {
                showError(birthdateInput, 'Please enter your birthdate');
                isValid = false;
            } else {
                const birthdate = new Date(birthdateInput.value);
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                
                if (birthdate >= today) {
                    showError(birthdateInput, 'Birthdate cannot be today or a future date');
                    isValid = false;
                } else if (getAge(birthdate) < 15) {
                    showError(birthdateInput, 'You must be at least 15 years old');
                    isValid = false;
                }
            }
        } else {
            showError(roleSelect, 'Please select a role');
            isValid = false;
        }
        
        if (isValid) {
            // Show loader and disable button
            showLoader();
            
            // Create a FormData object
            const formData = new FormData(form);
            
            // Submit via AJAX
            fetch('/register', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => {
                return response.json().then(data => {
                    // Add status to the returned data for error handling
                    return { ...data, status: response.status };
                });
            })
            .then(data => {
                // Hide loader regardless of result
                hideLoader();
                
                if (data.status === 422) {
                    // Handle validation errors
                    if (data.errors) {
                        // Log all the errors to console for debugging
                        console.log("Validation Errors:", data.errors);
                        
                        Object.keys(data.errors).forEach(field => {
                            const inputField = document.getElementById(field);
                            if (inputField) {
                                showError(inputField, data.errors[field][0]);
                            } else {
                                // Handle special cases for field matching
                                if (field === 'first_name') {
                                    const role = document.getElementById('role').value;
                                    const fieldId = role === 'Faculty' ? 'facultyFirstName' : 'studentFirstName';
                                    showError(document.getElementById(fieldId), data.errors[field][0]);
                                } 
                                else if (field === 'middle_name') {
                                    const role = document.getElementById('role').value;
                                    const fieldId = role === 'Faculty' ? 'facultyMiddleName' : 'studentMiddleName';
                                    showError(document.getElementById(fieldId), data.errors[field][0]);
                                }
                                else if (field === 'last_name') {
                                    const role = document.getElementById('role').value;
                                    const fieldId = role === 'Faculty' ? 'facultyLastName' : 'studentLastName';
                                    showError(document.getElementById(fieldId), data.errors[field][0]);
                                }
                                else if (field === 'phone_number') {
                                    showError(document.getElementById('facultyPhoneNumber'), data.errors[field][0]);
                                }
                                else if (field === 'student_number') {
                                    showError(document.getElementById('studentNumber'), data.errors[field][0]);
                                }
                                else {
                                    // If field cannot be found, show a general error
                                    showErrorMessage(`Error with ${field}: ${data.errors[field][0]}`);
                                }
                            }
                        });
                    }
                } else if (data.status === 200 || data.status === 201) {
                    // Show success message
                    showSuccessMessage(data.message);
                    form.reset();
                    facultyFields.style.display = 'none';
                    studentFields.style.display = 'none';
                    submitButton.style.display = 'none';
                } else {
                    // Show general error message
                    showErrorMessage(data.message || 'An error occurred. Please try again later.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                hideLoader();
                showErrorMessage('An error occurred. Please try again later.');
            });
        }
    });
    
    // Helper functions for validation
    function validateField(id, pattern, errorMsg, required = true) {
        const field = document.getElementById(id);
        
        if (!field) return true; // Skip if field doesn't exist
        
        // For select elements
        if (field.tagName === 'SELECT') {
            if (required && (!field.value || field.value === '')) {
                showError(field, errorMsg);
                return false;
            }
            return true;
        }
        
        // For regular input elements
        if (required && (!field.value || field.value.trim() === '')) {
            showError(field, errorMsg || 'This field is required');
            return false;
        }
        
        // If not required and empty, skip pattern validation
        if (!required && (!field.value || field.value.trim() === '')) {
            return true;
        }
        
        // Pattern validation for text inputs
        if (pattern && !pattern.test(field.value)) {
            showError(field, errorMsg);
            return false;
        }
        
        return true;
    }
    
    function showError(field, message) {
        if (!field) return; // Guard against null fields
        
        // Remove any existing error message
        const existingFeedback = field.nextElementSibling;
        if (existingFeedback && existingFeedback.classList.contains('invalid-feedback')) {
            existingFeedback.remove();
        }
        
        // Add error class to the field
        field.classList.add('is-invalid');
        
        // Create and append the error message
        const feedbackDiv = document.createElement('div');
        feedbackDiv.classList.add('invalid-feedback');
        feedbackDiv.innerText = message;
        field.parentNode.appendChild(feedbackDiv);
    }
    
    function clearValidationErrors() {
        // Remove all validation error messages
        const invalidFields = form.querySelectorAll('.is-invalid');
        invalidFields.forEach(field => {
            field.classList.remove('is-invalid');
            const feedbackDiv = field.nextElementSibling;
            if (feedbackDiv && feedbackDiv.classList.contains('invalid-feedback')) {
                feedbackDiv.remove();
            }
        });
        
        // Remove any alert messages
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => alert.remove());
    }
    
    function showLoader() {
        // Disable the button
        submitButton.querySelector('button').disabled = true;
        
        // Store original button content
        const originalHTML = submitButton.querySelector('button').innerHTML;
        submitButton.querySelector('button').setAttribute('data-original-html', originalHTML);
        
        // Replace with loader
        submitButton.querySelector('button').innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
    }
    
    function hideLoader() {
        const button = submitButton.querySelector('button');
        
        // Re-enable the button
        button.disabled = false;
        
        // Restore original content
        const originalHTML = button.getAttribute('data-original-html');
        if (originalHTML) {
            button.innerHTML = originalHTML;
        }
    }
    
    function showSuccessMessage(message) {
        clearValidationErrors();
        
        const alertDiv = document.createElement('div');
        alertDiv.classList.add('alert', 'alert-success', 'mt-3');
        alertDiv.innerText = message;
        
        // Insert before the form
        form.parentNode.insertBefore(alertDiv, form);
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }
    
    function showErrorMessage(message) {
        const alertDiv = document.createElement('div');
        alertDiv.classList.add('alert', 'alert-danger', 'mt-3');
        alertDiv.innerText = message;
        
        // Insert before the form
        form.parentNode.insertBefore(alertDiv, form);
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }
    
    function getAge(birthdate) {
        const today = new Date();
        let age = today.getFullYear() - birthdate.getFullYear();
        const monthDiff = today.getMonth() - birthdate.getMonth();
        
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthdate.getDate())) {
            age--;
        }
        
        return age;
    }
    
    // Function to enable/disable all form elements within a container
    function toggleFieldsDisabled(container, disabled) {
        if (!container) return;
        
        const formElements = container.querySelectorAll('input, select, textarea');
        formElements.forEach(element => {
            element.disabled = disabled;
        });
    }
});