@extends('layouts.slaughter_master')

@section('content')

<!-- weigh -->
<form id="form_slaughter_weigh" action="{{ route('save_weigh') }}" method="post">
    @csrf
    <div class="card-group">
        <div class="card ">
            <div class="card-body text-center">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <button type="button" onclick="getScaleReading()" class="btn btn-success btn-lg"><i
                                    class="fas fa-balance-scale"></i> Weigh</button>
                        </div>
                        <div class="col-md-6">
                            <small><label>Reading from ComPort:</label><strong><input type="text"
                                        style="text-align: center; border:none" id="comport_value"
                                        value="{{ $configs[0]->comport?? "" }}" disabled></strong></small>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="exampleInputEmail1">Scale Reading</label>
                    <input type="number" style="text-align: center" step="0.01" class="form-control" id="reading"
                        name="reading" value="0.00" placeholder="" onclick="select()" readonly required>
                </div>
                @if (config('app.show_manual_weight') == 1)
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="manual_weight">
                    <label class="form-check-label" for="manual_weight">Enter Manual weight</label>
                </div> 
                @endif                
                <br>
                <div class="row form-group">
                    <div class="col-md-8">
                        <label for="exampleInputPassword1">Tare-Weight</label>
                        <select class="form-control select2" name="tare_weight" id="tare_weight" required>
                            @if (old('tare_weight') == '1.5' || old('tare_weight') == null )
                            <option value="1.5" selected> 1.5</option>
                            <option value="1.8"> 1.8</option>
                            <option value="1.9"> 1.9</option>
                            <option value="2.0"> 2.0</option>
                            <option value="2.2"> 2.2</option>

                            @elseif (old('tare_weight') == '1.8')
                            <option value="1.5"> 1.5</option>
                            <option value="1.8" selected> 1.8</option>
                            <option value="1.9"> 1.9</option>
                            <option value="2.0"> 2.0</option>
                            <option value="2.2"> 2.2</option>

                            @elseif(old('tare_weight') == '1.9')
                            <option value="1.5"> 1.5</option>
                            <option value="1.8"> 1.8</option>
                            <option value="1.9" selected> 1.9</option>
                            <option value="2.0"> 2.0</option>
                            <option value="2.2"> 2.2</option>

                            @elseif(old('tare_weight') == '2.0')
                            <option value="1.5"> 1.5</option>
                            <option value="1.8"> 1.8</option>
                            <option value="1.9"> 1.9</option>
                            <option value="2.0" selected> 2.0</option>
                            <option value="2.2"> 2.2</option>

                            @elseif(old('tare_weight') == '2.2')
                            <option value="1.5"> 1.5</option>
                            <option value="1.8"> 1.8</option>
                            <option value="1.9"> 1.9</option>
                            <option value="2.0"> 2.0</option>
                            <option value="2.2" selected> 2.2</option>
                            @endif
                        </select>
                    </div>
                    <div class="col-md-4" style="padding-top: 7.5%">
                        <button class="btn btn-secondary btn-sm form-control" onclick="getReset()" type="button">
                            <strong>Reset</strong>
                        </button>
                    </div>
                </div>
                <div class="row text-center beef">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="exampleInputPassword1">Side A</label>
                            <input type="number" style="text-align: center" class="form-control" id="side_A"
                                name="side_A" step="0.01" value="0.00" readonly required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="exampleInputPassword1">Side B</label>
                            <input type="number" style="text-align: center" class="form-control" id="side_B"
                                name="side_B" step="0.01" value="0.00" readonly required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="exampleInputPassword1">Total </label>
                            <input type="number" style="text-align: center" class="form-control" id="total_weight"
                                name="total_weight" step="0.01" value="0.00" readonly required>
                        </div>
                    </div>
                </div>
                <div class="row text-center goats" style="text-align: center; display:inline-block;">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="exampleInputPassword1">Total </label>
                            <input type="number" style="text-align: center" class="form-control" id="total_weight2"
                                name="total_weight2" step="0.01" value="0.00" readonly required>
                        </div>
                    </div>
                </div>
                <input type="hidden" class="form-control " id="settlement_weight" name="settlement_weight" value="0.00">
            </div>
        </div>
        <div class="card ">
            <div class="card-body text-center">
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-12" style="text-align: center">
                            <label for="exampleInputPassword1">Receipt No.</label>
                            <select class="form-control select2" name="receipt_no" id="receipt_no" required>
                                @foreach($receipts as $receipt)

                                @if (old('receipt_no') == $receipt->receipt_no)
                                <option value="{{ $receipt->receipt_no }}" selected>
                                    {{ ucwords($receipt->receipt_no.'-'.$receipt->vendor_name) }}
                                </option>

                                @else
                                <option value="{{ $receipt->receipt_no }}">
                                    {{ ucwords($receipt->receipt_no.'-'.$receipt->vendor_name) }}</option>
                                @endif

                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="exampleInputPassword1">Item Code</label>
                    <input type="text" style="text-align: center" class="form-control" value="" name="item_code"
                        id="item_code" placeholder="" readonly required>
                </div>
                <div class="form-group">
                    <label for="exampleInputPassword1">Item Description</label>
                    <input type="text" style="text-align: center" class="form-control" value="" name="item_desc"
                        id="item_desc" placeholder="" readonly required>
                </div>
                <div class="form-group">
                    <label for="exampleInputPassword1">Vendor Number</label>
                    <input type="text" style="text-align: center" class="form-control" value="" name="vendor_no"
                        id="vendor_no" placeholder="" readonly required>
                </div>
                <div class="form-group">
                    <label for="exampleInputPassword1">Vendor Name</label>
                    <input type="text" style="text-align: center" class="form-control" name="vendor_name"
                        id="vendor_name" placeholder="" readonly required>
                </div>
            </div>
        </div>
        <div class="card text-center" style="padding-top: ">
            <div class="card-body">
                <div class="row form-group">
                    <div class="text-center" style="width: 50%; margin: 0 auto;">
                        <label for="exampleInputPassword1">Agg No. </label>
                        <input type="number" style="text-align: center" class="form-control" value="" name="agg_no"
                            id="agg_no" placeholder="" readonly required>
                    </div>
                    {{-- <div class="col-md-6" style="padding-top: 10%">
                        <input type="checkbox" class="form-check-input" id="manual_agg">
                        <label class="form-check-label" for="manual_agg">Enter Manual Agg No</label>
                    </div> --}}
                </div>
                <div class="row form-group">
                    <div class="text-center" style="width: 70%; margin: 0 auto;">
                        <label for="exampleInputPassword1">Total Received From Vendor </label>
                        <input type="number" style="text-align: center" class="form-control" value="" name=""
                            id="total_received" placeholder="" readonly required>
                    </div>
                </div>
                <div class="row form-group">
                    <div class="col-md-6">
                        <label for="exampleInputPassword1">Total weighed </label>
                        <input type="number" style="text-align: center" class="form-control" value="" name=""
                            id="total_weighed" placeholder="" readonly required>
                    </div>
                    <div class="col-md-6">
                        <label for="exampleInputPassword1">Total remaining </label>
                        <input type="number" style="text-align: center" class="form-control" value="" name=""
                            id="total_remaining" placeholder="" readonly required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="exampleInputPassword1">Classification Code </label>
                    <input type="text" style="text-align: center" class="form-control" name="classification_code"
                        id="classification_code" placeholder="" readonly required>
                </div>
                <div class="form-group" style="padding-top: 5%">
                    <button id="submit_form" type="submit" onclick="return validateOnSubmit()"
                        class="btn btn-success btn-lg "><i class="fa fa-paper-plane" aria-hidden="true"></i>
                        Save</button>
                </div>
            </div>
        </div>
    </div>
