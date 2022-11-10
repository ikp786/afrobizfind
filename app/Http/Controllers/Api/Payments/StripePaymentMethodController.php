<?php

namespace App\Http\Controllers\Api\Payments;

use App\Http\Controllers\Controller;
use App\Models\StripePaymentMethods;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class StripePaymentMethodController extends Controller
{
    public function index()
    {
        try {

            $stripePaymentMethods = StripePaymentMethods::where('user_id', Auth::user()->id)
                ->where('is_attached', 1)
                ->select(
                    'id',
                    'last_four_digits',
                    'payment_method_id',
                    'country',
                    'card_brand',
                    'type',
                    'code',
                    'expiry_month',
                    'expiry_year',
                )
                ->get();


            return response()->json([
                'result' => 1,
                'message' => 'Payment methods found.',
                'data' => [
                    'payment_methods' => $stripePaymentMethods
                ]
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'result' => 0,
                'message' => $th->getMessage()
            ], 422);
        }
    }

    public function storePaymentMethod(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'last_four_digits' => 'numeric|nullable',
            'is_attached' => 'required',
            'payment_method_responce' => 'required|json',
            'customer_attach_responce' => 'required|json',
        ]);

        if ($validator->fails()) {
            return response()->json(['result' => 0, 'message' => "Validation error", 'errors' => $validator->errors()->messages()]);
        }

        try {
            if (@Auth::user()->id) {
                $stripePaymentMethod = new StripePaymentMethods();
                $stripePaymentMethod->user_id = Auth::user()->id;
                $stripePaymentMethod->last_four_digits = (@$request->last_four_digits) ? $request->last_four_digits : null;
                $stripePaymentMethod->is_attached = $request->is_attached;
                $stripePaymentMethod->payment_method_responce = @($request->payment_method_responce) ? ($request->payment_method_responce) : null;
                $stripePaymentMethod->customer_attach_responce = @($request->customer_attach_responce) ? ($request->customer_attach_responce) : null;
                $stripePaymentMethod->payment_method_id = (@$request->payment_method_id) ? $request->payment_method_id : null;
                $stripePaymentMethod->country = (@$request->country) ? $request->country : null;
                $stripePaymentMethod->card_brand = (@$request->card_brand) ? $request->card_brand : null;
                $stripePaymentMethod->type = (@$request->type) ? $request->type : null;
                $stripePaymentMethod->code = (@$request->code) ? $request->code : null;
                $stripePaymentMethod->expiry_month = (@$request->expiry_month) ? $request->expiry_month : null;
                $stripePaymentMethod->expiry_year = (@$request->expiry_year) ? $request->expiry_year : null;

                $conf = $stripePaymentMethod->save();
                if ($conf) {
                    return response()->json([
                        'result' => 1,
                        'message' => 'Payment method added.'
                    ], 201);
                }
                return response()->json([
                    'result' => 0,
                    'message' => 'Something went wrong.'
                ], 422);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'result' => 0,
                'message' => $th->getMessage()
            ], 422);
        }
    }

    public function getCustomer()
    {
        try {

            if (@Auth::user()->id) {
                $user = User::with('stripePaymentMethods')->where('id', Auth::user()->id)->first();
                return $user;
                if ($user) {
                    $stripeCustomer = $user->createOrGetStripeCustomer();
                    $stripeCustomer = $user->updateStripeCustomer([
                        "name" => $user->first_name . ' - ' . $user->email
                    ]);
                    return response()->json([
                        'result' => 1,
                        'message' => 'Customer Found.',
                        'data' => [
                            'customer' => $stripeCustomer
                        ]
                    ], 200);
                }
                return response()->json([
                    'result' => 0,
                    'message' => 'Something went wrong.'
                ], 422);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'result' => 0,
                'message' => $th->getMessage()
            ], 422);
        }
    }

    public function deletePaymentMethod($id)
    {
        try {

            $stripePaymentMethod = StripePaymentMethods::where('id', $id)->first();
            if ($stripePaymentMethod) {
                $conf = $stripePaymentMethod->delete();
                if ($conf) {
                    return response()->json([
                        'result' => 1,
                        'message' => 'Payment method deleted successfully.',
                    ], 200);
                }
            }
            return response()->json([
                'result' => 0,
                'message' => 'Something went wrong.'
            ], 422);
        } catch (\Throwable $th) {
            return response()->json([
                'result' => 0,
                'message' => $th->getMessage()
            ], 422);
        }
    }
}
