<!DOCTYPE html>
<!--
This is a starter template page. Use this page to start your new project from
scratch. This page gets rid of all links and provides the needed markup only.
-->
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="author" content="Ephantus Karanja">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <link rel="shortcut icon" style="" href="{{ asset('assets/img/choice1.png') }}">
    <title>WMS | {{ $title }}</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet"
        href="{{ asset('assets/plugins/fontawesome-free/css/all.min.css') }}">
    <!-- DataTables -->
    <link rel="stylesheet"
        href="{{ asset('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet"
        href="{{ asset('assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet"
        href="{{ asset('assets/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet"
        href="{{ asset('assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <!-- Tempusdominus Bootstrap 4 -->
    <link rel="stylesheet"
        href="{{ asset('assets/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css') }}">
    <!-- iCheck -->
    <link rel="stylesheet"
        href="{{ asset('assets/plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
    <!-- JQVMap -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/jqvmap/jqvmap.min.css') }}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ asset('assets/dist/css/adminlte.min.css') }}">
    <!-- overlayScrollbars -->
    <link rel="stylesheet"
        href="{{ asset('assets/plugins/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
    <!-- Daterange picker -->
    <link rel="stylesheet"
        href="{{ asset('assets/plugins/daterangepicker/daterangepicker.css') }}">
    <!-- summernote -->
    <link rel="stylesheet"
        href="{{ asset('assets/plugins/summernote/summernote-bs4.min.css') }}">

    <!-- toastr -->
    <link rel="stylesheet" href="http://cdn.bootcss.com/toastr.js/latest/css/toastr.min.css">
</head>

<body class="hold-transition layout-top-nav">
    <div class="wrapper">

        <!-- Navbar -->
        @include('layouts.headers.router_header')
        <!-- /.navbar -->

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <div class="content-header">
                @yield('content-header')
            </div>
            <!-- /.content-header -->

            <!-- Main content -->
            <div class="content">
                <div class="row">
                    <div class="container-fluid">
                        <div class="card-deck-wrapper">
                            <div class="card-deck">
                                <div class="card p-2 bg-info" style="height: 250px">
                                    <a class="card-body text-center card-block stretched-link text-decoration-none"
                                        href="{{ route('slaughter_dashboard') }}">
                                        <h4 class="card-title">Slaughter</h4>
                                        <p class="card-text">Select this option to switch to slaughter.
                                        </p>
                                    </a>
                                </div>
                                <div class="card p-2 bg-warning" style="height: 250px">
                                    <a class="card-body text-center card-block stretched-link text-decoration-none"
                                        href="#">
                                        <h4 class="card-title">Butchery</h4>
                                        <p class="card-text">Select this option to switch to butchery.
                                        </p>
                                    </a>
                                </div>
                                <div class="card p-2 bg-success" style="height: 250px">
                                    <a class="card-body text-center card-block stretched-link text-decoration-none card-link"
                                        href="#">
                                        <h4 class="card-title">Admin</h4>
                                        <p class="card-text">Select this option to switch to Admin.
                                        </p>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- /.content -->
            </div>
            <!-- /.content-wrapper -->

            <!-- Main Footer -->
            <div class="row">
                <div class="div" style="position: fixed; bottom: 0; width: 100%">
                    @include('layouts.footers.footer')
                </div>
            </div>
        </div>
        <!-- ./wrapper -->

        <!-- REQUIRED SCRIPTS -->

        <!-- jQuery -->
        <script src="{{ asset('assets/plugins/jquery/jquery.min.js') }}"></script>
        <!-- Bootstrap 4 -->
        <script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.bundle.min.js') }}">
        </script>
        <!-- DataTables  & Plugins -->
        <script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js') }}">
        </script>
        <script
            src="{{ asset('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}">
        </script>
        <script
            src="{{ asset('assets/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}">
        </script>
        <script
            src="{{ asset('assets/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}">
        </script>
        <script
            src="{{ asset('assets/plugins/datatables-buttons/js/dataTables.buttons.min.js') }}">
        </script>
        <script
            src="{{ asset('assets/plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}">
        </script>
        <script src="{{ asset('assets/plugins/jszip/jszip.min.js') }}"></script>
        <script src="{{ asset('assets/plugins/pdfmake/pdfmake.min.js') }}"></script>
        <script src="{{ asset('assets/plugins/pdfmake/vfs_fonts.js') }}"></script>
        <script
            src="{{ asset('assets/plugins/datatables-buttons/js/buttons.html5.min.js') }}">
        </script>
        <script
            src="{{ asset('assets/plugins/datatables-buttons/js/buttons.print.min.js') }}">
        </script>
        <script
            src="{{ asset('assets/plugins/datatables-buttons/js/buttons.colVis.min.js') }}">
        </script>
        <!-- Select2 -->
        <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
        <!-- AdminLTE App -->
        <script src="{{ asset('assets/dist/js/adminlte.min.js') }}"></script>
        {{-- <script src="{{asset('assets/dist/js/pages/dashboard.js') }}">
        </script> --}}
        <!-- jQuery UI 1.11.4 -->
        <script src="{{ asset('assets/plugins/jquery-ui/jquery-ui.min.js') }}"></script>
        <script>
            $.widget.bridge('uibutton', $.ui.button)

        </script>
        <!-- ChartJS -->
        <script src="{{ asset('assets/plugins/chart.js/Chart.min.js') }}"></script>
        <!-- Sparkline -->
        <script src="{{ asset('assets/plugins/sparklines/sparkline.js') }}"></script>
        <!-- JQVMap -->
        <script src="{{ asset('assets/plugins/jqvmap/jquery.vmap.min.js') }}"></script>
        <script src="{{ asset('assets/plugins/jqvmap/maps/jquery.vmap.usa.js') }}"></script>
        <!-- jQuery Knob Chart -->
        <script src="{{ asset('assets/plugins/jquery-knob/jquery.knob.min.js') }}"></script>
        <!-- daterangepicker -->
        <script src="{{ asset('assets/plugins/moment/moment.min.js') }}"></script>
        <script src="{{ asset('assets/plugins/daterangepicker/daterangepicker.js') }}"></script>
        <!-- Tempusdominus Bootstrap 4 -->
        <script
            src="{{ asset('assets/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js') }}">
        </script>
        <!-- Summernote -->
        <script src="{{ asset('assets/plugins/summernote/summernote-bs4.min.js') }}"></script>
        <!-- overlayScrollbars -->
        <script
            src="{{ asset('assets/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}">
        </script>


        <!-- toastr -->
        <script src="http://cdn.bootcss.com/toastr.js/latest/js/toastr.min.js"></script>
        {!! Toastr::message() !!}

        @yield('scripts')

</body>

</html>
