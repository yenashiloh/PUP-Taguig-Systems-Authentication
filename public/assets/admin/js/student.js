/**
 * description: This script handles the functionality for managing student accounts in an admin panel.
 */
// Get CSRF token from the meta tag
var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

function toggleAccountStatus(userId, action) {
    let actionText = action === 'deactivate' ? 'deactivate' : 'reactivate';
    let confirmButtonText = action === 'deactivate' ? 'Yes, deactivate it!' : 'Yes, reactivate it!';
    let statusText = action === 'deactivate' ? 'Deactivated' : 'Active';
    
    // Opposite action that will be available after the current action completes
    let oppositeAction = action === 'deactivate' ? 'reactivate' : 'deactivate';
    let oppositeButtonText = action === 'deactivate' ? 'Reactivate' : 'Deactivate';
    let oppositeButtonClass = action === 'deactivate' ? 'warning-btn' : 'danger-btn';

    Swal.fire({
        title: 'Are you sure?',
        text: `Do you want to ${actionText} this account?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: action === 'deactivate' ? '#d33' : '#F7C800',
        cancelButtonColor: '#3085d6',
        confirmButtonText: confirmButtonText,
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/admin/toggle-user-status/' + userId,
                type: 'POST',
                data: {
                    _token: csrfToken,
                    action: action
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire(
                            `${statusText}!`,
                            `The account has been ${statusText.toLowerCase()}.`,
                            'success'
                        );

                        // Update the status text in the table
                        $(`#user-${userId} .status-text`).text(statusText);
                        
                        // Replace the current action button with the opposite action
                        let newButton = `<button class="main-button ${oppositeButtonClass} btn-hover mb-1" 
                            onclick="toggleAccountStatus(${userId}, '${oppositeAction}')">
                            ${oppositeButtonText}
                        </button>`;
                        
                        // Replace the button
                        $(`#user-${userId} .toggle-btn-container`).html(newButton);
                        
                        // No page reload needed!
                    } else {
                        Swal.fire(
                            'Error!',
                            'There was an error updating the account status.',
                            'error'
                        );
                    }
                },
                error: function() {
                    Swal.fire(
                        'Error!',
                        'There was an error processing your request.',
                        'error'
                    );
                }
            });
        }
    });
}
/**
 * Filter Table Functionality
 */