</form>
<!--End weigh -->
<hr>

<div class="div">
    <button class="btn btn-success " data-toggle="collapse" data-target="#slaughter_entries"><i class="fa fa-plus"></i>
        Entries
    </button>
</div>
<hr>

<div id="slaughter_entries" class="row collapse">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Slaughter Data Report| <span id="subtext-h1-title"><small> showing all
                            entries</small> </span></h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <div class="hidden" hidden>{{ $i = 1 }}</div>
                <div class="table-responsive">
                    <table id="example1" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Agg No </th>
                                <th>Receipt No.</th>
                                <th>Code</th>
                                <th>Description</th>
                                <th>Vendor No.</th>
                                <th>Vendor Name</th>
                                <th>Side A</th>
                                <th>Side B</th>
                                <th>Total Weight</th>
                                <th>Tareweight</th>
                                <th>Total Net</th>
                                <th>Settlement</th>
                                <th>Class Code</th>
                                <th>Slaughter Date</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Agg No </th>
                                <th>Receipt No.</th>
                                <th>Code</th>
                                <th>Description</th>
                                <th>Vendor No.</th>
                                <th>Vendor Name</th>
                                <th>Side A</th>
                                <th>Side B</th>
                                <th>Total Weight</th>
                                <th>Tareweight</th>
                                <th>Total Net</th>
                                <th>Settlement</th>
                                <th>Class Code</th>
                                <th>Slaughter Date</th>
                            </tr>
                        </tfoot>
                        <tbody>
                            @foreach($slaughter_data as $data)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $data->agg_no }}</td>
                                <td id="editModalShow" data-id="{{$data->id}}" data-receipt="{{ $data->receipt_no }}"
                                    data-weight1="{{ number_format($data->sideA_weight, 2) }}"
                                    data-weight2="{{ number_format($data->sideB_weight, 2) }}"
                                    data-total="{{number_format($data->total_weight, 2) }}"
                                    data-tare_weight="{{ $data->tare_weight }}" data-item_code="{{ $data->item_code }}" data-item_desc="{{ $data->description }}" data-vendor_no="{{ $data->vendor_no }}" data-net="{{ $data->total_net }}"  data-settlement="{{ number_format($data->settlement_weight, 2) }}"  data-class_code="{{ $data->classification_code }}"  data-vendor_name="{{ $data->vendor_name }}"
                                    data-item_name="{{ $data->vendor_name }}"><a href="#">{{ $data->receipt_no }}</a></td>
                                <td>{{ $data->item_code }}</td>
                                <td>{{ $data->description }}</td>
                                <td>{{ $data->vendor_no }}</td>
                                <td>{{ $data->vendor_name }}</td>
                                <td>{{ number_format($data->sideA_weight, 2) }}</td>
                                <td>{{ number_format($data->sideB_weight, 2) }}</td>
                                <td>{{ number_format($data->total_weight, 2) }}</td>
                                <td>{{ number_format($data->tare_weight, 2) }}</td>
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
<!--End weigh Table-->


