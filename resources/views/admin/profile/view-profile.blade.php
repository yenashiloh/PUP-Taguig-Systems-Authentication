@include('admin.partials.link')
<title>Admin Profile</title>

@include('admin.partials.side-bar')

<!-- ======== main-wrapper start =========== -->
<main class="main-wrapper">

    @include('admin.partials.header')

    <!-- ========== section start ========== -->
    <section class="section">
        <div class="container-fluid">
            <!-- ========== title-wrapper start ========== -->
            <div class="title-wrapper pt-30">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="title">
                            <h2>Admin Profile</h2>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="breadcrumb-wrapper">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item">
                                        <a href="{{ route('admin.dashboard') }}">Home</a>
                                    </li>
                                    <li class="breadcrumb-item active" aria-current="page">
                                        Profile
                                    </li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
                <!-- end row -->
            </div>

            <div class="form-elements-wrapper">
                <div class="row">
                    <!-- Profile Information Card -->
                    <div class="col-lg-8">
                        <div class="card-style mb-30">
                            <h4 class="mb-25 fw-bold">Profile Information</h4>
                            <hr>

                            @if (session('success'))
                                <div class="alert alert-success alert-dismissible fade show my-success-alert" role="alert">
                                    <i class="fas fa-check-circle me-2"></i>
                                    {{ session('success') }}
                                </div>
                            @endif

                            @if (session('error'))
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    {{ session('error') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            <form action="{{ route('admin.profile.update') }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="input-style-1">
                                            <label>First Name <span class="text-danger">*</span></label>
                                            <input type="text" name="first_name" value="{{ old('first_name', $admin->first_name) }}" required />
                                            @error('first_name')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="input-style-1">
                                            <label>Last Name <span class="text-danger">*</span></label>
                                            <input type="text" name="last_name" value="{{ old('last_name', $admin->last_name) }}" required />
                                            @error('last_name')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="input-style-1">
                                            <label>Email Address <span class="text-danger">*</span></label>
                                            <input type="email" name="email" value="{{ old('email', $admin->email) }}" required />
                                            @error('email')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="input-style-1">
                                            <label>Contact Number <span class="text-danger">*</span></label>
                                            <input type="text" name="contact_number" value="{{ old('contact_number', $admin->contact_number) }}" required />
                                            @error('contact_number')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-12 text-end">
                                        <button type="submit" class="main-button primary-btn btn-hover">
                                            <i class="fas fa-save me-1"></i> Update Profile
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Profile Summary Card -->
                    <div class="col-lg-4">
                        <div class="card-style mb-30">
                            <h4 class="mb-25 fw-bold">Profile Summary</h4>
                            <hr>
                            
                            <div class="profile-summary">
                                <!-- Profile Image -->
                                <div class="text-center mb-3">
                                    <div class="profile-image-wrapper">
                                        <img src="../../assets/admin/images/profile-picture.png" alt="Profile Picture" 
                                             class="rounded-circle" style="width: 120px; height: 120px; object-fit: cover; border: 4px solid #7e0e09;">
                                    </div>
                                    <h5 class="mt-3 mb-1">{{ $admin->first_name }} {{ $admin->last_name }}</h5>
                                    <span class="badge bg-primary">System Administrator</span>
                                </div>

                                <!-- Profile Details -->
                                <div class="profile-details">
                                    <div class="detail-item mb-3">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-envelope text-primary me-2"></i>
                                            <div>
                                                <small class="text-muted">Email</small>
                                                <p class="mb-0 fw-medium">{{ $admin->email }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="detail-item mb-3">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-phone text-primary me-2"></i>
                                            <div>
                                                <small class="text-muted">Contact</small>
                                                <p class="mb-0 fw-medium">{{ $admin->contact_number }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="detail-item mb-3">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-calendar text-primary me-2"></i>
                                            <div>
                                                <small class="text-muted">Member Since</small>
                                                <p class="mb-0 fw-medium">{{ $admin->created_at->format('F Y') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="detail-item">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-clock text-primary me-2"></i>
                                            <div>
                                                <small class="text-muted">Last Updated</small>
                                                <p class="mb-0 fw-medium">{{ $admin->updated_at->format('M d, Y') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Change Password Card -->
                    <div class="col-lg-8">
                        <div class="card-style mb-30">
                            <h4 class="mb-25 fw-bold">Change Password</h4>
                            <hr>

                            @if (session('password_success'))
                                <div class="alert alert-success alert-dismissible fade show my-success-alert" role="alert">
                                    <i class="fas fa-check-circle me-2"></i>
                                    {{ session('password_success') }}
                                </div>
                            @endif

                            @if (session('password_error'))
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    {{ session('password_error') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            <form action="{{ route('admin.profile.update-password') }}" method="POST" id="passwordForm">
                                @csrf
                                @method('PUT')
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="input-style-1">
                                            <label>Current Password <span class="text-danger">*</span></label>
                                            <div class="password-container position-relative">
                                                <input type="password" name="current_password" id="current_password" required />
                                                <span class="password-toggle position-absolute" onclick="togglePassword('current_password')" style="right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;">
                                                    <i class="fas fa-eye" id="current_password_icon"></i>
                                                </span>
                                            </div>
                                            @error('current_password')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="input-style-1">
                                            <label>New Password <span class="text-danger">*</span></label>
                                            <div class="password-container position-relative">
                                                <input type="password" name="new_password" id="new_password" required />
                                                <span class="password-toggle position-absolute" onclick="togglePassword('new_password')" style="right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;">
                                                    <i class="fas fa-eye" id="new_password_icon"></i>
                                                </span>
                                            </div>
                                            <small class="text-muted">
                                                Password must contain at least 8 characters, including uppercase, lowercase, number, and special character.
                                            </small>
                                            @error('new_password')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="input-style-1">
                                            <label>Confirm Password <span class="text-danger">*</span></label>
                                            <div class="password-container position-relative">
                                                <input type="password" name="new_password_confirmation" id="new_password_confirmation" required />
                                                <span class="password-toggle position-absolute" onclick="togglePassword('new_password_confirmation')" style="right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;">
                                                    <i class="fas fa-eye" id="new_password_confirmation_icon"></i>
                                                </span>
                                            </div>
                                            @error('new_password_confirmation')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-12 text-end">
                                        <button type="button" class="main-button light-btn btn-hover me-2" onclick="clearPasswordForm()">
                                            <i class="fas fa-times me-1"></i> Clear
                                        </button>
                                        <button type="submit" class="main-button primary-btn btn-hover">
                                            <i class="fas fa-key me-1"></i> Update Password
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Account Activity Card -->
                    <div class="col-lg-4">
                        <div class="card-style mb-30">
                            <h4 class="mb-25 fw-bold">Account Activity</h4>
                            <hr>
                            
                            <div class="activity-summary">
                                <div class="activity-item d-flex align-items-center mb-3">
                                    <div class="activity-icon bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                        <i class="fas fa-sign-in-alt"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Last Login</h6>
                                        <small class="text-muted">Today at {{ now()->format('h:i A') }}</small>
                                    </div>
                                </div>
                                
                                <div class="activity-item d-flex align-items-center mb-3">
                                    <div class="activity-icon bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                        <i class="fas fa-edit"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Profile Updated</h6>
                                        <small class="text-muted">{{ $admin->updated_at->diffForHumans() }}</small>
                                    </div>
                                </div>
                                
                                <div class="activity-item d-flex align-items-center mb-3">
                                    <div class="activity-icon bg-warning text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                        <i class="fas fa-shield-alt"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Security Status</h6>
                                        <small class="text-success">All systems secure</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<script>
    // Toggle password visibility
    function togglePassword(fieldId) {
        const field = document.getElementById(fieldId);
        const icon = document.getElementById(fieldId + '_icon');
        
        if (field.type === 'password') {
            field.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            field.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    // Clear password form
    function clearPasswordForm() {
        document.getElementById('passwordForm').reset();
        
        // Reset all password fields to password type
        ['current_password', 'new_password', 'new_password_confirmation'].forEach(fieldId => {
            const field = document.getElementById(fieldId);
            const icon = document.getElementById(fieldId + '_icon');
            
            field.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        });
    }

    // Auto-hide success alerts
    document.addEventListener('DOMContentLoaded', function() {
        const successAlerts = document.querySelectorAll('.my-success-alert');
        successAlerts.forEach(alert => {
            setTimeout(() => {
                alert.remove();
            }, 5000);
        });
    });
</script>

@include('admin.partials.footer')

<style>
    .profile-image-wrapper {
        position: relative;
        display: inline-block;
    }
    
    .profile-details .detail-item {
        padding: 10px 0;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .profile-details .detail-item:last-child {
        border-bottom: none;
    }
    
    .activity-summary .activity-item {
        padding: 8px 0;
    }
    
    .password-container {
        position: relative;
    }
    
    .password-toggle {
        z-index: 10;
    }
    
    .card-style {
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        border-radius: 8px;
    }
</style>