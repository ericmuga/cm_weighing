@extends('layouts.transfers_master')

@section('content')

<h3 class="card-header">
    Transfers Due for Receipt | <small>Issued in the last 1 week</small>
</h3>
<div class="card m-4">
    <div class="card-body table-responsive">
        <table id="example1"  class="table table-bordered table-striped table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th>Batch No.</th>
                    <th>From Location</th>
                    <th>To Location</th>
                    <th>Narration</th>
                    <th>Created At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transfersDue as $transfer)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $transfer->item_code }}</td>
                        <td>{{ $transfer->batch_no }}</td>
                        <td>{{ $transfer->from_location_code }}</td>
                        <td>{{ $transfer->to_location_code }}</td>
                        <td>{{ $transfer->narration }}</td>
                        <td>{{ $helpers->dateToHumanFormat($transfer->created_at) }}</td>
                        <td>
                            <button
                                type="button"
                                class="btn btn-sm btn-warning"
                                data-toggle="modal"
                                data-target="#transferReceiptModal"
                                data-transfer-id="{{ $transfer->id }}"
                                data-product-name="{{ $transfer->item_code }}"
                                data-batch-no="{{ $transfer->batch_no }}"
                                data-net-weight="{{ $transfer->net_weight }}"
                                onclick="setCurrentTransfer(event)"
                            >
                                <i class="fa fa-download"></i> Receive
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th>Batch No.</th>
                    <th>From Location</th>
                    <th>To Location</th>
                    <th>Narration</th>
                    <th>Created At</th>
                    <th>Action</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<h3 class="card-header">
 Received Transfers | <small>Received in the last 1 week</small>
</h3>
<div class="card m-4">
    <div class="card-body table-responsive">
        <table id="example2"  class="table table-bordered table-striped table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th>Batch No.</th>
                    <th>From Location</th>
                    <th>To Location</th>
                    <th>Narration</th>
                    <th>Issued Weight</th>
                    <th>Received Weight</th>
                    <th>Received Pieces</th>
                    <th>Received Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transfersReceived as $transfer)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $transfer->item_code }}</td>
                        <td>{{ $transfer->batch_no }}</td>
                        <td>{{ $transfer->from_location_code }}</td>
                        <td>{{ $transfer->to_location_code }}</td>
                        <td>{{ $transfer->narration }}</td>
                        <td>{{ number_format($transfer->net_weight, 2) }}</td>
                        <td>{{ number_format($transfer->received_weight, 2) }}</td>
                        <td>{{ $transfer->received_pieces }}</td>
                        <td>{{ $helpers->dateToHumanFormat($transfer->received_date) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th>Batch No.</th>
                    <th>From Location</th>
                    <th>To Location</th>
                    <th>Narration</th>
                    <th>Issued Weight</th>
                    <th>Received Weight</th>
                    <th>Received Pieces</th>
                    <th>Received Date</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<div id="transferReceiptModal" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <form class="modal-content" action="{{ route('transfer_update') }}" onsubmit="receiveTransfer()">
        <div class="modal-header">
          <h5 class="modal-title">Receive Transfer</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div id="receive-transfer-form" class="modal-body">
            <div>
                <p>
                    <strong>Product: </strong> <span id="product-name"></span>
                </p>
                <p>
                    <strong>Batch No.: </strong> <span id="batch-no"></span>
                </p>
            </div>
            <div class="form-group">
                <label for="received_weight">Received Weight</label>
                <input type="number" class="form-control" id="received_weight" name="received_weight" placeholder="0.00" step="0.01" required>
            </div>

            <div class="form-group">
                <label for="received_pieces">Received Pieces</label>
                <input type="number" class="form-control" id="received_pieces" name="received_pieces" placeholder="0.00">
            </div>
            <input type="hidden" id="transfer_id" name="transfer_id" value="">
            <input type="hidden" id="net_weight" name="net_weight" value="">
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
            <button id="save-btn" type="submit" class="btn btn-primary">Recieve</button>
        </div>
    </form>
    </div>
</div>


@endsection

@section('scripts')
<script>
    editingProductName = document.getElementById('product-name');
    editingBatchNo = document.getElementById('batch-no');
    transferIdInput = document.getElementById('transfer_id');
    let editingNetWeight
    setCurrentTransfer = (event) => {
        var btn = event.target;
        var transferId = btn.getAttribute('data-transfer-id');
        var productName = btn.getAttribute('data-product-name');
        var batchNo = btn.getAttribute('data-batch-no');
        editingNetWeight = parseFloat(btn.getAttribute('data-net-weight')).toFixed(2);
        editingProductName.innerHTML = productName;
        editingBatchNo.innerHTML = batchNo;
        transferIdInput.value = transferId;
    }

    function receiveTransfer() {
        console.log('Editing weight: ', editingNetWeight);
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        const url = form.action;
        const saveBtn = document.getElementById('save-btn');
        saveBtn.disabled = true;
        saveBtn.classList.add('disabled');
        saveBtn.innerHTML = 'Saving...';

        try {
            if (editingNetWeight != formData.get('received_weight')) {
                const response = confirm(
                    "Issued weight does not match Receiving Weight. Are you sure you want to continue?"
                );

                if (response) {
                    alert("Thanks for confirming");
                } else {
                    status = false
                    alert("You have cancelled this process");
                    return;
                }
            }

            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]')
                        .attr('content'),
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    transfer_id: formData.get('transfer_id'),
                    received_weight: formData.get('received_weight'),
                    received_pieces: formData.get('received_pieces'),
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    toastr.success('Transfer receipt saved successfully');
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
                toastr.error('Failed to save transfer receipt');
            }
        } finally {
            saveBtn.disabled = false;
            saveBtn.classList.remove('disabled');
            saveBtn.innerHTML = 'Receive';
            return;
        }
    }
</script>
@endsection