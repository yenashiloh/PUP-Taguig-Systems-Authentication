function toggleKeyStatus(keyId) {
    fetch(`/admin/api-keys/${keyId}/toggle`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
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
        Swal.fire('Error!', 'An error occurred while updating the API key status.', 'error');
    });
}

function regenerateKey(keyId) {
    Swal.fire({
        title: 'Regenerate API Key?',
        html: `
            <div class="text-start">
                <p>This will generate a new API key and deactivate the current one.</p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> The current API key will stop working immediately.
                    Make sure to update your applications with the new key.
                </div>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#007bff',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, regenerate it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading state
            Swal.fire({
                title: 'Regenerating...',
                text: 'Please wait while we generate a new API key.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Create and submit form for POST request
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
}

function deleteKey(keyId) {
    Swal.fire({
        title: 'Delete API Key?',
        text: 'This action cannot be undone! The API key will be permanently deleted.',
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
}

function testApiKey(keyId) {
    // Show loading state
    const testButton = document.querySelector('button[onclick="testApiKey(' + keyId + ')"]');
    const originalText = testButton.innerHTML;
    testButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Testing...';
    testButton.disabled = true;

    fetch(`/admin/api-keys/${keyId}/test`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        let resultsHtml = '';
        
        if (data.success) {
            resultsHtml = `
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    ${data.message}
                </div>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Endpoint</th>
                                <th>Status</th>
                                <th>Response Time</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            data.results.forEach(result => {
                const statusClass = result.status === 'success' ? 'success' : 'danger';
                const statusIcon = result.status === 'success' ? 'check' : 'times';
                
                resultsHtml += `
                    <tr>
                        <td>${result.endpoint}</td>
                        <td>
                            <span class="badge bg-${statusClass}">
                                <i class="fas fa-${statusIcon} me-1"></i>
                                ${result.status}
                            </span>
                        </td>
                        <td>${result.response_time || result.error || 'N/A'}</td>
                    </tr>
                `;
            });
            
            resultsHtml += '</tbody></table></div>';
        } else {
            resultsHtml = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    ${data.message}
                </div>
            `;
        }
        
        document.getElementById('testResultsContent').innerHTML = resultsHtml;
        new bootstrap.Modal(document.getElementById('testResultsModal')).show();
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('testResultsContent').innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                An error occurred while testing the API key.
            </div>
        `;
        new bootstrap.Modal(document.getElementById('testResultsModal')).show();
    })
    .finally(() => {
        // Restore button state
        testButton.innerHTML = originalText;
        testButton.disabled = false;
    });
}