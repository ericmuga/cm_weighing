@extends('layouts.slaughter_master')

@section('content')

<div id="slaughter_entries" class="">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    Slaughter Data Report | 
                    <span id="subtext-h1-title"><small>showing all entries</small></span>
                    <span id="last-refresh" class="badge badge-info ml-2" style="font-size: 10px;">
                        Auto-refresh: 5s
                    </span>
                </h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <table id="farmersView" class="table table-striped table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Agg No</th>
                            <th>Receipt No</th>
                            <th>Item Code</th>
                            <th>Description</th>
                            <th>Vendor No</th>
                            <th>Vendor Name</th>
                            <th>Settlement Weight</th>
                            <th>Classification</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($slaughter_data as $index => $data)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $data->agg_no }}</td>
                            <td><a href="#">{{ $data->receipt_no }}</a></td>
                            <td>{{ $data->item_code }}</td>
                            <td>{{ $data->description }}</td>
                            <td>{{ $data->vendor_no }}</td>
                            <td>{{ $data->vendor_name }}</td>
                            <td>{{ number_format($data->settlement_weight, 2) }}</td>
                            <td>{{ $data->classification_code }}</td>
                            <td>{{ $helpers->shortDateTime($data->created_at) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <!-- /.card -->
    </div>
    <!-- /.col -->
</div>
<!--End weigh Table-->
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        console.log("Page loaded - Initializing DataTable...");

        // Check if DataTable is already initialized
        if ($.fn.DataTable.isDataTable('#farmersView')) {
            console.log("DataTable already initialized, destroying first...");
            $('#farmersView').DataTable().destroy();
        }

        // Initialize DataTable ONCE
        let table = $('#farmersView').DataTable({
            "order": [[0, "asc"]],
            "pageLength": 25,
            "responsive": true,
            "autoWidth": false
        });

        console.log("DataTable initialized successfully");

        function refreshTableData() {
            console.log('Refreshing table data at:', new Date().toLocaleTimeString());
            
            $.ajax({
                url: '{{ route("farmers_view_data") }}',
                method: 'GET',
                dataType: 'json',
                cache: false,
                headers: {
                    'Cache-Control': 'no-cache, no-store, must-revalidate',
                    'Pragma': 'no-cache'
                },
                success: function(response) {
                    console.log('========== REFRESH DATA ==========');
                    console.log('Server timestamp:', response.timestamp);
                    console.log('Received rows from server:', response.count);
                    console.log('Current table rows (before update):', table.rows().count());

                    // Clear existing data from DataTable
                    table.clear();

                    // Add new rows to DataTable
                    $.each(response.data, function(index, data) {
                        table.row.add([
                            index + 1,
                            data.agg_no || '',
                            '<a href="#">' + (data.receipt_no || '') + '</a>',
                            data.item_code || '',
                            data.description || '',
                            data.vendor_no || '',
                            data.vendor_name || '',
                            parseFloat(data.settlement_weight || 0).toFixed(2),
                            data.classification_code || '',
                            data.created_at_formatted || ''
                        ]);
                    });

                    // Redraw the table with new data
                    table.draw();

                    console.log('Table updated. New row count:', table.rows().count());
                    console.log('==================================');

                    $('#last-refresh').text('Last: ' + new Date().toLocaleTimeString() + ' (' + response.count + ' rows)');
                },
                error: function(xhr, status, error) {
                    console.error("Refresh error:", {
                        status: status,
                        error: error,
                        responseText: xhr.responseText
                    });
                }
            });
        }

        // Auto refresh every 30 seconds
        setInterval(refreshTableData, 5000);

        console.log('Auto-refresh interval set (5 seconds)');
    });
</script>
@endsection


