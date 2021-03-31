@extends('layouts.slaughter_master')

@section('content')

<!-- weigh -->
<form id="form-slaughter-weigh" action="" method="post">
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
                                        style="text-align: center; border:none" id="comport_value" value=""
                                        disabled></strong></small>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="exampleInputEmail1">Reading</label>
                    <input type="number" step="0.01" class="form-control" id="reading" name="reading" value="0.00"
                        oninput="getNet()" placeholder="" readonly required>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="manual_weight">
                    <label class="form-check-label" for="manual_weight">Enter Manual weight</label>
                </div> <br>
                <div class="form-group">
                    <label for="exampleInputPassword1">Tare-Weight</label>
                    <input type="number" class="form-control" id="tareweight" name="tareweight" value="" readonly>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="exampleInputPassword1">Net</label>
                            <input type="number" class="form-control" id="net" name="net" value="0.00" step="0.01"
                                placeholder="" readonly required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="exampleInputPassword1">Settlement Weight</label>
                            <input type="number" class="form-control" id="settlement_weight" name="settlement_weight"
                                value="0.00" step="0.01" placeholder="" readonly required>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card ">
            <div class="card-body text-center">
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="exampleInputPassword1">Ear Tag </label>
                            <select class="form-control select2" name="slapmark" id="slapmark" required>
                                {{-- @foreach($receipts as $receipt)
                            @if (old('slapmark') == $receipt->vendor_tag)
                            <option value="{{ $receipt->vendor_tag }}" selected>{{ ucwords($receipt->vendor_tag) }}
                                </option>
                                @else
                                <option value="{{ $receipt->vendor_tag }}">{{ ucwords($receipt->vendor_tag) }}</option>
                                @endif
                                @endforeach --}}
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="exampleInputPassword1">Animal Description</label>
                            <select class="form-control select2" name="carcass_type" id="carcass_type" required>
                                {{-- @foreach($carcass_types as $type)
                        <option value="{{ $type->code }}" @if($loop->first) selected="selected" @endif>
                                {{ ucwords($type->description) }}
                                </option>
                                @endforeach --}}
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="exampleInputPassword1">Receipt No.</label>
                    <input type="text" class="form-control" value="" name="receipt_no" id="receipt_no" placeholder=""
                        readonly required>
                </div>

                <div class="form-group">
                    <label for="exampleInputPassword1">Vendor Number</label>
                    <input type="text" class="form-control" value="" name="vendor_no" id="vendor_no" placeholder=""
                        readonly required>
                </div>
                <div class="form-group">
                    <label for="exampleInputPassword1">Vendor Name</label>
                    <input type="text" class="form-control" name="vendor_name" id="vendor_name" placeholder="" readonly
                        required>
                </div>
            </div>
        </div>
        <div class="card ">
            <div class="card-body text-center">
                <div class="row form-group">
                    <div class="col-md-6">
                        <label for="exampleInputPassword1">Total Received From Vendor </label>
                        <input type="text" class="form-control" value="" name="delivered_per_vendor"
                            id="delivered_per_vendor" placeholder="" readonly required>
                    </div>
                    <div class="col-md-6">
                        <label for="exampleInputPassword1">Total Received per slapmark </label>
                        <input type="text" class="form-control" value="" name="total_by_vendor" id="total_by_vendor"
                            placeholder="" readonly required>
                    </div>
                </div>
                <div class=" row form-group">
                    <div class="col-md-6">
                        <label for="exampleInputPassword1">Total weighed </label>
                        <input type="text" class="form-control" value="" name="total_per_slap" id="total_per_slap"
                            placeholder="" readonly required>
                    </div>
                    <div class="col-md-6">
                        <label for="exampleInputPassword1">Total remaining </label>
                        <input type="text" class="form-control" value="" name="total_remaining" id="total_remaining"
                            placeholder="" readonly required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="exampleInputPassword1">Classification Code</label>
                    <input type="text" class="form-control" id="classification_code" name="classification_code"
                        placeholder="" readonly required>
                </div>
                <div class="form-group" style="padding-top: 10%">
                    <button type="submit" onclick="return validateOnSubmit()"
                        class="btn btn-success btn-lg btn-block btn-huge"><i class="fa fa-paper-plane"
                            aria-hidden="true"></i>
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

<div id="slaughter_entries" class="collapse">
    <hr>
    <div class="row">
        <!-- slaughter data Table-->
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"></h3>
                    <h3 class="card-title"> Weighed Entries | <span id="subtext-h1-title"><small> view list ordered
                                by latest</small> </span></h3>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="hidden" hidden>{{ $i = 1 }}</div>
                    <table id="example1" class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Receipt No.</th>
                                <th>Ear Tag </th>
                                <th>Vendor No.</th>
                                <th>Vendor Name</th>
                                <th>Side A</th>
                                <th>Side B</th>
                                <th>Total Weight</th>
                                <th>Production Name</th>
                                <th>Slaughter Date</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Receipt No.</th>
                                <th>Ear Tag </th>
                                <th>Vendor No.</th>
                                <th>Vendor Name</th>
                                <th>Side A</th>
                                <th>Side B</th>
                                <th>Total Weight</th>
                                <th>Production Name</th>
                                <th>Slaughter Date</th>
                            </tr>
                        </tfoot>
                        <tbody>
                            {{-- @foreach($slaughter_data as $data)
                            <tr>
                                <td>{{ $i++ }}</td>
                            <td>{{ $data->receipt_no }}</td>
                            <td>{{ $data->slapmark }}</td>
                            <td>{{ $data->item_code }}</td>
                            <td>{{ $data->description }}</td>
                            <td>{{ number_format($data->actual_weight, 2) }}</td>
                            <td>{{ $data->meat_percent }}</td>
                            <td>{{ $data->classification_code }}</td>
                            <td>{{ $data->created_at }}</td>
                            </tr>
                            @endforeach --}}
                        </tbody>
                    </table>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->
        </div>
        <!-- /.col -->
    </div>
</div>
<!--End users Table-->

@endsection

@section('scripts')
<script>

</script>
@endsection
