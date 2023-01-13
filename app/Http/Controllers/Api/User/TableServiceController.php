<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\TableService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class TableServiceController extends Controller
{

    public function getStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id'     => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'result' => 0,
                'message' => "Validation error",
                'errors' => $validator->errors()->messages()
            ], 422);
        }

        $company = Company::where('id', $request->company_id)->first();

        if ($company) {
            $status = $company->table_service_status;
            if ($status == null) {
                $status = 0;
            }

            return response()->json([
                'result' => 1,
                'message' => 'Table service status found.',
                'data' => [
                    'table_service_status' => $status
                ]
            ], 200);
        } else {
            return response()->json([
                'result' => 0,
                'message' => 'Compnay not found',
                'data' => []
            ], 422);
        }
    }

    public function statusChange(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id'     => 'required',
            'status' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'result' => 0,
                'message' => "Validation error",
                'errors' => $validator->errors()->messages()
            ], 422);
        }

        $company = Company::where('id', $request->company_id)->first();

        if ($company) {
            $company->table_service_status = $request->status;
            $company->save();

            return response()->json([
                'result' => 1,
                'message' => 'Table service status changed successfully.',
                'data' => $company
            ], 200);
        } else {
            return response()->json([
                'result' => 0,
                'message' => 'Compnay not found',
                'data' => []
            ], 422);
        }
    }

    public function addRange(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'company_id' => 'required',
                'to' => 'required|integer',
                'from' => 'required|integer',
                'password' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'result' => 0,
                    'message' => "Validation error",
                    'errors' => $validator->errors()->messages()
                ], 422);
            }

            // Password varification
            if (!Hash::check($request->password, Auth::user()->password)) {
                return response()->json(['result' => 0, 'message' => 'Invalid password']);
            }

            $company_id = $request->company_id;
            $to = $request->to;
            $from = $request->from;

            $tableSevices = TableService::where('company_id', $company_id)->delete('id');

            if (($to < 1 || $from < 1)) {

                return response()->json([
                    'result' => 1,
                    'message' => "Table services removed successfully.",
                ], 201);
            } else {

                for ($i = $from; $i <= $to; $i++) {
                    $tableSevice = new TableService();
                    $tableSevice->table_number = $i;
                    $tableSevice->company_id = $company_id;
                    $tableSevice->active = 1;
                    $tableSevice->save();
                }

                return response()->json([
                    'result' => 1,
                    'message' => "Table services created successfully.",
                ], 201);
            }
        } catch (\Throwable $th) {
            throw $th;
            logger('addRange Error :: ' . $th->getMessage());
            if ($validator->fails()) {
                return response()->json([
                    'result' => 0,
                    'message' => "Internal Server Error",
                    'errors' => $th->getMessage()
                ], 500);
            }
        }
    }

    public function getTableServices(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required|integer',
            'only_active' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'result' => 0,
                'message' => "Validation error",
                'errors' => $validator->errors()->messages()
            ], 422);
        }

        $company_id = $request->company_id;
        $only_active = $request->only_active;

        if ($only_active) {
            $tableSevices = TableService::where('company_id', $company_id)
                ->where('active', 1)
                ->select('id', 'table_number', 'company_id', 'active')
                ->get();
        } else {
            $tableSevices = TableService::where('company_id', $company_id)
                ->select('id', 'table_number', 'company_id', 'active')
                ->get();
        }

        if (count($tableSevices) > 0) {
            return response()->json([
                'result' => 1,
                'message' => 'Table services found for this comapany.',
                'data' => $tableSevices
            ], 200);
        }
        return response()->json([
            'result' => 0,
            'message' => 'Table services not found for this comapany',
            'data' => []
        ], 200);
    }

    public function tableStatusChange(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required|integer',
            'table_number' => 'required|integer',
            'status' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'result' => 0,
                'message' => "Validation error",
                'errors' => $validator->errors()->messages()
            ], 422);
        }

        $tableSevice = TableService::where('company_id', $request->company_id)
            ->where('table_number', $request->table_number)
            ->first();

        if ($tableSevice) {

            $tableSevice->active = $request->status;
            $conf = $tableSevice->save();

            if ($conf) {
                return response()->json([
                    'result' => 1,
                    'message' => 'Table service status changed.',
                    'data' => $tableSevice
                ], 200);
            } else {
                return response()->json([
                    'result' => 0,
                    'message' => 'Something went wrong.',
                    'data' => []
                ], 422);
            }
        }
        return response()->json([
            'result' => 0,
            'message' => 'Table services not found.',
            'data' => []
        ], 422);
    }
}
