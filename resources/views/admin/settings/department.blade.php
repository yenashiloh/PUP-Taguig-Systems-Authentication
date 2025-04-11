@include('admin.partials.link')
<title>Department</title>

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
                            <h2>Department</h2>
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
                                    data-bs-target="#addDepartmentModal"><i class="fas fa-plus"></i> Add
                                    Department</button>
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
                                                <h6>Department Name</h6>
                                            </th>
                                            <th>
                                                <h6>Status</h6>
                                            </th>
                                            <th>
                                                <h6>Action</h6>
                                            </th>
                                        </tr>
                                        <!-- end table row-->
                                    </thead>
                                    <tbody>
                                        @foreach ($departments as $department)
                                            <tr id="department-{{ $department->id }}">
                                                <td class="min-width">
                                                    <div class="lead">
                                                        <p>{{ $department->dept_name }}</p>
                                                    </div>
                                                </td>
                                                <td class="min-width">
                                                    <p class="status-text">
                                                        {{ $department->status }}
                                                    </p>
                                                </td>
                                                <td>
                                                    <button class="main-button warning-btn btn-hover mb-1">
                                                        Edit
                                                    </button>
                                                    <button class="main-button danger-btn btn-hover mb-1">
                                                        Disable
                                                    </button>
                                                    <button class="main-button danger-btn btn-hover mb-1 delete-department-btn" data-id="{{ $department->department_id }}">
                                                        Delete
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                        <!-- end table row -->
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
            <div class="modal fade" id="addDepartmentModal" tabindex="-1" aria-labelledby="addDepartmentModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title mb-2" id="addDepartmentModalLabel">Add Course</h5>
                            <button type="button" class="btn-close mb-2" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="addCourseForm" method="POST"
                                action="{{ route('admin.settings.department.store') }}">
                                @csrf
                                <div class="input-style-1">
                                    <label>Department</label>
                                    <input type="text" name="dept_name" placeholder="Enter Department Name"
                                        required />
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