<!-- Edit scale Modal -->
<div id="editModal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
        <!--Start create user modal-->
        <form id="form-edit-slaughter" action="{{route('slaughter_edit')}}" method="post">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Edit Slaughter Entry: <strong><input
                                style="border:none" type="text" id="edit_item_name" name="edit_item_name" value=""
                                readonly></strong></h5>
                                <strong><input
                                style="border:none" type="text" id="edit_receipt" value=""
                                readonly></strong></h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row text-center">
                        <div class="col-md-6 form-group">
                            <label for="exampleInputPassword1">Item Code</label>
                            <input type="text" style="text-align: center" class="form-control" value=""
                                id="edit_item_code" placeholder="" readonly required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="exampleInputPassword1">Item Description</label>
                            <input type="text" style="text-align: center" class="form-control" value=""
                                id="edit_item_desc" placeholder="" readonly required>
                        </div>
                    </div>
                    <div class="row text-center">
                        <div class="col-md-6 form-group">
                            <label for="exampleInputPassword1">Vendor Number</label>
                            <input type="text" style="text-align: center" class="form-control" value=""
                                id="edit_vendor_no" placeholder="" readonly required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="exampleInputPassword1">Vendor Name</label>
                            <input type="text" style="text-align: center" class="form-control"
                                id="edit_vendor_name" placeholder="" readonly required>
                        </div>
                    </div>
                    <div class="row text-center">
                        <div class="col-md-3">
                            <label for="exampleInputPassword1" class="col-form-label">Tare-Weight</label>
                            <select class="form-control" name="edit_tareweight" id="edit_tareweight">
                                <option value="1.5">1.5</option>
                                <option value="1.8">1.8</option>
                                <option value="1.9">1.9</option>
                                <option value="2.2">2.2</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="exampleInputPassword1" class="col-form-label">Side A</label>
                                <input type="number" style="text-align: center" class="form-control" id="edit_A"
                                    name="edit_A" step="0.01" value="0.00" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="exampleInputPassword1" class="col-form-label">Side B</label>
                                <input type="number" style="text-align: center" class="form-control" id="edit_B"
                                    name="edit_B" step="0.01" value="0.00" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="exampleInputPassword1" class="col-form-label">Total </label>
                                <input type="number" style="text-align: center" class="form-control" id="edit_total"
                                    name="edit_total" step="0.01" value="" required>
                            </div>
                        </div>
                    </div>
                    <div class="row form-group">
                        <div class="col-md-3">
                            <label for="exampleInputPassword1" class="col-form-label">Net</label>
                            <input type="number" style="text-align: center" class="form-control" id="edit_net"
                                name="edit_net" step="0.01" value="0.00" readonly required>
                        </div>
                        <div class="col-md-4">
                            <label for="exampleInputPassword1" class="col-form-label">Settlement Weight</label>
                            <input type="number" style="text-align: center" class="form-control" id="edit_settlement"
                                name="edit_settlement" step="0.01" value="0.00" readonly required>
                        </div>
                        <div class="col-md-5">
                            <label for="exampleInputPassword1" class="col-form-label">Class Code</label>
                            <input type="text" style="text-align: center" class="form-control" id="edit_classification_code"
                                name="edit_classification_code" value="" readonly required>
                        </div>
                    </div>
                    <input type="hidden" name="item_id" id="item_id" value="">
                </div>
                <div class="modal-footer">
                    <div class="form-group">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button class="btn btn-warning" onclick="return validateOnSubmitEdit()">
                            <i class="fa fa-save"></i> Update
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<!--End Edit scale1 modal-->

