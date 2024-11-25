@extends('layouts.stocks_master')

@section('content')
<div>
    <h3 class="card-header">
        Stocks | Issue Transfers
    </h3>
    <form id="transfers-form" class="card-group m-4 text-center" onsubmit="saveTransfer()" action="{{ route('save_transfer') }}">   
        <div class="card p-4">
            <div class="form-group">
                <label for="item_code">Product</label>
                <select class="form-control select2" name="item_code" id="item_code" required>
                    <option value="">Select Product</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->code }}">{{ $product->code }} {{ $product->description }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="batch_no">Batch No.</label>
                <input type="text" class="form-control" name="batch_no" id="batch_no" />
            </div>

            <div class="form-row">
                <div class="form-group col-6">
                    <label for="total_crates">Total Crates</label>
                    <input type="number" class="form-control" id="total_crates" name="total_crates" min="0" oninput="updateTare()" required/>
                </div>
                <div class="form-group col-6">
                    <label for="black_crates">Black Crates</label>
                    <input type="number" class="form-control" id="black_crates" name="black_crates" min="0" oninput="updateTare()" required/>
                </div>
            </div>

            <div class="form-group">
                <label for="narration">Narration.</label>
                <textarea class="form-control" name="narration" id="narration" >
                </textarea>
            </div>
        </div>
        <div class="card p-4">
            <div class="form-group">
                <label for="reading">Scale Reading</label>
                <input type="number" id="reading" name="reading" class="form-control" oninput="getNet()" placeholder="0.00" step='0.01' readonly="true" required/>
            </div>

            <div>
                <input type="checkbox" name="manual_weight" id="manual_weight" onchange="toggleManualWeight()">
                <label for="manual_weight">Enter Manual Weight</label>
            </div>

            <div class="form-row">
                <div class="form-group col-6">
                    <label for="tare_weight">Tare Weight</label>
                    <input type="number" class="form-control" id="tare_weight" name="tare_weight" placeholder="0.00" step='0.01' readonly required/>
                </div>
                <div class="form-group col-6">
                    <label for="net_weight">Net Weight</label>
                    <input type="number" class="form-control" id="net_weight" name="net_weight" placeholder="0.00" step='0.01' readonly required/>
                </div>
            </div>

            <div class="mt-4">
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
                <button type="button" onclick="getScaleReading()" class="btn btn-primary btn-lg">
                    <i class="fas fa-balance-scale"></i> Weigh
                </button>
            </div>
            
        </div>
        <div class="card p-4">

            <div class="form-group">
                <label for="from_location_code">From Location</label>
                <select class="form-control select2" name="from_location_code" id="from_location_code" required>
                    <option value="">Select Transfer from Location</option>
                    <option value="B1020">Slaughter</option>
                    <option value="B1570">Butchery</option>
                    <option value="B3535">Despatch</option>
                </select>
            </div>

            <div class="form-group">
                <label for="to_location_code">To Location</label>
                <select class="form-control select2" name="to_location_code" id="to_location_code" required>
                    <option value="">Select Transfer to Location</option>
                    <option value="B1020">Slaughter</option>
                    <option value="B1570">Butchery</option>
                    <option value="B3535">Despatch</option>
                    <option value="B3535">FCL</option>
                </select>
            </div>

            <button type="submit" id="save-btn" class="btn btn-primary btn-lg align-self-center">
                <i class="fas fa-paper-plane"></i> Save
            </button>
            
        </div>
    </form> 
</div>

<hr>
<button class="btn btn-primary mb-2" data-toggle="collapse" data-target="#entries"><i class="fa fa-plus"></i>
    Entries
</button>

<div id="entries" class="card collapse m-4">
    <div class="card-header">
        <h3 class="card-title"> Transfers History</h3>
    </div>
    <div class="card-body table-responsive">
        <table id="example1"  class="table table-bordered table-striped table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th>Batch No.</th>
                    <th>Scale Reading</th>
                    <th>Net Weight</th>
                    <th>From Location</th>
                    <th>To Location</th>
                    <th>Transfer Type</th>
                    <th>Narration</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transfers as $transfer)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $transfer->item_code }}</td>
                        <td>{{ $transfer->batch_no }}</td>
                        <td>{{ number_format($transfer->scale_reading, 2) }}</td>
                        <td>{{ number_format($transfer->net_weight, 2) }}</td>
                        <td>{{ $transfer->from_location_code }}</td>
                        <td>{{ $transfer->to_location_code }}</td>
                        <td>{{ $transfer->transfer_type }}</td>
                        <td>{{ $transfer->narration }}</td>
                        <td>{{ $helpers->dateToHumanFormat($transfer->created_at) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th>Batch No.</th>
                    <th>Scale Reading</th>
                    <th>Net Weight</th>
                    <th>From Location</th>
                    <th>To Location</th>
                    <th>Transfer Type</th>
                    <th>Narration</th>
                    <th>Created At</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const tareInput = document.getElementById('tare_weight');
    const readingInput = document.getElementById('reading');
    const netInput = document.getElementById('net_weight');

    function updateTare() {
        const totalCrates = document.getElementById('total_crates').value;
        const blackCrates = document.getElementById('black_crates').value;
        tareInput.value = (totalCrates * 1.8) + ( blackCrates * 0.2);
        if (readingInput.value) {
            getNet();
        }
    }

    function toggleManualWeight() {
        const manualWeightInput = document.getElementById('manual_weight');
        if (manualWeightInput.checked) {
            readingInput.readOnly = false;
            readingInput.value = '';
            netInput.value = '';
            readingInput.focus();
        } else {
            readingInput.readOnly = true;
            readingInput.value = '';
            netInput.value = '';
        }
    }

    function getNet() {
        netInput.value = readingInput.value - tareInput.value;
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

    

    function saveTransfer() {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        const url = form.action;
        const saveBtn = document.getElementById('save-btn');
        saveBtn.disabled = true;
        saveBtn.classList.add('disabled');

        try {

            // ensure weight is entered
            if (!formData.get('reading') || !formData.get('net_weight')) {
                throw new Error('Please enter weight');
            }

            // ensure from and to locations are not the same
            if (formData.get('from_location_code') == formData.get('to_location_code')) {
                throw new Error('From and To locations cannot be the same');
            }

            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]')
                        .attr('content'),
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    item_code: formData.get('item_code'),
                    batch_no: formData.get('batch_no'),
                    scale_reading: formData.get('reading'),
                    net_weight: formData.get('net_weight'),
                    from_location_code: formData.get('from_location_code'),
                    to_location_code: formData.get('to_location_code'),
                    transfer_type: formData.get('transfer_type'),
                    narration: formData.get('narration'),
                    manual_weight: formData.get('manual_weight'),
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    toastr.success('Transfer saved successfully');
                    form.reset();
                    location.reload();
                } else {
                    console.error(data);
                    toastr.error(data.message);
                }
            })

        } catch (error) {
            console.error(error);

            if (error.message) {
                toastr.error(error.message);
            } else {
                toastr.error('Failed to save transfer');
            }
        } finally {
            saveBtn.disabled = false;
            saveBtn.classList.remove('disabled');
            return;
        }
    }
</script>
@endsection