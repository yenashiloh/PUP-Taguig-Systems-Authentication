// Get CSRF token from the meta tag
var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

function toggleAccountStatus(userId, action) {
    let actionText = action === 'deactivate' ? 'deactivate' : 'reactivate';
    let confirmButtonText = action === 'deactivate' ? 'Yes, deactivate it!' : 'Yes, reactivate it!';
    let statusText = action === 'deactivate' ? 'Deactivated' : 'Activate';
    
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

