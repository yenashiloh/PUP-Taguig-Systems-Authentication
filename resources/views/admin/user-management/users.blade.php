@include('admin.partials.link')
<title>User Management</title>

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
                            <h2>User Management</h2>
                        </div>
                    </div>
                </div>
                <!-- end row -->
            </div>

            <div class="row">
                <!-- end col -->
                <div class="col-xl-6 col-lg-4 col-md-6 col-sm-6">
                    <div class="card-style-3 mb-30">
                        <div class="card-content">
                            <h3 class="text-center fw-bold mb-2"><a href="#0">Faculty </a></h3>
                            <div class="d-flex justify-content-center">
                                <img src="../../assets/admin/images/users.png" alt="Faculty Image"
                                    class="img-fluid w-25">
                            </div>
                            <div class="d-flex justify-content-center mt-3">
                                <a href="{{ route('admin.user-management.faculty')}}" class="main-btn primary-btn btn-hover">View</a>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end col -->
                <div class="col-xl-6 col-lg-4 col-md-6 col-sm-6">
                    <div class="card-style-3 mb-30">
                        <div class="card-content">
                            <h3 class="text-center fw-bold mb-2"><a href="#0">Student</a></h3>
                            <div class="d-flex justify-content-center">
                                <img src="../../assets/admin/images/users.png" alt="Faculty Image"
                                    class="img-fluid w-25">
                            </div>
                            <div class="d-flex justify-content-center mt-3">
                                <a href="{{ route('admin.user-management.student')}}" class="main-btn primary-btn btn-hover">View</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end container -->
    </section>
    <!-- ========== section end ========== -->
</main>
<!-- ======== main-wrapper end =========== -->

@include('admin.partials.footer')

</body>

</html>