@endsection

@section('scripts')
<script>
    $(document).ready(function () {
        listenForEnterKeyPress();

        hideShowDivs();

        loadWeighData();

        $("#reading").change(function (e) {
            e.preventDefault();
            getReadingRouter();
        });

        $("#tare_weight").change(function (e) {
            e.preventDefault();
            getSettlementWeight();
        });

        $("#receipt_no").change(function (e) {
            e.preventDefault();
            loadWeighData();
            getClassificationCode($("#settlement_weight").val());
        });

        $("#edit_tareweight").change(function (e) {
            calculateNetEdit();
        });

        $("#edit_A").change(function (e) {
            computeTotalWeightEdit();
        });

        $("#edit_B").change(function (e) {
            computeTotalWeightEdit();
        });

        $("#edit_total").change(function (e) {
            e.preventDefault();
            calculateNetEdit();            
        });

        $('#manual_weight').change(function () {
            var manual_weight = document.getElementById('manual_weight');
            var reading = document.getElementById('reading');
            if (manual_weight.checked == true) {
                reading.readOnly = false;
                reading.focus();
                $('#reading').val("");

            } else {
                reading.readOnly = true;

            }

        });

        $('#manual_agg').change(function () {
            var manual_agg = document.getElementById('manual_agg');
            var agg_no = document.getElementById('agg_no');
            if (manual_agg.checked == true) {
                agg_no.readOnly = false;
                agg_no.focus();
                $('#agg_no').val("");

            } else {
                agg_no.readOnly = true;

            }

        });

        // edit scale 1
        $("body").on("click", "#editModalShow", function (a) {
            a.preventDefault();

            var id = $(this).data('id');
            var receipt = $(this).data('receipt');
            var side_A = $(this).data('weight1');
            var side_B = $(this).data('weight2');
            var item_code = $(this).data('item_code');
            var item_desc = $(this).data('item_desc');
            var vendor_no = $(this).data('vendor_no');
            var vendor_name = $(this).data('vendor_name');
            var net = $(this).data('net');
            var settlement = $(this).data('settlement');
            var class_code = $(this).data('class_code');
            var total = $(this).data('total');
            var tare_weight = $(this).data('tare_weight');
            var item_name = $(this).data('item_name');

            $('#item_id').val(id);
            $('#edit_receipt').val(receipt);
            $('#edit_item_code').val(item_code);
            $('#edit_item_desc').val(item_desc);
            $('#edit_vendor_no').val(vendor_no);
            $('#edit_vendor_name').val(vendor_name);
            $('#edit_net').val(net);
            $('#edit_settlement').val(settlement);
            $('#edit_classification_code').val(class_code);
            $('#edit_vendor_name').val(vendor_name);
            $('#edit_A').val(side_A);
            $('#edit_B').val(side_B);
            $('#edit_total').val(total);
            $('#edit_tareweight').val(tare_weight);
            $('#edit_item_name').val(item_name);

            $('#editModal').modal('show');
        });

    });

    function listenForEnterKeyPress() {
        document.onkeypress = function (e) {
            if (e.key === 'Enter' || e.keyCode === 13) {
                e.preventDefault();
                document.getElementById("submit_form").click();
            }
        }
    }

    function hideShowDivs() {
        if ($('#item_code').val() == 'BG1101' || $('#item_code').val() == 'BG1201') {
            $('.beef').hide();
            $('.goats').show();

        } else {
            $('.beef').show();
            $('.goats').hide();
        }
    }

    function getScaleReading() {
        var comport = $('#comport_value').val();

        if (comport != null) {
            $.ajax({
                type: "GET",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]')
                        .attr('content')
                },
                url: "{{ url('slaughter/read-scale') }}",

                data: {
                    'comport': comport,

                },
                dataType: 'JSON',
                success: function (data) {

                    var obj = JSON.parse(data);

                    if (obj.success == true) {
                        var reading = document.getElementById('reading');
                        reading.value = obj.response;

                        getReadingRouter();

                    } else if (obj.success == false) {
                        alert('error occured in response: ' + obj.response);

                    } else {
                        alert('No response from service');

                    }

                },
                error: function (data) {
                    var errors = data.responseJSON;
                    console.log(errors);
                    alert('error occured when sending request');
                }
            });

        } else {
            alert("Please set comport value first");
        }
    }

    function loadWeighData() {
        /* Start weigh data ajax */
        var receipt = $('#receipt_no').val();
        if (receipt != null) {
            $.ajax({
                type: "GET",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{ url('slaughter/weigh-data-ajax') }}",
                data: {
                    'receipt': receipt,

                },
                dataType: 'JSON',
                success: function (res) {
                    if (res) {
                        var str = JSON.stringify(res);
                        var obj = JSON.parse(str);

                        $('#agg_no').val(obj.agg_count + 1);
                        $('#item_code').val(obj.vendor[0].item_code);
                        $('#item_desc').val(obj.vendor[0].description);
                        $('#vendor_no').val(obj.vendor[0].vendor_no);
                        $('#vendor_name').val(obj.vendor[0].vendor_name);
                        $('#total_received').val(obj.vendor[0].total_received);
                        $('#total_weighed').val(obj.total_weighed);
                        $('#total_remaining').val(obj.vendor[0].total_received - obj.total_weighed);
                    }

                    // show/hide total weights divs
                    hideShowDivs();
                }
            });

        }
        /* End weigh data ajax */
    }

    function loadWeighDataEdit() {
        /* Start weigh data ajax */
        var receipt = $('#edit_receipt').val();
        if (receipt != null) {
            $.ajax({
                type: "GET",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{ url('slaughter/weigh-data-ajax') }}",
                data: {
                    'receipt': receipt,

                },
                dataType: 'JSON',
                success: function (res) {
                    if (res) {
                        var str = JSON.stringify(res);
                        var obj = JSON.parse(str);

                        $('#edit_item_code').val(obj.vendor[0].item_code);
                        $('#edit_item_desc').val(obj.vendor[0].description);
                        $('#edit_vendor_no').val(obj.vendor[0].vendor_no);
                        $('#edit_vendor_name').val(obj.vendor[0].vendor_name);
                    }

                }
            });

        }
        /* End weigh data ajax */
    }

    function validateOnSubmit() {
        if ($('#item_code').val() == 'BG1101' || $('#item_code').val() == 'BG1201') {
            // lamb or goat
            var net = $('#total_weight2').val();

            if (!(net >= 5 && net <= 70)) {
                alert("Please ensure you have valid weight of between 5-70 kgs.");
                return false;
            }

        } else {
            var side_A = $('#side_A').val();
            var side_B = $('#side_B').val();

            if (!(side_A >= 5 && side_A <= 250) || !(side_B >= 5 && side_B <= 250)) {
                alert("Please ensure you have valid weight of between 5-250 kgs in both sides.");
                return false;
            }
        }

        var total_remaining = $('#total_remaining').val();

        if (total_remaining < 1) {
            alert("You have exhausted vendor received Qty.");
            return false;
        }
    }

    function validateOnSubmitEdit() {     
        var net = $('#edit_net').val();

        if (net < 5) {
            alert("Please ensure you have valid weight of min 5kgs.");
            return false;
        }

    }

    function getReset() {
        $("#side_A").val('0.00');
        $('#side_B').val('0.00');
        $('#total_weight').val('0.00');
        $('#total_weight2').val('0.00');
        $('#settlement_weight').val('0.00');
    }

    function getReadingRouter() {
        var reading = $('#reading').val();

        if ($('#item_code').val() == 'BG1101' || $('#item_code').val() == 'BG1201') {
            $('#total_weight2').val(reading);

        } else {
            var side_a = $('#side_A').val();
            var side_b = $('#side_B').val();

            if (side_a > 0) {
                $('#side_B').val(reading);

            } else {
                $('#side_A').val(reading);
            }
        }

        computeTotalWeight();
    }

    function computeTotalWeight() {
        if ($('#item_code').val() == 'BG1101' || $('#item_code').val() == 'BG1201') {
            var reading = $('#reading').val();
            $('#total_weight2').val((Math.round(reading * 100) / 100).toFixed(2));

        } else {
            var sideA = $('#side_A').val();
            var sideB = $('#side_B').val();
            var add = parseFloat(sideA) + parseFloat(sideB);
            var total = (Math.round(add * 100) / 100).toFixed(2);
            $('#total_weight').val(total);
        }

        getSettlementWeight();
    }

    function computeTotalWeightEdit() {
        var sideA = $('#edit_A').val();
        var sideB = $('#edit_B').val();
        var add = parseFloat(sideA) + parseFloat(sideB);
        var total = (Math.round(add * 100) / 100).toFixed(2);
        $('#edit_total').val(total);

        getSettlementWeight();
    }

    function getNet() {
        if ($('#item_code').val() == 'BG1101' || $('#item_code').val() == 'BG1201') {
            var total_gross = $('#total_weight2').val();

        } else {
            var total_gross = $('#total_weight').val();
        }

        var tareweight = $('#tare_weight').val();

        return total_gross - tareweight;
    }

    function calculateNetEdit() {
        var total_gross = $('#edit_total').val();
        
        var tareweight = $('#edit_tareweight').val();

        var new_net = total_gross - tareweight;

        $('#edit_net').val(new_net);

        getSettlementWeightEdit();
    }

    function getSettlementWeight() {
        var settlement_weight = document.getElementById('settlement_weight');

        var net = getNet();

        var cold_weight = 0.975 * net;

        // return settlement_weight in two decimal places without rounding
        settlement_weight.value = (parseInt(cold_weight * 100) / 100).toFixed(2);

        // get classification
        getClassificationCode((parseInt(cold_weight * 100) / 100).toFixed(2));
    }

    function getSettlementWeightEdit(){
        var net = $('#edit_net').val();
        var cold_weight = 0.975 * net;
        var settlement_weight = (parseInt(cold_weight * 100) / 100).toFixed(2);
        $('#edit_settlement').val(settlement_weight);
        getClassificationCodeEdit(settlement_weight);
    }

    function getNextReceipt(receipt) {
        $.ajax({
            type: "GET",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "{{ url('slaughter/next-receipt-ajax') }}",
            data: {
                'receipt': receipt,

            },
            dataType: 'JSON',
            success: function (res) {
                if (res) {
                    $('#receipt_no').val(res);
                }

            }
        });
    }

    function getClassificationCode(s_weight) {
        $('#classification_code').val('--');

        var item_code = $('#item_code').val();

        if (s_weight > 1 && item_code != '') {
            if (item_code == 'BG1101' || item_code == 'BG1201') {
                // lamb/goat classes
                switch (true) {
                    case (item_code == 'BG1101'):
                        // lamb
                        if (s_weight > 25) {
                            $('#classification_code').val('LAMB-STD');

                        } else if (s_weight >= 14 && s_weight < 25) {
                            $('#classification_code').val('LAMB-PRM');

                        } else if (s_weight >= 11 && s_weight < 14) {
                            $('#classification_code').val('LAMB-STD');
                        }
                        break;

                    case (item_code == 'BG1201'):
                        // goat
                        $('#classification_code').val('GOATLCL');
                        break;

                    default:
                        $('#classification_code').val('**');
                }

            } else if (item_code == 'BG1005' || item_code == 'BG1006' || item_code == 'BG1007' || item_code ==
                'BG1008' ||
                item_code == 'BG1009') {
                // High Grade 
                switch (true) {
                    case (s_weight >= 165 && s_weight < 170):
                        // code block
                        $('#classification_code').val('HG+165');
                        break;

                    case (s_weight >= 170 && s_weight < 175):
                        // code block
                        $('#classification_code').val('HG+170');
                        break;

                    case (s_weight >= 175 && s_weight <= 250):
                        // code block
                        $('#classification_code').val('HG+175');
                        break;

                    case (s_weight > 250):
                        // code block
                        $('#classification_code').val('HG+250.1');
                        break;

                    default:
                        $('#classification_code').val('**');
                }
            } else if (item_code == 'BG1011' || item_code == 'BG1012' || item_code == 'BG1013' || item_code ==
                'BG1014') {
                // comm-beef
                switch (true) {
                    case (s_weight < 120):
                        $('#classification_code').val('CG-120');
                        break;

                    case (s_weight >= 120 && s_weight < 150):
                        $('#classification_code').val('CG+150');
                        break;

                    case (s_weight >= 150 && s_weight < 160):
                        $('#classification_code').val('CG+150');
                        break;

                    case (s_weight >= 160 && s_weight < 165):
                        $('#classification_code').val('CG+160');
                        break;

                    case (s_weight >= 165 && s_weight < 170):
                        $('#classification_code').val('CG+165');
                        break;

                    case (s_weight >= 170 && s_weight < 175):
                        $('#classification_code').val('CG+170');
                        break;

                    case (s_weight > 175):
                        $('#classification_code').val('CG+175');
                        break;

                    default:
                        $('#classification_code').val('**');
                }

            } else if (item_code == 'BG1016') {
                // CMFAQ
                switch (true) {
                    case (s_weight >= 150 && s_weight < 160):
                        $('#classification_code').val('FAQ+150');
                        break;

                    case (s_weight >= 160):
                        $('#classification_code').val('FAQ+160');
                        break;

                    default:
                        $('#classification_code').val('**');
                }

            } else if (item_code == 'BG1018') {
                // CMSTD
                switch (true) {
                    case (s_weight >= 120 && s_weight <= 149):
                        $('#classification_code').val('STDA-149');
                        break;

                    case (s_weight <= 119):
                        $('#classification_code').val('STDB-119');
                        break;

                    default:
                        $('#classification_code').val('**');
                }
            }
        }

    }

    function transformToLivestockCode(item_code)
    {
        if (item_code == 'BG1021') {
            return 'BG1006';
            
        } else if (item_code == 'BG1022') {
            return 'BG1007';

        } else if (item_code == 'BG1023') {
            return 'BG1008';

        } else if (item_code == 'BG1024') {
            return 'BG1009';

        } else if (item_code == 'BG1031') {
            return 'BG1011';

        } else if (item_code == 'BG1032') {
            return 'BG1012';

        } else if (item_code == 'BG1033') {
            return 'BG1013';

        } else if (item_code == 'BG1034') {
            return 'BG1014';
            
        } else if (item_code == 'BG1036') {
            return 'BG1016';

        } else if (item_code == 'BG1037') {
            return 'BG1018';

        } else if (item_code == 'BG1900') {
            return 'BG1101';

        } else if (item_code == 'BG1202') {
            return 'BG1201';

        } else {
            return '';
        }
    }

    function getClassificationCodeEdit(s_weight) {
        $('#edit_classification_code').val('--');

        var carcass_code = $('#edit_item_code').val();
        var item_code = transformToLivestockCode(carcass_code);

        if (s_weight > 1 && item_code != '') {
            if (item_code == 'BG1101' || item_code == 'BG1201') {
                // lamb/goat classes
                switch (true) {
                    case (item_code == 'BG1101'):
                        // lamb
                        if (s_weight > 25) {
                            $('#edit_classification_code').val('LAMB-STD');

                        } else if (s_weight >= 14 && s_weight < 25) {
                            $('#edit_classification_code').val('LAMB-PRM');

                        } else if (s_weight >= 11 && s_weight < 14) {
                            $('#edit_classification_code').val('LAMB-STD');
                        }
                        break;

                    case (item_code == 'BG1201'):
                        // goat
                        $('#edit_classification_code').val('GOATLCL');
                        break;

                    default:
                        $('#edit_classification_code').val('**');
                }

            } else if (item_code == 'BG1005' || item_code == 'BG1006' || item_code == 'BG1007' || item_code ==
                'BG1008' ||
                item_code == 'BG1009') {
                // High Grade 
                switch (true) {
                    case (s_weight >= 165 && s_weight < 170):
                        // code block
                        $('#edit_classification_code').val('HG+165');
                        break;

                    case (s_weight >= 170 && s_weight < 175):
                        // code block
                        $('#edit_classification_code').val('HG+170');
                        break;

                    case (s_weight >= 175 && s_weight <= 250):
                        // code block
                        $('#edit_classification_code').val('HG+175');
                        break;

                    case (s_weight > 250):
                        // code block
                        $('#edit_classification_code').val('HG+250.1');
                        break;

                    default:
                        $('#edit_classification_code').val('**');
                }
            } else if (item_code == 'BG1011' || item_code == 'BG1012' || item_code == 'BG1013' || item_code ==
                'BG1014') {
                // comm-beef
                switch (true) {
                    case (s_weight < 120):
                        $('#edit_classification_code').val('CG-120');
                        break;

                    case (s_weight >= 120 && s_weight < 150):
                        $('#edit_classification_code').val('CG-150');
                        break;

                    case (s_weight >= 150 && s_weight < 160):
                        $('#edit_classification_code').val('CG+150');
                        break;

                    case (s_weight >= 160 && s_weight < 165):
                        $('#edit_classification_code').val('CG+160');
                        break;

                    case (s_weight >= 165 && s_weight < 170):
                        $('#edit_classification_code').val('CG+165');
                        break;

                    case (s_weight >= 170 && s_weight < 175):
                        $('#edit_classification_code').val('CG+170');
                        break;

                    case (s_weight > 175):
                        $('#edit_classification_code').val('CG+175');
                        break;

                    default:
                        $('#edit_classification_code').val('**');
                }

            } else if (item_code == 'BG1016') {
                // CMFAQ
                switch (true) {
                    case (s_weight >= 150 && s_weight < 160):
                        $('#edit_classification_code').val('FAQ+150');
                        break;

                    case (s_weight >= 160):
                        $('#edit_classification_code').val('FAQ+160');
                        break;

                    default:
                        $('#edit_classification_code').val('**');
                }

            } else if (item_code == 'BG1018') {
                // CMSTD
                switch (true) {
                    case (s_weight >= 120 && s_weight <= 149):
                        $('#edit_classification_code').val('STDA-149');
                        break;

                    case (s_weight <= 119):
                        $('#edit_classification_code').val('STDB-119');
                        break;

                    default:
                        $('#edit_classification_code').val('**');
                }
            }
        }

    }

</script>
@endsection
