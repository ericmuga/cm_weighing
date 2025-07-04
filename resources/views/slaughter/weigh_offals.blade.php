@extends('layouts.slaughter_master')

@section('styles')
<style>
    .no-input {
        border: none;
        background-color: transparent;
    }
    .no-input:focus {
        outline: none;
    }
</style>
@endsection

@section('content')

<div class="container">
    <div class="card">
        <h2 class="card-header">Weigh Offals</h2>
        <div class="card-body">
            <form id="form-weigh-offals" action="{{ route('offals_save') }}" class="form-prevent-multiple-submits" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="customer_id">Customer</label>
                            <select class="form-control select2" id="customer_id" name="customer_id" required>
                                <option {{ old('customer_id') ? '' : 'selected' }} disabled value="">Select Customer</option>
                                @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="customer">Product</label>
                            <select class="form-control select2" id="product_code" name="product_code" required>
                                <option selected disabled value="">Select... </option>
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
                            @if(count($configs) == 0)
                                <small class="d-block">No comport configured</small>
                            @else
                            @if(isset($configs[0]))
                            <small class="d-block mt-2">
                                <label>Reading from ComPort:</label>
                                <span id="comport_value" disabled >{{ $configs[0]->comport }}</span>
                            </small>
                            @endif
                            @endif
                            <button type="button" onclick="getScaleReading()" class="btn btn-primary btn-lg">
                                <i class="fas fa-balance-scale"></i> Weigh
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="tare_weight">Tare-Weight</label>
                            <select name="tare_weight" id="tare_weight" class="form-control" onchange="updateNetWeight()">
                                <option disabled value="0" {{ old('tare_weight') ? '' : 'selected' }} >Select Tare-Weight</option>
                                <option value="2" {{ old('tare_weight') == '2' ? 'selected' : '' }}>Crate 2kg</option>
                                <option value="1.8" {{ old('tare_weight') == '1.8' ? 'selected' : '' }}>Crate 1.8kg</option>
                                <option value="0.1" {{ old('tare_weight') == '0.1' ? 'selected' : '' }}>Hook 100 grams</option>
                                <option value="1.8" {{ old('tare_weight') == '1.8' ? 'selected' : '' }}>Bucket 1.8 kg</option>
                            </select>
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

    <!-- Button show table -->
    <button class="btn btn-primary " data-toggle="collapse" data-target="#entries" class="my-4">
        <i class="fa fa-plus"></i>
        Entries
    </button>

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

            @if(count($entryCustomers) > 0)
            <button type="button" id="publishBtn" class="btn btn-primary mb-2" data-toggle="modal" data-target="#confirmPublishModal">
                Push For Invoicing
            </button>
            @endif

            <table id="example1" class="table table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product Code</th>
                        <th>Product Name</th>
                        <th>Net Weight (kgs)</th>
                        <th>Scale Reading (kgs)</th>
                        <th>Manually Recorded</th>
                        <th>Customer</th>
                        <th>Recorded by</th>
                        <th>Recorded DateTime</th>
                        <th class="no-export no-sort">Action</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th>#</th>
                        <th>Product Code</th>
                        <th>Product Name</th>
                        <th>Net Weight (kgs)</th>
                        <th>Scale Reading (kgs)</th>
                        <th>Manually Recorded</th>
                        <th>Customer</th>
                        <th>Recorded by</th>
                        <th>Recorded DateTime</th>
                        <th class="no-export no-sort">Action</th>
                    </tr>
                </tfoot>
                <tbody>
                    @foreach($entries as $entry)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $entry->product_code }}</td>
                        <td>{{ $entry->product_name }}</td>
                        <td>{{ number_format($entry->net_weight, 2) }}</td>
                        <td>{{ number_format($entry->scale_reading, 2) }}</td>
                        @if($entry->is_manual == 0)
                            <td>
                                <span class="badge badge-success">No</span>
                            </td>
                        @else
                            <td>
                                <span class="badge badge-warning">Yes</span>
                            </td>
                        @endif
                        <td>{{ $entry->customer_name }}</td>
                        <td>{{ $entry->username }}</td>
                        <td>{{  $helpers->shortDateTime($entry->created_at) }}</td>
                        <td class="no-sort no-export">
                            @if($entry->published == 0)
                            <button
                                type="button"
                                data-toggle="modal"
                                data-target="#editWeightEntry"
                                class="btn btn-primary btn-sm"
                                data-id="{{ $entry->id }}"
                                data-customer="{{ $entry->customer_id }}"
                                data-product="{{ $entry->product_name }}"
                                data-reading="{{ $entry->scale_reading }}"
                                data-net_weight="{{ $entry->net_weight }}"
                                onclick="updateEditValues(event)"
                            >
                                <i class="fa fa-edit"></i> Edit </button>
                            <button
                                type="button"
                                data-toggle="modal"
                                data-target="#archiveWeightEntry"
                                class="btn btn-danger btn-sm m-1"
                                data-id="{{ $entry->id }}"
                                onclick="updateArchiveId(event)"
                            >
                                <i class="fa fa-trash"></i> Delete</button>
                            @else
                            <span class="badge badge-warning">published</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </form>
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
                    <input type="hidden" name="id" id="edit_id">
                    <div class="form-group">
                        <label for="customer">Customer</label>
                        <select class="form-control" id="edit_customer" name="customer_id" required>
                            <option disabled value="">Select Customer</option>
                            @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="customer">Product</label>
                        <input type="text" class="form-control" id="edit_product" name="product" readonly>
                    </div>
                    <div class="form-group">
                        <label for="reading">Scale Reading</label>
                        <input type="number" step="0.01" class="form-control" id="edit_reading" name="reading" readonly>
                    </div>
                    <div class="form-group">
                        <label for="edit_net_weight">Net-Weight</label>
                        <input type="number" class="form-control" id="edit_net_weight" name="edit_net_weight" readonly>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- Modal for deleting weight entry -->
