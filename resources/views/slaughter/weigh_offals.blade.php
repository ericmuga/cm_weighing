@extends('layouts.slaughter_master')

@section('content')

<!-- weigh -->

<div class="card m-3">
    <h2 class="card-header">Weigh Offals</h2>
    <div class="card-body">
        <form id="form-weigh-offals" onsubmit="saveWeight()">
            <div class="row text-center">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="product_code">Product Name</label>
                        <select class="custom-select" id="product_code" name="product_code" required>
                            <option value="">Choose...</option>
                            @foreach ($offals_products as $product)
                            <option value="{{ $product->code }}">{{ $product->code }} {{ $product->description }}</option>                                
                            @endforeach
                        </select>    
                    </div>

                    <div class="row">
                        <div class="col-12">
                            @if(empty($configs))
                                <small>No comport conifgured</small>
                            @else
                            <small>
                                <label>Reading from ComPort:</label>
                                <strong>
                                <input 
                                    type="text" style="text-align: center; border:none" id="comport_value" 
                                    value="{{ $configs[0]->comport?? "" }}" disabled
                                    >
                                </strong>
                            </small>
                            @endif
                        </div>
                        <div class="col-12">
                            <button type="button" onclick="getScaleReading()" class="btn btn-primary btn-lg">
                                <i class="fas fa-balance-scale"></i> Weigh</button>
                        </div>
                    </div>
                    
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="reading">Reading</label>
                        <input type="number" step="0.01" class="form-control" id="reading" name="reading" value=""
                            oninput="updateNetWeight()" placeholder="" readonly required>
                    </div>

                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="manual_weight" name="manual_weight" onchange="toggleManualWeight()">
                        <label class="form-check-label" for="manual_weight">Enter Manual weight</label>
                    </div>

                    <div class="row">
                        <div class="col-6">
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
                        <div class="col-6">
                            <div class="form-group">
                                <label for="net_weight">Net-Weight</label>
                                <input type="number" class="form-control" id="net_weight" name="net_weight"
                                    value="" readonly required>
                            </div>
                        </div>
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

<!--End weigh -->
>

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
            url: "{{ url('slaughter/read-scale-api-service') }}",

            data: {
                'comport': comport,

            },
            dataType: 'JSON',
            success: function (data) {
                //console.log(data);

                var obj = JSON.parse(data);
                //console.log(obj.success);

                if (obj.success == true) {
                    var reading = document.getElementById('reading');
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

async function saveWeight() {
    event.preventDefault();
    console.log('saving weight');
    var btn_save = document.getElementById('btn_save');
    btn_save.disabled = true;
    btn_save.innerHTML = 'Saving...';

    scaleInput = document.getElementById('reading');
    if (scaleInput.value == '' || scaleInput.value == 0) {
        toastr.error('Please add offal weight');
        btn_save.disabled = false;
        btn_save.innerHTML = 'Save';
        return;
    }

    var form = document.getElementById('form-weigh-offals');
    const formData = new FormData(form);
    console.log(formData.get('manual_weight'));

    await fetch("{{ route('slaughter_save_offals_weights') }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]')
                .attr('content'),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            manual_weight: formData.get('manual_weight'),
            product_code: formData.get('product_code'),
            net_weight: formData.get('net_weight'),
            scale_reading: formData.get('reading'),
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            toastr.success(data.message);
            form.reset();
            btn_save.disabled = false;
            btn_save.innerHTML = 'Save';
        } else {
            throw new Error(data.message);
        }
    })
    .catch((error) => {
        console.error('Error:', error);
        toastr.error(error.message);
        btn_save.disabled = false;
        btn_save.innerHTML = 'Save';
    });

}
</script>
@endsection