@include('admin.partials.link')
<title>View Deactivated User</title>

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
                            <h2>View Deactivated User Details</h2>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="breadcrumb-wrapper">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item">
                                        <a href="{{ route('admin.dashboard') }}">Home</a>
                                    </li>
                                    <li class="breadcrumb-item">
                                        <a href="{{ route('admin.deactivated-users') }}">Deactivated Users</a>
                                    </li>
                                    <li class="breadcrumb-item active" aria-current="page">
                                        View Details
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
                    <div class="col-lg-12">
                        <div class="card-style mb-30">
                            <!-- User Status Alert -->
                            <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
                                <div>
                                    <strong>Account Status:</strong> This user account is currently
                                    <strong>DEACTIVATED</strong>.
                                    <br>
                                    <small class="text-muted">
                                        Deactivated on:
                                        {{ $user->updated_at->timezone('Asia/Manila')->format('F d, Y \a\t h:i A') }}
                                    </small>

                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4 class="fw-bold">Account Details</h4>

                            </div>
                            <hr>

                            @if (session('success'))
                                <div class="alert alert-success alert-dismissible fade show my-success-alert"
                                    role="alert">
                                    {{ session('success') }}
                                </div>
                            @endif

                            @if (session('error'))
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    {{ session('error') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                                </div>
                            @endif

                            @if ($user->role === 'Faculty')
                                <!-- Faculty Form -->
                                <form action="{{ route('admin.user-management.update-faculty', $user->id) }}"
                                    method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="input-style-1">
                                                <label>First Name</label>
                                                <input type="text" name="first_name"
                                                    value="{{ old('first_name', $user->first_name) }}" required />
                                                @error('first_name')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="input-style-1">
                                                <label>Middle Name</label>
                                                <input type="text" name="middle_name"
                                                    value="{{ old('middle_name', $user->middle_name) }}" />
                                                @error('middle_name')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="input-style-1">
                                                <label>Last Name</label>
                                                <input type="text" name="last_name"
                                                    value="{{ old('last_name', $user->last_name) }}" required />
                                                @error('last_name')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="input-style-1">
                                                <label>Phone Number</label>
                                                <input type="text" name="phone_number"
                                                    value="{{ old('phone_number', $user->phone_number) }}" required />
                                                @error('phone_number')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="input-style-1">
                                                <label>Email Address</label>
                                                <input type="email" name="email"
                                                    value="{{ old('email', $user->email) }}" required />
                                                @error('email')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="input-style-1">
                                                <label>Employee Number</label>
                                                <input type="text" name="employee_number"
                                                    value="{{ old('employee_number', $user->employee_number) }}"
                                                    required />
                                                @error('employee_number')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="select-style-1">
                                                <label>Department</label>
                                                <div class="select-position">
                                                    <select name="department" required>
                                                        <option value="">Select Department</option>
                                                        @foreach ($departments as $department)
                                                            <option value="{{ $department->dept_name }}"
                                                                {{ old('department', $user->department) == $department->dept_name ? 'selected' : '' }}>
                                                                {{ $department->dept_name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                @error('department')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="select-style-1">
                                                <label>Employment Status</label>
                                                <div class="select-position">
                                                    <select name="employment_status" required>
                                                        <option value="">Select Employment Status</option>
                                                        <option value="Full-Time"
                                                            {{ old('employment_status', $user->employment_status) == 'Full-Time' ? 'selected' : '' }}>
                                                            Full-Time
                                                        </option>
                                                        <option value="Part-Time"
                                                            {{ old('employment_status', $user->employment_status) == 'Part-Time' ? 'selected' : '' }}>
                                                            Part-Time
                                                        </option>
                                                    </select>
                                                </div>
                                                @error('employment_status')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        @php
                                            $today = date('Y-m-d');
                                        @endphp

                                        <div class="col-md-4">
                                            <div class="input-style-1">
                                                <label>Birthdate</label>
                                                <input type="date" name="birthdate" class="form-control"
                                                    value="{{ old('birthdate', $user->birthdate) }}"
                                                    max="{{ $today }}" required>
                                                @error('birthdate')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-12 text-end">
                                            <button type="button" class="main-button success-btn btn-hover me-2"
                                                onclick="reactivateUser({{ $user->id }})">Reactivate Account
                                            </button>
                                            <button type="submit" class="main-button primary-btn btn-hover">
                                                Update
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            @else
                                <!-- Student Form -->
                                <form action="{{ route('admin.user-management.update-student', $user->id) }}"
                                    method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="input-style-1">
                                                <label>First Name</label>
                                                <input type="text" name="first_name"
                                                    value="{{ $user->first_name }}" required />
                                                @error('first_name')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="input-style-1">
                                                <label>Middle Name</label>
                                                <input type="text" name="middle_name"
                                                    value="{{ $user->middle_name ?? 'N/A' }}" required />
                                                @error('middle_name')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="input-style-1">
                                                <label>Last Name</label>
                                                <input type="text" name="last_name"
                                                    value="{{ $user->last_name }}" required />
                                                @error('last_name')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="input-style-1">
                                                <label>Email Address</label>
                                                <input type="email" name="email" value="{{ $user->email }}"
                                                    required />
                                                @error('email')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="select-style-1">
                                                <label>Program</label>
                                                <div class="select-position">
                                                    <select name="program" required>
                                                        <option value="">Select Program</option>
                                                        @foreach ($courses as $course)
                                                            <option value="{{ $course->course_name }}"
                                                                {{ $user->program == $course->course_name ? 'selected' : '' }}>
                                                                {{ $course->course_name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                @error('program')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="input-style-1">
                                                <label>Student Number</label>
                                                <input type="text" name="student_number"
                                                    value="{{ $user->student_number }}" required />
                                                @error('student_number')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="select-style-1">
                                                <label>Year</label>
                                                <div class="select-position">
                                                    <select name="year" required>
                                                        <option value="">Select Year</option>
                                                        <option value="1st Year"
                                                            {{ $user->year == '1st Year' ? 'selected' : '' }}>1st Year
                                                        </option>
                                                        <option value="2nd Year"
                                                            {{ $user->year == '2nd Year' ? 'selected' : '' }}>2nd Year
                                                        </option>
                                                        <option value="3rd Year"
                                                            {{ $user->year == '3rd Year' ? 'selected' : '' }}>3rd Year
                                                        </option>
                                                        <option value="4th Year"
                                                            {{ $user->year == '4th Year' ? 'selected' : '' }}>4th Year
                                                        </option>
                                                    </select>
                                                </div>
                                                @error('year')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="select-style-1">
                                                <label>Section</label>
                                                <div class="select-position">
                                                    <select name="section" required>
                                                        <option value="">Select Section</option>
                                                        <option value="1"
                                                            {{ $user->section == '1' ? 'selected' : '' }}>1</option>
                                                        <option value="2"
                                                            {{ $user->section == '2' ? 'selected' : '' }}>2</option>
                                                        <option value="3"
                                                            {{ $user->section == '3' ? 'selected' : '' }}>3</option>
                                                        <option value="4"
                                                            {{ $user->section == '4' ? 'selected' : '' }}>4</option>
                                                        <option value="5"
                                                            {{ $user->section == '5' ? 'selected' : '' }}>5</option>
                                                        <option value="6"
                                                            {{ $user->section == '6' ? 'selected' : '' }}>6</option>
                                                        <option value="7"
                                                            {{ $user->section == '7' ? 'selected' : '' }}>7</option>
                                                        <option value="8"
                                                            {{ $user->section == '8' ? 'selected' : '' }}>8</option>
                                                        <option value="9"
                                                            {{ $user->section == '9' ? 'selected' : '' }}>9</option>
                                                        <option value="10"
                                                            {{ $user->section == '10' ? 'selected' : '' }}>10</option>
                                                    </select>
                                                </div>
                                                @error('section')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        @php
                                            $today = date('Y-m-d');
                                        @endphp

                                        <div class="col-md-4">
                                            <div class="input-style-1">
                                                <label>Birthdate</label>
                                                <input type="date" name="birthdate" class="form-control"
                                                    value="{{ old('birthdate', $user->birthdate) }}"
                                                    max="{{ $today }}" required>
                                                @error('birthdate')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-12 text-end">
                                            <button type="button" class="main-button success-btn btn-hover me-2"
                                                onclick="reactivateUser({{ $user->id }})">
                                                <i class="fas fa-check-circle me-1"></i> Reactivate Account
                                            </button>
                                            <button type="submit" class="main-button primary-btn btn-hover">
                                                Update
                                            </button>
                                            <a href="" class="main-button secondary-btn btn-hover">
                                                View Activity Log
                                            </a>
                                        </div>
                                    </div>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<script>
    // Reactivate user function
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
                // Show loading state
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
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
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
                                // Redirect to the active user management page or reload
                                window.location.href = '{{ route('admin.deactivated-users') }}';
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
    document.addEventListener('DOMContentLoaded', function() {
        const successAlert = document.querySelector('.my-success-alert');
        if (successAlert) {
            setTimeout(() => {
                successAlert.remove();
            }, 5000);
        }
    });
</script>
<script>
    const routeToDeactivatedUsers = @json(route('admin.deactivated-users'));
</script>
@include('admin.partials.footer')
<script src="../../assets/admin/js/view-deactivated-user.js"></script>


