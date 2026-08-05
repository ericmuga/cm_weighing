@extends('layouts.QA_master')

@section('content')
<div class="container-fluid">
    <div class="card">
            <div class="card-header row align-items-center">
                <div class="col-lg-8">
                    <h1 class="card-title">Grading Work Sheet V2 | <span id="subtext-h1-title"><small> showing
                                <strong>Today's</strong>
                                entries</small> | <button class="btn btn-success" id="execute-grading-btn">Generate
                                Classifications</button></span></h1>
                </div>
                <div class="col-lg-4 text-right">
                    <button type="button" class="btn btn-outline-success btn-sm mr-1" id="btn-qa-report">
                        <i class="fas fa-file-excel"></i> QA Report
                    </button>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="btn-slaughter-report">
                        <i class="fas fa-chart-bar"></i> Summary Report
                    </button>
                    <br>
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
                                <th>Sno</th>
                                <th>Agg No </th>
                                <th>Receipt No.</th>
                                <th>Item Code</th>
                                <th>Item Name</th>
                                <th>Vendor No</th>
                                <th>Settlement</th>
                                <th>Weight Classification</th>
                                <th>Grading Status</th>
                                <th>QA Classification</th>
                                <th>Downgraded?</th>
                                <th>Slaughter Date</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>Sno</th>
                                <th>Agg No </th>
                                <th>Receipt No.</th>
                                <th>Item Code</th>
                                <th>Item Name</th>
                                <th>Vendor No</th>
                                <th>Settlement</th>
                                <th>Weight Classification</th>
                                <th>Grading Status</th>
                                <th>QA Classification</th>
                                <th>Downgraded?</th>
                                <th>Slaughter Date</th>
                            </tr>
                        </tfoot>
                        <tbody>
                            @foreach($grading_data as $data)
                                <tr>
                                    <td>{{ $data->id }}</td>
                                    <td>{{ $data->slaughter_agg_no ?? '--' }}</td>
                                    <td>{{ $data->receipt_no }}</td>
                                    <td>{{ $data->item_code }}</td>
                                    <td>{{ $data->description }}</td>
                                    <td>{{ $data->vendor_no }}</td>
                                    <td>{{ number_format($data->settlement_weight, 2) }}</td>                                  

                                    <td>{{ $data->classification_code }}</td>

                                    @php
    $tdAttrs = 'class="gradingShow"'
        .' data-agg_no="'.($data->slaughter_agg_no ?? '').'"'
        .' data-item_code="'.$data->item_code.'"'
        .' data-id="'.$data->id.'"'
        .' data-settlement_weight="'.$data->settlement_weight.'"'
        .' data-item_name="'.$data->description.'"'
        .' data-vendor="'.$data->vendor_no.'"'
        .' data-classification="'.($data->classification ?? '').'"'
        .' data-dentition="'.($data->dentition ?? '').'"'
        .' data-fat_cover="'.($data->fat_cover ?? '').'"'
        .' data-fat_color="'.($data->fat_color ?? '').'"'
        .' data-meat_color="'.($data->meat_color ?? '').'"'
        .' data-bruising="'.($data->bruising ?? '').'"'
        .' data-muscle="'.($data->muscle_conformation ?? '').'"'
        .' data-narration="'.e($data->narration ?? '').'"';
