@extends('layouts.master')

@section('header')
    @include('layouts.headers.router_header')
@endsection

@section('content')
<div>
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h3 class="card-title"> {{ $title }} | <small> ordered by last created</small></h3>
            <button class="btn btn-primary ml-auto" data-toggle="modal" data-target="#customerModal" onclick="setModalToCreate(event)"><i class="fa fa-plus"></i> Add Customer</button>
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
                        <th scope="col" class="no-export">Edit</th>
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
                        <th scope="col" class="no-export">Edit</th>
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
                        <td>
                            <button
                                class="btn btn-primary btn-sm"
                                data-id="{{ $entry->id }}"
                                data-name="{{ $entry->name }}"
                                data-phone-number="{{ $entry->phone_number }}"
                                data-kra-pin="{{ $entry->kra_pin }}"
                                data-location="{{ $entry->location }}"
                                data-toggle="modal"
                                data-target="#customerModal"
                                onclick="setModalToUpdate(event)"
                            >
                                <i class="fa fa-pencil-alt"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Add Customer Modal -->
    <div class="modal fade" id="customerModal" tabindex="-1" role="dialog" aria-labelledby="customerModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
        <form id="customer-form" class="modal-content" action={{ route('create_customer') }} method="POST">
            @csrf
            <div class="modal-header">
            <h5 id="customerModalTitle" class="modal-title" id="customerModalLabel">Customer Form</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span>&times;</span>
            </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" class="form-control" id="name" name="name" required />
                </div>
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="text" class="form-control" id="phone_number" name="phone_number" required />
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

    function setModalToCreate(event) {
        var button = event.currentTarget;
        document.getElementById('customerModalTitle').textContent = 'Create Customer';
        document.getElementById('customer-form').setAttribute('action', 'customers/create');
    }

    function setModalToUpdate(event) {
        var button = event.currentTarget;
        var id = button.getAttribute('data-id');
        var name = button.getAttribute('data-name');
        var phoneNumber = button.getAttribute('data-phone-number');
        var kraPin = button.getAttribute('data-kra-pin');
        var location = button.getAttribute('data-location');

        document.getElementById('customerModalTitle').textContent = 'Update Customer';
        document.getElementById('customer-form').setAttribute('action', 'customers/update/' + id);
        document.getElementById('name').value = name;
        document.getElementById('phone_number').value = phoneNumber;
        document.getElementById('kra_pin').value = kraPin;
        document.getElementById('location').value = location;
    }

    
</script>
@endsection
