@extends('layouts.butchery_master')

@section('content-header')

<div class="card-header h3 mb-4">
    {{ $title }} | <span id=""><small> showing deboning list for last <strong>{{ $filter ?? '3 days' }}</strong> </small></span>
</div>
@endsection

@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"> Scale 3 Deboned output registry | <span id="subtext-h1-title"><small> entries
                            ordered by
                            latest</small> </span></h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <div class="hidden" hidden>{{ $i = 1 }}</div>
                <div class="table-responsive">
                    <table id="example1" class="table table-striped table-bordered table-hover" width="100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Code </th>
                                <th>product </th>
                                <th>Scale Weight(kgs)</th>
                                <th>Net Weight(kgs)</th>
                                <th>No. of pieces</th>
                                <th>Narration</th>
                                <th>Created At </th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Code </th>
                                <th>product </th>
                                <th>Scale Weight(kgs)</th>
                                <th>Net Weight(kgs)</th>
                                <th>No. of Pieces</th>
                                <th>Narration</th>
                                <th>Created At </th>
                            </tr>
                        </tfoot>
                        <tbody>
                            @foreach($report_data as $data)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    {{-- Allow edits for only today --}}
                                    @php
                                        $createdAtDate = \Carbon\Carbon::parse($data->created_at)->format('Y-m-d');
                                        $todayDate = \Carbon\Carbon::today()->format('Y-m-d');
                                    @endphp

                                    @if($createdAtDate === $todayDate)
                                        <td id="itemCodeModalShow" data-id="{{ $data->id }}"
                                            data-weight="{{ number_format($data->scale_reading, 2) }}"
                                            data-no_of_pieces="{{ $data->no_of_pieces }}"
                                            data-code="{{ $data->product_code }}"   data-narration="{{ $data->narration }}"
                                            data-production_process="{{ $data->process_code }}"
                                            data-item="{{ $data->description }}"><a
                                                href="#">{{ $data->product_code }}</a>
                                        </td>
                                    @else
                                        <td><span class="badge badge-warning">Edit closed</span></td>
                                    @endif

                                    <td>{{ $data->description }}</td>
                                    <td> {{ number_format($data->scale_reading, 2) }}</td>
                                    <td> {{ number_format($data->netweight, 2) }}</td>
                                    <td> {{ $data->no_of_pieces }}</td>
                                    <td> {{ $data->narration }}</td>
                                    <td> {{ $data->created_at }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- /.card-body -->
        </div>
        <!-- /.card -->
        <!-- /.col -->
    </div>
</div>
<!-- slicing ouput data show -->

@endsection

@section('scripts')
<script>

</script>
@endsection
