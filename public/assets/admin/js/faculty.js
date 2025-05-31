/**
 * Filter table functionality for faculty
 */
function filterTable() {
    const departmentFilter = document.getElementById('departmentFilter').value.toLowerCase();
    const employmentStatusFilter = document.getElementById('employmentStatusFilter').value.toLowerCase();
    const accountStatusFilter = document.getElementById('accountStatusFilter').value.toLowerCase();
    
    const tableRows = document.querySelectorAll('#userTable tbody tr:not(.no-records-row):not(.no-filter-results-row)');
    let visibleRows = 0;
    
    const hasActiveFilters = departmentFilter || employmentStatusFilter || accountStatusFilter;
    
    tableRows.forEach(row => {
        const department = row.getAttribute('data-department')?.toLowerCase() || '';
        const employmentStatus = row.getAttribute('data-employment-status')?.toLowerCase() || '';
        const status = row.getAttribute('data-status')?.toLowerCase() || '';
        
        const departmentMatch = !departmentFilter || department.includes(departmentFilter);
        const employmentMatch = !employmentStatusFilter || employmentStatus.includes(employmentStatusFilter);
        const statusMatch = !accountStatusFilter || status.includes(accountStatusFilter);
        
        if (departmentMatch && employmentMatch && statusMatch) {
            row.style.display = '';
            visibleRows++;
        } else {
            row.style.display = 'none';
            // Uncheck hidden rows
            const checkbox = row.querySelector('.user-checkbox');
            if (checkbox) {
                checkbox.checked = false;
            }
        }
    });
    
    // Handle no records scenarios
    const originalNoRecordsRow = document.querySelector('.no-records-row');
    let noFilterResultsRow = document.querySelector('.no-filter-results-row');
    
    if (noFilterResultsRow) {
        noFilterResultsRow.remove();
        noFilterResultsRow = null;
    }
    
    if (visibleRows === 0) {
        if (hasActiveFilters) {
            const tbody = document.querySelector('#userTable tbody');
            noFilterResultsRow = document.createElement('tr');
            noFilterResultsRow.className = 'no-filter-results-row';
            noFilterResultsRow.innerHTML = `
                <td colspan="7" class="text-center py-4">
                    <div class="d-flex flex-column align-items-center">
                        <p class="text-muted mb-2 fw-bold">No records found matching the selected filters.</p>
                        <button class="btn btn-sm btn-outline-primary" onclick="clearAllFacultyFilters()">
                            <i class="fas fa-times me-1"></i> Clear Filters
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(noFilterResultsRow);
            
            if (originalNoRecordsRow) {
                originalNoRecordsRow.style.display = 'none';
            }
        } else {
            if (originalNoRecordsRow) {
                originalNoRecordsRow.style.display = '';
            }
        }
    } else {
        if (originalNoRecordsRow) {
            originalNoRecordsRow.style.display = 'none';
        }
    }
    
    // Update select all state after filtering
    updateSelectAll();
}
/**
 * Update select all checkbox based on individual selections
 */
function updateSelectAll() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const userCheckboxes = document.querySelectorAll('.user-checkbox');
    const visibleCheckboxes = Array.from(userCheckboxes).filter(checkbox => {
        const row = checkbox.closest('tr');
        return row.style.display !== 'none';
    });
    
    const checkedCount = visibleCheckboxes.filter(checkbox => checkbox.checked).length;
    const totalVisible = visibleCheckboxes.length;
    
    if (checkedCount === 0) {
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = false;
    } else if (checkedCount === totalVisible) {
        selectAllCheckbox.checked = true;
        selectAllCheckbox.indeterminate = false;
    } else {
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = true;
    }
    
    updateBulkActionsBar();
}

/**
 * Clear all selections
 */
function clearSelection() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const userCheckboxes = document.querySelectorAll('.user-checkbox');
    
    selectAllCheckbox.checked = false;
    selectAllCheckbox.indeterminate = false;
    
    userCheckboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    
    updateBulkActionsBar();
}


// Function to clear all filters
function clearAllFilters() {
    document.getElementById('departmentFilter').value = '';
    document.getElementById('employmentStatusFilter').value = '';
    document.getElementById('accountStatusFilter').value = '';
    
    // Remove the no-filter-results row
    const noFilterResultsRow = document.querySelector('.no-filter-results-row');
    if (noFilterResultsRow) {
        noFilterResultsRow.remove();
    }
    
    // Show all rows
    const tableRows = document.querySelectorAll('#userTable tbody tr:not(.no-records-row)');
    tableRows.forEach(row => {
        row.style.display = '';
    });
    
    // Check if we should show the original no records row
    const originalNoRecordsRow = document.querySelector('.no-records-row');
    if (originalNoRecordsRow && tableRows.length === 0) {
        originalNoRecordsRow.style.display = '';
    } else if (originalNoRecordsRow) {
        originalNoRecordsRow.style.display = 'none';
    }
}

// Toggle account status functionality
function toggleAccountStatus(userId, action) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    Swal.fire({
        title: 'Are you sure?',
        text: `You are about to ${action} this account.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: action === 'deactivate' ? '#d33' : '#3085d6',
        cancelButtonColor: '#6c757d',
        confirmButtonText: `Yes, ${action}!`,
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/admin/toggle-user-status/${userId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ action: action })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire(
                        'Success!',
                        `Account has been ${action}d successfully.`,
                        'success'
                    ).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire(
                        'Error!',
                        'Failed to update account status.',
                        'error'
                    );
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire(
                    'Error!',
                    'An error occurred while updating the account.',
                    'error'
                );
            });
        }
    });
}

