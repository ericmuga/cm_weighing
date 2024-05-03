@extends('layouts.QA_master')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header row">
                <div class="col-md-8">
                    <h3 class="card-title">Grading Work Sheet V| <span id="subtext-h1-title"><small> showing
                                <strong>Today's</strong>
                                entries</small> | <button class="btn btn-success" id="execute-grading-btn">Generate
                                Classifications</button></span></h3>
                </div>
                <div class="col-md-4">
                    <span class="text-danger" id="err"></span>
                    <span class="text-success" id="succ"></span>
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
                                <th>QA Classification</th>
                                <th>Classification Code</th>
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
                                <th>QA Classification</th>
                                <th>Classification Code</th>
                                <th>Grading Status</th>
                                <th>Slaughter Date</th>
                            </tr>
                        </tfoot>
                        <tbody>
                            @foreach($grading_data as $data)
                                <tr>
                                    <td>{{ $i++ }}</td>
                                    <td>{{ $data->agg_no }}</td>
                                    <td>{{ $data->receipt_no }}</td>
                                    <td>{{ $data->description }}</td>
                                    <td>{{ $data->vendor_no }}</td>

                                    @if($data->classification == 1)
                                        <td>Premium</td>
                                    @elseif($data->classification == 2)
                                        <td>High Grade</td>
                                    @elseif($data->classification == 3)
                                        <td>Commercial</td>
                                    @elseif($data->classification == 4)
                                        <td>Poor C</td>
                                    @else
                                        <td><span class="text-danger">Pending QA Class</span></td>
                                    @endif

                                    <td>{{ $data->classification_code }}</td>

                                    @if($data->classification_code == null)                                        
                                        <td class="gradingShow" data-agg_no="{{ $data->agg_no }}"
                                            data-item_code="{{ $data->item_code }}" data-id="{{ $data->id }}"
                                            data-item_name="{{ $data->description }}"
                                            data-vendor="{{ $data->vendor_no }}"><a href="#"
                                                class="text-warning">pending</a>
                                        </td>
                                    @else
                                        <td class="gradingShow" data-agg_no="{{ $data->agg_no }}"
                                            data-item_code="{{ $data->item_code }}" data-id="{{ $data->id }}"
                                            data-item_name="{{ $data->description }}"
                                            data-vendor="{{ $data->vendor_no }}"><a href="#"
                                                class="text-success">graded</a>
                                        </td>
                                    @endif
                                    <td>{{ $helpers->shortDateTime($data->updated_at) }}</td>
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
    <div class="modal-dialog modal-xl">
        <form id="form-grading-slaughter" class="form-prevent-multiple-submits"
            action="{{ route('qa_update_grading_v2') }}" method="post">
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
                    <div class="row text-center form-group">
                        <div class="col-md-3">
                            <label for="exampleInputPassword1">Vendor Number</label>
                            <input type="text" style="text-align: center" class="form-control" value="" id="vendor_no"
                                placeholder="" readonly>
                        </div>
                        <div class="col-md-3">
                            <label for="exampleInputPassword1">Item Code</label>
                            <input type="text" style="text-align: center" class="form-control" value="" id="item_code"
                                placeholder="" readonly>
                        </div>
                        <div class="col-md-3">
                            <label for="exampleInputPassword1">Item Description</label>
                            <input type="text" style="text-align: center" class="form-control" value="" id="item_name"
                                placeholder="" readonly>
                        </div>
                        <div class="col-md-3">
                            <label for="exampleInputPassword1">Settlement Weight</label>
                            <input type="text" style="text-align: center" class="form-control" id="settlement_weight"
                                placeholder="" readonly>
                        </div>
                    </div>
                    <div class="row text-center form-group">
                        <div class="col-md-2">
                            <label for="email" class="col-form-label">Dentition</label>
                            <select class="form-control select2 params" name="dentition" id="dentition" required>
                                <<option value="" selected> Select an option </option>
                                <option value="1">Full mouth </option>
                                <option value="2">3 pairs </option>
                                <option value="3">2 pairs </option>
                                <option value="4">1 pair </option>
                                <option value="5">Milk Teeth </option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="email" class="col-form-label">Fat Cover</label>
                            <select class="form-control select2 params" name="fat_cover" id="fat_cover" required>
                                <option disabled selected> select an option </option>
                                <option value="1">Good fat cover (to be 3 to 10mm (or more), evenly and well distributed </option>
                                <option value="2">Fair fat cover(2-7mm) </option>
                                <option value="3">Minimum/inadequate fat cover </option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="email" class="col-form-label">Fat Color</label>
                            <select class="form-control select2 params" name="fat_color" id="fat_color" required>
                                <option disabled selected> select an option </option>
                                <option value="1">Creamish white fat </option>
                                <option value="2">Deep yellow fat </option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="email" class="col-form-label">Meat Color</label>
                            <select class="form-control select2 params" name="meat_color" id="meat_color" required>
                                <option disabled selected> select an option </option>
                                <option value="1">Bright red colour </option>
                                <option value="2">Dark meat colour </option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="email" class="col-form-label">Bruising</label>
                            <select class="form-control select2 params" name="bruising" id="bruising" required>
                                <option disabled selected> select an option </option>
                                <option value="1">Mild Bruises </option>
                                <option value="2">Extensive bruises </option>
                                <option value="3">Severely bruised </option>
                                <option value="4">Cysts Bovis infestation </option>
                                <option value="5">Other strange discolouration </option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="email" class="col-form-label">Muscles</label>
                            <select class="form-control select2 params" name="muscle" id="muscle" required>
                                <option disabled selected> select an option </option>
                                <option value="1">Well finished(good shape, well developed and thick flesh) </option>
                                <option value="2">Fair muscle conformation </option>
                                <option value="3">Poor muscle conformation </option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="email" class="col-form-label">Classification</label>
                        <select class="form-control select2 params" name="fat_group" id="fat_group" required>
                            <option disabled selected> select an option </option>
                            <option value="1"> Premium</option>
                            <option value="2" selected="selected"> High Grade</option>
                            <option value="3"> Commercial</option>
                            <option value="4"> Poor C</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="exampleInputIdNumber">Narration (<em>optional</em>) </label>
                        <input type="text" class="form-control params" id="narration" name="narration" value="">
                    </div>
                    <input type="hidden" id="item_id" name="item_id" value="">
                    <div class="modal-footer">
                        <div class="form-group">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-warning btn-lg btn-prevent-multiple-submits">
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

        $('.form-prevent-multiple-submits').on('submit', function () {
            $(".btn-prevent-multiple-submits").attr('disabled', true);
        });

        $("#execute-grading-btn").click(function (e) {
            e.preventDefault();
            runGradingClasses();
        });

        $('.params').on("change", function(a){
            a.preventDefault();
            $('.btn-prevent-multiple-submits').prop('disabled', false);
        })

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

            $('#fat_group').select2('destroy').select2();

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

    const setUserMessage = (field_succ, field_err, message_succ, message_err) => {
        document.getElementById(field_succ).innerHTML = message_succ
        document.getElementById(field_err).innerHTML = message_err
    }

    const runGradingClasses = () => {
        setUserMessage('succ', 'err', 'initiated classifications..', '')
        $.ajax({
            url: "{{ url('QA/run/grading-classes') }}",
            type: 'POST',
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                // Redirect on success
                console.log(response)
                setUserMessage('succ', 'err', 'run grading successfully.reloading...', '')
                setTimeout(function () {
                    // Reload current route after delay
                    window.location.reload();
                }, 1000); // 1 seconds delay
            },
            error: function (data) {
                var errors = data.responseJSON;
                console.log(errors);
                alert('error occured when sending request');
            }
        });
    }

</script>
@endsection
