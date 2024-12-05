@extends('layouts.master')

@section('header')
    @include('layouts.headers.router_header')
@endsection

@section('content')
<div>
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h3 class="card-title"> {{ $title }} | <small> ordered by last created</small></h3>
            <button class="btn btn-primary ml-auto" data-toggle="modal" data-target="#customerModal" ><i class="fa fa-plus"></i> Add Customer</button>
        </div>

        <div class="card-body">
            <table id="example1" class="table table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Phone Number</th>
                        <th>KRA PIN</th>
                        <th>Location</th>
                        <th>Last Updated</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Phone Number</th>
                        <th>KRA PIN</th>
                        <th>Location</th>
                        <th>Last Updated</th>
                    </tr>
                </tfoot>
                <tbody>
                    @foreach($entries as $entry)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $entry->name }}</td>
                        <td>{{ $entry->phone_number }}</td>
                        <td>{{ $entry->kra_pin }}</td>
                        <td>{{ $entry->location }}</td>>
                        <td>{{ $helpers->dateToHumanFormat($entry->updated_at) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Add Customer Modal -->
    <div class="modal fade" id="customerModal" tabindex="-1" role="dialog" aria-labelledby="customerModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
        <form class="modal-content" action={{ route('create_customer') }} method="POST">
            @csrf
            <div class="modal-header">
            <h5 class="modal-title" id="customerModalLabel">Add Customer</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" class="form-control" id="name" name="name" required />
                </div>
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="text" class="form-control" id="phone_input" name="phone_number" required />
                </div>
                <div class="form-group">
                    <label for="location">Location</label>
                    <input type="text" class="form-control" id="location" name="location" />
                </div>
                <div class="form-group">
                    <label for="kra_pin">KRA PIN</label>
                    <input type="text" class="form-control" id="kra_pin" name="kra_pin" />
                </div>
            </div>
            <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
        </div>
    </div>
    </div>
@endsection


@section('scripts')
<script>
    $('#customerModal').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });
</script>
@endsection
