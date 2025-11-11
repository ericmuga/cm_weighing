@extends('layouts.slaughter_master')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header row">
                <div class="col-md-7">
                    <h3 class="card-title">Offals Data Report| <span id="subtext-h1-title"><small> showing
                        <strong>{{ $filter? : "Last 3 days " }}</strong>
                        entries</small> </span></h3>
                </div>

                <div class="col-md-5">
                    <button class="btn btn-success" data-toggle="modal" data-target="#export_data"><i
                            class="fas fa-file-excel"></i>
                        Generate Offals Report</button>
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
                                <th>Product Code</th>
                                <th>Product Name</th>
                                <th>Net Weight (kgs)</th>
                                <th>Scale Reading (kgs)</th>
                                <th>Manually Recorded</th>
                                <th>Customer</th>
                                <th>Grade</th>
                                <th>Recorded by</th>
                                <th>Recorded DateTime</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Product Code</th>
                                <th>Product Name</th>
                                <th>Net Weight (kgs)</th>
                                <th>Scale Reading (kgs)</th>
                                <th>Manually Recorded</th>
                                <th>Customer</th>
                                <th>Grade</th>
                                <th>Recorded by</th>
                                <th>Recorded DateTime</th>
                            </tr>
                        </tfoot>
                        <tbody>
                            @foreach($offals_data as $entry)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $entry->product_code }}</td>
                                <td>{{ $entry->product_name }}</td>
                                <td>{{ number_format($entry->net_weight, 2) }}</td>
                                <td>{{ number_format($entry->scale_reading, 2) }}</td>
                                @if($entry->is_manual == 0)
                                    <td>
                                        <span class="badge badge-success">No</span>
                                    </td>
                                @else
                                    <td>
                                        <span class="badge badge-warning">Yes</span>
                                    </td>
                                @endif
                                <td>{{ $entry->customer_name }}</td>
                                @if($entry->grade == 'reject')
                                    <td><span class="badge badge-danger">Reject</span></td>
                                @elseif($entry->grade == '0')
                                    <td><span class="badge badge-success">0</span></td>
                                @elseif($entry->grade == 'edge')
                                    <td><span class="badge badge-warning">Edge</span></td>
                                @else
                                    <td>{{ $entry->grade }}</td>
                                @endif
                                <td>{{ $entry->username }}</td>
                                <td>{{ $helpers->shortDateTime($entry->created_at) }}</td>
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
    <form id="form-orders-export" action="{{ route('offals_report_export') }}" method="post">
        @csrf
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Export Offals Report</h5>
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
