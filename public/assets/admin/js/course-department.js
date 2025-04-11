
/**
 * Courses Destroy and Confirmation 
 */
document.addEventListener('DOMContentLoaded', function() {
    // Get CSRF token from meta tag
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    document.querySelectorAll('.delete-course-btn').forEach(button => {
        button.addEventListener('click', function() {
            const courseId = this.getAttribute('data-id');
            // Find the closest parent row/container element
            const courseElement = this.closest('tr') || this.closest('.course-item');

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