@include('admin.partials.link')
<title> Department</title>

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
                            <h2>Course</h2>
                        </div>
                    </div>
                </div>
                <!-- end row -->
            </div>

            <div class="tables-wrapper">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card-style mb-30">
                            <div class="d-flex justify-content-start align-items-center mb-3">
                                <button class="main-button primary-btn btn-hover mb-1" data-bs-toggle="modal"
                                    data-bs-target="#addCourseModal"><i class="fas fa-plus"></i> Add Course</button>
                            </div>
                            @if (session('success'))
                                <div class="alert alert-success alert-dismissible fade show my-success-alert"
                                    role="alert">
                                    {{ session('success') }}
                                </div>
                            @endif

                            @if ($errors->any())
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                                </div>
                            @endif

                            <div class="table-wrapper table-responsive">
                                <table class="table" id="userTable">
                                    <thead>
                                        <tr>
                                            <th>
                                                <h6>Course Name</h6>
                                            </th>
                                            <th>
                                                <h6>Department</h6>
                                            </th>
                                            <th>
                                                <h6>Status</h6>
                                            </th>
                                            <th>
                                                <h6>Actions</h6>
                                            </th>
                                        </tr>
                                        <!-- end table row-->
                                    </thead>
                                    <tbody>
                                        @foreach ($courses as $course)
                                            <tr id="course-{{ $course->id }}">
                                                <td class="min-width">
                                                    <div class="lead">
                                                        <p>{{ $course->course_name }}</p>
                                                    </div>
                                                </td>
                                                <td class="min-width">
                                                    <p>{{ $course->department->dept_name ?? 'N/A' }}</p>
                                                </td>
                                                <td class="min-width">
                                                    <p class="status-text">
                                                        {{ ucfirst($course->status) }}
                                                    </p>
                                                </td>
                                                <td>
                                                    <button class="main-button warning-btn btn-hover mb-1">
                                                        Edit
                                                    </button>
                                                    <button class="main-button danger-btn btn-hover mb-1">
                                                        Disable
                                                    </button>
                                                    <button
                                                        class="main-button danger-btn btn-hover mb-1 delete-course-btn"
                                                        data-id="{{ $course->course_id }}">
                                                        Delete
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>

                                </table>
                                <!-- end table -->
                            </div>
                        </div>
                        <!-- end card -->
                    </div>
                    <!-- end col -->
                </div>
            </div>

            <!-- Add Course Modal -->
            <div class="modal fade" id="addCourseModal" tabindex="-1" aria-labelledby="addCourseModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title mb-2" id="addCourseModalLabel">Add Course</h5>
                            <button type="button" class="btn-close mb-2" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form action="{{ route('store.course') }}" method="POST">
                                @csrf
                                <div class="input-style-1">
                                    <label>Course Name</label>
                                    <input type="text" name="course_name" placeholder="Course Name" required />
                                </div>
                                <div class="select-style-1">
                                    <label>Department</label>
                                    <div class="select-position">
                                        <select name="department_id" required>
                                            <option value="" disabled selected>Select your department</option>
                                            @foreach ($departments as $department)
                                                <option value="{{ $department->department_id }}">
                                                    {{ $department->dept_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <button type="button" class="main-button light-btn btn-hover mb-1 me-2"
                                        data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="main-button primary-btn btn-hover mb-1">Save</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end row -->
            <!-- end container -->
    </section>
    <!-- ========== section end ========== -->
</main>
<!-- ======== main-wrapper end =========== -->
<script src="../../assets/admin/js/course-department.js"></script>
@include('admin.partials.footer')



</body>

</html>
