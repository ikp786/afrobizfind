<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Api\Controller;
use App\Models\Company;

class CompanyController extends Controller
{

    public function getcompany(Request $request)
    {
        $id = $request->company_id;
        if ($id) {
            $company   = Company::with(['products', 'activeoffers', 'customers', 'category'])->find($id);
            if ($company) {
                return response()->json(['result' => 1, "company" => $company]);
            }
        }
        return response()->json(['result' => 0, 'message' => "Something went wrong"]);
    }
}