function filterTable() {
    const programFilter = document.getElementById('programFilter').value.toLowerCase();
    const yearFilter = document.getElementById('yearFilter').value.toLowerCase();
    const sectionFilter = document.getElementById('sectionFilter').value.toLowerCase();
    const statusFilter = document.getElementById('accountStatusFilter').value.toLowerCase();
    
    console.log('Filters:', {
        programFilter,
        yearFilter,
        sectionFilter,
        statusFilter
    });
    
    const rows = document.querySelectorAll('#userTable tbody tr:not(.no-records-row)');
    let visibleRowCount = 0;
    
    // First remove any existing "no records" message
    const existingNoRecordsRow = document.querySelector('.no-records-row');
    if (existingNoRecordsRow) {
        existingNoRecordsRow.remove();
    }
    
    // Filter the rows
    rows.forEach(row => {
        // Get data attributes from the row
        const program = row.dataset.program?.toLowerCase() || '';
        const year = row.dataset.year?.toLowerCase() || '';
        const section = row.dataset.section?.toLowerCase() || '';
        const status = row.dataset.status?.toLowerCase() || '';
        
        console.log('Row data:', {
            program,
            year,
            section,
            status
        });
        
        // Check if the row matches all selected filters
        // Show the row if no filter is selected or if it matches the filter
        const matchesProgram = !programFilter || program.includes(programFilter);
        const matchesYear = !yearFilter || year.includes(yearFilter);
        const matchesSection = !sectionFilter || section.includes(sectionFilter);
        const matchesStatus = !statusFilter || status.includes(statusFilter);
        
        if (matchesProgram && matchesYear && matchesSection && matchesStatus) {
            row.style.display = '';
            visibleRowCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    // If no rows are visible, add a "No records found" row
    if (visibleRowCount === 0) {
        const tbody = document.querySelector('#userTable tbody');
        const noRecordsRow = document.createElement('tr');
        noRecordsRow.className = 'no-records-row';
        
        // Create a cell that spans all columns
        const noRecordsCell = document.createElement('td');
        const columnCount = document.querySelectorAll('#userTable thead th').length;
        noRecordsCell.colSpan = columnCount;
        noRecordsCell.className = 'text-center py-3';
        noRecordsCell.innerHTML = '<p class="text-muted mb-0">No records found matching the selected filters.</p>';
        
        noRecordsRow.appendChild(noRecordsCell);
        tbody.appendChild(noRecordsRow);
    }
}
/**
 * Add Student Data 
 */

document.addEventListener('DOMContentLoaded', function() {
    const addUserForm = document.getElementById('addUserForm');

    if (addUserForm) {
        addUserForm.addEventListener('submit', function(event) {
            event.preventDefault();

            // Form validation
            if (!addUserForm.checkValidity()) {
                event.stopPropagation();
                addUserForm.classList.add('was-validated');
                return;
            }

            // Show loading state on the button
            const submitButton = document.querySelector('button[type="submit"][form="addUserForm"]');
            const originalButtonText = submitButton.innerHTML;
            submitButton.disabled = true;
            submitButton.innerHTML =
                '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';

            // Get form data
            const formData = new FormData(addUserForm);
            const formDataObj = {};
            formData.forEach((value, key) => {
                formDataObj[key] = value;
            });

            // Send AJAX request
            fetch('/students/store', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': formData.get('_token')
                },
                body: JSON.stringify(formDataObj)
            })
                .then(response => response.json())
                .then(data => {
                    const formAlert = document.getElementById('formAlert');
                
                    if (data.errors) {
                        // Display validation errors
                        formAlert.className = 'alert alert-danger';
                        let errorMessages = '';
                        for (const [key, errors] of Object.entries(data.errors)) {
                            errors.forEach(error => {
                                errorMessages += `<li>${error}</li>`;
                            });
                        }
                        formAlert.innerHTML = `<ul>${errorMessages}</ul>`;
                        formAlert.classList.remove('d-none');
                    } else {
                        // Success message
                        formAlert.className = 'alert alert-success';
                        formAlert.textContent = data.message;
                        formAlert.classList.remove('d-none');
                
                        // Hide success message after 5 seconds
                        setTimeout(() => {
                            formAlert.classList.add('d-none');
                        }, 5000);
                
                        // Reset form
                        addUserForm.reset();
                        addUserForm.classList.remove('was-validated');
                
                        // Update the table with the new student
                        const userTable = document.querySelector('#userTable tbody');
                        const newRow = document.createElement('tr');
                        newRow.id = `user-${data.user.id}`;
                        newRow.setAttribute('data-program', data.user.program || '');
                        newRow.setAttribute('data-year', data.user.year || '');
                        newRow.setAttribute('data-section', data.user.section || '');
                        newRow.setAttribute('data-status', data.user.status || 'Active');
                
                        newRow.innerHTML = `
                            <td class="min-width">
                                <div class="lead">
                                    <p>${data.user.student_number || 'No ID Available'}</p>
                                </div>
                            </td>
                            <td class="min-width">
                                <p>${data.user.last_name || ''}</p>
                            </td>
                            <td class="min-width">
                                <p><a href="#0">${data.user.first_name || ''}</a></p>
                            </td>
                            <td class="min-width">
                                <p><a href="#0">${data.user.email || ''}</a></p>
                            </td>
                            <td class="min-width">
                                <p class="status-text">${data.user.status || 'Active'}</p>
                            </td>
                            <td>
                                <button class="main-button secondary-btn btn-hover mb-1"
                                    onclick="window.location='/admin/user-management/view-student/${data.user.id}'">
                                    View
                                </button>
                                <span class="toggle-btn-container">
                                    <button class="main-button danger-btn btn-hover mb-1"
                                        onclick="toggleAccountStatus(${data.user.id}, 'deactivate')">
                                        Deactivate
                                    </button>
                                </span>
                            </td>
                        `;
                
                        // Append the new row to the table
                        userTable.appendChild(newRow);
                
                        // Close modal after 2 seconds
                        setTimeout(() => {
                            const modal = bootstrap.Modal.getInstance(document.getElementById('addUserModal'));
                            modal.hide();
                        }, 2000);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    const formAlert = document.getElementById('formAlert');
                    formAlert.className = 'alert alert-danger';
                    formAlert.textContent = 'An error occurred. Please try again.';
                    formAlert.classList.remove('d-none');
                })
                .finally(() => {
                    // Reset button state regardless of success or failure
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalButtonText;
                });
        });
    }
});

//
document.addEventListener('DOMContentLoaded', function() {
    const importForm = document.getElementById('importForm');
    const importBtn = document.getElementById('importBtn');
    const importBtnText = document.getElementById('importBtnText');
    const importBtnLoading = document.getElementById('importBtnLoading');
    const importLoading = document.getElementById('importLoading');
    const cancelBtn = document.getElementById('cancelBtn');
    const closeModalBtn = document.getElementById('closeModalBtn');
    const importFile = document.getElementById('importFile');

    if (importForm) {
        importForm.addEventListener('submit', function(e) {
            // Check if file is selected
            if (!importFile.files.length) {
                e.preventDefault();
                alert('Please select a file to import.');
                return;
            }

            // Show loading state
            importBtnText.style.display = 'none';
            importBtnLoading.style.display = 'inline';
            importBtn.disabled = true;
            
            // Disable close buttons during processing
            cancelBtn.disabled = true;
            closeModalBtn.disabled = true;
            
            // Show loading message
            importLoading.style.display = 'block';
            
            // Disable file input
            importFile.disabled = true;
        });
    }

    // Reset form when modal is closed
    const importModal = document.getElementById('importModal');
    if (importModal) {
        importModal.addEventListener('hidden.bs.modal', function() {
            // Reset loading state
            importBtnText.style.display = 'inline';
            importBtnLoading.style.display = 'none';
            importBtn.disabled = false;
            
            // Re-enable buttons
            cancelBtn.disabled = false;
            closeModalBtn.disabled = false;
            
            // Hide loading message
            importLoading.style.display = 'none';
            
            // Re-enable file input
            importFile.disabled = false;
            
            // Reset form
            importForm.reset();
        });
    }
});