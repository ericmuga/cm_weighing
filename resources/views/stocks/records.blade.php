@extends('layouts.transfers_master')

@section('content')

<h3 class="card-header">
    Stocks | Create Stock Take Entries
</h3>
<form id="stock-form" class="card-group m-4 text-center" onsubmit="saveStocks()" action="{{ route('stock_update') }}">   
    <div class="card p-4">
        <div class="form-group">
            <label for="item_code">Product</label>
            <select class="form-control select2" name="item_code" id="item_code" onchange="updateMeasure()" required>
                <option value="">Select Product</option>
                @foreach ($products as $product)
                    <option value="{{ $product->code }}">{{ $product->code }} {{ $product->description }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="unit_of_measure">Unit of measure</label>
            <input type="text" class="form-control" name="unit_of_measure" id="unit_of_measure" readonly />
        </div>

    </div>
    <div class="card p-4">
        <div class="form-group">
            <label for="reading">Weight</label>
            <input type="number" id="weight-input" name="weight" class="form-control" placeholder="0.00" step='0.01' min="0" required/>
        </div>

        <div class="form-group">
            <label for="reading">Pieces</label>
            <input type="number" id="pieces-input" name="pieces" class="form-control" placeholder="0.00" step='1'/>
        </div>
        
    </div>
    <div class="card p-4">

        <div class="form-group">
            <label for="from_location_code">Stock Take Date</label>
            <input class="form-control" type="date" id="stock_date" name="stock_date" max="today" required/>
        </div>

        <div class="form-group">
            <label for="from_location_code">Location</label>
            <select class="form-control select2" name="location_code" id="location_code" required>
                <option value="">Select Location</option>
                <option value="B1020">Slaughter</option>
                <option value="B1570">Butchery</option>
                <option value="B3535">Despatch</option>
            </select>
        </div>

        <button type="submit" id="save-btn" class="btn btn-primary btn-lg align-self-center">
            <i class="fas fa-paper-plane"></i> Save
        </button>
        
    </div>
</form>
<hr>
<button class="btn btn-primary mb-2" data-toggle="collapse" data-target="#entries"><i class="fa fa-plus"></i>
    Entries
</button>
<div id="entries" class="card collapse m-4">
    <div class="card-header">
        <h3 class="card-title">Stock Take Entries</h3>
    </div>
    <div class="card-body table-responsive">
        <table id="example1" class="table table-striped table-bordered table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product Code</th>
                    <th>Weight</th>
                    <th>Unit of Measure</th>
                    <th>Pieces</th>
                    <th>Stock Location</th>
                    <th>Stock Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($entries as $entry)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $entry->item_code }}</td>
                    <td>{{ $entry->weight }}</td>
                    <td>{{ $entry->unit_of_measure }}</td>
                    <td>{{ $entry->pieces }}</td>
                    <td>{{ $entry->location_code }}</td>
                    <td>{{ $helpers->dateToHumanFormat($entry->stock_date) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th>#</th>
                    <th>Product Code</th>
                    <th>Weight</th>
                    <th>Unit of Measure</th>
                    <th>Pieces</th>
                    <th>Stock Location</th>
                    <th>Stock Date</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

@endsection

@section('scripts')
<script>
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('stock_date').max = today
    let products = @json($products); // Convert PHP array to a JavaScript array

    function updateMeasure() {
        const itemCode = document.getElementById('item_code').value;  
        if (itemCode) {
            document.getElementById('unit_of_measure').value = products.filter(item => item.code === itemCode)[0].unit_of_measure;
        }
    }
    function saveStocks() {
        console.log('Saving stocks');
        event.preventDefault();
        const saveBtn = document.getElementById('save-btn');
        saveBtn.disabled = true;
        saveBtn.classList.add('btn-secondary');
        const form = document.getElementById('stock-form');
        const formData = new FormData(form);

        try {
            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]')
                        .attr('content'),
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    item_code: formData.get('item_code'),
                    unit_of_measure: formData.get('unit_of_measure'),
                    weight: formData.get('weight'),
                    pieces: formData.get('pieces'),
                    stock_date: formData.get('stock_date'),
                    location_code: formData.get('location_code'),
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    toastr.success('Stock saved successfully');
                    form.reset();
                    location.reload();
                } else {
                    console.error(data);
                    toastr.error(data.message || 'Failed to save transfer');
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
            saveBtn.classList.remove('btn-secondary');
            return;
        }
    }
   
</script>
@endsection