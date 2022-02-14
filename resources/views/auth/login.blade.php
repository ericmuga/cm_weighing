@extends('layouts.auth_master')
@section('content')
<div class="login-box">
    <div class="login-logo">
        <a href="#"><b><small>Weight Management System</small></b> Login</a>
    </div>
    <!-- /.login-logo -->
    <div class="card">
        <div class="card-body login-card-body">
            <img src="{{ asset('assets/img/choice1.png') }}" alt="Choicemeats Logo" class="brand-image"
                style="display: block; margin-left: auto; margin-right: auto; width: 50%; ">
            <p class="login-box-msg">Sign in to start your session</p>

            <form action="#" method="post">
                @csrf
                <div class="input-group mb-3">
                    <input type="text" class="form-control" placeholder="Username" name="username" required value=""
                        autofocus>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-user"></span>
                        </div>
                    </div>
                </div>
                <div class="input-group mb-3">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-7">
                        <div class="icheck-primary">
                            <input type="checkbox" id="remember" name="remember"
                                {{ old('remember') ? 'checked' : '' }} onclick="showPassword()">
                            <label for="remember">
                                Show Password
                            </label>
                        </div>
                    </div>
                    <!-- /.col -->
                    <div class="col-5">
                        <button type="submit" class="btn btn-primary btn-block">Sign In</button>
                    </div>
                    <!-- /.col -->
                </div>
            </form>

            <div class="social-auth-links text-center mb-3">

            </div>
            <!-- /.social-auth-links -->

            <p class="mb-1">
                {{-- <a href="forgot-password.html">I forgot my password</a> --}}
            </p>
            <p class="mb-0">
                {{-- <a href="register.html" class="text-center">Register a new membership</a> --}}
            </p>
        </div>
        <!-- /.login-card-body -->
    </div>
</div>
<!-- /.login-box -->
@endsection
