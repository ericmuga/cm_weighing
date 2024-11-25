@extends('layouts.master')

@section('header')
    @include('layouts.headers.router_header')
@endsection

@section('content')

    <!-- Main content -->
    <div class="card-deck-wrapper">
        <div class="card-deck">
            <div class="card p-2 bg-info" style="height: 250px">
                <a class="card-body text-center card-block stretched-link text-decoration-none"
                    href="{{ route('slaughter_dashboard') }}">
                    <h4 class="card-title">Slaughter</h4>
                    <p class="card-text">Select this option to switch to slaughter.
                    </p>
                </a>
                <div class="icon text-center">
                    <i class="fa fa-shopping-basket fa-4x" aria-hidden="true"></i>
                </div>
            </div>
            <div class="card p-2 bg-warning" style="height: 250px">
                <a class="card-body text-center card-block stretched-link text-decoration-none card-link"
                    href="{{ route('stocks_dashboard') }}">
                    <h4 class="card-title">Stocks</h4>
                    <p class="card-text">Select this option to switch to stocks.
                    </p>
                </a>
                <div class="icon text-center">
                    <i class="fa fa-arrow-right fa-4x" aria-hidden="true"></i>
                </div>
            </div>
            <div class="card p-2 bg-danger" style="height: 250px">
                <a class="card-body text-center card-block stretched-link text-decoration-none"
                    href="#">
                    <h4 class="card-title">Butchery</h4>
                    <p class="card-text">This Inteface is in development
                    </p>
                </a>
                <div class="icon text-center">
                    <i class="fa fa-cut fa-4x" aria-hidden="true"></i>
                </div>
            </div>
            <div class="card p-2 bg-success" style="height: 250px">
                <a class="card-body text-center card-block stretched-link text-decoration-none card-link"
                    href="{{ route('qa_dashboard') }}">
                    <h4 class="card-title">QA</h4>
                    <p class="card-text">This Inteface is in development.
                    </p>
                </a>
                <div class="icon text-center">
                    <i class="fa fa-check fa-4x" aria-hidden="true"></i>
                </div>
            </div>
        </div>
    </div>
    <!-- /.content -->

@endsection