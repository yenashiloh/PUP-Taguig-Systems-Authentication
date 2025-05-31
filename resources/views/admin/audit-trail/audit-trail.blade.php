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
                                <div class="col-md-3">
                                    <select class="form-select" id="actionFilter" onchange="filterTable()">
                                        <option value="">All Actions</option>
                                        @foreach ($actions as $action)
                                            <option value="{{ $action }}">
                                                {{ ucwords(str_replace('_', ' ', $action)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                               
                                <div class="col-md-3">
                                    <select class="form-select" id="targetTypeFilter" onchange="filterTable()">
                                        <option value="">All Types</option>
                                        @foreach ($targetTypes as $type)
                                            <option value="{{ $type }}">
                                                {{ $type }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <input type="date" class="form-control" id="dateFromFilter" 
                                           placeholder="From Date" onchange="filterTable()">
                                </div>
                                <div class="col-md-2">
                                    <input type="date" class="form-control" id="dateToFilter" 
                                           placeholder="To Date" onchange="filterTable()">
                                </div>
                                <div class="col-md-2">
                                    <div class="btn-group w-100" role="group">
                                        <button type="button" class="btn btn-secondary btn-sm" onclick="clearFilters()">
                                            <i class="fas fa-times me-1"></i> Clear
                                        </button>
                                     
                                    </div>
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
                                <table class="table" id="userTable">
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
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($auditTrails as $trail)
                                            <tr data-action="{{ $trail->action }}" 
                                                data-admin="{{ $trail->admin_email }}" 
                                                data-target-type="{{ $trail->target_type ?? '' }}"
                                                data-date="{{ $trail->created_at->format('Y-m-d') }}">
                                                <td class="min-width">
                                                    <div>
                                                        <p class="mb-0">{{ $trail->created_at->format('M d, Y') }}</p>
                                                        <small class="text-muted">{{ $trail->created_at->setTimezone('Asia/Manila')->format('h:i A') }}</small>
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
                                                        {{ $trail->description }}
                                                    </p>
                                                </td>
                                                <td class="min-width">
                                                    @if ($trail->target_type && $trail->target_name)
                                                        <div>
                                                            <p class="mb-0 fw-medium">{{ $trail->target_type }}</p>
                                                            <small class="text-muted">{{ Str::limit($trail->target_name, 30) }}</small>
                                                        </div>
                                                    @else
                                                        <span class="text-muted">N/A</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr class="no-records-row">
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td class="text-center py-4" style="font-style: italic; color: #6c757d;">
                                                    No audit trail records found
                                                </td>
                                                <td></td>
                                                <td></td>
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

<!-- Description Modal -->
<div class="modal fade" id="descriptionModal" tabindex="-1" aria-labelledby="descriptionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="descriptionModalLabel">Full Description</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="fullDescriptionText"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@include('admin.partials.footer')

<script src="../../assets/admin/js/audit-trail.js"></script>


</body>

</html>