@include('admin.partials.link')
<title>View Details</title>

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
                            <h2>View Details</h2>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="breadcrumb-wrapper">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item">
                                        <a href="{{ route('admin.user-management.users') }}">User Management</a>
                                    </li>
                                    <li class="breadcrumb-item">
                                        <a href="{{ route('admin.user-management.student') }}">Student</a>
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
                            <h4 class="mb-25 fw-bold">Account Details</h4>
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

                            <form action="{{ route('admin.user-management.update-student', $student->id) }}"
                                method="POST">
                                @csrf
                                @method('PUT')
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="input-style-1">
                                            <label>First Name</label>
                                            <input type="text" name="first_name" value="{{ $student->first_name }}"
                                                required />
                                            @error('first_name')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="input-style-1">
                                            <label>Middle Name</label>
                                            <input type="text" name="middle_name"
                                                value="{{ $student->middle_name ?? 'N/A' }}" required />
                                            @error('middle_name')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="input-style-1">
                                            <label>Last Name</label>
                                            <input type="text" name="last_name" value="{{ $student->last_name }}"
                                                required />
                                            @error('last_name')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="input-style-1">
                                            <label>Email Address</label>
                                            <input type="email" name="email" value="{{ $student->email }}"
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
                                                            {{ $student->program == $course->course_name ? 'selected' : '' }}>
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
                                                value="{{ $student->student_number }}" required />
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
                                                        {{ $student->year == '1st Year' ? 'selected' : '' }}>1st Year
                                                    </option>
                                                    <option value="2nd Year"
                                                        {{ $student->year == '2nd Year' ? 'selected' : '' }}>2nd Year
                                                    </option>
                                                    <option value="3rd Year"
                                                        {{ $student->year == '3rd Year' ? 'selected' : '' }}>3rd Year
                                                    </option>
                                                    <option value="4th Year"
                                                        {{ $student->year == '4th Year' ? 'selected' : '' }}>4th Year
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
                                                        {{ $student->section == '1' ? 'selected' : '' }}>1</option>
                                                    <option value="2"
                                                        {{ $student->section == '2' ? 'selected' : '' }}>2</option>
                                                    <option value="3"
                                                        {{ $student->section == '3' ? 'selected' : '' }}>3</option>
                                                    <option value="4"
                                                        {{ $student->section == '4' ? 'selected' : '' }}>4</option>
                                                    <option value="5"
                                                        {{ $student->section == '5' ? 'selected' : '' }}>5</option>
                                                    <option value="6"
                                                        {{ $student->section == '6' ? 'selected' : '' }}>6</option>
                                                    <option value="7"
                                                        {{ $student->section == '7' ? 'selected' : '' }}>7</option>
                                                    <option value="8"
                                                        {{ $student->section == '8' ? 'selected' : '' }}>8</option>
                                                    <option value="9"
                                                        {{ $student->section == '9' ? 'selected' : '' }}>9</option>
                                                    <option value="10"
                                                        {{ $student->section == '10' ? 'selected' : '' }}>10</option>
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
                                                value="{{ old('birthdate', $student->birthdate) }}"
                                                max="{{ $today }}" required>
                                            @error('birthdate')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-12 text-end">
                                        <button type="submit" class="main-button primary-btn btn-hover">
                                            Update
                                        </button>
                                        <a href="" class="main-button secondary-btn btn-hover">
                                            View Activity Log
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- end row -->
            </div>
        </div>
        <!-- end container -->
    </section>
    <!-- ========== section end ========== -->
</main>
<!-- ======== main-wrapper end =========== -->
<script src="../../assets/admin/js/faculty.js"></script>
@include('admin.partials.footer')

</body>

</html>
