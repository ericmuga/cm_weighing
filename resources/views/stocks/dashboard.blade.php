@extends('layouts.transfers_master')

@section('content')
<div class="card-header h3 mb-4">
    Dashboard | <span id=""><small> showing today's numbers | Date:<code> {{ $helpers->dateToHumanFormat(today()) }}</code></small>
</div>


<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $todaysStockEntriesCount }}</h3>
                <p>No. of Stock Entries made for today</p>
            </div>
            <div class="icon">
                <i class="fa fa-box"></i>
            </div>
            <a href="{{ route('stock_take') }}" class="small-box-footer">More info <i
                    class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $todaysStockTransferIssuesCount }}<sup style="font-size: 20px"></sup></h3>
                <p>No. of Stock Transfers Issued</p>
            </div>
            <div class="icon">
                <i class="fa fa-exchange-alt"></i>
            </div>
            <a href="{{ route('stock_transfers_issue') }}" class="small-box-footer">More info <i
                    class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $todaysStockTransferReceiptsCount }} </h3>
                <p>No. of Stock Transfers Received</p>
            </div>
            <div class="icon">
                <i class="fa fa-download" aria-hidden="true"></i>
            </div>
            <a href="{{ route('stock_transfers_receive') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>
<!-- /.row -->

@endsection