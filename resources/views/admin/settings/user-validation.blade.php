@include('admin.partials.link')
<title>User Validation</title>

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
                            <h2>User Validation</h2>
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
                                        User Validation
                                    </li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
                <!-- end row -->
            </div>

            <!-- Alert Messages -->
            <div id="alertContainer"></div>

            <!-- User Validation Settings -->
            <div class="form-elements-wrapper">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card-style mb-30">
                            <h4 class="mb-25 fw-bold">Validation Settings</h4>
                            <p class="text-muted mb-4">Configure validation rules for student numbers and employee
                                numbers. These settings will be applied during registration.</p>
                            <hr>

                             <!-- Current Settings Summary -->
                            <div class="row mb-2">
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <h6 class="alert-heading mb-2">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Current Validation Rules
                                        </h6>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <strong>Student Number:</strong>
                                                <ul class="mb-0 mt-1">
                                                    <li>Length: {{ $studentValidation->min_digits }} -
                                                        {{ $studentValidation->max_digits }} characters</li>
                                                    <li>Characters:
                                                        @if ($studentValidation->numbers_only)
                                                            Numbers Only (0-9)
                                                        @elseif($studentValidation->letters_only)
                                                            Letters Only (A-Z, a-z)
                                                        @else
                                                            Letters, Numbers & Symbols (A-Z, a-z, 0-9, -, _)
                                                        @endif
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="col-md-6">
                                                <strong>Employee Number:</strong>
                                                <ul class="mb-0 mt-1">
                                                    <li>Length: {{ $employeeValidation->min_digits }} -
                                                        {{ $employeeValidation->max_digits }} characters</li>
                                                    <li>Characters:
                                                        @if ($employeeValidation->numbers_only)
                                                            Numbers Only (0-9)
                                                        @elseif($employeeValidation->letters_only)
                                                            Letters Only (A-Z, a-z)
                                                        @else
                                                            Letters, Numbers & Symbols (A-Z, a-z, 0-9, -, _)
                                                        @endif
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Student Number Validation -->
                            <div class="row mb-5">
                                <div class="col-12">
                                    <h5 class="mb-3 text-primary">Student Number Validation</h5>
                                    <form id="studentValidationForm">
                                        @csrf
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="input-style-1">
                                                    <label>Minimum Characters <span class="text-danger">*</span></label>
                                                    <input type="number" name="min_digits" id="student_min_digits"
                                                        value="{{ $studentValidation->min_digits }}" min="1"
                                                        max="50" required class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="input-style-1">
                                                    <label>Maximum Characters <span class="text-danger">*</span></label>
                                                    <input type="number" name="max_digits" id="student_max_digits"
                                                        value="{{ $studentValidation->max_digits }}" min="1"
                                                        max="50" required class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="select-style-1">
                                                    <label>Allowed Characters <span class="text-danger">*</span></label>
                                                    <div class="select-position">
                                                        <select name="allowed_characters" required>
                                                            <option value="numbers_only"
                                                                {{ $studentValidation->numbers_only ? 'selected' : '' }}>
                                                                Numbers Only
                                                            </option>
                                                            <option value="letters_only"
                                                                {{ $studentValidation->letters_only ? 'selected' : '' }}>
                                                                Letters Only
                                                            </option>
                                                            <option value="letters_symbols_numbers"
                                                                {{ $studentValidation->letters_symbols_numbers ? 'selected' : '' }}>
                                                                Letters, Numbers & Symbols
                                                            </option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-1 d-flex align-items-end">
                                                <button type="submit" class="main-button primary-btn btn-hover w-100">
                                                   </i> Save
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Employee Number Validation -->
                            <div class="row">
                                <div class="col-12">
                                    <h5 class="mb-3 text-primary">Employee Number Validation</h5>
                                    <form id="employeeValidationForm">
                                        @csrf
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="input-style-1">
                                                    <label>Minimum Characters <span class="text-danger">*</span></label>
                                                    <input type="number" name="min_digits" id="employee_min_digits"
                                                        value="{{ $employeeValidation->min_digits }}" min="1"
                                                        max="50" required class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="input-style-1">
                                                    <label>Maximum Characters <span class="text-danger">*</span></label>
                                                    <input type="number" name="max_digits" id="employee_max_digits"
                                                        value="{{ $employeeValidation->max_digits }}" min="1"
                                                        max="50" required class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="select-style-1">
                                                    <label>Allowed Characters <span class="text-danger">*</span></label>
                                                    <div class="select-position">
                                                        <select name="allowed_characters" required>
                                                            <option value="numbers_only"
                                                                {{ $employeeValidation->numbers_only ? 'selected' : '' }}>
                                                                Numbers Only
                                                            </option>
                                                            <option value="letters_only"
                                                                {{ $employeeValidation->letters_only ? 'selected' : '' }}>
                                                                Letters Only
                                                            </option>
                                                            <option value="letters_symbols_numbers"
                                                                {{ $employeeValidation->letters_symbols_numbers ? 'selected' : '' }}>
                                                                Letters, Numbers & Symbols
                                                            </option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                              <div class="col-md-1 d-flex align-items-end">
                                                <button type="submit" class="main-button primary-btn btn-hover w-100">
                                                   </i> Save
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- end container -->
    </section>
    <!-- ========== section end ========== -->
