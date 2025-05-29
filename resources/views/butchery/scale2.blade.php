@extends('layouts.butchery_master')

@section('content')

<div class="container-fluid">
    <div class="card">
        <h2 class="card-header">Butchery Scale 2 Weighings| Sides</h2>
        <div class="card-body">
            <form id="form-weigh-offals" action="{{ route('butchery_scale2_save') }}"
                class="form-prevent-multiple-submits" method="POST">
                @csrf
                <div class="row text-center">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="customer_id">No. of Sides</label>
                            <select class="form-control select2" id="side_count" name="side_count" required>
                                <option selected value="1"
                                    {{ old('side_count') == '1' ? 'selected' : 'selected' }}>
                                    1</option>
                                <option value="2"
                                    {{ old('side_count') == '2' ? 'selected' : '' }}>
                                    2</option>
                                <option value="3"
                                    {{ old('side_count') == '3' ? 'selected' : '' }}>
                                    3</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="reading">Scale Reading</label>
                            <input type="number" step="0.01" class="form-control" id="reading" name="reading" value=""
                                oninput="updateNetWeight()" placeholder="" readonly required>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="manual_weight" name="manual_weight"
                                onchange="toggleManualWeight()">
                            <label class="form-check-label" for="manual_weight">Enter Manual weight</label>
                        </div>
                        <div>
                            @if(count($configs) == 0)
                                <small class="d-block">No comport configured</small>
                            @else
                                @if(isset($configs[0]))
                                    <small class="d-block mt-2">
                                        <label>Reading from ComPort:</label>
                                        <span id="comport_value" disabled>{{ $configs[0]->comport }}</span>
                                    </small>
                                @endif
                            @endif
                            <button type="button" onclick="getScaleReading()" class="btn btn-primary btn-lg">
                                <i class="fas fa-balance-scale"></i> Weigh
                            </button>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="tare_weight">Tare-Weight</label>
                            <select name="tare_weight" id="tare_weight" class="form-control"
                                onchange="updateNetWeight()">
                                <option disabled value="0"
                                    {{ old('tare_weight') ? '' : 'selected' }}>
                                    Select Tare-Weight</option>
                                <option value="1.5"
                                    {{ old('tare_weight') == '1.5' ? 'selected' : '' }}>
                                    Hook 1.5kg</option>
                                <option value="1.7"
                                    {{ old('tare_weight') == '1.7' ? 'selected' : '' }}>
                                    Hook 1.7kg</option>
                                <option value="2.1"
                                    {{ old('tare_weight') == '2.1' ? 'selected' : '' }}>
                                    Hook 2.1kgs</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="net_weight">Net-Weight</label>
                            <input type="number" class="form-control" id="net_weight" name="net_weight" value=""
                                readonly required>
                        </div>
                        <button type="submit" id="btn_save"
                            class="btn btn-primary btn-lg btn-prevent-multiple-submits mt-3"
                            onclick="return validateOnSubmit()">
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

<!-- Button show table -->
<button class="btn btn-primary" id="toggleEntriesButton" onclick="toggleEntries()">
    <i class="fa fa-minus" id="toggleEntriesIcon"></i>
    Entries
</button>

<script>
    function toggleEntries() {
        const entriesDiv = document.getElementById('entries');
        const toggleIcon = document.getElementById('toggleEntriesIcon');
    
        if (entriesDiv.style.display === 'none') {
            entriesDiv.style.display = 'block';
            toggleIcon.classList.remove('fa-plus');
            toggleIcon.classList.add('fa-minus');
        } else {
            entriesDiv.style.display = 'none';
            toggleIcon.classList.remove('fa-minus');
            toggleIcon.classList.add('fa-plus');
        }
    }
</script>

<!-- Table of saved entries -->
<div id="entries" class="my-4" style="display: block;">

    <!-- offals data Table-->
    <div class="card">
        <!-- /.card-header -->
        <div class="card-header">
            <h3 class="card-title"> Weighed Entries | <span id="subtext-h1-title"><small> view weighed ordered
                        by latest</small> </span></h3>
        </div>
        <!-- /.card-body -->
        <div class="card-body table-responsive">
            <table id="example3" class="table table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product Code</th>
                        <th>Product Name</th>
                        <th>No. of Sides</th>
                        <th>Scale Reading (kgs)</th>
                        <th>Net Weight (kgs)</th>
                        <th>Manually Recorded</th>
                        <th>Recorded by</th>
                        <th>Recorded DateTime</th>
                        <!-- <th class="no-export no-sort">Action</th> -->
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th>#</th>
                        <th>Product Code</th>
                        <th>Product Name</th>
                        <th>No. of Sides</th>
                        <th>Scale Reading (kgs)</th>
                        <th>Net Weight (kgs)</th>
                        <th>Manually Recorded</th>
                        <th>Recorded by</th>
                        <th>Recorded DateTime</th>
                        <!-- <th class="no-export no-sort">Action</th> -->
                    </tr>
                </tfoot>
                <tbody>
                    @foreach ($data as $d)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $d->product_code }}</td>
                            <td>Steer - High Grade Carcass</td>
                            <td>{{ $d->carcass_count }}</td>
                            <td>{{ number_format($d->scale_reading, 2) }}</td>
                            <td>{{ number_format($d->tareweight, 2) }}</td>
                            <td>{{ number_format($d->netweight, 2) }}</td>
                            @if ($d->is_manual == 1)
                                <td><span class="badge badge-warning">Yes</span></td>
                            @else
                                <td><span class="badge badge-success">No</span></td>
                            @endif
                            <td>{{ $helpers->shortDateTime($d->created_at) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal for editing weight entry -->
<div class="modal fade" id="editWeightEntry" tabindex="-1" role="dialog" aria-labelledby="editWeightEntryLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('offals_update') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="editWeightEntryLabel">Edit Weight Entry</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </form>
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

        console.log('Tare Weight:', tareweight);
        
        if (tareweight <= 0) {
            netWeightInput.value = '';
            return;
        }

        netWeightInput.value = (parseFloat(reading) - parseFloat(tareweight)).toFixed(2);
    }

    function validateOnSubmit() {
        var netWeight = document.getElementById('net_weight').value;
        if (parseFloat(netWeight) <= 0 || isNaN(netWeight) || netWeight == '') {
            alert('Net weight must be greater than zero.');
            return false;
        }
        return true;
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

    function updateEditValues(event) {
        var button = event.currentTarget;
        var id = button.getAttribute('data-id');
        var customer = button.getAttribute('data-customer');
        var product = button.getAttribute('data-product');
        var reading = button.getAttribute('data-reading');
        var net_weight = button.getAttribute('data-net_weight');
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_customer').value = customer;
        document.getElementById('edit_product').value = product;
        document.getElementById('edit_reading').value = reading;
        document.getElementById('edit_net_weight').value = net_weight;
    }

    $(document).ready(function () {
        $('.form-prevent-multiple-submits').on('submit', function () {
            $(".btn-prevent-multiple-submits").attr('disabled', true);
        });
    });

</script>
@endsection
