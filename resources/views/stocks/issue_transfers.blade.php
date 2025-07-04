@extends('layouts.stocks_master')

@section('content')
<div>
    <h3 class="card-header">
        Stocks | Issue Transfers
    </h3>
    <form id="transfers-form" class="card-group m-4 text-center form-prevent-multiple-submits" onsubmit="saveTransfer()" action="{{ route('save_transfer') }}" method="POST">   
        @csrf
        <div class="card p-4">
            <div class="form-group">
                <label for="item_code">Product</label>
                <select class="form-control select2" name="item_code" id="item_code" onchange="updateDescription()" required>
                    <option value="">Select Product</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->code }}">{{ $product->code }} {{ $product->description }}</option>
                    @endforeach
                </select>
            </div>
            <input type="hidden" name="description" id="description" value="">

            <div class="form-group">
                <label for="batch_no">Batch No.</label>
                <input type="text" class="form-control" name="batch_no" id="batch_no" />
            </div>

            <div class="form-row">
                <div class="form-group col-6">
                    <label for="crate_tareweight">Vessel Type</label>
                    <select class="form-control crate-tareweight" onchange="handleVesselTypeChange()" name="crate_tareweight[]">
                        @foreach ($vessels as $vessel)
                            <option 
                                value="{{ $vessel->tare_weight }}" 
                                data-type="{{ stripos($vessel->name, 'van') !== false ? 'van' : 'crate' }}"
                                {{ $loop->first ? 'selected' : '' }}
                            >
                                {{ $vessel->name }}
                            </option>
                        @endforeach
                    </select>
                    <script>
                    function handleVesselTypeChange() {
                        const vesselSelect = document.querySelector('.crate-tareweight');
                        const selectedOption = vesselSelect.options[vesselSelect.selectedIndex];
                        const vesselType = selectedOption.getAttribute('data-type');
                        const totalCratesDiv = document.getElementById('total_crates').closest('.form-group');
                        const blackCratesDiv = document.getElementById('black_crates').closest('.form-group');
                        const totalCratesInput = document.getElementById('total_crates');
                        const blackCratesInput = document.getElementById('black_crates');

                        if (vesselType === 'van') {
                            // Hide or disable crate fields
                            totalCratesDiv.style.display = 'none';
                            blackCratesDiv.style.display = 'none';
                            totalCratesInput.disabled = true;
                            blackCratesInput.disabled = true;

                            // Set tare weight directly from van value
                            document.getElementById('tare_weight').value = parseFloat(vesselSelect.value).toFixed(2);
                            if (document.getElementById('reading').value) {
                                getNet();
                            }
                        } else {
                            // Show and enable crate fields
                            totalCratesDiv.style.display = '';
                            blackCratesDiv.style.display = '';
                            totalCratesInput.disabled = false;
                            blackCratesInput.disabled = false;
                            updateTare();
                        }
                    }

                    // Ensure this runs on page load in case of old value
                    document.addEventListener('DOMContentLoaded', function() {
                        handleVesselTypeChange();
                    });
                    </script>
                </div>
                <div class="form-group col-3">
                    <label for="total_crates">Total Crates</label>
                    <input type="number" class="form-control" id="total_crates" name="total_crates" min="2" value="5" max="8" oninput="updateTare()" required/>
                </div>
                <div class="form-group col-3">
                    <label for="black_crates">Black Crates</label>
                    <input type="number" class="form-control" id="black_crates" name="black_crates" min="0" value="1" max="5" oninput="updateTare()" required/>
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
                    <input type="number" class="form-control" id="tare_weight" name="tare_weight" value="9.00" placeholder="0.00" step='0.01' readonly required/>
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
                <select name="from_location_code" id="from_location_code" class="form-control select2" required>
                    <option disabled value="" {{ old('from_location_code') ? '' : 'selected' }}>Select Transfer from Location</option>
                    @foreach ($transfer_locations as $location)
                        <option value="{{ $location->location_code }}" {{ old('from_location_code') == $location->location_code ? 'selected' : '' }}>
                            {{ $location->name ?? $location->location_code }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="to_location_code">To Location</label>
                <select class="form-control select2" name="to_location_code" id="to_location_code" required onchange="toggleVehicleInput()">
                    <!-- <option value="" {{ old('to_location_code') == '' ? 'selected' : '' }} >Select Transfer to Location</option>
                    <option value="B1020" {{ old('to_location_code') == 'B1020' ? 'selected' : '' }}>Slaughter</option>
                    <option value="B1570" {{ old('to_location_code') == 'B1570' ? 'selected' : '' }}>Butchery</option>
                    <option value="B3535" {{ old('to_location_code') == 'B3535' ? 'selected' : '' }}>Despatch</option>
                    <option value="FCL" {{ old('to_location_code') == 'FCL' ? 'selected' : '' }}>FCL</option> -->

                    <option disabled value="" {{ old('to_location_code') == '' ? 'selected' : '' }}>Select Transfer to Location</option>
                    @foreach ($transfer_locations as $location)
                        <option value="{{ $location->location_code }}" {{ old('to_location_code') == $location->location_code ? 'selected' : '' }}>
                            {{ $location->name ?? $location->location_code }}
                        </option>
                    @endforeach
                    <option value="FCL" {{ old('to_location_code') == 'FCL' ? 'selected' : '' }}>FCL</option>
                </select>
            </div>

            <div id="vehicle-form-group" class="form-group" hidden>
                <label for="vehicle_no">Vehicle No.</label>
                <select class="form-control select2" name="vehicle_no" id="vehicle_no">
                    <option value="" {{ old('vehicle_no') ? '' : 'selected' }} disabled >Select Vehicle</option>
                    <option value="KAQ714R" {{ old('vehicle_no') ? 'KAQ714R' : 'selected' }} >KAQ 714R</option>
                    <option value="KAS004G" {{ old('vehicle_no') == 'KAS004G' ? 'selected' : '' }}>KAS 004G</option>
                    <option value="KCE015W" {{ old('vehicle_no') == 'KCE015W' ? 'selected' : '' }}>KCE 015W</option>
                    <option value="KAX793L" {{ old('vehicle_no') == 'KAX793L' ? 'selected' : '' }}>KAX 793L</option>
                </select>
            </div>

            <button type="submit" id="save-btn" class="btn btn-primary btn-lg align-self-center btn-prevent-multiple-submits">
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
                    <th>IDT No</th>
                    <th>Product Code</th>
                    <th>Product Description</th>
                    <th>Batch No.</th>
                    <th>Scale Reading</th>
                    <th>Net Weight</th>
                    <th>From Location</th>
                    <th>To Location</th>
                    <th>Transfer Type</th>
                    <th>Vehichle No.</th>
                    <th>Narration</th>
                    <th>Issued By</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transfers as $transfer)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $transfer->id }}</td>
                        <td>{{ $transfer->item_code }}</td>
                        <td>{{ $transfer->item_description }}</td>
                        <td>{{ $transfer->batch_no }}</td>
                        <td>{{ number_format($transfer->scale_reading, 2) }}</td>
                        <td>{{ number_format($transfer->net_weight, 2) }}</td>
                        <td>{{ $transfer->from_location_code }}</td>
                        <td>{{ $transfer->to_location_code }}</td>
                        <td>{{ $transfer->transfer_type }}</td>
                        <td>{{ $transfer->vehicle_no ?? 'N/A' }}</td>
                        <td>{{ $transfer->narration }}</td>
                        <td>{{ $transfer->issuer }}</td>
                        <td>{{ \Carbon\Carbon::parse($transfer->created_at)->format('d/m/Y H:i') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th>#</th>
                    <th>IDT No</th>
                    <th>Product Code</th>
                    <th>Product Description</th>
                    <th>Batch No.</th>
                    <th>Scale Reading</th>
                    <th>Net Weight</th>
                    <th>From Location</th>
                    <th>To Location</th>
                    <th>Transfer Type</th>
                    <th>Vehichle No.</th>
                    <th>Narration</th>
                    <th>Issued By</th>
                    <th>Created At</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script>
     $(document).ready(function () {
        $('.form-prevent-multiple-submits').on('submit', function(){
            $(".btn-prevent-multiple-submits").attr('disabled', true);
        });

        updateTare();

        // toggleVehicleInput();
    });

    const tareInput = document.getElementById('tare_weight');
    const readingInput = document.getElementById('reading');
    const netInput = document.getElementById('net_weight');

    function updateTare() {
        const totalCratesInput = document.getElementById('total_crates');
        const blackCrates = document.getElementById('black_crates').value;
        const crateTareweight = document.querySelector('.crate-tareweight').value;
        let totalCrates = parseInt(totalCratesInput.value, 10);

        if (totalCrates < 2) {
            alert('Total Crates cannot be less than 2.');
            // Prevent change: reset to previous valid value or minimum allowed
            totalCratesInput.value = 2;
            totalCrates = 2;
        }

        tareInput.value = ((totalCrates * crateTareweight) + (blackCrates * 0.2)).toFixed(2);
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

    function toggleVehicleInput() {
        const vehichleFormGroup = document.getElementById('vehicle-form-group');
        const vehichleInput = document.getElementById('vehicle_no');
        const sendLocation = document.getElementById('to_location_code').value;
        if (sendLocation == 'FCL') {
            vehichleFormGroup.removeAttribute('hidden');
            vehichleInput.setAttribute('required', true);
        } else {
            vehichleFormGroup.setAttribute('hidden', true);
            vehichleInput.removeAttribute('required');
        }
    }

    function getNet() {
        netInput.value = (readingInput.value - tareInput.value).toFixed(2);
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
        // ensure weight is entered
        if (!formData.get('reading') || !formData.get('net_weight')) {
            throw new Error('Please enter weight');
        }

        // ensure from and to locations are not the same
        if (formData.get('from_location_code') == formData.get('to_location_code')) {
            throw new Error('From and To locations cannot be the same');
        }
    }

    function updateDescription(event) {
        const itemCode = document.getElementById('item_code').value;
        const description = document.getElementById('description');
        description.value = itemCode;
    }
</script>
@endsection