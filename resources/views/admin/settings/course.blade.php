@include('admin.partials.link')
<title>Course</title>

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
            </div>

            <div class="tables-wrapper">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card-style mb-30">
                            <div class="d-flex justify-content-start align-items-center mb-3">
                                <button class="main-button primary-btn btn-hover mb-1 me-2 btn-sm" data-bs-toggle="modal"
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
                                    </thead>
                                    <tbody>
                                        @foreach ($courses as $course)
                                            <tr id="course-{{ $course->course_id }}">
                                                <td class="min-width">
                                                    <div class="lead">
                                                        <p>{{ $course->course_name }}</p>
                                                    </div>
                                                </td>
                                                <td class="min-width">
                                                    <div class="lead">
                                                        <p>{{ $course->department->dept_name ?? 'No Department' }}</p>
                                                    </div>
                                                </td>
                                                <td class="min-width">
                                                    <p class="status-text">
                                                        {{ ucfirst($course->status) }}
                                                    </p>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button class="btn btn-outline-warning btn-sm edit-course-btn"
                                                            data-id="{{ $course->course_id }}"
                                                            data-name="{{ $course->course_name }}"
                                                            data-department-id="{{ $course->department_id }}"
                                                            data-status="{{ $course->status }}" data-bs-toggle="modal"
                                                            data-bs-target="#editCourseModal">
                                                            <i class="fas fa-edit me-1"></i> Edit
                                                        </button>

                                                        @if ($course->status === 'Active')
                                                            <button
                                                                class="btn btn-outline-danger btn-sm toggle-course-status-btn"
                                                                data-id="{{ $course->course_id }}"
                                                                data-current-status="{{ $course->status }}">
                                                                <i class="fas fa-ban me-1"></i> Disable
                                                            </button>
                                                        @else
                                                            <button
                                                                class="btn btn-outline-success btn-sm toggle-course-status-btn"
                                                                data-id="{{ $course->course_id }}"
                                                                data-current-status="{{ $course->status }}">
                                                                <i class="fas fa-check-circle me-1"></i> Enable
                                                            </button>
                                                        @endif

                                                        <button class="btn btn-outline-danger btn-sm delete-course-btn"
                                                            data-id="{{ $course->course_id }}">
                                                            <i class="fas fa-trash me-1"></i> Delete
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
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
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="input-style-1">
                                            <label>Course Name <span class="text-danger">*</span></label>
                                            <input type="text" name="course_name" placeholder="Enter Course Name" required />
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="select-style-1">
                                            <label>Department <span class="text-danger">*</span></label>
                                            <div class="select-position">
                                                <select name="department_id" required>
                                                    <option value="" disabled selected>Select Department</option>
                                                    @foreach ($departments as $department)
                                                        <option value="{{ $department->department_id }}">
                                                            {{ $department->dept_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
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

            <!-- Edit Course Modal -->
            <div class="modal fade" id="editCourseModal" tabindex="-1" aria-labelledby="editCourseModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title mb-2" id="editCourseModalLabel">Edit Course</h5>
                            <button type="button" class="btn-close mb-2" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="editCourseForm" method="POST">
                                @csrf
                                @method('PUT')
                                <input type="hidden" id="edit_course_id" name="course_id">

                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <div class="input-style-1">
                                            <label>Course Name <span class="text-danger">*</span></label>
                                            <input type="text" id="edit_course_name" name="course_name"
                                                placeholder="Enter Course Name" required />
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="select-style-1">
                                            <label>Department <span class="text-danger">*</span></label>
                                            <div class="select-position">
                                                <select id="edit_course_department" name="department_id" required>
                                                    <option value="" disabled>Select Department</option>
                                                    @foreach ($departments as $department)
                                                        <option value="{{ $department->department_id }}">
                                                            {{ $department->dept_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="select-style-1">
                                            <label>Status <span class="text-danger">*</span></label>
                                            <div class="select-position">
                                                <select id="edit_course_status" name="status" required>
                                                    <option value="Active">Active</option>
                                                    <option value="Inactive">Inactive</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="button" class="main-button light-btn btn-hover mb-1 me-2"
                                        data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit"
                                        class="main-button primary-btn btn-hover mb-1">Update</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Edit course button functionality
        document.querySelectorAll('.edit-course-btn').forEach(button => {
            button.addEventListener('click', function() {
                const courseId = this.getAttribute('data-id');
                const courseName = this.getAttribute('data-name');
                const departmentId = this.getAttribute('data-department-id');
                const courseStatus = this.getAttribute('data-status');
                
                // Populate the edit form
                document.getElementById('edit_course_id').value = courseId;
                document.getElementById('edit_course_name').value = courseName;
                document.getElementById('edit_course_department').value = departmentId;
                document.getElementById('edit_course_status').value = courseStatus;
                
                // Set form action
                document.getElementById('editCourseForm').action = `/courses/${courseId}/update`;
            });
        });

        // Edit course form submission
        document.getElementById('editCourseForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const courseId = document.getElementById('edit_course_id').value;
            
            fetch(`/courses/${courseId}/update`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Success!', data.message, 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error!', data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error!', 'An error occurred while updating the course.', 'error');
            });
        });
    });
</script>

<script src="../../assets/admin/js/course-department.js"></script>
@include('admin.partials.footer')
</body>

</html>