@include('admin.partials.link')
<title>Student</title>

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
                            <h2>Student</h2>
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
                                        Student
                                    </li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
                <!-- end row -->
            </div>

            <div class="tables-wrapper">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card-style mb-30">
                            <div class="table-wrapper table-responsive">
                                <table class="table" id="userTable">
                                    <thead>
                                        <tr>
                                            <th>
                                                <h6>ID Number</h6>
                                            </th>
                                            <th>
                                                <h6>First Name</h6>
                                            </th>
                                            <th>
                                                <h6>Last Name</h6>
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
                                        @foreach ($users as $user)
                                            <tr id="user-{{ $user->id }}">
                                                <td class="min-width">
                                                    <div class="lead">
                                                        <p>
                                                            {{ $user->student_number ?? ($user->employee_number ?? 'No ID Available') }}
                                                        </p>
                                                    </div>
                                                </td>
                                                <td class="min-width">
                                                    <p><a href="#0">{{ $user->first_name }}</a></p>
                                                </td>
                                                <td class="min-width">
                                                    <p>{{ $user->last_name }}</p>
                                                </td>
                                                <td class="min-width">
                                                    <p class="status-text">{{ $user->status }}</p>
                                                </td>
                                                <td>
                                                    <button class="main-button secondary-btn btn-hover mb-1" onclick="window.location='{{ route('admin.dashboard.view-total-student', ['user' => $user->id]) }}'">
                                                        View
                                                    </button>   
                                                    <span class="toggle-btn-container">
                                                        @if ($user->status === 'Activate' || $user->status === 'Active')
                                                            <button class="main-button danger-btn btn-hover mb-1"
                                                                onclick="toggleAccountStatus({{ $user->id }}, 'deactivate')">
                                                                Deactivate
                                                            </button>
                                                        @elseif ($user->status === 'Deactivated')
                                                            <button class="main-button warning-btn btn-hover mb-1"
                                                                onclick="toggleAccountStatus({{ $user->id }}, 'reactivate')">
                                                                Reactivate
                                                            </button>
                                                        @endif
                                                    </span>
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
                <!-- end row -->
                <!-- end container -->
    </section>
    <!-- ========== section end ========== -->
</main>
<!-- ======== main-wrapper end =========== -->
<script src="../../assets/admin/js/faculty.js"></script>
@include('admin.partials.footer')

</body>

</html>
