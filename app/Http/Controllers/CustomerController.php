<?php

namespace App\Http\Controllers;

use App\Models\Helpers;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Brian2694\Toastr\Facades\Toastr;

class CustomerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function listCustomers(Helpers $helpers) {
        $title = 'Customers';
        $entries = Customer::all();
        return view('customer-list', compact('title', 'entries', 'helpers'));

    }

    public function createCustomer(Request $request) {
        try {
            // Remove the _token and filter out null values
            $data = array_filter($request->except('_token'), function ($value) {
                return !is_null($value);
            });
            Customer::create($data);
            Toastr::success('Customer created successfully', 'Success');
            return redirect()->back();

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            Toastr::error($e->getMessage(), 'Error!');
            return redirect()->back();
        }
    }

    public function updateCustomer(Request $request, $id = null) {
        try {
            // Get the customer
            $customer = Customer::find($request->id);

            // Update the model with new data
            $customer->fill($request->except(['_token', 'id']));

            // Get only the changed fields
            if ($customer->isDirty()) {
                // Save only the changed attributes to the database
                $customer->save();
                Toastr::success('Customer updated successfully', 'Success');
                return redirect()->back();
            } else {
                Toastr::info('No changes made', 'Info');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            Toastr::error($e->getMessage(), 'Error!');
            return redirect()->back();
        }
        
    }
}
