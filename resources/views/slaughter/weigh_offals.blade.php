@extends('layouts.slaughter_master')

@section('content')

<div class="container">
    <div class="card">
        <h2 class="card-header">Weigh Offals</h2>
        <div class="card-body">
            <form id="form-weigh-offals" action="{{ route('save_offals_weights') }}" class="form-prevent-multiple-submits" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="customer">Customer</label>
                            <select class="form-control select2" id="customer_id" name="customer_id" required>
                                <option disabled value="">Select Customer</option>
                                @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="customer">Product</label>
                            <select class="form-control select2" id="product_code" name="product_code" required>
                                <option value="">Select... </option>
                                @foreach ($offals_products as $product)
                                <option value="{{ $product->code }}">{{ $product->code }} {{ $product->description }}</option>                                
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row text-center">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="reading">Scale Reading</label>
                            <input type="number" step="0.01" class="form-control" id="reading" name="reading" value=""
                                oninput="updateNetWeight()" placeholder="" readonly required>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="manual_weight" name="manual_weight" onchange="toggleManualWeight()">
                            <label class="form-check-label" for="manual_weight">Enter Manual weight</label>
                        </div>
                        <div>
                            @if(empty($configs))
                                <small class="d-block">No comport conifgured</small>
                            @else
                            <small class="d-block mt-2">
                                <label>Reading from ComPort:</label>
                                <span id="comport_value" disabled >{{ $configs[0]->comport?? "" }}</span>
                            </small>
                            @endif
                            <button type="button" onclick="getScaleReading()" class="btn btn-primary btn-lg">
                                <i class="fas fa-balance-scale"></i> Weigh
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="tare_weight">Tare-Weight</label>
                            @if(empty($configs))
                            <input type="number" class="form-control" id="tare_weight" name="tare_weight" value="0.00" readonly required>
                            @else
                            <input type="number" class="form-control" id="tare_weight" name="tare_weight"
                                value="{{ number_format($configs[0]->tareweight, 2)?? "" }}" readonly required>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="net_weight">Net-Weight</label>
                            <input type="number" class="form-control" id="net_weight" name="net_weight"
                                value="" readonly required>
                        </div>
                        <button type="submit" id="btn_save" class="btn btn-primary btn-lg btn-prevent-multiple-submits mt-3">
                            <i class="fa fa-paper-plane" aria-hidden="true"></i>
                            Save
                        </button>
                    </div>
                </div>
            </form>
        </div>
        
    </div>   
</div>   

<hr class="my-4">

<!--End weigh -->



<div class="div">
    <button class="btn btn-primary " data-toggle="collapse" data-target="#entries"><i class="fa fa-plus"></i>
        Entries
    </button>
</div>

<!-- Table of saved entries -->
<div id="entries" class="collapse my-4">
    <!-- offals data Table-->
    <div class="card">
        <!-- /.card-header -->
        <div class="card-header">
            <h3 class="card-title"> Weighed Entries | <span id="subtext-h1-title"><small> view weighed ordered
                        by latest</small> </span></h3>
        </div>
        <!-- /.card-body -->
        <div class="card-body table-responsive">
            <table id="example1" class="table table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product Code</th>
                        <th>Net Weight (kgs)</th>
                        <th>Scale Reading (kgs)</th>
                        <th>Manually Recorded</th>
                        <th>Customer</th>
                        <th>Recorded by</th>
                        <th>Recorded Date Time</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th>#</th>
                        <th>Product Code</th>
                        <th>Net Weight (kgs)</th>
                        <th>Scale Reading (kgs)</th>
                        <th>Manually Recorded</th>
                        <th>Customer</th>
                        <th>Recorded by</th>
                        <th>Recorded Date Time</th>
                    </tr>
                </tfoot>
                <tbody>
                    @foreach($entries as $entry)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $entry->product_code }}</td>
                        <td>{{ number_format($entry->net_weight, 2) }}</td>
                        <td>{{ number_format($entry->scale_reading, 2) }}</td>
                        @if($entry->is_manual == 0)
                            <td>
                                <span class="badge badge-success">No</span>
                            </td>
                        @else
                            <td>
                                <span class="badge badge-danger">Yes</span>
                            </td>
                        @endif
                        <td>{{ $entry->customer_name }}</td>
                        <td>{{ $entry->username }}</td>
                        <td>{{  $helpers->dateToHumanFormat($entry->created_at) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
        

@endsection

@section('scripts')
<script>
function updateNetWeight() {
    var reading = document.getElementById('reading').value;
    var tareweight = document.getElementById('tare_weight').value;
    var netWeightInput = document.getElementById('net_weight');
    netWeightInput.value = parseFloat(reading) - parseFloat(tareweight);
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
            url: "{{ route('read_scale') }}",

            data: {
                'comport': comport,

            },
            dataType: 'JSON',
            success: function (data) {

                var obj = JSON.parse(data);

                if (obj.success == true) {
                    var reading = document.getElementById('reading');
                    reading.value = obj.response;
                    updateNetWeight();

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

function toggleManualWeight() {
    const manualWeightInput = document.getElementById('manual_weight');
    const readingInput = document.getElementById('reading');
    if (manualWeightInput.checked) {
        readingInput.readOnly = false;
        readingInput.value = '';
        readingInput.focus();
    } else {
        readingInput.readOnly = true;
        readingInput.value = '';
    }
}

$(document).ready(function () {
    $('.form-prevent-multiple-submits').on('submit', function(){
        $(".btn-prevent-multiple-submits").attr('disabled', true);
    });
});

</script>
@endsection