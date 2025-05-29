@include('admin.partials.link')
<title>Deactivated Users</title>

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
                            <h2>Deactivated Users</h2>
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
                                        Deactivated Users
                                    </li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
                <!-- end row -->
            </div>

            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-xl-4 col-lg-4 col-sm-6">
                    <div class="icon-card mb-30">
                        <div class="icon orange">
                            <i class="lni lni-ban"></i>
                        </div>
                        <div class="content">
                            <h6 class="mb-10">Total Deactivated Users</h6>
                            <h3 class="text-bold mb-10">{{ $users->count() }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-4 col-sm-6">
                    <div class="icon-card mb-30">
                        <div class="icon purple">
                            <i class="lni lni-users"></i>
                        </div>
                        <div class="content">
                            <h6 class="mb-10">Deactivated Faculty</h6>
                            <h3 class="text-bold mb-10">{{ $roleCounts['Faculty'] }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-4 col-sm-6">
                    <div class="icon-card mb-30">
                        <div class="icon success">
                            <i class="lni lni-user"></i>
                        </div>
                        <div class="content">
                            <h6 class="mb-10">Deactivated Students</h6>
                            <h3 class="text-bold mb-10">{{ $roleCounts['Student'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tables-wrapper">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card-style mb-30">
                            <!-- Filter Section -->
                            <div class="row mb-3 align-items-center">
                                <div class="col-md-2">
                                    <select class="form-select" id="roleFilter" onchange="filterTable()">
                                        <option value="">All Roles</option>
                                        <option value="Faculty">Faculty ({{ $roleCounts['Faculty'] }})</option>
                                        <option value="Student">Student ({{ $roleCounts['Student'] }})</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select class="form-select" id="departmentFilter" onchange="filterTable()">
                                        <option value="">Department</option>
                                        @php
                                            $hasDepartment = false;
                                        @endphp
                                        @foreach ($departments as $department)
                                            @php
                                                $count = $departmentCounts[$department->dept_name] ?? 0;
                                            @endphp
                                            @if ($count > 0)
                                                @php $hasDepartment = true; @endphp
                                                <option value="{{ $department->dept_name }}">
                                                    {{ $department->dept_name }} ({{ $count }})
                                                </option>
                                            @endif
                                        @endforeach
                                        @unless ($hasDepartment)
                                            <option disabled>No departments available</option>
                                        @endunless
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <select class="form-select" id="programFilter" onchange="filterTable()">
                                        <option value="">Program</option>
                                        @php
                                            $hasProgram = false;
                                        @endphp
                                        @foreach ($courses as $course)
                                            @php
                                                $count = $programCounts[$course->course_name] ?? 0;
                                            @endphp
                                            @if ($count > 0)
                                                @php $hasProgram = true; @endphp
                                                <option value="{{ $course->course_name }}">
                                                    {{ $course->course_name }} ({{ $count }})
                                                </option>
                                            @endif
                                        @endforeach
                                        @unless ($hasProgram)
                                            <option disabled>No programs available</option>
                                        @endunless
                                    </select>
                                </div>

                                @php
                                    $totalCount = collect(['1st Year', '2nd Year', '3rd Year', '4th Year'])->sum(
                                        function ($year) use ($yearCounts) {
                                            return $yearCounts[$year] ?? 0;
                                        },
                                    );
                                @endphp

                                <div class="col-md-2">
                                    <select class="form-select" id="yearFilter" onchange="filterTable()">
                                        <option value="">Year</option>

                                        @foreach (['1st Year', '2nd Year', '3rd Year', '4th Year'] as $year)
                                            @php
                                                $count = $yearCounts[$year] ?? 0;
                                            @endphp
                                            @if ($count > 0)
                                                <option value="{{ $year }}">{{ $year }}
                                                    ({{ $count }})</option>
                                            @endif
                                        @endforeach

                                        @if ($totalCount === 0)
                                            <option disabled value="none">No Year Available</option>
                                        @endif
                                    </select>
                                </div>

                            </div>

                            <!-- Bulk Actions Bar -->
                            <div class="row mb-3" id="bulkActionsBar" style="display: none;">
                                <div class="col-12">
                                    <div class="alert alert-warning d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            <span id="selectedCount">0</span> deactivated user(s) selected
                                        </div>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-success"
                                                onclick="bulkReactivate()" id="bulkReactivateBtn"
                                                title="Reactivate Selected">
                                                <i class="fas fa-check-circle me-1"></i> Reactivate Selected
                                            </button>
                                            <button type="button" class="btn btn-sm btn-secondary"
                                                onclick="clearSelection()" title="Clear Selection">
                                                <i class="fas fa-times me-1"></i> Clear Selection
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="table-wrapper table-responsive">
                                <table class="table" id="userTable">
                                    <thead>
                                        <tr>
                                            <th style="width: 50px;">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="selectAll"
                                                        onchange="toggleSelectAll()" title="Select All">
                                                    <label class="form-check-label visually-hidden" for="selectAll">
                                                        Select All
                                                    </label>
                                                </div>
                                            </th>
                                            <th>
                                                <h6>Role</h6>
                                            </th>
                                            <th>
                                                <h6>ID Number</h6>
                                            </th>
                                            <th>
                                                <h6>Last Name</h6>
                                            </th>
                                            <th>
                                                <h6>First Name</h6>
                                            </th>
                                            <th>
                                                <h6>Email</h6>
                                            </th>
                                            <th>
                                                <h6>Department/Program</h6>
                                            </th>
                                            <th>
                                                <h6>Deactivated Date</h6>
                                            </th>
                                            <th>
                                                <h6>Action</h6>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($users as $deactivatedUser)
                                            <tr id="user-{{ $deactivatedUser->id }}"
                                                data-role="{{ $deactivatedUser->role }}"
                                                data-department="{{ $deactivatedUser->department ?? '' }}"
                                                data-program="{{ $deactivatedUser->program ?? '' }}"
                                                data-year="{{ $deactivatedUser->year ?? '' }}"
                                                data-user-id="{{ $deactivatedUser->id }}">
                                                <td>
                                                    <div class="form-check">
                                                        <input class="form-check-input user-checkbox" type="checkbox"
                                                            id="user-checkbox-{{ $deactivatedUser->id }}"
                                                            value="{{ $deactivatedUser->id }}"
                                                            onchange="updateSelectAll()">
                                                        <label class="form-check-label visually-hidden"
                                                            for="user-checkbox-{{ $deactivatedUser->id }}">
                                                            Select {{ $deactivatedUser->first_name }}
                                                            {{ $deactivatedUser->last_name }}
                                                        </label>
                                                    </div>
                                                </td>
                                                <td class="min-width">
                                                    <span
                                                        class="badge {{ $deactivatedUser->role == 'Faculty' ? 'bg-primary' : 'bg-info' }}">
                                                        {{ $deactivatedUser->role }}
                                                    </span>
                                                </td>
                                                <td class="min-width">
                                                    <div class="lead">
                                                        <p>
                                                            {{ $deactivatedUser->student_number ?? ($deactivatedUser->employee_number ?? 'No ID') }}
                                                        </p>
                                                    </div>
                                                </td>
                                                <td class="min-width">
                                                    <p>{{ $deactivatedUser->last_name }}</p>
                                                </td>
                                                <td class="min-width">
                                                    <p>{{ $deactivatedUser->first_name }}</p>
                                                </td>
                                                <td class="min-width">
                                                    <p>{{ $deactivatedUser->email }}</p>
                                                </td>
                                                <td class="min-width">
                                                    <p>{{ $deactivatedUser->department ?? ($deactivatedUser->program ?? 'N/A') }}
                                                    </p>
                                                </td>
                                                <td class="min-width">
                                                    <p>{{ $deactivatedUser->updated_at->format('M d, Y') }}</p>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button class="btn btn-outline-primary btn-sm"
                                                            onclick="window.location='{{ route('admin.dashboard.view-deactivated-user', ['user' => $deactivatedUser->id]) }}'">
                                                            <i class="fas fa-eye me-1"></i> View
                                                        </button>
                                                        <button class="btn btn-outline-success btn-sm"
                                                            onclick="reactivateUser({{ $deactivatedUser->id }})">
                                                            <i class="fas fa-check-circle me-1"></i> Reactivate
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <!-- No data row with correct column count -->
                                            <tr class="no-records-row">
                                                <td></td> <!-- Checkbox column -->
                                                <td></td> <!-- Role column -->
                                                <td></td> <!-- ID Number column -->
                                                <td></td> <!-- Last Name column -->
                                                <td class="text-center py-4"
                                                    style="font-style: italic; color: #6c757d;">

                                                    No deactivated users found. All users are currently active.
                                                </td>
                                                <td></td> <!-- Email column -->
                                                <td></td> <!-- Department/Program column -->
                                                <td></td> <!-- Deactivated Date column -->
                                                <td></td> <!-- Action column -->
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<script>
    // Filter table functionality
    function filterTable() {
        const roleFilter = document.getElementById('roleFilter').value.toLowerCase();
        const departmentFilter = document.getElementById('departmentFilter').value.toLowerCase();
        const programFilter = document.getElementById('programFilter').value.toLowerCase();
        const yearFilter = document.getElementById('yearFilter').value.toLowerCase();

        const rows = document.querySelectorAll('#userTable tbody tr:not(.no-records-row)');
        let visibleCount = 0;

        rows.forEach(row => {
            const role = row.getAttribute('data-role').toLowerCase();
            const department = row.getAttribute('data-department').toLowerCase();
            const program = row.getAttribute('data-program').toLowerCase();
            const year = row.getAttribute('data-year').toLowerCase();

            let showRow = true;

            if (roleFilter && role !== roleFilter) showRow = false;
            if (departmentFilter && department !== departmentFilter) showRow = false;
            if (programFilter && program !== programFilter) showRow = false;
            if (yearFilter && year !== yearFilter) showRow = false;

            row.style.display = showRow ? '' : 'none';
            if (showRow) visibleCount++;
        });

        // Update select all checkbox
        updateSelectAll();
    }

    // Select all functionality
    function toggleSelectAll() {
        const selectAllCheckbox = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.user-checkbox');
        const visibleCheckboxes = Array.from(checkboxes).filter(cb =>
            cb.closest('tr').style.display !== 'none'
        );

        visibleCheckboxes.forEach(checkbox => {
            checkbox.checked = selectAllCheckbox.checked;
        });

        updateBulkActionsBar();
    }

    function updateSelectAll() {
        const checkboxes = document.querySelectorAll('.user-checkbox');
        const visibleCheckboxes = Array.from(checkboxes).filter(cb =>
            cb.closest('tr').style.display !== 'none'
        );
        const checkedBoxes = visibleCheckboxes.filter(cb => cb.checked);

        const selectAllCheckbox = document.getElementById('selectAll');

        if (visibleCheckboxes.length === 0) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        } else if (checkedBoxes.length === visibleCheckboxes.length) {
            selectAllCheckbox.checked = true;
            selectAllCheckbox.indeterminate = false;
        } else if (checkedBoxes.length > 0) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = true;
        } else {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        }

        updateBulkActionsBar();
    }

    function updateBulkActionsBar() {
        const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
        const bulkActionsBar = document.getElementById('bulkActionsBar');
        const selectedCount = document.getElementById('selectedCount');

        if (checkedBoxes.length > 0) {
            bulkActionsBar.style.display = 'block';
            selectedCount.textContent = checkedBoxes.length;
        } else {
            bulkActionsBar.style.display = 'none';
        }
    }

    function clearSelection() {
        document.querySelectorAll('.user-checkbox').forEach(cb => cb.checked = false);
        document.getElementById('selectAll').checked = false;
        document.getElementById('selectAll').indeterminate = false;
        updateBulkActionsBar();
    }

    // Reactivate single user
    function reactivateUser(userId) {
        Swal.fire({
            title: 'Reactivate User?',
            text: 'Are you sure you want to reactivate this user?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Reactivate',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
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
                            Swal.fire('Success!', data.message, 'success').then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error!', data.message, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire('Error!', 'An error occurred while reactivating the user.', 'error');
                    });
            }
        });
    }

    // Bulk reactivate users
    function bulkReactivate() {
        const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
        const userIds = Array.from(checkedBoxes).map(cb => cb.value);

        if (userIds.length === 0) {
            Swal.fire('Warning!', 'Please select users to reactivate.', 'warning');
            return;
        }

        Swal.fire({
            title: 'Reactivate Selected Users?',
            text: `Are you sure you want to reactivate ${userIds.length} user(s)?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Reactivate All',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('/admin/bulk-reactivate-users', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        },
                        body: JSON.stringify({
                            user_ids: userIds
                        })
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
                        Swal.fire('Error!', 'An error occurred while reactivating users.', 'error');
                    });
            }
        });
    }

    // Add event listeners for checkboxes
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.user-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', updateSelectAll);
        });
    });
</script>

@include('admin.partials.footer')
