@extends('layouts.QA_master')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header row">
                <div class="col-md-7">
                    <h3 class="card-title">Grading Work Sheet | <span id="subtext-h1-title"><small> showing
                                <strong>Today's</strong>
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
                                <th>Item Name</th>
                                <th>Vendor No</th>
                                <th>Total net(kgs)</th>
                                <th>Settlement Weight</th>
                                <th>Classification</th>
                                <th>Fat Group</th>
                                <th>Grading Status</th>
                                <th>Slaughter Date</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Agg No </th>
                                <th>Receipt No.</th>
                                <th>Item Name</th>
                                <th>Vendor No</th>
                                <th>Total net(kgs)</th>
                                <th>Settlement Weight</th>
                                <th>Classification</th>
                                <th>Fat Group</th>
                                <th>Grading Status</th>
                                <th>Slaughter Date</th>
                            </tr>
                        </tfoot>
                        <tbody>
                            @foreach($slaughter_data as $data)
                                <tr>
                                    <td>{{ $i++ }}</td>
                                    <td>{{ $data->agg_no }}</td>
                                    <td>{{ $data->receipt_no }}</td>
                                    <td>{{ $data->item_name }}</td>
                                    <td>{{ $data->vendor_no }}</td>
                                    <td>{{ number_format($data->total_net, 2) }}</td>
                                    <td>{{ number_format($data->settlement_weight, 2) }}</td>
                                    <td>{{ $data->classification_code }}</td>
                                    <td>{{ $data->fat_group }}</td>

                                    @if($data->fat_group == null)
                                        <td class="gradingShow" data-agg_no="{{ $data->agg_no }}"
                                            data-item_code="{{ $data->item_code }}" data-id="{{ $data->id }}"
                                            data-item_name="{{ $data->item_name }}"
                                            data-vendor="{{ $data->vendor_no }}"
                                            data-settlement="{{ $data->settlement_weight }}"><a href="#"
                                                class="text-warning">pending</a>
                                        </td>

                                    @else

                                        <td class="gradingShow link-success" data-agg_no="{{ $data->agg_no }}"
                                            data-item_code="{{ $data->item_code }}" data-id="{{ $data->id }}"
                                            data-item_name="{{ $data->item_name }}"
                                            data-vendor="{{ $data->vendor_no }}"
                                            data-fat_group="{{ $data->fat_group }}"
                                            data-narration="{{ $data->narration }}"
                                            data-settlement="{{ $data->settlement_weight }}"><a href="#"
                                                class="text-success">Graded</a>
                                        </td>

                                    @endif
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

<div id="gradingShow" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
        <form id="form-grading-slaughter" action="{{ route('qa_update_grading') }}" method="post">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Grading For Carcass No: <strong><input
                                style="border:none" type="text" id="agg_no" name="agg_no" value="" readonly></strong>
                    </h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row text-center">
                        <div class="col-md-6 form-group">
                            <label for="exampleInputPassword1">Item Code</label>
                            <input type="text" style="text-align: center" class="form-control" value="" id="item_code"
                                placeholder="" readonly>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="exampleInputPassword1">Item Description</label>
                            <input type="text" style="text-align: center" class="form-control" value="" id="item_name"
                                placeholder="" readonly>
                        </div>
                    </div>
                    <div class="row text-center">
                        <div class="col-md-6 form-group">
                            <label for="exampleInputPassword1">Vendor Number</label>
                            <input type="text" style="text-align: center" class="form-control" value="" id="vendor_no"
                                placeholder="" readonly>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="exampleInputPassword1">Settlement Weight</label>
                            <input type="text" style="text-align: center" class="form-control" id="settlement_weight"
                                placeholder="" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="email" class="col-form-label">Fat Group Classifications</label>
                        <select class="form-control select2" name="fat_group" id="fat_group" required>
                            {{-- <option disabled selected> select an option </option> --}}
                            @foreach($classifications as $cls)
                                <option value="{{ $cls->code }}" selected="selected">
                                    {{ $cls->code }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="exampleInputIdNumber">Narration (<em>optional</em>) </label>
                        <input type="text" class="form-control" id="narration" name="narration" value="">
                    </div>
                    <input type="hidden" id="item_id" name="item_id" value="">
                    <div class="modal-footer">
                        <div class="form-group">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-warning btn-lg">
                                <i class="fa fa-paper-plane" aria-hidden="true"></i> Update
                            </button>
                        </div>
                    </div>
                </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function () {

        $("body").on("click", ".gradingShow", function (a) {
            a.preventDefault();

            var id = $(this).data('id');
            var agg_no = $(this).data('agg_no');
            var item_code = $(this).data('item_code');
            var item_name = $(this).data('item_name');
            var vendor = $(this).data('vendor');
            var settlement = $(this).data('settlement');
            var fat_group = $(this).data('fat_group');
            var narration = $(this).data('narration');

            $('#item_id').val(id);
            $('#agg_no').val(agg_no);
            $('#item_code').val(item_code);
            $('#item_name').val(item_name);
            $('#vendor_no').val(vendor);
            $('#settlement_weight').val((Math.round(settlement * 100) / 100).toFixed(2));
            $('#fat_group').val(fat_group);
            $('#narration').val(narration);

            $('#gradingShow').modal('show');
        });

        $('#form-grading-slaughter').validate({
            rules: {
                fat_group: {
                    required: true,
                },
                narration: {
                    maxlength: 50,
                },
            },
            messages: {
                fat_group: "Please select a fat group first",
                maxlength: "Your narration must be at most 50 characters long",
            },
            errorElement: 'span',
            errorPlacement: function (error, element) {
                error.addClass('invalid-feedback');
                element.closest('.form-group').append(error);
            },
            highlight: function (element, errorClass, validClass) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function (element, errorClass, validClass) {
                $(element).removeClass('is-invalid');
            }
        });
    });

</script>
@endsection
