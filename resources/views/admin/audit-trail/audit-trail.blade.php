@include('admin.partials.link')
<title>Audit Trail</title>

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
                            <h2>Audit Trail</h2>
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
                                        Audit Trail
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
                <div class="col-xl-3 col-lg-3 col-sm-6">
                    <div class="icon-card mb-30">
                        <div class="icon purple">
                            <i class="lni lni-files"></i>
                        </div>
                        <div class="content">
                            <h6 class="mb-10">Total Logs</h6>
                            <h3 class="text-bold mb-10">{{ number_format($totalLogs) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-3 col-sm-6">
                    <div class="icon-card mb-30">
                        <div class="icon success">
                            <i class="lni lni-calendar"></i>
                        </div>
                        <div class="content">
                            <h6 class="mb-10">Today</h6>
                            <h3 class="text-bold mb-10">{{ number_format($logsToday) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-3 col-sm-6">
                    <div class="icon-card mb-30">
                        <div class="icon orange">
                            <i class="lni lni-stats-up"></i>
                        </div>
                        <div class="content">
                            <h6 class="mb-10">This Week</h6>
                            <h3 class="text-bold mb-10">{{ number_format($logsThisWeek) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-3 col-sm-6">
                    <div class="icon-card mb-30">
                        <div class="icon primary">
                            <i class="lni lni-pie-chart"></i>
                        </div>
                        <div class="content">
                            <h6 class="mb-10">This Month</h6>
                            <h3 class="text-bold mb-10">{{ number_format($logsThisMonth) }}</h3>
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
                                <div class="col-12">
                                    <form method="GET" action="{{ route('admin.audit-trail.audit-trail') }}" class="row g-3">
                                        <div class="col-md-2">
                                            <select class="form-select" name="action">
                                                <option value="">All Actions</option>
                                                @foreach ($actions as $action)
                                                    <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                                                        {{ ucwords(str_replace('_', ' ', $action)) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <select class="form-select" name="admin_email">
                                                <option value="">All Admins</option>
                                                @foreach ($adminEmails as $email)
                                                    <option value="{{ $email }}" {{ request('admin_email') == $email ? 'selected' : '' }}>
                                                        {{ $email }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <select class="form-select" name="target_type">
                                                <option value="">All Types</option>
                                                @foreach ($targetTypes as $type)
                                                    <option value="{{ $type }}" {{ request('target_type') == $type ? 'selected' : '' }}>
                                                        {{ $type }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="date" class="form-control" name="date_from" 
                                                   value="{{ request('date_from') }}" placeholder="From Date">
                                        </div>
                                        <div class="col-md-2">
                                            <input type="date" class="form-control" name="date_to" 
                                                   value="{{ request('date_to') }}" placeholder="To Date">
                                        </div>
                                        <div class="col-md-2">
                                            <div class="btn-group w-100" role="group">
                                                <button type="submit" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-filter me-1"></i> Filter
                                                </button>
                                                <a href="{{ route('admin.audit-trail.audit-trail') }}" class="btn btn-secondary btn-sm">
                                                    <i class="fas fa-times me-1"></i> Clear
                                                </a>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <h5 class="mb-0">Activity Logs</h5>
                                    <small class="text-muted">System activity tracking and monitoring</small>
                                </div>
                            </div>

                            <div class="table-wrapper table-responsive">
                                <table class="table" id="auditTable">
                                    <thead>
                                        <tr>
                                            <th>
                                                <h6>Date/Time</h6>
                                            </th>
                                            <th>
                                                <h6>Admin</h6>
                                            </th>
                                            <th>
                                                <h6>Action</h6>
                                            </th>
                                            <th>
                                                <h6>Description</h6>
                                            </th>
                                            <th>
                                                <h6>Target</h6>
                                            </th>
                                            <th>
                                                <h6>Actions</h6>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($auditTrails as $trail)
                                            <tr>
                                                <td class="min-width">
                                                    <div>
                                                        <p class="mb-0">{{ $trail->created_at->format('M d, Y') }}</p>
                                                        <small class="text-muted">{{ $trail->created_at->format('h:i A') }}</small>
                                                    </div>
                                                </td>
                                                <td class="min-width">
                                                    <div>
                                                        <p class="mb-0 fw-medium">{{ $trail->admin_name }}</p>
                                                        <small class="text-muted">{{ $trail->admin_email }}</small>
                                                    </div>
                                                </td>
                                                <td class="min-width">
                                                    <span class="badge bg-{{ $trail->action_color }}">
                                                        {{ $trail->formatted_action }}
                                                    </span>
                                                </td>
                                                <td class="min-width">
                                                    <p class="mb-0" style="max-width: 300px;">
                                                        {{ Str::limit($trail->description) }}
                                                    </p>
                                                </td>
                                                <td class="min-width">
                                                    @if($trail->target_type && $trail->target_name)
                                                        <div>
                                                            <p class="mb-0 fw-medium">{{ $trail->target_type }}</p>
                                                            <small class="text-muted">{{ Str::limit($trail->target_name, 30) }}</small>
                                                        </div>
                                                    @else
                                                        <span class="text-muted">N/A</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <button class="btn btn-outline-primary btn-sm" 
                                                            onclick="viewAuditDetails({{ $trail->id }})">
                                                        <i class="fas fa-eye me-1"></i> View
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-4">
                                                    <div class="text-muted">
                                                        <i class="fas fa-search fa-2x mb-3"></i>
                                                        <p>No audit trail records found matching your criteria.</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            @if($auditTrails->hasPages())
                                <div class="d-flex justify-content-between align-items-center mt-4">
                                    <div>
                                        <p class="text-muted mb-0">
                                            Showing {{ $auditTrails->firstItem() }} to {{ $auditTrails->lastItem() }} 
                                            of {{ $auditTrails->total() }} results
                                        </p>
                                    </div>
                                    <div>
                                        {{ $auditTrails->appends(request()->query())->links() }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- Cleanup Modal -->
<div class="modal fade" id="cleanupModal" tabindex="-1" aria-labelledby="cleanupModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cleanupModalLabel">Cleanup Old Audit Logs</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> This action will permanently delete old audit log entries and cannot be undone.
                </div>
                <form id="cleanupForm">
                    <div class="mb-3">
                        <label for="retentionDays" class="form-label">Delete logs older than (days):</label>
                        <select class="form-select" id="retentionDays" name="days" required>
                            <option value="">Select retention period</option>
                            <option value="30">30 days</option>
                            <option value="60">60 days</option>
                            <option value="90">90 days (recommended)</option>
                            <option value="180">180 days</option>
                            <option value="365">1 year</option>
                        </select>
                        <div class="form-text">
                            Logs older than the selected period will be permanently deleted.
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="performCleanup()">
                    <i class="fas fa-broom me-1"></i> Cleanup Logs
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Audit Details Modal -->
<div class="modal fade" id="auditDetailsModal" tabindex="-1" aria-labelledby="auditDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="auditDetailsModalLabel">Audit Trail Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="auditDetailsContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit form when filters change
    const filterSelects = document.querySelectorAll('select[name="action"], select[name="admin_email"], select[name="target_type"]');
    const dateInputs = document.querySelectorAll('input[name="date_from"], input[name="date_to"]');
    
    filterSelects.forEach(select => {
        select.addEventListener('change', function() {
            this.form.submit();
        });
    });
    
    dateInputs.forEach(input => {
        input.addEventListener('change', function() {
            this.form.submit();
        });
    });
});

function viewAuditDetails(id) {
    fetch(`{{ route('admin.audit-trail.audit-trail') }}/${id}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('auditDetailsContent').innerHTML = html;
            new bootstrap.Modal(document.getElementById('auditDetailsModal')).show();
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error!', 'Failed to load audit details.', 'error');
        });
}



</script>

@include('admin.partials.footer')

</body>
</html>