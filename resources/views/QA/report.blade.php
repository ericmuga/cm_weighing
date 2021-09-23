@extends('layouts.QA_master')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header row">
                <div class="col-md-7">
                    <h3 class="card-title">Grading Data Report| <span id="subtext-h1-title"><small> showing
                        <strong>{{ $filter? : "Last 1,000" }}</strong>
                        entries</small> </span></h3>
                </div>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <div class="hidden" hidden>{{ $i = 1 }}</div>
                <div class="table-responsive">
                    <table id="example1" class="table table-bordered table-striped " width="100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Agg No </th>
                                <th>Receipt No.</th>
                                <th>Item Code </th>
                                <th>Item Name</th>
                                <th>Vendor No</th>
                                <th>Vendor Name</th>
                                <th>Side A Weight(kgs)</th>
                                <th>Side B Weight(kgs)</th>
                                <th>Total weight(kgs)</th>
                                <th>Total net(kgs)</th>
                                <th>Settlement Weight</th>
                                <th>Classification</th>
                                <th>Slaughter Date</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Agg No </th>
                                <th>Receipt No.</th>
                                <th>Item Code </th>
                                <th>Item Name</th>
                                <th>Vendor No</th>
                                <th>Vendor Name</th>
                                <th>Side A Weight(kgs)</th>
                                <th>Side B Weight(kgs)</th>
                                <th>Total weight(kgs)</th>
                                <th>Total net(kgs)</th>
                                <th>Settlement Weight</th>
                                <th>Classification</th>
                                <th>Slaughter Date</th>
                            </tr>
                        </tfoot>
                        <tbody>
                            @foreach($slaughter_data as $data)
                            <tr>
                                <td>{{ $i++ }}</td>
                                <td>{{ $data->agg_no }}</td>
                                <td>{{ $data->receipt_no }}</td>
                                <td>{{ $data->item_code }}</td>
                                <td>{{ $data->item_name }}</td>
                                <td>{{ $data->vendor_no }}</td>
                                <td>{{ $data->vendor_name }}</td>
                                <td>{{ number_format($data->sideA_weight, 2) }}</td>
                                <td>{{ number_format($data->sideB_weight, 2) }}</td>
                                <td>{{ number_format($data->total_weight, 2) }}</td>
                                <td>{{ number_format($data->total_net, 2) }}</td>
                                <td>{{ number_format($data->settlement_weight, 2) }}</td>
                                <td>{{ $data->classification_code }}</td>
                                <td>{{ $helpers->shortDateTime($data->created_at) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- /.card-body -->
        </div>
        <!-- /.card -->
    </div>
    <!-- /.col -->
</div>

@endsection
