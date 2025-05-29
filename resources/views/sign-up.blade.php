<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PUP-Taguig Systems Authentication</title>

    <!-- Bootstrap CSS first -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS after Bootstrap (to override Bootstrap styles) -->
    <link rel="stylesheet" href="assets/css/sign-up.css">
    <link rel="icon" type="image/png" href="{{ asset('assets/images/PUPLogo.png') }}">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>

<body>
    <div class="main-container">
        <div class="bg-image"></div>
        <div class="diagonal-overlay">
            <div class="d-flex flex-column align-items-center" style="z-index: 10; position: relative;">
                <a href="{{ url('/') }}" class="btn btn-secondary"
                    style="position: absolute; top: 20px; left: 20px; z-index: 100;">
                    Go Back
                </a>
            </div>
        </div>
        <div class="signup-container">
            <div class="signup-title">
                <h2>Create Account</h2>
            </div>

            <form class="signup-form" action="/register" method="POST">
                @csrf

                <div class="row mb-2">
                    <!-- Initial role selection and email in same row -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="role">Select Role <span class="text-danger">*</span></label>
                            <select id="role" class="form-control form-select" name="role" required>
                                <option value="" disabled selected>Select your role</option>
                                <option value="Student">Student</option>
                                <option value="Faculty">Faculty</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email">Email Address <span class="text-danger">*</span></label>
                            <input type="email" id="email" class="form-control"
                                placeholder="Enter your email address" name="email" required>
                        </div>
                    </div>
                </div>

                <!-- Faculty Form Fields (Hidden by default) -->
                <div id="facultyFields" style="display: none;">
                    <div class="row mb-2">
                        <!-- Name fields-->
                        <div class="col-md-4 mb-2 mb-md-0">
                            <div class="form-group">
                                <label for="facultyFirstName">First Name <span class="text-danger">*</span></label>
                                <input type="text" id="facultyFirstName" class="form-control"
                                    placeholder="Enter your first name" name="first_name" data-required="true" required>
                            </div>
                        </div>
                        <div class="col-md-4 mb-2 mb-md-0">
                            <div class="form-group">
                                <label for="facultyMiddleName">Middle Name</label>
                                <input type="text" id="facultyMiddleName" class="form-control"
                                    placeholder="Enter your middle name (optional)" name="middle_name">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="facultyLastName">Last Name <span class="text-danger">*</span></label>
                                <input type="text" id="facultyLastName" class="form-control"
                                    placeholder="Enter your last name" name="last_name" data-required="true" required>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <!-- Faculty specific info -->
                        <div class="col-md-6 mb-2 mb-md-0">
                            <div class="form-group">
                                <label for="facultyPhoneNumber">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" id="facultyPhoneNumber" class="form-control"
                                    placeholder="Enter your phone number" name="phone_number" data-required="true"
                                    required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="employeeNumber">Employee Number <span class="text-danger">*</span></label>
                                <input type="text" id="employeeNumber" class="form-control"
                                    placeholder="Enter your employee number" name="employee_number" data-required="true"
                                    required>
                                @if ($employeeValidation)
                                    <small class="text-muted">
                                        {{ $employeeValidation->min_digits }}-{{ $employeeValidation->max_digits }}
                                        characters,
                                        @if ($employeeValidation->numbers_only)
                                            numbers only
                                        @elseif($employeeValidation->letters_only)
                                            letters only
                                        @else
                                            letters, numbers & symbols
                                        @endif
                                    </small>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="facultyBirthdate">Birthdate <span class="text-danger">*</span></label>
                                <input type="date" id="facultyBirthdate" class="form-control" name="birthdate"
                                    data-required="true" required max="{{ date('Y-m-d') }}">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="employmentStatus">Employment Status <span
                                        class="text-danger">*</span></label>
                                <select id="employmentStatus" class="form-control form-select"
                                    name="employment_status" data-required="true" required>
                                    <option value="" disabled selected>Select employment status</option>
                                    <option value="Full-Time">Full-Time</option>
                                    <option value="Part-Time">Part-Time</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="facultyDepartment">Department <span class="text-danger">*</span></label>
                                <select id="facultyDepartment" class="form-control form-select" name="department"
                                    data-required="true" required>
                                    <option value="" disabled selected>Select your department</option>
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->dept_name }}">{{ $department->dept_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Student Form Fields (Hidden by default) -->
                <div id="studentFields" style="display: none;">
                    <div class="row mb-2">
                        <!-- Name fields -->
                        <div class="col-md-4 mb-2 mb-md-0">
                            <div class="form-group">
                                <label for="studentFirstName">First Name <span class="text-danger">*</span></label>
                                <input type="text" id="studentFirstName" class="form-control"
                                    placeholder="Enter your first name" name="first_name" data-required="true"
                                    required>
                            </div>
                        </div>
                        <div class="col-md-4 mb-2 mb-md-0">
                            <div class="form-group">
                                <label for="studentMiddleName">Middle Name</label>
                                <input type="text" id="studentMiddleName" class="form-control"
                                    placeholder="Enter your middle name (optional)" name="middle_name">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="studentLastName">Last Name <span class="text-danger">*</span></label>
                                <input type="text" id="studentLastName" class="form-control"
                                    placeholder="Enter your last name" name="last_name" data-required="true"
                                    required>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <!-- Student number and program-->
                        <div class="col-md-6 mb-2 mb-md-0">
                            <div class="form-group">
                                <label for="studentNumber">Student Number <span class="text-danger">*</span></label>
                                <input type="text" id="studentNumber" class="form-control"
                                    placeholder="Enter your student number" name="student_number"
                                    data-required="true" required>
                                @if ($studentValidation)
                                    <small class="text-muted">
                                        {{ $studentValidation->min_digits }}-{{ $studentValidation->max_digits }}
                                        characters,
                                        @if ($studentValidation->numbers_only)
                                            numbers only
                                        @elseif($studentValidation->letters_only)
                                            letters only
                                        @else
                                            letters, numbers & symbols
                                        @endif
                                    </small>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="program">Program <span class="text-danger">*</span></label>
                                <select id="program" class="form-control form-select" name="program"
                                    data-required="true" required>
                                    <option value="" disabled selected>Select your program</option>
                                    @foreach ($courses as $course)
                                        <option value="{{ $course->course_name }}">{{ $course->course_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <!-- Year, section, and birthdate  -->
                        <div class="col-md-4 mb-2 mb-md-0">
                            <div class="form-group">
                                <label for="year">Year <span class="text-danger">*</span></label>
                                <select id="year" class="form-control form-select" name="year"
                                    data-required="true" required>
                                    <option value="" disabled selected>Select your year</option>
                                    <option value="1st Year">1st Year</option>
                                    <option value="2nd Year">2nd Year</option>
                                    <option value="3rd Year">3rd Year</option>
                                    <option value="4th Year">4th Year</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 mb-2 mb-md-0">
                            <div class="form-group">
                                <label for="section">Section <span class="text-danger">*</span></label>
                                <select id="section" class="form-control form-select" name="section"
                                    data-required="true" required>
                                    <option value="" disabled selected>Select your section</option>
                                    <option value="1">Section 1</option>
                                    <option value="2">Section 2</option>
                                    <option value="3">Section 3</option>
                                    <option value="4">Section 4</option>
                                    <option value="5">Section 5</option>
                                    <option value="6">Section 6</option>
                                    <option value="7">Section 7</option>
                                    <option value="8">Section 8</option>
                                    <option value="9">Section 9</option>
                                    <option value="10">Section 10</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="studentBirthdate">Birthdate <span class="text-danger">*</span></label>
                                <input type="date" id="studentBirthdate" class="form-control" name="birthdate"
                                    data-required="true" required max="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit button (initially hidden) -->
                <div id="submitButton" style="display: none;">
                    <button type="submit" class="submit-btn mt-4 w-50 text-center">Sign Up</button>
                </div>
            </form>

        </div>
    </div>

    <!-- Pass validation settings to JavaScript -->
    <script>
        window.studentValidation = @json($studentValidation);
        window.employeeValidation = @json($employeeValidation);
    </script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('assets/js/sign-up.js') }}"></script>
</body>
</html>
