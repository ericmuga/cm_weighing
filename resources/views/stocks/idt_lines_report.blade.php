@extends('layouts.stocks_master')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header row">
                <div class="col-md-7">
                    <h3 class="card-title">Stocks IDT Data Report| <span id="subtext-h1-title"><small>entries</small> </span></h3>
                </div>

                <div class="col-md-5">
                    <button class="btn btn-success" data-toggle="modal" data-target="#export_data"><i
                            class="fas fa-file-excel"></i>
                        Generate Lines Report</button>
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
                                <th>IDT No</th>
                                <th>Product Code</th>
                                <th>Product Description</th>
                                <th>Batch No.</th>
                                <th>Total Weight</th>
                                <th>From Location</th>
                                <th>To Location</th>
                                <th>Narration</th>
                                <th>Issued By</th>
                                <th>Created At</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>IDT No</th>
                                <th>Product Code</th>
                                <th>Product Description</th>
                                <th>Batch No.</th>
                                <th>Total Weight</th>
                                <th>From Location</th>
                                <th>To Location</th>
                                <th>Narration</th>
                                <th>Issued By</th>
                                <th>Created At</th>
                            </tr>
                        </tfoot>
                        <tbody>
                            @foreach($entries as $transfer)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $transfer->id }}</td>
                                <td>{{ $transfer->item_code }}</td>
                                <td>{{ $transfer->item_description }}</td>
                                <td>{{ $transfer->batch_no }}</td>
                                <td>{{ number_format($transfer->net_weight, 2) }}</td>
                                <td>{{ $transfer->from_location_code }}</td>
                                <td>{{ $transfer->to_location_code }}</td>
                                <td>{{ $transfer->narration }}</td>
                                <td>{{ $transfer->issuer }}</td>
                                <td>{{ \Carbon\Carbon::parse($transfer->created_at)->format('d/m/Y H:i') }}</td>
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

<!-- Start Export lines Modal -->
<div class="modal fade" id="export_data" tabindex="-1" role="dialog" aria-hidden="true">
    <form id="form-orders-export" action="{{ route('idt_report_export') }}" method="POST">
        @csrf
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Export IDT Lines Report</h5>
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
                            <label for="from_location">From Location:</label>
                            <select class="form-control" name="from_location" id="from_location">
                                <option value="">Select From Location</option>
                                @foreach($locations as $code => $description)
                                <option value="{{ $code }}">{{ $description }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="to_location">To Location:</label>
                            <select class="form-control" name="to_location" id="to_location">
                                <option value="">Select To Location</option>
                                @foreach($locations as $code => $description)
                                <option value="{{ $code }}">{{ $description }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="from_idt_no">From IDT No:</label>
                            <input type="number" class="form-control" name="from_idt_no" id="from_idt_no" onchange="validateToIDTNo()">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="to_idt_no">To IDT No:</label>
                            <input type="number" class="form-control" name="to_idt_no" id="to_idt_no" onchange="validateToIDTNo()">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="item_code">Product:</label>
                            <select class="form-control select2" name="item_code" id="item_code">
                                <option disabled selected>Select Product</option>
                                @foreach($products as $product)
                                <option value="{{ $product->code }}">{{ $product->code }} - {{ $product->description }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="user_id">Issuer:</label>
                            <select class="form-control" name="user_id" id="user_id">
                                <option value="">Select Issuer</option>
                                @foreach($issuers as $user)
                                <option value="{{ $user->id }}">{{ $user->username }}</option>
                                @endforeach
                            </select>
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

@section('scripts')
<script>
    function validateToIDTNo() {
        var from_idt_no = document.getElementById('from_idt_no').value;
        var to_idt_no = document.getElementById('to_idt_no').value;

        if (from_idt_no && to_idt_no && (from_idt_no > to_idt_no)) {
            alert('To IDT No. must be greater than From IDT No.');
            document.getElementById('to_idt_no').value = '';
        }
    }
</script>
@endsection
