@extends('layouts.slaughter_master')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header row">
                <div class="col-md-7">
                    <h3 class="card-title">Slaughter Data Report| <span id="subtext-h1-title"><small> showing
                        <strong>{{ $filter? : "Last 1,000" }}</strong>
                        entries</small> </span></h3>
                </div>

                <div class="col-md-5">
                    <button class="btn btn-success" data-toggle="modal" data-target="#export_data"><i
                            class="fas fa-file-excel"></i>
                        Generate Summary Report</button>
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
                                <td>{{ $data->created_at }}</td>
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

<!-- Start Export combined Modal -->
<div class="modal fade" id="export_data" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <form id="form-orders-export" action="{{ route('slaughter_summary_report') }}" method="post">
        @csrf
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Export Slaughter Summary</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    *Filter by date (format:dd/mm/yyyy)
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="stemplate_date_created_from_flagged">From:</label>
                            <input type="date" class="form-control" name="from_date"
                                id="stemplate_date_created_from_flagged" autofocus required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="stemplate_date_created_from_flagged">To:</label>
                            <input type="date" class="form-control" name="to_date"
                                id="stemplate_date_created_from_flagged" autofocus required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary float-left" type="button" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-lg  float-right"><i class="fa fa-send"></i>
                        Export</button>
                </div>
            </div>
        </div>
    </form>
</div>
<!-- End Export combined Modal -->
@endsection
