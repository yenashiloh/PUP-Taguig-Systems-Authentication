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

$(document).ready(function () {
    const table = $('#userTable').DataTable({
        dom: '<"top"fB>rt<"bottom"lip><"clear">', // f = filter (search), B = buttons
        buttons: ['copy', 'csv', 'excel', 'pdf', 'print']
    });

    // Move Add Users button below search
    $('#customButtons').insertAfter('.dataTables_filter');
});

/**
 * Function to filter the faculty table based on selected filters.
 */
function filterTable() {
    const departmentFilter = document.getElementById('departmentFilter')?.value.toLowerCase() || '';
    const employmentStatusFilter = document.getElementById('employmentStatusFilter')?.value.toLowerCase() || '';
    const accountStatusFilter = document.getElementById('accountStatusFilter')?.value.toLowerCase() || '';
    
    console.log('Faculty Filters:', {
        departmentFilter,
        employmentStatusFilter,
        accountStatusFilter
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
        const department = row.dataset.department?.toLowerCase() || '';
        const employmentStatus = row.dataset.employmentStatus?.toLowerCase() || '';
        const status = row.dataset.status?.toLowerCase() || '';
        
        console.log('Row data:', {
            department,
            employmentStatus,
            status
        });
        
        // Check if the row matches all selected filters
        // Show the row if no filter is selected or if it matches the filter
        const matchesDepartment = !departmentFilter || department.includes(departmentFilter);
        const matchesEmploymentStatus = !employmentStatusFilter || employmentStatus.includes(employmentStatusFilter);
        const matchesAccountStatus = !accountStatusFilter || status.includes(accountStatusFilter);
        
        if (matchesDepartment && matchesEmploymentStatus && matchesAccountStatus) {
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