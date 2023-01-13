<?php

namespace App\Http\Controllers\Api;

use App\Models\Company;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    public function getcompanycustomers(Request $request)
    {
        $company  = Company::with('customers')->find($request->id);
        if ($company) {
            //dd($company);
            $customers = $company->customers;
            return response()->json(['result' => 1, "users" => $customers]);
        }
        return response()->json(['result' => 0]);
    }

    public function addtocustomers(Request $request)
    {
        $user = Auth::user();
        $customer = Customer::updateOrCreate(
            ['company_id' => $request->id, 'user_id' => $user->id, 'mobileallowed' => $request->mobileallowed ? 1 : 0],
            ['company_id' => $request->id, 'user_id' => $user->id]
        );

        $lastid = $customer->id;
        return response()->json(['result' => 1, "message" => "Added as customer", 'customerid' => $lastid, 'loginuserid' => $user->id]);
    }

    public function removecustomer(Request $request)
    {
        $user = Auth::user();
        $cus = Customer::where(
            ['company_id' => $request->id, 'user_id' => $user->id]
        )->first();
        if ($cus) {
            $cus->delete();
        }
        return response()->json(['result' => 1, "message" => "Removed from customer"]);
    }
}
