<nav class="main-header navbar navbar-expand-md navbar-light navbar-white">
    <div class="container-fluid">
        <a href="#" class="navbar-brand">
            <img src="{{ asset('assets/img/choice1.png') }}" alt="FCL Calibra Logo"
                class="brand-image" style="">
            <span class="brand-text font-weight-light"><strong> CML Weight Management System</strong></span>
        </a>

        <button class="navbar-toggler order-1" type="button" data-toggle="collapse" data-target="#navbarCollapse"
            aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse order-3" id="navbarCollapse">

          <!-- Right navbar links -->
        @include('partials.rightnav')
    </div>
</nav>

<!-- logout modal -->
<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">Please confirm if you are ready to end your current session.</div>
            <div class="modal-footer">
                <button class="btn btn-secondary btn-flat " type="button" data-dismiss="modal">Cancel</button>
                <a href="{{ route('logout') }}" type="submit"
                    class="btn btn-warning btn-lg  float-right"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </div>
</div>
<!-- end logout -->