// Add Faculty Form Handler
document.addEventListener('DOMContentLoaded', function() {
    const addFacultyForm = document.getElementById('addFacultyForm');
    const formAlert = document.getElementById('formAlert');

    if (addFacultyForm) {
        addFacultyForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get submit button and add loading state
            const submitBtn = document.querySelector('button[form="addFacultyForm"]');
            const originalText = submitBtn.innerHTML;
            
            // Show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Processing...';
            
            // Clear previous validation states
            const inputs = addFacultyForm.querySelectorAll('.form-control');
            inputs.forEach(input => {
                input.classList.remove('is-invalid', 'is-valid');
            });
            
            // Hide alert
            formAlert.classList.add('d-none');
            
            const formData = new FormData(addFacultyForm);
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            fetch('/faculty/store', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(response => response.json())
            .then(data => {
                // Reset button state
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                
                if (data.message && !data.errors) {
                    // Success
                    showAlert('success', data.message);
                    addFacultyForm.reset();
                    
                    // Close modal after 2 seconds
                    setTimeout(() => {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('addFacultyModal'));
                        modal.hide();
                        location.reload();
                    }, 2000);
                } else if (data.errors) {
                    // Validation errors
                    showAlert('danger', data.message || 'Please correct the errors below.');
                    
                    // Show field-specific errors
                    Object.keys(data.errors).forEach(field => {
                        const input = addFacultyForm.querySelector(`[name="${field}"]`);
                        if (input) {
                            input.classList.add('is-invalid');
                            const feedback = input.parentNode.querySelector('.invalid-feedback');
                            if (feedback) {
                                feedback.textContent = data.errors[field][0];
                            }
                        }
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Reset button state
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                showAlert('danger', 'An error occurred while adding the faculty member.');
            });
        });
    }

    function showAlert(type, message) {
        formAlert.className = `alert alert-${type}`;
        formAlert.textContent = message;
        formAlert.classList.remove('d-none');
    }

    // Import form handler with proper loading states
    const importForm = document.getElementById('importForm');
    if (importForm) {
        importForm.addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent default to handle loading state first
            
            const importBtn = document.getElementById('importBtn');
            const importBtnText = document.getElementById('importBtnText');
            const importBtnLoading = document.getElementById('importBtnLoading');
            const cancelBtn = document.getElementById('cancelBtn');
            const closeModalBtn = document.getElementById('closeModalBtn');
            const fileInput = document.getElementById('importFile');
            
            // Validate file is selected
            if (!fileInput.files.length) {
                Swal.fire('Error!', 'Please select a file to import.', 'error');
                return;
            }
            
            // Show loading state
            importBtn.disabled = true;
            cancelBtn.disabled = true;
            closeModalBtn.disabled = true;
            importBtnText.style.display = 'none';
            importBtnLoading.style.display = 'inline';
            
           
            // Now submit the form
            setTimeout(() => {
                importForm.submit();
            }, 500);
        });
    }

    // Auto-hide success alerts
    const successAlert = document.querySelector('.my-success-alert');
    if (successAlert) {
        setTimeout(() => {
            successAlert.style.transition = 'opacity 0.5s';
            successAlert.style.opacity = '0';
            setTimeout(() => {
                successAlert.remove();
            }, 500);
        }, 3000);
    }

    // Reset modal state when closed
    const addFacultyModal = document.getElementById('addFacultyModal');
    if (addFacultyModal) {
        addFacultyModal.addEventListener('hidden.bs.modal', function() {
            // Reset form
            addFacultyForm.reset();
            
            // Clear validation states
            const inputs = addFacultyForm.querySelectorAll('.form-control');
            inputs.forEach(input => {
                input.classList.remove('is-invalid', 'is-valid');
            });
            
            // Hide alert
            formAlert.classList.add('d-none');
            
            // Reset submit button
            const submitBtn = document.querySelector('button[form="addFacultyForm"]');
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Add Faculty';
        });
    }

    // Reset import modal state when closed
    const importModal = document.getElementById('importModal');
    if (importModal) {
        importModal.addEventListener('hidden.bs.modal', function() {
            // Reset form
            importForm.reset();
            
            // Reset button states
            const importBtn = document.getElementById('importBtn');
            const importBtnText = document.getElementById('importBtnText');
            const importBtnLoading = document.getElementById('importBtnLoading');
            const cancelBtn = document.getElementById('cancelBtn');
            const closeModalBtn = document.getElementById('closeModalBtn');
            
            if (importBtn) {
                importBtn.disabled = false;
                cancelBtn.disabled = false;
                closeModalBtn.disabled = false;
                importBtnText.style.display = 'inline';
                importBtnLoading.style.display = 'none';
            }
        });
    }
});

