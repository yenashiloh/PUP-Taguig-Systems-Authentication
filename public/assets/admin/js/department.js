// Handle edit department button click
document.addEventListener('DOMContentLoaded', function() {
    // Edit Department Modal
    const editButtons = document.querySelectorAll('.edit-department-btn');
    const editForm = document.getElementById('editDepartmentForm');
    const editDeptId = document.getElementById('edit_department_id');
    const editDeptName = document.getElementById('edit_dept_name');
    const editDeptStatus = document.getElementById('edit_dept_status');

    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const departmentId = this.getAttribute('data-id');
            const departmentName = this.getAttribute('data-name');
            const departmentStatus = this.getAttribute('data-status');

            // Populate the edit form
            editDeptId.value = departmentId;
            editDeptName.value = departmentName;
            editDeptStatus.value = departmentStatus;

            // Set the form action
            editForm.action = `/admin/settings/department/${departmentId}/update`;
        });
    });

    // Handle edit form submission
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const departmentId = editDeptId.value;

            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    Swal.fire({
                        title: 'Success!',
                        text: data.message,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        // Close modal and reload page
                        const modal = bootstrap.Modal.getInstance(document.getElementById('editDepartmentModal'));
                        modal.hide();
                        location.reload();
                    });
                } else {
                    // Show error message
                    Swal.fire({
                        title: 'Error!',
                        text: data.message || 'An error occurred while updating the department.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'An error occurred while updating the department.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            });
        });
    }

    // Handle toggle status button
    const toggleStatusButtons = document.querySelectorAll('.toggle-status-btn');
    toggleStatusButtons.forEach(button => {
        button.addEventListener('click', function() {
            const departmentId = this.getAttribute('data-id');
            const currentStatus = this.getAttribute('data-current-status');
            const newStatus = currentStatus === 'Active' ? 'Inactive' : 'Active';
            const actionText = currentStatus === 'Active' ? 'disable' : 'enable';

            Swal.fire({
                title: 'Are you sure?',
                text: `Do you want to ${actionText} this department?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: `Yes, ${actionText} it!`
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/admin/settings/department/${departmentId}/toggle-status`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            status: newStatus
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Updated!',
                                text: data.message,
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: data.message || 'An error occurred.',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            title: 'Error!',
                            text: 'An error occurred while updating the department status.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    });
                }
            });
        });
    });

    // Delete Department functionality
    const deleteButtons = document.querySelectorAll('.delete-department-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const departmentId = this.getAttribute('data-id');

            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/departments/${departmentId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Deleted!',
                                text: data.message,
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                // Remove the row from the table
                                document.getElementById(`department-${departmentId}`).remove();
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: data.message || 'An error occurred.',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            title: 'Error!',
                            text: 'An error occurred while deleting the department.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    });
                }
            });
        });
    });

    // Auto-hide success alerts
    setTimeout(function() {
        const successAlert = document.querySelector('.my-success-alert');
        if (successAlert) {
            successAlert.style.display = 'none';
        }
    }, 5000);
});