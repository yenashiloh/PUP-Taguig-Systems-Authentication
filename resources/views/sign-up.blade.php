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
</head>
<body>
    <div class="main-container">
        <div class="bg-image"></div>
        <div class="diagonal-overlay"></div>
        <div class="signup-container">
            <div class="signup-title">
                <h2>Create Account</h2>
            </div>
            
            <form class="signup-form" action="/register"  method="POST">
                @csrf

                <div class="row mb-2">
                    <!-- Initial role selection and email in same row -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="role">Select Role</label>
                            <select id="role" class="form-control form-select" name="role">
                                <option value="" disabled selected>Select your role</option>
                                <option value="Student">Student</option>
                                <option value="Faculty">Faculty</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" class="form-control" placeholder="Enter your email address" name="email">
                        </div>
                    </div>
                </div>
                
                <!-- Faculty Form Fields (Hidden by default) -->
                <div id="facultyFields" style="display: none;">
                    <div class="row mb-2">
                        <!-- Name fields-->
                        <div class="col-md-4 mb-2 mb-md-0">
                            <div class="form-group">
                                <label for="facultyFirstName">First Name</label>
                                <input type="text" id="facultyFirstName" class="form-control" placeholder="Enter your first name" name="first_name">
                            </div>
                        </div>
                        <div class="col-md-4 mb-2 mb-md-0">
                            <div class="form-group">
                                <label for="facultyMiddleName">Middle Name</label>
                                <input type="text" id="facultyMiddleName" class="form-control" placeholder="Enter your middle name" name="middle_name">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="facultyLastName">Last Name</label>
                                <input type="text" id="facultyLastName" class="form-control" placeholder="Enter your last name" name="last_name">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-2">
                        <!-- Faculty specific info -->
                        <div class="col-md-6 mb-2 mb-md-0">
                            <div class="form-group">
                                <label for="facultyPhoneNumber">Phone Number</label>
                                <input type="tel" id="facultyPhoneNumber" class="form-control" placeholder="Enter your phone number" name="phone_number">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="employeeNumber">Employee Number</label>
                                <input type="text" id="employeeNumber" class="form-control" placeholder="Enter your employee number" name="employee_number">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-2">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="department">Department</label>
                                <select id="department" class="form-control form-select" name="department">
                                    <option value="" disabled selected>Select your department</option>
                                    <option value="Department of Business Administration">Department of Business Administration</option>
                                    <option value="Information Technology Education">Information Technology Education</option>
                                    <option value="Department of Information Technology">Department of Information Technology</option>
                                    <option value="Department of Office Administration">Department of Office Administration</option>
                                    <option value="Department of Psychology">Department of Psychology</option>
                                    <option value="Department of Office Accountant">Department of Office Accountant</option>
                                    <option value="Department of Engineering">Department of Engineering</option>
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
                                <label for="studentFirstName">First Name</label>
                                <input type="text" id="studentFirstName" class="form-control" placeholder="Enter your first name" name="first_name">
                            </div>
                        </div>
                        <div class="col-md-4 mb-2 mb-md-0">
                            <div class="form-group">
                                <label for="studentMiddleName">Middle Name</label>
                                <input type="text" id="studentMiddleName" class="form-control" placeholder="Enter your middle name" name="middle_name">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="studentLastName">Last Name</label>
                                <input type="text" id="studentLastName" class="form-control" placeholder="Enter your last name" name="last_name">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-2">
                        <!-- Student number and program-->
                        <div class="col-md-6 mb-2 mb-md-0">
                            <div class="form-group">
                                <label for="studentNumber">Student Number</label>
                                <input type="text" id="studentNumber" class="form-control" placeholder="Enter your student number" name="student_number">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="program">Program/Course</label>
                                <select id="program" class="form-control form-select" name="program">
                                    <option value="" disabled selected>Select your program/course</option>
                                    <option value="Bachelor of Science in Information Technology">Bachelor of Science in Information Technology</option>
                                    <option value="Bachelor of Science in Business Administration">Bachelor of Science in Business Administration</option>
                                    <option value="Bachelor of Science in Office Administration">Bachelor of Science in Office Administration</option>
                                    <option value=">Bachelor of Science in Psychology">Bachelor of Science in Psychology</option>
                                    <option value="Bachelor of Science in Electrical Engineering">Bachelor of Science in Electrical Engineering</option>
                                    <option value="Bachelor of Science in Mechanical Engineering">Bachelor of Science in Mechanical Engineering</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-2">
                        <!-- Year, section, and birthdate  -->
                        <div class="col-md-4 mb-2 mb-md-0">
                            <div class="form-group">
                                <label for="year">Year</label>
                                <select id="year" class="form-control form-select" name="year">
                                    <option value="" disabled selected>Select your year</option>
                                    <option value="1">1st Year</option>
                                    <option value="2">2nd Year</option>
                                    <option value="3">3rd Year</option>
                                    <option value="4">4th Year</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 mb-2 mb-md-0">
                            <div class="form-group">
                                <label for="section">Section</label>
                                <select id="section" class="form-control form-select" name="section">
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
                                <label for="birthdate">Birthdate</label>
                                <input type="date" id="birthdate" class="form-control" name="birthdate">
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
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('assets/js/sign-up.js') }}"></script>

</body>
</html>