/**
 * Export all faculty data to CSV
 */
function exportAllFaculty() {
    // Show loading confirmation
    Swal.fire({
        title: 'Exporting Faculty Data...',
        text: 'Please wait while we prepare your CSV file.',
        icon: 'info',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Create and submit form for export
    const form = document.createElement('form');
    form.method = 'GET';
    form.action = '/user-management/export-faculty';
    
    // Add CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = csrfToken;
    form.appendChild(csrfInput);
    
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
    
    // Close loading after a short delay
    setTimeout(() => {
        Swal.close();
        
        // Show success message
        Swal.fire({
            title: 'Export Complete!',
            text: 'Faculty data has been exported successfully.',
            icon: 'success',
            timer: 3000,
            showConfirmButton: false
        });
    }, 2000);
}

/**
 * Export filtered faculty data to CSV
 */
function exportFilteredFaculty() {
    const departmentFilter = document.getElementById('departmentFilter').value;
    const employmentStatusFilter = document.getElementById('employmentStatusFilter').value;
    const accountStatusFilter = document.getElementById('accountStatusFilter').value;
    
    // Check if any filters are applied
    const hasFilters = departmentFilter || employmentStatusFilter || accountStatusFilter;
    
    if (!hasFilters) {
        Swal.fire({
            title: 'No Filters Applied',
            text: 'Please apply at least one filter to export filtered data, or use "Export All Faculty" instead.',
            icon: 'warning',
            confirmButtonText: 'OK'
        });
        return;
    }
    
    // Build filter description
    let filterDescription = 'Filters applied: ';
    const filters = [];
    
    if (departmentFilter) {
        filters.push(`Department: ${departmentFilter}`);
    }
    if (employmentStatusFilter) {
        filters.push(`Employment Status: ${employmentStatusFilter}`);
    }
    if (accountStatusFilter) {
        filters.push(`Account Status: ${accountStatusFilter}`);
    }
    
    filterDescription += filters.join(', ');
    
    // Show confirmation with filter details
    Swal.fire({
        title: 'Export Filtered Data?',
        html: `<p>You are about to export faculty data with the following filters:</p>
               <div class="text-start mt-3">
                   ${filters.map(filter => `<div class="mb-1"><i class="fas fa-filter text-primary me-2"></i>${filter}</div>`).join('')}
               </div>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Export!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Exporting Filtered Data...',
                text: 'Please wait while we prepare your CSV file.',
                icon: 'info',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Create and submit form for filtered export
            const form = document.createElement('form');
            form.method = 'GET';
            form.action = '/user-management/export-filtered-faculty';
            
            // Add CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            form.appendChild(csrfInput);
            
            // Add filter parameters
            if (departmentFilter) {
                const deptInput = document.createElement('input');
                deptInput.type = 'hidden';
                deptInput.name = 'department';
                deptInput.value = departmentFilter;
                form.appendChild(deptInput);
            }
            
            if (employmentStatusFilter) {
                const empStatusInput = document.createElement('input');
                empStatusInput.type = 'hidden';
                empStatusInput.name = 'employment_status';
                empStatusInput.value = employmentStatusFilter;
                form.appendChild(empStatusInput);
            }
            
            if (accountStatusFilter) {
                const statusInput = document.createElement('input');
                statusInput.type = 'hidden';
                statusInput.name = 'status';
                statusInput.value = accountStatusFilter;
                form.appendChild(statusInput);
            }
            
            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
            
            // Close loading after a short delay
            setTimeout(() => {
                Swal.close();
                
                // Show success message
                Swal.fire({
                    title: 'Export Complete!',
                    text: 'Filtered faculty data has been exported successfully.',
                    icon: 'success',
                    timer: 3000,
                    showConfirmButton: false
                });
            }, 2000);
        }
    });
}

/**
 * Get current filter status for display
 */
function getCurrentFilterStatus() {
    const departmentFilter = document.getElementById('departmentFilter').value;
    const employmentStatusFilter = document.getElementById('employmentStatusFilter').value;
    const accountStatusFilter = document.getElementById('accountStatusFilter').value;
    
    const activeFilters = [];
    
    if (departmentFilter) activeFilters.push(`Department: ${departmentFilter}`);
    if (employmentStatusFilter) activeFilters.push(`Employment: ${employmentStatusFilter}`);
    if (accountStatusFilter) activeFilters.push(`Status: ${accountStatusFilter}`);
    
    return activeFilters;
}

// Clear all faculty filters
function clearAllFacultyFilters() {
    document.getElementById('departmentFilter').value = '';
    document.getElementById('employmentStatusFilter').value = '';
    document.getElementById('accountStatusFilter').value = '';
    
    const noFilterResultsRow = document.querySelector('.no-filter-results-row');
    if (noFilterResultsRow) {
        noFilterResultsRow.remove();
    }
    
    const tableRows = document.querySelectorAll('#userTable tbody tr:not(.no-records-row)');
    tableRows.forEach(row => {
        row.style.display = '';
    });
    
    const originalNoRecordsRow = document.querySelector('.no-records-row');
    if (originalNoRecordsRow && tableRows.length === 0) {
        originalNoRecordsRow.style.display = '';
    } else if (originalNoRecordsRow) {
        originalNoRecordsRow.style.display = 'none';
    }
    
    // Update select all state
    updateSelectAll();
}


// Faculty-specific bulk action with proper terminology
function bulkAction(action) {
    const selectedCheckboxes = document.querySelectorAll('.user-checkbox:checked');
    const userIds = Array.from(selectedCheckboxes).map(checkbox => checkbox.value);
    
    if (userIds.length === 0) {
        Swal.fire('No Selection', 'Please select at least one faculty member to perform this action.', 'warning');
        return;
    }
    
    // Filter users by current status for the action
    const relevantUsers = Array.from(selectedCheckboxes).filter(checkbox => {
        const status = checkbox.getAttribute('data-status');
        return (action === 'deactivate' && status === 'Active') || 
               (action === 'reactivate' && status === 'Deactivated');
    });
    
    if (relevantUsers.length === 0) {
        const message = action === 'deactivate' ? 
            'No active faculty members selected to deactivate.' : 
            'No deactivated faculty members selected to reactivate.';
        Swal.fire('Invalid Selection', message, 'warning');
        return;
    }
    
    const actionText = action === 'deactivate' ? 'deactivate' : 'reactivate';
    const actionPastTense = action === 'deactivate' ? 'deactivated' : 'reactivated';
    const relevantUserIds = relevantUsers.map(checkbox => checkbox.value);
    
    Swal.fire({
        title: `${actionText.charAt(0).toUpperCase() + actionText.slice(1)} Faculty Members?`,
        html: `
            <p>You are about to <strong>${actionText}</strong> <strong>${relevantUserIds.length}</strong> faculty member(s).</p>
            <div class="text-start mt-3">
                <small class="text-muted">Selected faculty members:</small>
                <ul class="list-unstyled mt-2" style="max-height: 150px; overflow-y: auto;">
                    ${relevantUsers.map(checkbox => {
                        const row = checkbox.closest('tr');
                        const firstName = row.querySelector('td:nth-child(4) p').textContent.trim();
                        const lastName = row.querySelector('td:nth-child(3) p').textContent.trim();
                        const employeeId = row.querySelector('td:nth-child(2) p').textContent.trim();
                        return `<li><small><strong>${lastName}, ${firstName}</strong> (${employeeId})</small></li>`;
                    }).join('')}
                </ul>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: action === 'deactivate' ? '#d33' : '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: `Yes, ${actionText}!`,
        cancelButtonText: 'Cancel',
        width: 500
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading state
            Swal.fire({
                title: `${actionText.charAt(0).toUpperCase() + actionText.slice(1)}ing Faculty Members...`,
                text: 'Please wait while we process your request.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            performBulkAction(relevantUserIds, action, actionPastTense);
        }
    });
}

// Enhanced performBulkAction for faculty
function performBulkAction(userIds, action, actionPastTense, userType = 'user') {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    fetch('/admin/bulk-toggle-user-status', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            user_ids: userIds,
            action: action
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const pluralUserType = userType === 'faculty member' ? 'faculty members' : `${userType}s`;
            Swal.fire({
                title: 'Success!',
                text: `${data.updated_count} ${data.updated_count === 1 ? userType : pluralUserType} ${data.updated_count === 1 ? 'has' : 'have'} been ${actionPastTense} successfully.`,
                icon: 'success',
                timer: 3000,
                showConfirmButton: false
            }).then(() => {
                // Clear selection and reload page
                clearSelection();
                location.reload();
            });
        } else {
            Swal.fire(
                'Error!',
                data.message || `Failed to ${action} ${userType}s.`,
                'error'
            );
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire(
            'Error!',
            `An error occurred while trying to ${action} ${userType}s.`,
            'error'
        );
    });
}

/**
 * Toggle select all checkboxes
 */
function toggleSelectAll() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const userCheckboxes = document.querySelectorAll('.user-checkbox');
    const isChecked = selectAllCheckbox.checked;
    
    userCheckboxes.forEach(checkbox => {
        // Only select visible rows
        const row = checkbox.closest('tr');
        if (row.style.display !== 'none') {
            checkbox.checked = isChecked;
        }
    });
    
    updateBulkActionsBar();
}

/**
 * Update bulk actions bar visibility and content
 */
function updateBulkActionsBar() {
    const selectedCheckboxes = document.querySelectorAll('.user-checkbox:checked');
    const bulkActionsBar = document.getElementById('bulkActionsBar');
    const selectedCount = document.getElementById('selectedCount');
    const bulkReactivateBtn = document.getElementById('bulkReactivateBtn');
    const bulkDeactivateBtn = document.getElementById('bulkDeactivateBtn');
    
    if (selectedCheckboxes.length > 0) {
        bulkActionsBar.style.display = 'block';
        selectedCount.textContent = selectedCheckboxes.length;
        
        // Check if all selected users have the same status
        const selectedStatuses = Array.from(selectedCheckboxes).map(checkbox => 
            checkbox.getAttribute('data-status')
        );
        
        const hasActiveUsers = selectedStatuses.includes('Active');
        const hasDeactivatedUsers = selectedStatuses.includes('Deactivated');
        
        // Show/hide buttons based on selected user statuses
        bulkReactivateBtn.style.display = hasDeactivatedUsers ? 'inline-block' : 'none';
        bulkDeactivateBtn.style.display = hasActiveUsers ? 'inline-block' : 'none';
        
        // Update button text to be more specific
        if (hasActiveUsers && hasDeactivatedUsers) {
            bulkReactivateBtn.innerHTML = '<i class="fas fa-check-circle me-1"></i> Reactivate (' + 
                selectedStatuses.filter(s => s === 'Deactivated').length + ')';
            bulkDeactivateBtn.innerHTML = '<i class="fas fa-ban me-1"></i> Deactivate (' + 
                selectedStatuses.filter(s => s === 'Active').length + ')';
        } else {
            bulkReactivateBtn.innerHTML = '<i class="fas fa-check-circle me-1"></i> Reactivate';
            bulkDeactivateBtn.innerHTML = '<i class="fas fa-ban me-1"></i> Deactivate';
        }
    } else {
        bulkActionsBar.style.display = 'none';
    }
}


/**
 * Execute the bulk action via API
 */
function performBulkAction(userIds, action, actionPastTense) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    fetch('/admin/bulk-toggle-user-status', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            user_ids: userIds,
            action: action
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                title: 'Success!',
                text: `${data.updated_count} faculty member(s) have been ${actionPastTense} successfully.`,
                icon: 'success',
                timer: 3000,
                showConfirmButton: false
            }).then(() => {
                // Clear selection and reload page
                clearSelection();
                location.reload();
            });
        } else {
            Swal.fire(
                'Error!',
                data.message || `Failed to ${action} faculty members.`,
                'error'
            );
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire(
            'Error!',
            `An error occurred while trying to ${action} faculty members.`,
            'error'
        );
    });
}

