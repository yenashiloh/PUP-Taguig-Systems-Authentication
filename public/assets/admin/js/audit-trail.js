// Filter table functionality - FIXED VERSION
function filterTable() {
    const actionFilter = document.getElementById('actionFilter').value.toLowerCase();
    const targetTypeFilter = document.getElementById('targetTypeFilter').value.toLowerCase();
    const dateFromFilter = document.getElementById('dateFromFilter').value;
    const dateToFilter = document.getElementById('dateToFilter').value;

    const table = $('#userTable').DataTable();
    
    // Clear existing custom search functions
    $.fn.dataTable.ext.search = $.fn.dataTable.ext.search.filter(function(fn) {
        return fn.toString().indexOf('auditTrailCustomFilter') === -1;
    });
    
    // Add our custom filtering function with a unique identifier
    $.fn.dataTable.ext.search.push(
        function auditTrailCustomFilter(settings, data, dataIndex) {
            // Make sure we're filtering the right table
            if (settings.nTable.id !== 'userTable') {
                return true;
            }
            
            const row = table.row(dataIndex).node();
            if (!row) return true;
            
            const action = $(row).attr('data-action') || '';
            const admin = $(row).attr('data-admin') || '';
            const targetType = $(row).attr('data-target-type') || '';
            const date = $(row).attr('data-date') || '';

            // Action filter
            if (actionFilter && !action.toLowerCase().includes(actionFilter)) {
                return false;
            }

            // Target type filter
            if (targetTypeFilter && !targetType.toLowerCase().includes(targetTypeFilter)) {
                return false;
            }

            // Date range filter
            if (dateFromFilter && date < dateFromFilter) {
                return false;
            }
            if (dateToFilter && date > dateToFilter) {
                return false;
            }

            return true;
        }
    );

    // Redraw the table
    table.draw();
}

// Clear all filters - FIXED VERSION
function clearFilters() {
    // Clear form values
    document.getElementById('actionFilter').value = '';
    document.getElementById('targetTypeFilter').value = '';
    document.getElementById('dateFromFilter').value = '';
    document.getElementById('dateToFilter').value = '';
    
    // Clear custom search functions
    $.fn.dataTable.ext.search = $.fn.dataTable.ext.search.filter(function(fn) {
        return fn.toString().indexOf('auditTrailCustomFilter') === -1;
    });
    
    // Reset DataTable
    const table = $('#userTable').DataTable();
    table.search('').columns().search('').draw();
}

// View full description
function viewFullDescription(description) {
    document.getElementById('fullDescriptionText').textContent = description;
    new bootstrap.Modal(document.getElementById('descriptionModal')).show();
}

