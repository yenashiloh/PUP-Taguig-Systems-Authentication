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