<div class="modal fade" id="archiveWeightEntry" tabindex="-1" role="dialog" aria-labelledby="archiveWeightEntryLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('offals_archive') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="archiveWeightEntryLabel">Delete Weight Entry</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="archive_id">
                    <p>Are you sure you want to delete this entry?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" role="dialog" aria-labelledby="loadingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <p id="loading-text" class="mt-3">Please wait...</p>
            </div>
        </div>
    </div>
</div>

<!-- Modal for publishing offals to queue -->
<div class="modal fade" id="confirmPublishModal" tabindex="-1" role="dialog" aria-labelledby="confirmPublishModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form action="{{ route('offals_publish') }}" class="modal-content" method="POST" onsubmit="publishOffals(event)">
            <div class="modal-header">
                <h4 class="modal-title" id="confirmPublishModalLabel">Confirm Push for Invoicing</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-3">
               <div class="form-group">
                    <label for="customer">Customer</label>
                    <select class="form-control" id="publish_customer" name="customer_id" onchange="showCustomerEntries(event)" required>
                        <option disabled selected value="">Select Customer</option>
                        @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                        @endforeach
                    </select>
                </div>

                <table class="table table-bordered table-hover overflow-auto">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product Code</th>
                            <th>Product Name</th>
                            <th>Cummulative Net Weight (kgs)</th>
                        </tr>
                    </thead>
                    <tbody id="selectedEntriesTableBody">
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" id="confirmPublishButton" disabled>Confirm</button>
            </div>
        </form>
    </div>
</div>

<script>

</script>
        

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

function updateArchiveId(event) {
    var button = event.currentTarget;
    var id = button.getAttribute('data-id');
    document.getElementById('archive_id').value = id;
}

function showCustomerEntries(event) {
    const input = event.currentTarget;
    customerId = input.value

    // Group entries by product_code and sum net_weight per product_code for the selected customer and unpublished entries
    var rawOffals = @json($entries)
        .filter((entry) => entry.customer_id == customerId)
        .filter((entry) => entry.published == 0);

    // Aggregate by product_code
    var offalsMap = {};
    rawOffals.forEach(function(entry) {
        if (!offalsMap[entry.product_code]) {
            offalsMap[entry.product_code] = {
                product_code: entry.product_code,
                product_name: entry.product_name,
                net_weight: 0
            };
        }
        offalsMap[entry.product_code].net_weight += Number(entry.net_weight);
    });

    // Convert map to array
    var offals = Object.values(offalsMap);

    console.log(offals);

    const tableBody = document.getElementById('selectedEntriesTableBody');
    while (tableBody.firstChild) {
        tableBody.removeChild(tableBody.firstChild);
    }

    const confirmButton = document.getElementById('confirmPublishButton');

    if (offals.length == 0) {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td colspan="4" class="text-center">No entries for this customer not pushed for invoicing</td>
        `;
        tableBody.appendChild(row);

        confirmButton.disabled = true;
    } else {
        offals.forEach((entry, index) => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${index + 1}</td>
                <td>${entry.product_code}</td>
                <td>${entry.product_name}</td>
                <td>${Number(entry.net_weight).toFixed(2)}</td>
            `;
            tableBody.appendChild(row);
        });
        const totalWeight = offals.reduce((total, entry) => total + Number(entry.net_weight), 0);
        const totalRow = document.createElement('tr');
        totalRow.innerHTML = `
            <td colspan="3" class="text-right font-weight-bold">Total Weight</td>
            <td class="font-weight-bold">${totalWeight.toFixed(2)}</td>
        `;
        tableBody.appendChild(totalRow);

        confirmButton.disabled = false;
    }

}


$('#confirmPublishModal').on('hidden.bs.modal', function () {
    const tableBody = document.getElementById('selectedEntriesTableBody');
    while (tableBody.firstChild) {
        tableBody.removeChild(tableBody.firstChild);
    }
});

function publishOffals(event) {
    event.preventDefault();
    // Hide Confirm Publish Modal
    $('#confirmPublishModal').modal('hide');

    // Show loading modal
    const loadingText = document.getElementById('loading-text');
    loadingText.textContent = 'Pushing for invoicing...';
    $('#loadingModal').modal('show');


    // Get form data
    const form = event.target;
    const formData = new FormData(form);
    const customerId = formData.get('customer_id');
    const url = form.action;
    const offals = @json($entries).filter((entry) => entry.customer_id == customerId);

    try {
        // Send request to server
        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]')
                    .attr('content'),
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                entries: offals
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                toastr.success('Offal weights pushed for invoicing');
                location.reload();
            } else {
                console.error(data);
                console.log("message error")
                toastr.error(data.message);
            }
        })

    } catch (error) {
        console.log("catch error");
        console.error(error);

        if (error.message) {
            toastr.error(error.message);
        } else {
            toastr.error('Failed to push for invoicing');
        }
    } finally {
        console.log("finally");
        $('#loadingModal').modal('hide');
        return;
    }
}

$(document).ready(function () {
    $('.form-prevent-multiple-submits').on('submit', function(){
        $(".btn-prevent-multiple-submits").attr('disabled', true);
    });
});

</script>
@endsection
