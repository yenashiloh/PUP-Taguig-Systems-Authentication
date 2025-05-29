function reactivateUser(userId) {
    Swal.fire({
        title: 'Reactivate User Account?',
        html: `
            <div class="text-start">
                <p>Are you sure you want to reactivate this user account?</p>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>This action will:</strong>
                    <ul class="mt-2 mb-0">
                        <li>Restore full access to the system</li>
                        <li>Allow the user to log in again</li>
                        <li>Update the account status to "Active"</li>
                    </ul>
                </div>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-check-circle me-1"></i> Yes, Reactivate',
        cancelButtonText: '<i class="fas fa-times me-1"></i> Cancel',
        width: '500px'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Reactivating...',
                text: 'Please wait while we reactivate the user account.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch(`/admin/reactivate-user/${userId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: data.message,
                        icon: 'success',
                        confirmButtonColor: '#28a745',
                        confirmButtonText: 'Continue'
                    }).then(() => {
                        window.location.href = routeToDeactivatedUsers; // Defined globally in Blade
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: data.message,
                        icon: 'error',
                        confirmButtonColor: '#dc3545',
                        confirmButtonText: 'Try Again'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'An error occurred while reactivating the user. Please try again.',
                    icon: 'error',
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'Try Again'
                });
            });
        }
    });
}

// Auto-hide success alerts
document.addEventListener('DOMContentLoaded', function () {
    const successAlert = document.querySelector('.my-success-alert');
    if (successAlert) {
        setTimeout(() => {
            successAlert.remove();
        }, 5000);
    }
});