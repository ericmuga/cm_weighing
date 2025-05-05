<nav class="main-header navbar navbar-expand-md navbar-light navbar-white">
    <div class="container-fluid">
        <a href="{{ route('stock_transfers_issue') }}" class="navbar-brand">
            <img src="{{ asset('assets/img/choice1.png') }}" alt="CML Calibra Logo"
                class="brand-image" style="">
            <span class="brand-text font-weight-light"><strong> CML Weight Management System</strong></span>
        </a>

        <button class="navbar-toggler order-1" type="button" data-toggle="collapse" data-target="#navbarCollapse"
            aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse order-3" id="navbarCollapse">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a id="dropdownSubMenu1" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                        class="nav-link dropdown-toggle">Weigh</a>
                    <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                        <li>
                            <a href="{{ route('butchery_scale2') }}" class="dropdown-item">
                                Scale 2 - Halves
                            </a>
                        </li>
                        <hr class="dropdown-divider" />
                        <li>
                            <a href="{{ route('butchery_scale_3') }}" class="dropdown-item">
                                Scale 3 - Deboning
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a id="dropdownSubMenu2" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                        class="nav-link dropdown-toggle">Settings</a>
                    <ul aria-labelledby="dropdownSubMenu2" class="dropdown-menu border-0 shadow">
                        <li>
                            <a href="{{ route('scale_configs', $scale_filter) }}" class="dropdown-item">
                                Scale settings - {{ $scale_filter }}
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a id="dropdownSubMenu3" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                        class="nav-link dropdown-toggle">Data management</a>
                    <ul aria-labelledby="dropdownSubMenu3" class="dropdown-menu border-0 shadow">
                        <li>
                            <a href="{{ route('idt_lines_report') }}" class="dropdown-item">IDT Lines report</a>
                        </li>
                        <hr class="dropdown-divider" />
                        <li>
                            <a href="{{ route('idt_summary_report') }}" class="dropdown-item">IDT Summary report</a>
                        </li>
                    </ul>
                </li>
            </ul>

            <!-- SEARCH FORM -->
            <form class="form-inline ml-0 ml-md-3">
                <div class="input-group input-group-sm">
                    <input class="form-control form-control-navbar" type="search" placeholder="Search"
                        aria-label="Search">
                    <div class="input-group-append">
                        <button class="btn btn-navbar" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>

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
