@extends('layouts.slaughter_master')

@section('content')

<!-- weigh -->
<form id="form-slaughter-weigh" action="{{ route('save_weigh') }}" method="post">
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
                        name="reading" value="0.00" placeholder="" readonly required>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="manual_weight">
                    <label class="form-check-label" for="manual_weight">Enter Manual weight</label>
                </div> <br>
                <div class="row form-group">
                    <div class="col-md-8">
                        <label for="exampleInputPassword1">Tare-Weight</label>
                        <select class="form-control select2" name="tare_weight" id="tare_weight" required>
                            @if (old('tare_weight') == '1.5' || old('tare_weight') == null )
                            <option value="1.5" selected> 1.5</option>
                            <option value="1.8"> 1.8</option>
                            <option value="1.9"> 1.9</option>
                            <option value="2.2"> 2.2</option>

                            @elseif (old('tare_weight') == '1.8')
                            <option value="1.5"> 1.5</option>
                            <option value="1.8" selected> 1.8</option>
                            <option value="1.9"> 1.9</option>
                            <option value="2.2"> 2.2</option>

                            @elseif(old('tare_weight') == '1.9')
                            <option value="1.5"> 1.5</option>
                            <option value="1.8"> 1.8</option>
                            <option value="1.9" selected> 1.9</option>
                            <option value="2.2"> 2.2</option>

                            @elseif(old('tare_weight') == '2.2')
                            <option value="1.5"> 1.5</option>
                            <option value="1.8"> 1.8</option>
                            <option value="1.9"> 1.9</option>
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
                <div class="row text-center phase1">
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
                <div class="row text-center phase2" style="text-align: center; display:inline-block;">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="exampleInputPassword1">Total </label>
                            <input type="number" style="text-align: center" class="form-control" id="total_weight2"
                                name="total_weight2" step="0.01" value="0.00" readonly required>
                        </div>
                    </div>
                </div>
                <input type="hidden" class="form-control " id="settlement_weight" name="settlement_weight" value="">
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
                                <option value="{{ $receipt->receipt_no }}" selected>{{ ucwords($receipt->receipt_no) }}
                                </option>

                                @else
                                <option value="{{ $receipt->receipt_no }}">{{ ucwords($receipt->receipt_no) }}</option>
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
                <div class="form-group">
                    <label for="exampleInputPassword1">Agg No. </label>
                    <input type="number" style="text-align: center" class="form-control" value="" name="agg_no"
                        id="agg_no" placeholder="" readonly required>
                </div>
                <div class="row form-group">
                    <div class="text-center" style="width: 70%; margin: 0 auto;">
                        <label for="exampleInputPassword1">Total Received From Vendor </label>
                        <input type="number" style="text-align: center" class="form-control" value="" name=""
                            id="total_received" placeholder="" readonly required>
                    </div>
                </div>
                <div class=" row form-group">
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
                <div class="form-group" style="padding-top: 10%">
                    <button type="submit" onclick="return validateOnSubmit()" class="btn btn-success btn-lg "><i
                            class="fa fa-paper-plane" aria-hidden="true"></i>
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
</div><hr>

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
                    <table id="example1" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Agg No </th>
                                    <th>Receipt No.</th>
                                    <th>Code</th>
                                    <th>Description</th>
                                    <th>Vendor No.</th>
                                    <th>Vendor Name</th>
                                    <th>Side A</th>
                                    <th>Side B</th>
                                    <th>Total Weight</th>
                                    <th>Slaughter Date</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>Agg No </th>
                                    <th>Receipt No.</th>
                                    <th>Code</th>
                                    <th>Description</th>
                                    <th>Vendor No.</th>
                                    <th>Vendor Name</th>
                                    <th>Side A</th>
                                    <th>Side B</th>
                                    <th>Total Weight</th>
                                    <th>Slaughter Date</th>
                            </tr>
                        </tfoot>
                        <tbody>
                            @foreach($slaughter_data as $data)
                                <tr>
                                    <td>{{ $data->agg_no }}</td>
                                    <td>{{ $data->receipt_no }}</td>
                                    <td>{{ $data->item_code }}</td>
                                    <td>{{ $data->description }}</td>
                                    <td>{{ $data->vendor_no }}</td>
                                    <td>{{ $data->vendor_name }}</td>
                                    <td>{{ number_format($data->sideA_weight, 2) }}</td>
                                    <td>{{ number_format($data->sideB_weight, 2) }}</td>
                                    <td>{{ number_format($data->total_weight, 2) }}</td>
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
<!--End users Table-->

@endsection

@section('scripts')
<script>
    $(document).ready(function () {
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

    });

    function hideShowDivs() {
        if ($('#item_code').val() == 'BG1101' || $('#item_code').val() == 'BG1201') {
            $('.phase1').hide();
            $('.phase2').show();

        } else {
            $('.phase1').show();
            $('.phase2').hide();
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

    function validateOnSubmit() {
        if ($('#item_code').val() == 'BG1101' || $('#item_code').val() == 'BG1201') {
            if (!($('#total_weight2').val() > 10) && !($('#total_weight2').val() < 200)) {
                alert("Please ensure you have valid weight of between 10-200 kgs.");
                return false;
            }

        } else {
            var side_A = $('#side_A').val();
            var side_B = $('#side_B').val();

            if (!(side_A >= 10 && side_A <= 200) && !(side_B >= 10 && side_B <= 200)) {
                alert("Please ensure you have valid weight of between 10-200 kgs in both sides.");
                return false;
            }
        }

        var total_weighed = $('#total_weighed').val();
        var total_received = $('#total_received').val();

        if (total_weighed >= total_received) {
            alert("You have exhausted vendor received Qty.");
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

    function getSettlementWeight() {
        var settlement_weight = document.getElementById('settlement_weight');

        if ($('#item_code').val() == 'BG1101' || $('#item_code').val() == 'BG1201') {
            var total_gross = $('#total_weight2').val();

        } else {
            var total_gross = $('#total_weight').val();
        }

        var tareweight = $('#tare_weight').val();

        var net = total_gross - tareweight;

        var cold_weight = 0.975 * net;

        // return settlement_weight in two decimal places without rounding
        settlement_weight.value = (parseInt(cold_weight * 100) / 100).toFixed(2);
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

</script>
@endsection
