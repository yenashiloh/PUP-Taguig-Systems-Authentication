@include('admin.partials.link')
<title>Faculty</title>

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
                                        <a href="{{ route('admin.user-management.faculty') }}">Faculty</a>
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

                            <form action="{{ route('admin.user-management.update-faculty', $faculty->id) }}"
                                method="POST">
                                @csrf
                                @method('PUT')
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="input-style-1">
                                            <label>First Name</label>
                                            <input type="text" name="first_name"
                                                value="{{ old('first_name', $faculty->first_name) }}" required />
                                            @error('first_name')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="input-style-1">
                                            <label>Middle Name</label>
                                            <input type="text" name="middle_name"
                                                value="{{ old('middle_name', $faculty->middle_name) }}" />
                                            @error('middle_name')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="input-style-1">
                                            <label>Last Name</label>
                                            <input type="text" name="last_name"
                                                value="{{ old('last_name', $faculty->last_name) }}" required />
                                            @error('last_name')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="input-style-1">
                                            <label>Phone Number</label>
                                            <input type="text" name="phone_number"
                                                value="{{ old('phone_number', $faculty->phone_number) }}" required />
                                            @error('phone_number')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="input-style-1">
                                            <label>Email Address</label>
                                            <input type="email" name="email"
                                                value="{{ old('email', $faculty->email) }}" required />
                                            @error('email')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="input-style-1">
                                            <label>Employee Number</label>
                                            <input type="text" name="employee_number"
                                                value="{{ old('employee_number', $faculty->employee_number) }}"
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
                                                            {{ old('department', $faculty->department) == $department->dept_name ? 'selected' : '' }}>
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
                                                        {{ old('employment_status', $faculty->employment_status) == 'Full-Time' ? 'selected' : '' }}>
                                                        Full-Time
                                                    </option>
                                                    <option value="Part-Time"
                                                        {{ old('employment_status', $faculty->employment_status) == 'Part-Time' ? 'selected' : '' }}>
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
                                                value="{{ old('birthdate', $faculty->birthdate) }}"
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
                                    </div>
                                </div>
                            </form>
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
