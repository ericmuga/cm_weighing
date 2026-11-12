@extends('layouts.QA_master')

@section('content-header')
<div class="container">
    <div class="row mb-2">
        <div class="col-sm-9">
            {{-- <h1 class="m-0"> {{ $title }}<small></small></h1> --}}
            <h1 class="card-title"> Dashboard | <span id="subtext-h1-title"><small> showing
                        {{ $date->isToday() ? "today's" : '' }} numbers | Date:
                        <code> {{ $helpers->dateToHumanFormat($date) }}</code></small></span></h1>
        </div><!-- /.col -->
        <div class="col-sm-3">
            {{-- GET form so the chosen date is in the URL and survives a refresh --}}
            <form method="GET" action="{{ route('qa_dashboard') }}" class="form-inline justify-content-sm-end mt-2 mt-sm-0">
                <label for="dashboard-date" class="mr-2 small text-muted">Show date</label>
                <input type="date" id="dashboard-date" name="date" class="form-control form-control-sm mr-2"
                    value="{{ $date->toDateString() }}" max="{{ today()->toDateString() }}"
                    onchange="this.form.submit()">
                @unless($date->isToday())
                    <a href="{{ route('qa_dashboard') }}" class="btn btn-sm btn-outline-secondary">Today</a>
                @endunless
            </form>
        </div><!-- /.col -->
    </div><!-- /.row -->
</div><!-- /.container-fluid -->
<hr>
@endsection

@section('content')
<!-- Small boxes (Stat box) -->
<div class="row">
    <div class="col-lg-3 col-6">
        <!-- small box -->
        <div class="small-box bg-secondary">
            <div class="inner">
                <h3>{{ $lined_up }}</h3>
                <p>No. of Animals brought in for Slaughter</p>
            </div>
            <div class="icon">
                <i class="ion ion-pie-graph"></i>
            </div>
            <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <!-- small box -->
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $slaughtered }}</h3>
                <p>No. of Carcasses Weighed</p>
            </div>
            <div class="icon">
                <i class="ion ion-bag"></i>
            </div>
            <a href="#" class="small-box-footer">More info <i
                    class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->
    <div class="col-lg-3 col-6">
        <!-- small box -->
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $graded }}<sup style="font-size: 20px"></sup></h3>
                <p>No. of Carcass Graded</p>
            </div>
            <div class="icon">
                <i class="ion ion-stats-bars"></i>
            </div>
            <a href="{{ route('qa_grading_v2', ['status' => 'graded', 'date' => $date->toDateString()]) }}" class="small-box-footer">More info <i
                    class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <!-- small box -->
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $slaughtered - $graded }} </h3>
                <p>Remaining count</p>
            </div>
            <div class="icon">
                <i class="ion ion-alert"></i>
            </div>
            <a href="{{ route('qa_grading_v2', ['status' => 'pending', 'date' => $date->toDateString()]) }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->    
</div>
<!-- /.row -->

@endsection