/**
 * Batch Upload
 */
document.addEventListener('DOMContentLoaded', function() {
    const batchUploadForm = document.getElementById('batchUploadForm');
    const fileInput = document.getElementById('batchUploadFiles');
    const filesList = document.getElementById('filesList');
    const uploadValidation = document.getElementById('uploadValidation');
    const startUploadBtn = document.getElementById('startUploadBtn');
    const cancelUploadBtn = document.getElementById('cancelUploadBtn');

    // File input change handler
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            displaySelectedFiles();
            validateUpload();
        });
    }

    // Display selected files
    function displaySelectedFiles() {
        const files = fileInput.files;
        filesList.innerHTML = '';
        
        if (files.length === 0) {
            filesList.innerHTML = '<p class="text-muted mb-0">No files selected</p>';
            return;
        }

        const filesContainer = document.createElement('div');
        filesContainer.className = 'selected-files';

        Array.from(files).forEach((file, index) => {
            const fileItem = document.createElement('div');
            fileItem.className = 'file-item d-flex align-items-center justify-content-between p-2 mb-2 border rounded';
            
            const fileInfo = document.createElement('div');
            fileInfo.className = 'd-flex align-items-center';
            
            const fileIcon = document.createElement('i');
            fileIcon.className = getFileIcon(file.name);
            
            const fileName = document.createElement('span');
            fileName.className = 'ms-2 fw-medium';
            fileName.textContent = file.name;
            
            const fileSize = document.createElement('small');
            fileSize.className = 'ms-2 text-muted';
            fileSize.textContent = `(${formatFileSize(file.size)})`;
            
            fileInfo.appendChild(fileIcon);
            fileInfo.appendChild(fileName);
            fileInfo.appendChild(fileSize);
            
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'btn btn-sm btn-outline-danger';
            removeBtn.innerHTML = '<i class="fas fa-times"></i>';
            removeBtn.onclick = () => removeFile(index);
            
            fileItem.appendChild(fileInfo);
            fileItem.appendChild(removeBtn);
            filesContainer.appendChild(fileItem);
        });

        filesList.appendChild(filesContainer);
    }

    // Validate upload
    function validateUpload() {
        const files = fileInput.files;
        const validationMessages = [];
        let isValid = true;

        // Check file count
        if (files.length > 10) {
            validationMessages.push('⚠️ Maximum 10 files allowed');
            isValid = false;
        }

        // Check individual file sizes and total size
        let totalSize = 0;
        Array.from(files).forEach(file => {
            if (file.size > 10 * 1024 * 1024) { // 10MB
                validationMessages.push(`⚠️ ${file.name} exceeds 10MB limit`);
                isValid = false;
            }
            totalSize += file.size;
        });

        // Check total size limit (10MB combined)
        if (totalSize > 10 * 1024 * 1024) {
            validationMessages.push('⚠️ Total file size exceeds 10MB limit');
            isValid = false;
        }

        // Check file types
        const allowedTypes = ['.csv', '.xlsx', '.xls'];
        Array.from(files).forEach(file => {
            const extension = '.' + file.name.split('.').pop().toLowerCase();
            if (!allowedTypes.includes(extension)) {
                validationMessages.push(`⚠️ ${file.name} has unsupported format`);
                isValid = false;
            }
        });

        // Display validation results
        if (validationMessages.length > 0) {
            uploadValidation.innerHTML = `
                <div class="alert alert-warning py-2">
                    ${validationMessages.map(msg => `<div>${msg}</div>`).join('')}
                </div>
            `;
        } else if (files.length > 0) {
            uploadValidation.innerHTML = `
                <div class="alert alert-success py-2">
                    ✅ ${files.length} file(s) ready for upload (Total: ${formatFileSize(totalSize)})
                </div>
            `;
        } else {
            uploadValidation.innerHTML = '';
        }

        if (startUploadBtn) {
            startUploadBtn.disabled = !isValid || files.length === 0;
        }
    }

    // Remove file
    function removeFile(index) {
        const dt = new DataTransfer();
        const files = fileInput.files;
        
        for (let i = 0; i < files.length; i++) {
            if (i !== index) {
                dt.items.add(files[i]);
            }
        }
        
        fileInput.files = dt.files;
        displaySelectedFiles();
        validateUpload();
    }

    // Get file icon
    function getFileIcon(filename) {
        const extension = filename.split('.').pop().toLowerCase();
        switch (extension) {
            case 'csv':
                return 'fas fa-file-csv text-success';
            case 'xlsx':
            case 'xls':
                return 'fas fa-file-excel text-success';
            default:
                return 'fas fa-file text-secondary';
        }
    }

    // Format file size
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Handle form submission
    if (batchUploadForm) {
        batchUploadForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const files = fileInput.files;
            
            if (files.length === 0) {
                return;
            }

            // Show loading state
            const originalText = startUploadBtn.innerHTML;
            startUploadBtn.innerHTML = `
                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                Processing...
            `;
            startUploadBtn.disabled = true;
            cancelUploadBtn.disabled = true;

            // Submit form normally
            this.submit();
        });
    }

    // Reset form when modal is closed
    const modal = document.getElementById('batchUploadModal');
    if (modal) {
        modal.addEventListener('hidden.bs.modal', function () {
            resetForm();
        });
    }

    function resetForm() {
        if (startUploadBtn) {
            startUploadBtn.innerHTML = '<i class="fas fa-upload me-1"></i>Start Batch Upload';
            startUploadBtn.disabled = false;
        }
        if (cancelUploadBtn) {
            cancelUploadBtn.disabled = false;
        }
        if (fileInput) {
            fileInput.value = '';
        }
        if (filesList) {
            filesList.innerHTML = '';
        }
        if (uploadValidation) {
            uploadValidation.innerHTML = '';
        }
    }
});