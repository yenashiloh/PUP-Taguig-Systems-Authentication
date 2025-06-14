// Global API Keys Functions
window.ApiKeyFunctions = {
    // Toggle API key status
    toggleStatus: function(keyId) {
        fetch(`/admin/api-keys/${keyId}/toggle`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire('Success!', data.message, 'success').then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Error!', data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Toggle status error:', error);
            Swal.fire('Error!', 'An error occurred while updating the status.', 'error');
        });
    },

    // Regenerate API key
    regenerateKey: function(keyId) {
        Swal.fire({
            title: 'Regenerate API Key?',
            text: 'This will create a new key and deactivate the current one. This action cannot be undone!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ffc107',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, regenerate it!'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/admin/api-keys/${keyId}/regenerate`;
                form.innerHTML = `
                    <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        });
    },

    // Test API key
    testKey: function(keyId) {
        Swal.fire({
            title: 'Testing API Key...',
            text: 'Please wait while we test the API key.',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        fetch(`/admin/api-keys/${keyId}/test`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire('Success!', 'API key is working correctly!', 'success');
            } else {
                Swal.fire('Error!', data.message || 'API key test failed', 'error');
            }
        })
        .catch(error => {
            console.error('Test key error:', error);
            Swal.fire('Error!', 'Failed to test API key.', 'error');
        });
    },

    // Delete API key
    deleteApiKey: function(keyId) {
        Swal.fire({
            title: 'Delete API Key?',
            text: 'This action cannot be undone!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/admin/api-keys/${keyId}`;
                form.innerHTML = `
                    <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">
                    <input type="hidden" name="_method" value="DELETE">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        });
    },

    // Toggle key status with confirmation
    toggleKeyStatus: function(keyId) {
        const statusButton = document.querySelector(`[onclick*="toggleKeyStatus(${keyId})"]`);
        const isActive = statusButton && statusButton.textContent.includes('Deactivate');
        const action = isActive ? 'deactivate' : 'activate';
        const actionText = isActive ? 'Deactivate' : 'Activate';

        Swal.fire({
            title: `${actionText} API Key?`,
            text: `Are you sure you want to ${action} this API key?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#007bff',
            cancelButtonColor: '#6c757d',
            confirmButtonText: `Yes, ${action} it!`
        }).then((result) => {
            if (result.isConfirmed) {
                this.toggleStatus(keyId);
            }
        });
    },

    // Copy to clipboard function
    copyToClipboard: function(elementId = 'apiKeyCode') {
        const element = document.getElementById(elementId);
        if (!element) {
            console.error('Element not found:', elementId);
            Swal.fire('Error!', 'Could not find the API key to copy.', 'error');
            return;
        }

        const textToCopy = element.textContent.trim();
        
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(textToCopy).then(function() {
                Swal.fire({
                    title: 'Copied!',
                    text: 'API key copied to clipboard',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
            }).catch(function(err) {
                console.error('Clipboard error:', err);
                ApiKeyFunctions.fallbackCopyTextToClipboard(textToCopy);
            });
        } else {
            ApiKeyFunctions.fallbackCopyTextToClipboard(textToCopy);
        }
    },

    // Fallback copy method
    fallbackCopyTextToClipboard: function(text) {
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.opacity = '0';
        
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        try {
            const successful = document.execCommand('copy');
            if (successful) {
                Swal.fire({
                    title: 'Copied!',
                    text: 'API key copied to clipboard',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                throw new Error('Copy command failed');
            }
        } catch (err) {
            console.error('Fallback copy failed:', err);
            Swal.fire({
                title: 'Copy Failed',
                html: `Please manually copy the following API key:<br><br><code style="background: #f8f9fa; padding: 10px; border-radius: 4px; word-break: break-all;">${text}</code>`,
                icon: 'info',
                confirmButtonText: 'OK'
            });
        }
        
        document.body.removeChild(textArea);
    }
};

// Make functions available globally for onclick handlers
window.toggleStatus = ApiKeyFunctions.toggleStatus;
window.regenerateKey = ApiKeyFunctions.regenerateKey;
window.testKey = ApiKeyFunctions.testKey;
window.deleteApiKey = ApiKeyFunctions.deleteApiKey;
window.toggleKeyStatus = ApiKeyFunctions.toggleKeyStatus;
window.deleteKey = ApiKeyFunctions.deleteApiKey; // Alternative name
window.copyToClipboard = ApiKeyFunctions.copyToClipboard;

// Initialize event listeners when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Add event listeners for data-attribute based buttons
    document.querySelectorAll('.toggle-key').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const keyId = this.dataset.id;
            ApiKeyFunctions.toggleKeyStatus(keyId);
        });
    });

    document.querySelectorAll('.delete-key').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const keyId = this.dataset.id;
            ApiKeyFunctions.deleteApiKey(keyId);
        });
    });

    document.querySelectorAll('.regenerate-key').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const keyId = this.dataset.id;
            ApiKeyFunctions.regenerateKey(keyId);
        });
    });

    document.querySelectorAll('.test-key').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const keyId = this.dataset.id;
            ApiKeyFunctions.testKey(keyId);
        });
    });

    document.querySelectorAll('.copy-key').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.dataset.target || 'apiKeyCode';
            ApiKeyFunctions.copyToClipboard(targetId);
        });
    });
});