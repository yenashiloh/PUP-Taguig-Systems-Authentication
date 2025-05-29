

/**
 * Department Destroy and Confirmation
 */

// Department deletion functionality
document.addEventListener('DOMContentLoaded', function() {
    // Get CSRF token from meta tag
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    document.querySelectorAll('.delete-department-btn').forEach(button => {
        button.addEventListener('click', function() {
            const departmentId = this.getAttribute('data-id');
            // Find the closest parent row element
            const departmentElement = this.closest('tr');

            Swal.fire({
                title: 'Are you sure?',
                text: "This department will be deleted permanently!",
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
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            },
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                if (departmentElement) {
                                    departmentElement.remove();
                                }

                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: data.message,
                                    confirmButtonText: 'OK',
                                    confirmButtonColor: '#3085d6',
                                    showConfirmButton: true,
                                    allowOutsideClick: false,
                                });
                            }
                        })
                        .catch(error => {
                            Swal.fire('Error', 'Something went wrong.', 'error');
                            console.error('Delete failed:', error);
                        });
                }
            });
        });
    });
});

/**
 * Course Management - Deletion and Status Toggle
 */
document.addEventListener('DOMContentLoaded', function() {
    // Get CSRF token from meta tag
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Course Deletion Functionality
    document.querySelectorAll('.delete-course-btn').forEach(button => {
        button.addEventListener('click', function() {
            const courseId = this.getAttribute('data-id');
            const courseElement = this.closest('tr');

            Swal.fire({
                title: 'Are you sure?',
                text: "This course will be deleted permanently!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/courses/${courseId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            if (courseElement) {
                                courseElement.remove();
                            }

                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: data.message,
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#3085d6',
                                showConfirmButton: true,
                                allowOutsideClick: false,
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message,
                                confirmButtonColor: '#d33',
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Something went wrong.',
                            confirmButtonColor: '#d33',
                        });
                        console.error('Delete failed:', error);
                    });
                }
            });
        });
    });

    // Course Status Toggle Functionality
    document.querySelectorAll('.toggle-course-status-btn').forEach(button => {
        button.addEventListener('click', function() {
            const courseId = this.getAttribute('data-id');
            const currentStatus = this.getAttribute('data-current-status');
            const newStatus = currentStatus === 'Active' ? 'Inactive' : 'Active';
            const actionText = currentStatus === 'Active' ? 'disable' : 'enable';
            const courseElement = this.closest('tr');

            Swal.fire({
                title: 'Are you sure?',
                text: `Do you want to ${actionText} this course?`,
                text: `This will ${actionText} the course!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: `Yes, ${actionText} it!`
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/courses/${courseId}/toggle-status`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ status: newStatus })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            if (courseElement) {
                                const statusText = courseElement.querySelector('.status-text');
                                const toggleButton = courseElement.querySelector('.toggle-course-status-btn');
                                statusText.textContent = newStatus;

                                if (newStatus === 'Active') {
                                    toggleButton.classList.remove('btn-outline-success');
                                    toggleButton.classList.add('btn-outline-danger');
                                    toggleButton.innerHTML = '<i class="fas fa-ban me-1"></i> Disable';
                                    toggleButton.setAttribute('data-current-status', 'Active');
                                } else {
                                    toggleButton.classList.remove('btn-outline-danger');
                                    toggleButton.classList.add('btn-outline-success');
                                    toggleButton.innerHTML = '<i class="fas fa-check-circle me-1"></i> Enable';
                                    toggleButton.setAttribute('data-current-status', 'Inactive');
                                }
                            }

                            Swal.fire({
                                icon: 'success',
                                title: `${actionText.charAt(0).toUpperCase() + actionText.slice(1)}d!`,
                                text: data.message,
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#3085d6',
                                showConfirmButton: true,
                                allowOutsideClick: false,
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message,
                                confirmButtonColor: '#d33',
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Something went wrong.',
                            confirmButtonColor: '#d33',
                        });
                        console.error('Status toggle failed:', error);
                    });
                }
            });
        });
    });

  
});