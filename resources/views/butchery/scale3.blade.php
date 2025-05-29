@extends('layouts.butchery_master')

@section('content-header')
<div class="container">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0"> {{ $title }} |<small> Deboning </small></h1>
        </div><!-- /.col -->
    </div><!-- /.row -->
</div><!-- /.container-fluid -->

@endsection

@section('content')
<form id="form-save-scale3" class="form-prevent-multiple-submits"
    action="{{ route('butchery_scale3_save') }}" method="post">
    @csrf
    <div class="card-group">
        <div class="card">
            <div class="card-body " style="">
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-12">
                            <label for="exampleInputPassword1"> Product</label>
                            <select class="form-control select2" name="product" id="product" required>
                                <option value="">Select product</option>
                                @foreach($products as $product)
                                    <option
                                        value="{{ $product->code }}">
                                        {{ $product->code }} - {{ $product->description }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row form-group">
                    <div class="col-md-12">
                        <div class="form-group" id="product_type_select">
                            <label for="exampleInputPassword1">Production Process</label>
                            <input type="text" class="form-control" id="production_process" name="production_process"
                                value="Deboning" disabled>
                        </div>
                        <input type="hidden" class="form-control" id="production_process_code"
                            name="production_process_code" value="3">
                    </div>
                </div>
                <div class="form-group" style="padding-left: 30%;">
                    <button type="button" onclick="getScaleReading()" id="weigh" value=""
                        class="btn btn-primary btn-lg"><i class="fas fa-balance-scale"></i> Weigh</button> <br><br>
                    <small>Reading from <input type="text" id="comport_value" value="{{ $configs[0]->comport }}"
                            style="border:none" disabled></small>
                </div>

            </div>
        </div>
        <div class="card ">
            <div class="card-body text-center">
                <div class="form-group">
                    <label for="exampleInputEmail1">Reading</label>
                    <input type="number" step="0.01" class="form-control" id="reading" name="reading" value="0.00"
                        oninput="getNet()" placeholder="" readonly>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="manual_weight" name="manual_weight">
                    <label class="form-check-label" for="manual_weight">Enter Manual weight</label>
                </div> <br>
                <input type="hidden" id="old_manual" value="{{ old('manual_weight') }}">
                <div class="row">
                    <div class="col-6 form-group">
                        <label for="crate_weight">Crate Weight</label>
                        <select class="form-control" id="crate_weight" name="crate_weight" onchange="updateTotalTare()">
                            <option selected value="1.8">1.8</option>
                            <option value="1.5">1.5</option>
                        </select>
                    </div>
                    <div class="col-6 form-group">
                        <label for="black_crates">Black Crates</label>
                        <input type="number" class="form-control" id="black_crates" name="black_crates" min="0" oninput="updateTotalTare()" value="1" max="4">
                    </div>
                </div>
                <div class="row">
                    <div class="col-6 form-group">
                        <label for="tareweight">Crates Tare-Weight</label>
                        <input type="number" class="form-control" id="tareweight" name="tareweight" value="" readonly>
                    </div>
                    <div class="col-6 form-group">
                        <label for="exampleInputPassword1">Net</label>
                        <input type="number" class="form-control" id="net" name="net" value="0.00" step=".01" placeholder=""
                            readonly>
                    </div>
                </div>
            </div>
        </div>

        <div class="card ">
            <div class="card-body text-center">
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="no_of_crates">Total Crates(inc. Black)</label>
                            <input type="number" class="form-control" onClick="this.select();" id="no_of_crates" oninput="updateBlackCratesMax()"
                                value="4" name="no_of_crates" placeholder="" required>
                        </div>
                        <div class="col-md-6">
                            <label for="exampleInputPassword1">No. of pieces </label>
                            <input type="number" class="form-control" onClick="this.select();" id="no_of_pieces"
                                value="0" name="no_of_pieces" placeholder="" required>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-5">
                            <label for="exampleInputPassword1">Batch No </label>
                            <input type="text" class="form-control" onClick="this.select();" id="batch_no" value="{{ old('batch_no') }}" name="batch_no">
                        </div>
                        <div class="col-md-7">
                            <label for="exampleInputPassword1">Narration </label>
                            <input type="text" class="form-control" onClick="this.select();" id="desc" value="" name="desc"
                                placeholder="any further narration eg. java, jowl..">
                        </div>
                    </div>
                </div>
                <div id="transfer_div" class="form-group collapse">
                    <label for="exampleInputPassword1">Transfer To</label>
                    <select class="form-control select2" name="transfer_to" id="transfer_to" required>
                        <option selected value="1">Sausage</option>
                        <option value="2">High Care</option>
                        <option value="3">Despatch</option>
                    </select>
                </div>
                <div class="form-group" style="padding-top: 5%">
                    <button type="submit" onclick="return validateOnSubmit()"
                        class="btn btn-primary btn-lg btn-prevent-multiple-submits"><i
                            class="fa fa-paper-plane single-click" aria-hidden="true"></i> Save</button>
                </div>
            </div>
        </div>
    </div>

    <div id="loading" class="collapse">
        <div class="row d-flex justify-content-center">
            <div class="spinner-border" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
    </div>
</form>


<!-- product type modal -->
<div class="modal fade" id="productTypesModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="form-product-type" class="form-prevent-multiple-submits" action="#" method="post">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Edit Product Type</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class=" form-group">
                        <select name="edit_product_type" id="edit_product_type" class="form-control" required>
                            <option value="1">
                                Main Product
                            </option>
                            <option value="2">
                                By Product
                            </option>
                            <option value="3">
                                Intake
                            </option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary btn-prevent-multiple-submits" type="submit"
                        onclick="setProductCode()">
                        <i class="fa fa-save"></i> Edit
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<hr>
<!-- end product code modal -->

<div class="div">
    <button class="btn btn-primary " data-toggle="collapse" data-target="#slicing_output_show"><i
            class="fa fa-plus"></i>
        Output
    </button>
</div>

<div id="slicing_output_show" class="collapse">
    <hr>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"> Scale 3 Deboned output data | <span id="subtext-h1-title"><small> entries
                                ordered by
                                latest</small> </span></h3>
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
                                @foreach($deboning_data as $data)
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
</div>
<!-- slicing ouput data show -->

<!-- Edit Modal -->
<div id="itemCodeModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!--Start create user modal-->
        <form class="form-prevent-multiple-submits" id="form-edit-role"
            action="{{ route('butchery_scale3_update') }}" method="post">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Edit Scale3 Item: <strong><input style="border:none"
                                type="text" id="item_name" name="item_name" value="" readonly></strong></h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="exampleInputPassword1">Product</label>
                        <select class="form-control select2" name="edit_product" id="edit_product" required>
                            @foreach($products as $product)
                                <option value="{{ trim($product->code) }}" selected="selected">
                                    {{ ucwords($product->description) }} - {{ $product->code }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row form-group">
                        <div class="col-md-6">
                            <label for="exampleInputPassword1">No. of Crates</label>
                            <select class="form-control" name="edit_crates" id="edit_crates" required>
                                <option>1</option>
                                <option>2</option>
                                <option>3</option>
                                <option selected>4</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="exampleInputPassword1">Product Type</label>
                            <select class="form-control" name="edit_product_type2" id="edit_product_type2"
                                selected="selected" required>
                                <option value="1">
                                    Main Product
                                </option>
                                <option value="2">
                                    By Product
                                </option>
                                <option value="3">
                                    Intake
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-6">
                            <label for="email">Scale Weight(actual_weight)</label>
                            <input type="number" onClick="this.select();" class="form-control" name="edit_weight"
                                id="edit_weight" placeholder="" step="0.01" autocomplete="off" required autofocus>
                        </div>
                        <div class="col-md-6">
                            <label>No. of Pieces</label>
                            <input type="number" onClick="this.select();" onfocus="this.value=''" class="form-control"
                                id="edit_no_pieces" value="" name="edit_no_pieces" placeholder="">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label>Narration</label>
                        <input type="text" onClick="this.select();" class="form-control" id="edit_narration" value=""
                            name="edit_narration" placeholder="">

                    </div>
                    <div class="row form-group">
                        <label for="exampleInputPassword1">Production Process</label>
                        <select selected="selected" class="form-control" name="edit_production_process"
                            id="edit_production_process">
                        </select>
                    </div>

                    <input type="hidden" name="item_id" id="item_id" value="">
                    <input type="hidden" id="loading_val_edit" value="0">
                </div>
                <div class="modal-footer">
                    <div class="form-group">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button class="btn btn-warning btn-lg btn-prevent-multiple-submits"
                            onclick="return validateOnEditSubmit()" type="submit">
                            <i class="fa fa-save"></i> Update
                        </button>
                    </div>
                </div>

                <div id="loading2" class="collapse">
                    <div class="row d-flex justify-content-center">
                        <div class="spinner-border" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
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
    function updateBlackCratesMax() {
        black_crates_input = document.getElementById('black_crates');
        no_of_crates = document.getElementById('no_of_crates').value;
        if (parseInt(black_crates_input.value) > parseInt(no_of_crates)) {
            black_crates_input.value = '';
        };
        black_crates_input.max = no_of_crates;
        updateTotalTare();
    };

    function updateTotalTare() {
        crate_weight = document.getElementById('crate_weight').value;
        no_of_crates = document.getElementById('no_of_crates').value;
        black_crates = document.getElementById('black_crates').value;
        tareweight_input = document.getElementById('tareweight');
        tareweight_input.value = (no_of_crates * crate_weight + (black_crates * (2-crate_weight))).toFixed(2);
        getNet();
    };

    $(document).ready(function () {

        $('.form-prevent-multiple-submits').on('submit', function () {
            $(".btn-prevent-multiple-submits").attr('disabled', true);
        });

        updateTotalTare();

        let reading = document.getElementById('reading');
        if (($('#old_manual').val()) == "on") {
            $('#manual_weight').prop('checked', true);
            reading.readOnly = false;
            reading.focus();
            $('#reading').val("");

        } else {
            reading.readOnly = true;

        }

        $("body").on("click", "#itemCodeModalShow", function (e) {
            e.preventDefault();

            var code = $(this).data('code');
            var item = $(this).data('item');
            var weight = $(this).data('weight');
            var no_of_pieces = $(this).data('no_of_pieces');
            var narration = $(this).data('narration');
            var id = $(this).data('id');
            var process_code = $(this).data('production_process');
            var type_id = $(this).data('type_id');

            $('#edit_product').val(code);
            $('#item_name').val(item);
            $('#edit_weight').val(weight);
            $('#edit_no_pieces').val(no_of_pieces)
            $('#edit_narration').val(narration)
            $('#item_id').val(id);
            $('#edit_production_process').val(process_code);
            $('#edit_product_type2').val(type_id);

            $('#edit_product').select2('destroy').select2();
            $('#itemCodeModal').modal('show');
        });


        $('#edit_product').change(function () {
            $('#loading_val_edit').val(0)
            var code = $('#edit_product').val();

            var crates_no = $('#edit_crates').val();
            var net_weight = $('#edit_weight').val();

            var net = net_weight - (1.8 * crates_no);
        });

        $('#edit_weight').keyup(function () {
            var code = $('#edit_product').val();

            var crates_no = $('#edit_crates').val();
            var net_weight = $('#edit_weight').val();

            var net = net_weight - (1.8 * crates_no);

            getNumberOfPiecesEdit(code.trim(), net);
        });

        $('#edit_crates').change(function () {
            var code = $('#edit_product').val();

            var crates_no = $('#edit_crates').val();
            var net_weight = $('#edit_weight').val();

            var net = net_weight - (1.8 * crates_no);

            getNumberOfPiecesEdit(code.trim(), net);
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

    function getNet() {
        var reading = document.getElementById('reading').value;
        var tareweight = document.getElementById('tareweight').value;
        var net = document.getElementById('net');
        new_net_value = parseFloat(reading) - parseFloat(tareweight);
        net.value = Math.round((new_net_value + Number.EPSILON) * 100) / 100;

        //get number of pieces
        var code = $('#product').val();

        if (code != "" && net.value > 0) {
            var product_code = code.split('-')[1];
            getNumberOfPieces(product_code, net.value);

        } else {

            $('#no_of_pieces').val(0);
        }

    }

    function validateOnSubmit() {
        $valid = true;

        var net = $('#net').val();
        var product_type = $('#product_type').val();
        var no_of_pieces = $('#no_of_pieces').val();
        var process = $('#production_process').val();
        var process_substring = process.substr(0, process.indexOf(' '));        

        if (net == "" || net <= 0.00) {
            $valid = false;
            alert("Please ensure you have valid netweight.");
        }
        return $valid;
    }

    function validateOnEditSubmit() {
        $valid = true;

        return $valid;
    }

    //read scale
    function getScaleReading() {
        var comport = $('#comport_value').val();

        if (comport != null) {
            $.ajax({
                type: "GET",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]')
                        .attr('content')
                },
                url: "{{ url('butchery/read-scale-api-service') }}",

                data: {
                    'comport': comport,

                },
                dataType: 'JSON',
                success: function (data) {
                    // console.log(data);

                    var obj = JSON.parse(data);
                    // console.log(obj.success);

                    if (obj.success == true) {
                        var reading = document.getElementById('reading');
                        console.log('weight: ' + obj.response);
                        reading.value = obj.response;
                        getNet();

                    } else if (obj.success == false) {
                        alert('error occured in response: ' + obj.response);

                    } else {
                        alert('No response from service');

                    }

                },
                error: function (data) {
                    var errors = data.responseJSON;
                    // console.log(errors);
                    alert('error occured when sending request');
                }
            });
        } else {
            alert("Please set comport value first");
        }
    }

</script>
@endsection
