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
                                        <a href="{{ route('admin.dashboard') }}">Home</a>
                                    </li>
                                    <li class="breadcrumb-item">
                                        <a href="{{ route('admin.total-student') }}">Student</a>
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
                                            <input type="text" name="first_name" value="{{ $student->first_name }}" required/>
                                            @error('first_name')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                         <div class="input-style-1">
                                            <label>Middle Name</label>
                                            <input type="text" name="middle_name" value="{{ $student->middle_name ?? 'N/A' }}" required/>
                                            @error('middle_name')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                         <div class="input-style-1">
                                            <label>Last Name</label>
                                            <input type="text" name="last_name" value="{{ $student->last_name }}" required/>
                                            @error('last_name')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                         <div class="input-style-1">
                                            <label>Email Address</label>
                                            <input type="text" name="email" value="{{ $student->email }}" required/>
                                            @error('email')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="input-style-1">
                                            <label>Program</label>
                                            <input type="text" name="program" value="{{ $student->program }}" required/>
                                            @error('program')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                         <div class="input-style-1">
                                            <label>Student Number</label>
                                            <input type="text" name="student_number" value="{{ $student->student_number }}" required/>
                                            @error('student_number')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                         <div class="input-style-1">
                                            <label>Year</label>
                                            <input type="text" name="year" value="{{ $student->year }}" required/>
                                            @error('year')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                         <div class="input-style-1">
                                            <label>Section</label>
                                            <input type="text" name="section" value="{{ $student->section }}" required/>
                                            @error('section')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="input-style-1">
                                            <label>Birthdate</label>
                                            <input type="date" name="birthdate" class="form-control"
                                                value="{{ old('birthdate', $student->birthdate) }}" required>
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