@endphp

                                    @if($data->classification == null && !$data->settlement_weight)
                                        <td {!! $tdAttrs !!}><a href="#" class="text-warning">
                                            <i class="fas fa-exclamation-circle"></i> QA &amp; Wght pending <i class="fas fa-arrow-right"></i></a>
                                        </td>
                                    @elseif($data->classification == null)
                                        <td {!! $tdAttrs !!}><a href="#" class="text-info">
                                            <i class="fas fa-clipboard-check"></i> QA pending <i class="fas fa-arrow-right"></i></a>
                                        </td>
                                    @else
                                        <td {!! $tdAttrs !!}><a href="#" class="text-success">
                                            <i class="fas fa-check-circle"></i> graded <i class="fas fa-arrow-right"></i></a>
                                        </td>
                                    @endif

                                    @if($data->classification == 1)
                                        <td>Premium</td>
                                    @elseif($data->classification == 2)
                                        <td>High Grade</td>
                                    @elseif($data->classification == 3)
                                        <td>Commercial</td>
                                    @elseif($data->classification == 4)
                                        <td>Poor C</td>
                                    @elseif($data->classification == 5)
                                        <td>1st grade</td>
                                    @elseif($data->classification == 6)
                                        <td>2nd grade</td>
                                    @elseif($data->classification == 7)
                                        <td>Class R</td>
                                    @else
                                        <td>---</td>
                                    @endif

                                    @if($data->is_downgraded === null)
                                        <td class="downgraded-cell"><span class="badge badge-secondary">--</span></td>
                                    @elseif($data->is_downgraded == 0)
                                        <td class="downgraded-cell"><span class="badge badge-success">No</span></td>
                                    @else
                                        <td class="downgraded-cell"><span class="badge badge-danger">Yes</span></td>
                                    @endif

                                    <td>{{ $helpers->shortDateTime($data->updated_at) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- /.card-body -->
        <!-- /.card -->
    </div>
    <!-- /.col -->
</div>

<div id="gradingShow" class="modal fade" role="dialog">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form id="form-grading-slaughter" class="form-prevent-multiple-submits"
                action="{{ route('qa_update_grading_v2') }}" method="post">
                @csrf
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
                            <select class="form-control select2 params" name="dentition" id="dentition">
                                <option value="" selected> Select an option </option>
                                <option value="1">Full mouth </option>
                                <option value="2">3 pairs </option>
                                <option value="3">2 pairs </option>
                                <option value="4">1 pair </option>
                                <option value="5">Milk Teeth </option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="email" class="col-form-label">Fat Cover</label>
                            <select class="form-control select2 params" name="fat_cover" id="fat_cover">
                                <option disabled selected> select an option </option>
                                <option value="1">Good fat cover (to be 3 to 10mm (or more), evenly and well distributed </option>
                                <option value="2">Fair fat cover(2-7mm) </option>
                                <option value="3">Minimum/inadequate fat cover </option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="email" class="col-form-label">Fat Color</label>
                            <select class="form-control select2 params" name="fat_color" id="fat_color">
                                <option disabled selected> select an option </option>
                                <option value="1">Creamish white fat </option>
                                <option value="2">Deep yellow fat </option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="email" class="col-form-label">Meat Color</label>
                            <select class="form-control select2 params" name="meat_color" id="meat_color">
                                <option disabled selected> select an option </option>
                                <option value="1">Bright red colour </option>
                                <option value="2">Dark meat colour </option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="email" class="col-form-label">Bruising</label>
                            <select class="form-control select2 params" name="bruising" id="bruising">
                                <option disabled selected> select an option </option>
                                <option value="0">No Bruises </option>
                                <option value="1">Mild Bruises </option>
                                <option value="2">Extensive bruises </option>
                                <option value="3">Severely bruised </option>
                                <option value="4">Cysts Bovis infestation </option>
                                <option value="5">Other strange discolouration </option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="email" class="col-form-label">Muscles</label>
                            <select class="form-control select2 params" name="muscle" id="muscle">
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
                            {{-- @if ()
                                <option disabled selected> select an option </option>
                                <option value="1"> Premium >170kg</option>
                                <option value="2" selected="selected"> High Grade >170kg</option>
                                <option value="5"> FAQ >150kg </option>
                                <option value="6"> Standard >120Kg </option>
                                <option value="7"> Standard below 120kg </option>
                                <option value="3"> Commercial</option>
                                <option value="4"> Poor Commercial</option>
                            @else
                               <option value="5"> Lamb 1st grade</option>                             
                               <option value="6"> Lamb 2nd grade</option>                             
                               <option value="7"> Lamb Class R</option>                             
                            @endif --}}
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="exampleInputIdNumber">Narration (<em>optional</em>) </label>
                        <input type="text" class="form-control params" id="narration" name="narration" value="">
                    </div>
                    <input type="hidden" id="item_id" name="item_id" value="">
                </div>{{-- /modal-body --}}
                <div class="modal-footer">
                    <div class="form-group">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-warning btn-lg btn-prevent-multiple-submits">
                            <i class="fa fa-paper-plane" aria-hidden="true"></i> Update
                        </button>
                    </div>
                </div>
            </form>
        </div>{{-- /modal-content --}}
    </div>
</div>

<!-- QA Grading Report Modal -->
<div class="modal fade" id="qaGradingReportModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('qa_grading_report_export') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-file-excel text-success"></i> QA Grading Report</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">Per-carcass grading details: vendor, carcass no, QA parameters, grade and narration.</p>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label>From Date</label>
                            <input type="date" class="form-control" name="from_date" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>To Date</label>
                            <input type="date" class="form-control" name="to_date" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-download"></i> Export Excel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Slaughter Grading Summary Report Modal -->
<div class="modal fade" id="slaughterGradingReportModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('slaughter_grading_report_export') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-chart-bar text-primary"></i> Slaughter Grading Summary</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">Summary per vendor/receipt: qty supplied, total CDW, grading breakdown and downgrade count.</p>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label>From Date</label>
                            <input type="date" class="form-control" name="from_date" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>To Date</label>
                            <input type="date" class="form-control" name="to_date" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-download"></i> Export Excel</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    $(document).ready(function () {

        // Report buttons — explicit JS to avoid Bootstrap data-api conflicts
        $('#btn-qa-report').on('click', function () {
            $('#qaGradingReportModal').modal('show');
        });
        $('#btn-slaughter-report').on('click', function () {
            $('#slaughterGradingReportModal').modal('show');
        });

        // Auto-open report modal when navigating from the header dropdown
        var exportParam = new URLSearchParams(window.location.search).get('export');
        if (exportParam === 'qa')        $('#qaGradingReportModal').modal('show');
        if (exportParam === 'slaughter') $('#slaughterGradingReportModal').modal('show');

        var currentGradingRow = null;

        $("#execute-grading-btn").click(function (e) {
            e.preventDefault();
            runGradingClasses();
        });

        $('.params').on("change", function () {
            $('.btn-prevent-multiple-submits').prop('disabled', false);
        });

        $("body").on("click", ".gradingShow", function (a) {
            a.preventDefault();

            currentGradingRow = $(this).closest('tr');

            var id             = $(this).data('id');
            var agg_no         = $(this).data('agg_no');
            var item_code      = $(this).data('item_code');
            var item_name      = $(this).data('item_name');
            var vendor         = $(this).data('vendor');
            var settlement     = $(this).data('settlement_weight');
            // Saved QA values for pre-population
            var savedClass     = $(this).data('classification');
            var savedDentition = $(this).data('dentition');
            var savedFatCover  = $(this).data('fat_cover');
            var savedFatColor  = $(this).data('fat_color');
            var savedMeatColor = $(this).data('meat_color');
            var savedBruising  = $(this).data('bruising');
            var savedMuscle    = $(this).data('muscle');
            var savedNarration = $(this).data('narration');

            $('#item_id').val(id);
            $('#agg_no').val(agg_no);
            $('#item_code').val(item_code);
            $('#item_name').val(item_name);
            $('#vendor_no').val(vendor);
            $('#settlement_weight').val((Math.round(settlement * 100) / 100).toFixed(2));
            $('#narration').val(savedNarration || '');

            // Build fat_group options based on animal type
            $('#fat_group').empty().append('<option disabled selected> select an option </option>');
            if (item_code === 'BG1021') {
                $('#fat_group').append('<option value="1">Premium</option>');
                $('#fat_group').append('<option value="2">High Grade</option>');
                $('#fat_group').append('<option value="3">Commercial</option>');
                $('#fat_group').append('<option value="4">Poor C</option>');
                $('#fat_group').append('<option value="5">FAQ >150kg</option>');
                $('#fat_group').append('<option value="5">Standard >120kg</option>');
                $('#fat_group').append('<option value="5">Standard <120kg</option>');
            } else {
                $('#fat_group').append('<option value="5">Lamb 1st grade</option>');
                $('#fat_group').append('<option value="6">Lamb 2nd grade</option>');
                $('#fat_group').append('<option value="7">Lamb Class R</option>');
            }

            // Re-init select2 on fat_group (dynamic options), then pre-select saved value
            $('#fat_group').select2('destroy').select2();
            if (savedClass !== undefined && savedClass !== '') {
                $('#fat_group').val(savedClass).trigger('change');
            }

            // Pre-populate remaining selects with saved values ('' resets to placeholder)
            // Use !== undefined check so 0 (No Bruises) is correctly restored
            var setSelect = function(id, val) {
                $(id).val((val !== undefined && val !== '') ? val : '').trigger('change');
            };
            setSelect('#dentition',  savedDentition);
            setSelect('#fat_cover',  savedFatCover);
            setSelect('#fat_color',  savedFatColor);
            setSelect('#meat_color', savedMeatColor);
            setSelect('#bruising',   savedBruising);
            setSelect('#muscle',     savedMuscle);

            $('#gradingShow').modal('show');
        });

        $('#form-grading-slaughter').validate({
            rules: {
                fat_group: { required: true },
                narration:  { maxlength: 50 },
            },
            messages: {
                fat_group: "Please select a classification first",
                narration: { maxlength: "Narration must be at most 50 characters" },
            },
            errorElement: 'span',
            errorPlacement: function (error, element) {
                error.addClass('invalid-feedback');
                element.closest('.form-group').append(error);
            },
            highlight: function (element) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function (element) {
                $(element).removeClass('is-invalid');
            },
            submitHandler: function (form) {
                var $btn = $('.btn-prevent-multiple-submits');
                var originalBtnHtml = $btn.html();
                $btn.prop('disabled', true)
                    .html('<span class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span> Saving...');

                var savedRowId    = $('#item_id').val();
                var savedClassText = $('#fat_group option:selected').text().trim();

                $.ajax({
                    url: $(form).attr('action'),
                    type: 'POST',
                    data: $(form).serialize(),
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    success: function (response) {
                        // Find the td by data-id (works regardless of DataTable page/order)
                        var $gradingTd = $('td.gradingShow[data-id="' + savedRowId + '"]');
                        $gradingTd.find('a')
                            .attr('class', 'text-success')
                            .html('<i class="fas fa-check-circle"></i> graded <i class="fas fa-arrow-right"></i>');
                        $gradingTd.next('td').text(savedClassText);

                        // Update is_downgraded badge
                        var $downgradedTd = $gradingTd.closest('tr').find('.downgraded-cell');
                        if (response.is_downgraded === null || response.is_downgraded === undefined) {
                            $downgradedTd.html('<span class="badge badge-secondary">--</span>');
                        } else if (response.is_downgraded === 0) {
                            $downgradedTd.html('<span class="badge badge-success">No</span>');
                        } else {
                            $downgradedTd.html('<span class="badge badge-danger">Yes</span>');
                        }

                        $('#gradingShow').modal('hide');
                        $btn.prop('disabled', false).html(originalBtnHtml);
                        toastr.success(response.message, 'Saved');
                        setUserMessage('succ', 'err', response.message, '');
                        setTimeout(function () { setUserMessage('succ', 'err', '', ''); }, 3000);
                    },
                    error: function (xhr) {
                        var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'An error occurred';
                        toastr.error(msg, 'Error');
                        setUserMessage('succ', 'err', '', msg);
                        $btn.prop('disabled', false).html(originalBtnHtml);
                    }
                });
            }
        });
    });

    const setUserMessage = (field_succ, field_err, message_succ, message_err) => {
        document.getElementById(field_succ).innerHTML = message_succ;
        document.getElementById(field_err).innerHTML = message_err;
    };

    const runGradingClasses = () => {
        setUserMessage('succ', 'err', 'Generating classifications...', '');
        $.ajax({
            url: "{{ url('QA/run/grading-classes') }}",
            type: 'POST',
            dataType: 'json',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (response) {
                setUserMessage('succ', 'err', 'Classifications generated. Reloading...', '');
                setTimeout(function () { window.location.reload(); }, 1000);
            },
            error: function (data) {
                console.log(data.responseJSON);
                setUserMessage('succ', 'err', '', 'Error generating classifications');
            }
        });
    };

</script>
@endsection
