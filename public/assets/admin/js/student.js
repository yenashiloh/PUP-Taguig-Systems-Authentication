// Student Management JavaScript with Export Functions

// Export Students Functions
function exportAllStudents() {
    Swal.fire({
        title: 'Exporting Students Data...',
        text: 'Please wait while we prepare your CSV file.',
        icon: 'info',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    const form = document.createElement('form');
    form.method = 'GET';
    form.action = '/user-management/export-students';
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = csrfToken;
    form.appendChild(csrfInput);
    
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
    
    setTimeout(() => {
        Swal.close();
        Swal.fire({
            title: 'Export Complete!',
            text: 'Students data has been exported successfully.',
            icon: 'success',
            timer: 3000,
            showConfirmButton: false
        });
    }, 2000);
}

function exportFilteredStudents() {
    const programFilter = document.getElementById('programFilter').value;
    const yearFilter = document.getElementById('yearFilter').value;
    const sectionFilter = document.getElementById('sectionFilter').value;
    const accountStatusFilter = document.getElementById('accountStatusFilter').value;
    
    const hasFilters = programFilter || yearFilter || sectionFilter || accountStatusFilter;
    
    if (!hasFilters) {
        Swal.fire({
            title: 'No Filters Applied',
            text: 'Please apply at least one filter to export filtered data, or use "Export All Students" instead.',
            icon: 'warning',
            confirmButtonText: 'OK'
        });
        return;
    }
    
    const filters = [];
    if (programFilter) {
        const displayProgram = programFilter.length > 30 ? programFilter.substring(0, 30) + '...' : programFilter;
        filters.push(`Program: ${displayProgram}`);
    }
    if (yearFilter) filters.push(`Year: ${yearFilter}`);
    if (sectionFilter) filters.push(`Section: ${sectionFilter}`);
    if (accountStatusFilter) filters.push(`Status: ${accountStatusFilter}`);
    
    Swal.fire({
        title: 'Export Filtered Data?',
        html: `<p>You are about to export student data with the following filters:</p>
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
            
            const form = document.createElement('form');
            form.method = 'GET';
            form.action = '/user-management/export-filtered-students';
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            form.appendChild(csrfInput);
            
            if (programFilter) {
                const programInput = document.createElement('input');
                programInput.type = 'hidden';
                programInput.name = 'program';
                programInput.value = programFilter;
                form.appendChild(programInput);
            }
            
            if (yearFilter) {
                const yearInput = document.createElement('input');
                yearInput.type = 'hidden';
                yearInput.name = 'year';
                yearInput.value = yearFilter;
                form.appendChild(yearInput);
            }
            
            if (sectionFilter) {
                const sectionInput = document.createElement('input');
                sectionInput.type = 'hidden';
                sectionInput.name = 'section';
                sectionInput.value = sectionFilter;
                form.appendChild(sectionInput);
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
            
            setTimeout(() => {
                Swal.close();
                Swal.fire({
                    title: 'Export Complete!',
                    text: 'Filtered student data has been exported successfully.',
                    icon: 'success',
                    timer: 3000,
                    showConfirmButton: false
                });
            }, 2000);
        }
    });
}

/**
 * Enhanced filter table function that updates select all state
 */
function filterTable() {
    const programFilter = document.getElementById('programFilter').value.toLowerCase();
    const yearFilter = document.getElementById('yearFilter').value.toLowerCase();
    const sectionFilter = document.getElementById('sectionFilter').value.toLowerCase();
    const accountStatusFilter = document.getElementById('accountStatusFilter').value.toLowerCase();
    
    const tableRows = document.querySelectorAll('#userTable tbody tr:not(.no-records-row):not(.no-filter-results-row)');
    let visibleRows = 0;
    
    const hasActiveFilters = programFilter || yearFilter || sectionFilter || accountStatusFilter;
    
    tableRows.forEach(row => {
        const program = row.getAttribute('data-program')?.toLowerCase() || '';
        const year = row.getAttribute('data-year')?.toLowerCase() || '';
        const section = row.getAttribute('data-section')?.toLowerCase() || '';
        const status = row.getAttribute('data-status')?.toLowerCase() || '';
        
        const programMatch = !programFilter || program.includes(programFilter);
        const yearMatch = !yearFilter || year.includes(yearFilter);
        const sectionMatch = !sectionFilter || section.includes(sectionFilter);
        const statusMatch = !accountStatusFilter || status.includes(accountStatusFilter);
        
        if (programMatch && yearMatch && sectionMatch && statusMatch) {
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
                        <button class="btn btn-sm btn-outline-primary" onclick="clearAllStudentFilters()">
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

// Initialize when document is ready
document.addEventListener('DOMContentLoaded', function() {
    // Add event listeners to all user checkboxes
    const userCheckboxes = document.querySelectorAll('.user-checkbox');
    userCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectAll);
    });
    
    // Initialize bulk actions bar state
    updateBulkActionsBar();
    
    // Add keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + A to select all visible
        if ((e.ctrlKey || e.metaKey) && e.key === 'a' && !e.target.matches('input[type="text"], textarea')) {
            e.preventDefault();
            const selectAllCheckbox = document.getElementById('selectAll');
            selectAllCheckbox.checked = true;
            toggleSelectAll();
        }
        
        // Escape to clear selection
        if (e.key === 'Escape') {
            clearSelection();
        }
    });
});

/**
 * Clear all student filters and update selection state
 */
function clearAllStudentFilters() {
    document.getElementById('programFilter').value = '';
    document.getElementById('yearFilter').value = '';
    document.getElementById('sectionFilter').value = '';
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

// Student form management
document.addEventListener('DOMContentLoaded', function() {
    const addUserForm = document.getElementById('addUserForm');
    const formAlert = document.getElementById('formAlert');

    if (addUserForm) {
        addUserForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = document.querySelector('button[form="addUserForm"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Processing...';
            
            const inputs = addUserForm.querySelectorAll('.form-control');
            inputs.forEach(input => {
                input.classList.remove('is-invalid', 'is-valid');
            });
            
            formAlert.classList.add('d-none');
            
            const formData = new FormData(addUserForm);
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            fetch('/students/store', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(response => response.json())
            .then(data => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                
                if (data.message && !data.errors) {
                    showAlert('success', data.message);
                    addUserForm.reset();
                    
                    setTimeout(() => {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('addUserModal'));
                        modal.hide();
                        location.reload();
                    }, 2000);
                } else if (data.errors) {
                    showAlert('danger', data.message || 'Please correct the errors below.');
                    
                    Object.keys(data.errors).forEach(field => {
                        const input = addUserForm.querySelector(`[name="${field}"]`);
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
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                showAlert('danger', 'An error occurred while adding the student.');
            });
        });
    }

    function showAlert(type, message) {
        formAlert.className = `alert alert-${type}`;
        formAlert.textContent = message;
        formAlert.classList.remove('d-none');
    }

    // Import form handler
    const importForm = document.getElementById('importForm');
    if (importForm) {
        importForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const importBtn = document.getElementById('importBtn');
            const importBtnText = document.getElementById('importBtnText');
            const importBtnLoading = document.getElementById('importBtnLoading');
            const cancelBtn = document.getElementById('cancelBtn');
            const closeModalBtn = document.getElementById('closeModalBtn');
            const fileInput = document.getElementById('importFile');
            
            if (!fileInput.files.length) {
                Swal.fire('Error!', 'Please select a file to import.', 'error');
                return;
            }
            
            importBtn.disabled = true;
            cancelBtn.disabled = true;
            closeModalBtn.disabled = true;
            importBtnText.style.display = 'none';
            importBtnLoading.style.display = 'inline';
            
          
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

    // Reset modal states when closed
    const addUserModal = document.getElementById('addUserModal');
    if (addUserModal) {
        addUserModal.addEventListener('hidden.bs.modal', function() {
            addUserForm.reset();
            
            const inputs = addUserForm.querySelectorAll('.form-control');
            inputs.forEach(input => {
                input.classList.remove('is-invalid', 'is-valid');
            });
            
            formAlert.classList.add('d-none');
            
            const submitBtn = document.querySelector('button[form="addUserForm"]');
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Add Student';
        });
    }

    const importModal = document.getElementById('importModal');
    if (importModal) {
        importModal.addEventListener('hidden.bs.modal', function() {
            importForm.reset();
            
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

/**
 * Perform bulk action on selected users
 */
function bulkAction(action) {
    const selectedCheckboxes = document.querySelectorAll('.user-checkbox:checked');
    const userIds = Array.from(selectedCheckboxes).map(checkbox => checkbox.value);
    
    if (userIds.length === 0) {
        Swal.fire('No Selection', 'Please select at least one student to perform this action.', 'warning');
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
            'No active students selected to deactivate.' : 
            'No deactivated students selected to reactivate.';
        Swal.fire('Invalid Selection', message, 'warning');
        return;
    }
    
    const actionText = action === 'deactivate' ? 'deactivate' : 'reactivate';
    const actionPastTense = action === 'deactivate' ? 'deactivated' : 'reactivated';
    const relevantUserIds = relevantUsers.map(checkbox => checkbox.value);
    
    Swal.fire({
        title: `${actionText.charAt(0).toUpperCase() + actionText.slice(1)} Students?`,
        html: `
            <p>You are about to <strong>${actionText}</strong> <strong>${relevantUserIds.length}</strong> student(s).</p>
            <div class="text-start mt-3">
                <small class="text-muted">Selected students:</small>
                <ul class="list-unstyled mt-2" style="max-height: 150px; overflow-y: auto;">
                    ${relevantUsers.map(checkbox => {
                        const row = checkbox.closest('tr');
                        const firstName = row.querySelector('td:nth-child(4) p').textContent.trim();
                        const lastName = row.querySelector('td:nth-child(3) p').textContent.trim();
                        const studentId = row.querySelector('td:nth-child(2) p').textContent.trim();
                        return `<li><small><strong>${lastName}, ${firstName}</strong> (${studentId})</small></li>`;
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
                title: `${actionText.charAt(0).toUpperCase() + actionText.slice(1)}ing Students...`,
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
                text: `${data.updated_count} student(s) have been ${actionPastTense} successfully.`,
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
                data.message || `Failed to ${action} students.`,
                'error'
            );
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire(
            'Error!',
            `An error occurred while trying to ${action} students.`,
            'error'
        );
    });
}

/**
 * Batch File
 */
    document.addEventListener('DOMContentLoaded', function() {
        const batchUploadForm = document.getElementById('batchUploadForm');
        const fileInput = document.getElementById('batchUploadFiles');
        const filesList = document.getElementById('filesList');
        const uploadValidation = document.getElementById('uploadValidation');
        const startUploadBtn = document.getElementById('startUploadBtn');
        const cancelUploadBtn = document.getElementById('cancelUploadBtn');
        const uploadProgress = document.getElementById('uploadProgress');
        const messageContainer = document.getElementById('messageContainer');

        // File input change handler
        fileInput.addEventListener('change', function() {
            displaySelectedFiles();
            validateUpload();
        });

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
                fileItem.className =
                    'file-item d-flex align-items-center justify-content-between p-2 mb-2 border rounded';

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

            startUploadBtn.disabled = !isValid || files.length === 0;
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

        // Show message outside modal
        function showMessage(type, title, message, errors = null) {
            // Clear existing messages
            messageContainer.innerHTML = '';

            let alertClass = '';
            let icon = '';

            switch (type) {
                case 'success':
                    alertClass = 'alert-success';
                    icon = 'fas fa-check-circle';
                    break;
                case 'error':
                    alertClass = 'alert-danger';
                    icon = 'fas fa-exclamation-circle';
                    break;
                case 'warning':
                    alertClass = 'alert-warning';
                    icon = 'fas fa-exclamation-triangle';
                    break;
            }

            let errorsList = '';
            if (errors && errors.length > 0) {
                errorsList = `
                <div class="mt-3">
                    <h6>Detailed Errors:</h6>
                    <div style="max-height: 200px; overflow-y: auto; background: rgba(255,255,255,0.1); padding: 10px; border-radius: 5px;">
                        <ul class="mb-0" style="font-size: 0.9em;">
                            ${errors.map(error => `<li>${error}</li>`).join('')}
                        </ul>
                    </div>
                </div>
            `;
            }

            const messageHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert" style="box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                <div class="d-flex align-items-start">
                    <i class="${icon} me-2 mt-1"></i>
                    <div class="flex-grow-1">
                        <h6 class="alert-heading mb-1">${title}</h6>
                        <p class="mb-0">${message}</p>
                        ${errorsList}
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;

            messageContainer.innerHTML = messageHtml;

            // Auto dismiss after 10 seconds for success, 15 seconds for errors
            const dismissTime = type === 'success' ? 10000 : 15000;
            setTimeout(() => {
                const alert = messageContainer.querySelector('.alert');
                if (alert) {
                    alert.remove();
                }
            }, dismissTime);
        }

        // Handle form submission
        batchUploadForm.addEventListener('submit', function(e) {
            // Show loading state on the button
            startUploadBtn.disabled = true;
            cancelUploadBtn.disabled = true;

            startUploadBtn.innerHTML = `
        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
        Processing...
    `;
        });
    });