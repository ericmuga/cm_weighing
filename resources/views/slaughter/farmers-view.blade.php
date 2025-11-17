@extends('layouts.slaughter_master')

@section('content')

<div id="slaughter_entries" class="">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Slaughter Data Report| <span id="subtext-h1-title"><small> showing all
                            entries</small> </span></h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <div class="hidden" hidden>{{ $i = 1 }}</div>
                <div class="table-responsive">
                    <table id="farmersView" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Agg No </th>
                                <th>Receipt No.</th>
                                <th>Code</th>
                                <th>Description</th>
                                <th>Vendor No.</th>
                                <th>Vendor Name</th>
                                <!-- <th>Side A</th> -->
                                <!-- <th>Side B</th> -->
                                <!-- <th>Total Weight</th> -->
                                <!-- <th>Total Tareweight</th> -->
                                <!-- <th>Total Net</th> -->
                                <th>Settlement Weight</th>
                                <th>Class Code</th>
                                <th>Slaughter Date</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Agg No </th>
                                <th>Receipt No.</th>
                                <th>Code</th>
                                <th>Description</th>
                                <th>Vendor No.</th>
                                <th>Vendor Name</th>
                                <!-- <th>Side A</th>
                                <th>Side B</th>
                                <th>Total Weight</th>
                                <th>Total Tareweight</th>
                                <th>Total Net</th> -->
                                <th>Settlement Weight</th>
                                <th>Class Code</th>
                                <th>Slaughter Date</th>
                            </tr>
                        </tfoot>
                        <tbody>
                            @foreach($slaughter_data as $data)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $data->agg_no }}</td>
                                <td id="editModalShow" data-id="{{$data->id}}" data-receipt="{{ $data->receipt_no }}"
                                    data-weight1="{{ number_format($data->sideA_weight, 2) }}"
                                    data-weight2="{{ number_format($data->sideB_weight, 2) }}"
                                    data-total="{{number_format($data->total_weight, 2) }}"
                                    data-tare_weight="{{ number_format($data->tare_weight, 1) }}" data-item_code="{{ $data->item_code }}"
                                    data-item_desc="{{ $data->description }}" data-vendor_no="{{ $data->vendor_no }}"
                                    data-net="{{ $data->total_net }}"
                                    data-settlement="{{ number_format($data->settlement_weight, 2) }}"
                                    data-class_code="{{ $data->classification_code }}"
                                    data-vendor_name="{{ $data->vendor_name }}"
                                    data-item_name="{{ $data->vendor_name }}"><a href="#">{{ $data->receipt_no }}</a>
                                </td>
                                <td>{{ $data->item_code }}</td>
                                <td>{{ $data->description }}</td>
                                <td>{{ $data->vendor_no }}</td>
                                <td>{{ $data->vendor_name }}</td>
                                <!-- <td>{{ number_format($data->sideA_weight, 2) }}</td>
                                <td>{{ number_format($data->sideB_weight, 2) }}</td>
                                <td>{{ number_format($data->total_weight, 2) }}</td>
                                @if ($data->item_code == 'BG1900' || $data->item_code == 'BG1202')
                                    <td>{{ number_format($data->tare_weight, 2) }}</td>                                    
                                @else
                                    <td>{{ number_format(($data->tare_weight * 2), 2) }}</td>    
                                @endif
                                <td>{{ number_format($data->total_net, 2) }}</td> -->
                                <td>{{ number_format($data->settlement_weight, 2) }}</td>
                                <td>{{ $data->classification_code }}</td>
                                <td>{{ $helpers->shortDateTime($data->created_at) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- /.card-body -->
        </div>
        <!-- /.card -->
    </div>
    <!-- /.col -->
</div>
<!--End weigh Table-->
@endsection