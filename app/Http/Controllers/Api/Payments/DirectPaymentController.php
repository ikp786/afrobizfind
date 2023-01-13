<?php

namespace App\Http\Controllers\Api\Payments;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\StripeHelperTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Stripe\StripeClient;

class DirectPaymentController extends Controller
{
    use StripeHelperTrait;
    protected $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(env('STRIPE_SECRET'));
        Stripe::setApiKey(env('STRIPE_SECRET'));
    }


    public function makeAccount(Request $request)
    {

        try {

            $validator = Validator::make($request->all(), [
                'country' => 'required',
                'currency' => 'required',
                'account_number' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['result' => 0, 'message' => "Validation error", 'errors' => $validator->errors()->messages()]);
            }

            $user = User::where('id', Auth::user()->id)->first();
            if ($user) {

                $account = $this->makeConnectedAccount($this->stripe, $user, $request);

                return ($account);
            } else {
                return response()->json([
                    'result' => 0,
                    'message' => 'User not found.'
                ], 500);
            }
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'result' => 0,
                'message' => $th->getMessage()
            ], 422);
        }
    }

    public function transferPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required',
            'currency' => 'required',
            'reciever_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['result' => 0, 'message' => "Validation error", 'errors' => $validator->errors()->messages()]);
        }

        try {

            $user = User::where('id', $request->reciever_id)->first();
            if ($user) {
                $payment_intent = $this->makePaymentIntent($user, $request, 80, $this->stripe);
                return response()->json([
                    'result' => 1,
                    'message' => 'Payment intent created.',
                    'data' => $payment_intent
                ], 200);
            } else {
                return response()->json([
                    'result' => 0,
                    'message' => 'User not found.'
                ], 500);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'result' => 0,
                'message' => $th->getMessage()
            ], 422);
        }
    }
}
