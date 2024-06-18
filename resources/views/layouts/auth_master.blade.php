<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="author" content="Ephantus Karanja">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <link rel="shortcut icon" style="height: 50%; width: 100%;" href="{{ asset('assets/img/choice1.png') }}">
    <title>WMS | {{ $title }}</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
        href="{{ asset('assets/googlefonts.css') }}">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome-free/css/all.min.css') }}">
    <!-- icheck bootstrap -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ asset('assets/dist/css/adminlte.min.css') }}">
    <!-- toastr -->
    <link rel="stylesheet" href="{{ asset('assets/toastr.min.css') }}">
</head>

<body class="hold-transition login-page">

    @php
    if(session()->has('session_message')){
    $message = Session::get('session_message');
    Brian2694\Toastr\Facades\Toastr::warning($message, 'Warning!');
    Session::forget('session_message');
    }
    @endphp

    @yield('content')


    <!-- jQuery -->
    <script src="{{ asset('assets/plugins/jquery/jquery.min.js') }}"></script>
    <!-- Bootstrap 4 -->
    <script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <!-- AdminLTE App -->
    <script src="{{ asset('assets/dist/js/adminlte.min.js') }}"></script>
    <!-- toastr -->
    <script src="{{ asset('assets/toastr.min.js') }}"></script>
    {!! Toastr::message() !!}

    <script>
        function showPassword() {
            var x = document.getElementById("password");
            if (x.type === "password") {
                x.type = "text";
            } else {
                x.type = "password";
            }
        }
    </script>

    @if (app()->environment('production'))
        @include('prevent-inspection')
    @endif

    @yield('scripts')
</body>

</html>
