<div>
    <hr>

    <div class="div">
        <button class="btn btn-primary " data-toggle="collapse" data-target="#entries"><i class="fa fa-plus"></i>
            Entries
        </button>
    </div>

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
                            <th>Recorded by</th>
                            <th>Weigh Date</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>#</th>
                            <th>Product Code</th>
                            <th>Net Weight (kgs)</th>
                            <th>Scale Reading (kgs)</th>
                            <th>Manually Recorded</th>
                            <th>Recorded by</th>
                            <th>Weigh Date</th>
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
                            <td>{{ $entry->username }}</td>
                            <td>{{  $helpers->dateToHumanFormat($entry->created_at) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <!-- /.card-body -->
        </div>
    </div>

</div>
