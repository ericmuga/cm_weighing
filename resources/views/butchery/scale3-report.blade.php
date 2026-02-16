@extends('layouts.butchery_master')

@section('content-header')

<div class="card-header h3 mb-4">
    {{ $title }} | <span id=""><small> showing deboning entries for <strong>{{ $filter ?? '3 days' }}</strong> </small></span>
</div>
@endsection

@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-5">
                        <h3 class="card-title"> Scale 3 Deboned output registry | <span id="subtext-h1-title"><small> entries
                                    ordered by
                                    latest</small> </span></h3>
                    </div>
                    <div class="col-md-5">
                        <button class="btn btn-success" data-toggle="modal" data-target="#export_data"><i
                                class="fas fa-file-excel"></i>
                            Generate Lines Report</button>
                    </div>
                </div>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <div class="hidden" hidden>{{ $i = 1 }}</div>
                <div class="table-responsive">
                    <table id="example1" class="table table-striped table-bordered table-hover" width="100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Code </th>
                                <th>product </th>
                                <th>Scale Weight(kgs)</th>
                                <th>Net Weight(kgs)</th>
                                <th>No. of pieces</th>
                                <th>Narration</th>
                                <th>Created At </th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Code </th>
                                <th>product </th>
                                <th>Scale Weight(kgs)</th>
                                <th>Net Weight(kgs)</th>
                                <th>No. of Pieces</th>
                                <th>Narration</th>
                                <th>Created At </th>
                            </tr>
                        </tfoot>
                        <tbody>
                            @foreach($report_data as $data)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    {{-- Allow edits for only today --}}
                                    @php
                                        $createdAtDate = \Carbon\Carbon::parse($data->created_at)->format('Y-m-d');
                                        $todayDate = \Carbon\Carbon::today()->format('Y-m-d');
                                    @endphp

                                    @if($createdAtDate === $todayDate)
                                        <td id="itemCodeModalShow" data-id="{{ $data->id }}"
                                            data-weight="{{ number_format($data->scale_reading, 2) }}"
                                            data-no_of_pieces="{{ $data->no_of_pieces }}"
                                            data-code="{{ $data->product_code }}"   data-narration="{{ $data->narration }}"
                                            data-production_process="{{ $data->process_code }}"
                                            data-item="{{ $data->description }}"><a
                                                href="#">{{ $data->product_code }}</a>
                                        </td>
                                    @else
                                        <td><span class="badge badge-warning">Edit closed</span></td>
                                    @endif

                                    <td>{{ $data->description }}</td>
                                    <td> {{ number_format($data->scale_reading, 2) }}</td>
                                    <td> {{ number_format($data->netweight, 2) }}</td>
                                    <td> {{ $data->no_of_pieces }}</td>
                                    <td> {{ $data->narration }}</td>
                                    <td> {{ $data->created_at }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- /.card-body -->
        </div>
        <!-- /.card -->
        <!-- /.col -->
    </div>
</div>
<!-- slicing ouput data show -->

<!-- Start Export lines Modal -->
<div class="modal fade" id="export_data" tabindex="-1" role="dialog" aria-hidden="true">
    <form id="form-orders-export" action="{{ route('deboning_report_export') }}" method="POST" class="form-prevent-multiple-submits">
        @csrf
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Export Deboning Lines Report</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="from_date">From Date:</label>
                            <input type="date" class="form-control" name="from_date"
                                id="stemplate_date_created_from_flagged" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="to_date">To Date:</label>
                            <input type="date" class="form-control" name="to_date"
                                id="stemplate_date_created_from_flagged" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="item_code">Product <i>(For all items, leave this blank)</i></label>
                            <select class="form-control select2" name="item_code" id="item_code">
                                <option disabled selected>Select Product</option>
                                @foreach($report_data as $product)
                                <option value="{{ $product->product_code }}">{{ $product->product_code }} - {{ $product->description }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary float-left" type="button" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-lg  float-right  btn-prevent-multiple-submits"><i class="fa fa-send"></i>
                        Export</button>
                </div>
            </div>
        </div>
    </form>
</div>
<!-- End Export combined Modal -->

@endsection

@section('scripts')
<script>

</script>
@endsection
