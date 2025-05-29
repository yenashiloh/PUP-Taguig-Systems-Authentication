@include('admin.partials.link')
<title>Dashboard</title>
<style>
    .icon-card:hover {
    transform: translateY(-5px) scale(1.03);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
}
</style>

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
                            <h3> Dashboard</h3>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="breadcrumb-wrapper">

                        </div>
                    </div>
                </div>
                <!-- end row -->
            </div>
            <div class="row">
                <div class="col-xl-4 col-lg-4 col-sm-6">
                    <a href="{{ route ('admin.total-faculty')}}" class="icon-card mb-30">
                        <div class="icon purple">
                            <i class="lni lni-users"></i>
                        </div>
                        <div class="content">
                            <h6 class="mb-10">Total of Faculty Users</h6>
                            <h3 class="text-bold mb-10">{{ $facultyCount }}</h3>
                        </div>
                    </a>
                </div>
                <div class="col-xl-4 col-lg-4 col-sm-6">
                    <a href="{{ route ('admin.total-student')}}" class="icon-card mb-30">
                        <div class="icon success">
                            <i class="lni lni-user"></i>
                        </div>
                        <div class="content">
                            <h6 class="mb-10">Total of Student Users</h6>
                            <h3 class="text-bold mb-10">{{ $studentCount }}</h3>
                        </div>
                    </a>
                </div>
                <div class="col-xl-4 col-lg-4 col-sm-6">
                    <a href="{{ route('admin.deactivated-users') }}" class="icon-card mb-30">
                        <div class="icon orange">
                            <i class="lni lni-ban"></i>
                        </div>
                        <div class="content">
                            <h6 class="mb-10">Deactivated Users</h6>
                            <h3 class="text-bold mb-10">{{ $deactivatedCount }}</h3>
                        </div>
                    </a>
                </div>
                {{-- <div class="col-xl-4 col-lg-4 col-sm-6">
                    <div class="icon-card mb-30">
                        <div class="icon orange">
                            <i class="lni lni-ban"></i>
                        </div>
                        <div class="content">
                            <h6 class="mb-10">active Users</h6>
                            <h3 class="text-bold mb-10">{{ $activeCount }}</h3>
                        </div>
                    </div>
                </div> --}}
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="card-style mb-30">
                        <div class="title d-flex flex-wrap justify-content-between">
                            <div class="left">
                                <h4 class="text-bold">User Registration Trends</h4>
                            </div>
                        </div>
                        <div class="chart" style="height: 450px;">
                            <canvas id="registrationChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="card-style mb-30">
                        <div class="title d-flex flex-wrap justify-content-between">
                            <div class="left">
                                <h4 class="text-bold">User Status Summary</h4>
                            </div>
                        </div>
                        <div class="chart" style="height: 400px;">
                            <canvas id="statusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Row -->
        </div>
        <!-- end container -->
    </section>
    <!-- ========== section end ========== -->
</main>
<!-- ======== main-wrapper end =========== -->

@include('admin.partials.footer')
<script src="../../assets/admin/js/dashboard.js"></script>


<script>
   var activeCount = {{ $activeCount }};
    var deactivatedCount = {{ $deactivatedCount }};
    
    // For the monthly chart
    window.monthlyChartData = {
        months: @json($months),
        registrations: @json($registrations)
    };
</script>

</body>

</html>
