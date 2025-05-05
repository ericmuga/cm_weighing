@extends('layouts.butchery_master')

@section('content')
<div class="card-header h3 mb-4">
    Dashboard | <span id=""><small> showing today's numbers | Date:<code> {{ $helpers->dateToHumanFormat(today()) }}</code></small>
</div>


<div class="row">
    <div class="col-lg-4 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>60</h3>
                <p>No. of expected sides</p>
            </div>
            <div class="icon">
                <i class="fa fa-box"></i>
            </div>
            <a href="{{ route('stock_take') }}" class="small-box-footer">More info <i
                    class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-4 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>35<sup style="font-size: 20px"></sup></h3>
                <p>No. of Sides transfered</p>
            </div>
            <div class="icon">
                <i class="fa fa-exchange-alt"></i>
            </div>
            <a href="{{ route('stock_transfers_issue') }}" class="small-box-footer">More info <i
                    class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-4 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>133,567 <sup style="font-size: 20px">kgs</sup></h3>
                <p>Weight of deboned products</p>
            </div>
            <div class="icon">
                <i class="fa fa-balance-scale"></i>
            </div>
            <a href="{{ route('stock_transfers_receive') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>
<!-- /.row -->

@endsection