</main>
<!-- ======== main-wrapper end =========== -->

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const alertContainer = document.getElementById('alertContainer');

        // Function to show alert
        function showAlert(message, type = 'success') {
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            const iconClass = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';

            const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                <i class="fas ${iconClass} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;

            alertContainer.innerHTML = alertHtml;

            // Auto hide after 5 seconds
            setTimeout(() => {
                const alertElement = alertContainer.querySelector('.alert');
                if (alertElement) {
                    alertElement.remove();
                }
            }, 5000);
        }

        // Student validation form
        document.getElementById('studentValidationForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;

            // Show loading state
            submitBtn.innerHTML =
                '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Saving...';
            submitBtn.disabled = true;

            fetch('{{ route('admin.settings.user-validation.update-student') }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                            .getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert(data.message, 'success');
                        // Update the current settings display
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        showAlert(data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('An error occurred while saving the settings.', 'error');
                })
                .finally(() => {
                    // Restore button state
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                });
        });

        // Employee validation form
        document.getElementById('employeeValidationForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;

            // Show loading state
            submitBtn.innerHTML =
                '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Saving...';
            submitBtn.disabled = true;

            fetch('{{ route('admin.settings.user-validation.update-employee') }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                            .getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert(data.message, 'success');
                        // Update the current settings display
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        showAlert(data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('An error occurred while saving the settings.', 'error');
                })
                .finally(() => {
                    // Restore button state
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                });
        });

        // Validate min/max relationship
        function validateMinMax(minInput, maxInput) {
            const minValue = parseInt(minInput.value);
            const maxValue = parseInt(maxInput.value);

            if (minValue >= maxValue) {
                maxInput.setCustomValidity('Maximum must be greater than minimum');
            } else {
                maxInput.setCustomValidity('');
            }
        }

        // Add validation for student inputs
        const studentMinInput = document.getElementById('student_min_digits');
        const studentMaxInput = document.getElementById('student_max_digits');

        studentMinInput.addEventListener('input', () => validateMinMax(studentMinInput, studentMaxInput));
        studentMaxInput.addEventListener('input', () => validateMinMax(studentMinInput, studentMaxInput));

        // Add validation for employee inputs
        const employeeMinInput = document.getElementById('employee_min_digits');
        const employeeMaxInput = document.getElementById('employee_max_digits');

        employeeMinInput.addEventListener('input', () => validateMinMax(employeeMinInput, employeeMaxInput));
        employeeMaxInput.addEventListener('input', () => validateMinMax(employeeMinInput, employeeMaxInput));
    });
</script>

@include('admin.partials.footer')

</body>

</html>
