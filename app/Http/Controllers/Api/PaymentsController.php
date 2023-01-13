<?php

namespace App\Http\Controllers\Api;

use Braintree;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\Controller;
use App\Mail\InvoiceMail;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Referral;
use App\Models\User;
use App\Traits\StripeHelperTrait;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Stripe\StripeClient;

class PaymentsController extends Controller
{

    use StripeHelperTrait;

    // public $gateway;
    public $stripe;


    public function __construct()
    {
        // $this->gateway = new Braintree\Gateway([
        //     'environment' => 'sandbox',
        //     'merchantId' => 'ywskmmxn4mt5v5nv',
        //     'publicKey' => 'g84zvqvgp6xrywmb',
        //     'privateKey' => '61dc3e1d6bab92b7b85f4f543f4a8f55'
        // ]);
        $this->stripe = new StripeClient(env('STRIPE_SECRET'));
        Stripe::setApiKey(env('STRIPE_SECRET'));
    }

    public function testStripe()
    {

        $stripe = $this->stripe;

        $user = Auth::user();
        $user = User::where('id', Auth::user()->id)->first();

        $amount = 5;
        $currency = 'EUR';
        $testRecieverAccountId = 'acct_1Lexn8SJE1MRJAjo';
        // $testRecieverAccountId = 'acct_1Lexc0SDuKD9Nk37';

        $stripeCustomer = $user->createOrGetStripeCustomer();
        // $card = $this->makePaymentMethod($stripe);

        // logger(" -------------- Card :: --------------");
        // logger(json_encode($card));
        // dump(($card));

        // $attach = $this->attachPaymentMethodToCustomer($stripe, $card->id, $user->stripe_id);
        // return ($attach);

        // $stripeCustomer = $user->updateStripeCustomer([
        //     "name" => 'Testing User for payments'
        // ]);
        // $user->debitBalance(500, 'Bad usage penalty.');
        // $balance = $user->balance();
        // $paymentMethods = $user->paymentMethods();

        $payment_intent = \Stripe\PaymentIntent::create([
            'amount' => $amount * 100,
            'currency' => $currency,
            'transfer_data' => [
                'amount' => $amount * 80,
                'destination' => $testRecieverAccountId,
            ],
            'use_stripe_sdk' => true,
            'customer' => $stripeCustomer
        ]);
        if ($payment_intent) {
            $conf = $stripe->paymentIntents->confirm(
                $payment_intent->id,
                ['payment_method' => 'pm_card_visa']
            );

            return $conf;
        }

        // $payment_intent = $stripe->transfers->create([
        //     'amount' => $amount * 100,
        //     'currency' => $currency,
        //     'destination' => $testRecieverAccountId,
        //     'transfer_group' => 'ORDER_95',
        // ]);

        return $payment_intent;


        // $paymentMethod = $this->makePaymentMethod($stripe);
        // if (isset($paymentMethod->id)) {
        //     return $this->attachPaymentMethodToCustomer($stripe, $paymentMethod->id, $user->stripe_id);
        // } else {
        //     return $paymentMethod;
        // }


        // return PaymentIntent::create([
        //     'amount' => 1099,
        //     'currency' => 'inr',
        //     'payment_method_types' => ['card'],
        // ]);

        // return (new User)->charge(100,$paymentMethod);

        // --------------------- Make Customer ---------------------

        // $stripeCustomer = $user->createOrGetStripeCustomer();
        $stripeCustomer = $user->updateStripeCustomer([
            "name" => 'Testing User for payments'
        ]);
        $user->debitBalance(500, 'Bad usage penalty.');
        $balance = $user->balance();
        $paymentMethods = $user->paymentMethods();
        return $stripeCustomer;
    }

    public function transfer(Request $request)
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

                $stripe = $this->stripe;

                // $stripe->transfers->create([
                //     'amount' => $request->amount * 100,
                //     'currency' => $request->currency,
                //     'destination' => $user->account_id,
                //     'transfer_group' => 'ORDER_95',
                // ]);

                $payment_intent = \Stripe\PaymentIntent::create([
                    'amount' => $request->amount * 100,
                    'currency' => $request->currency,
                    'transfer_data' => [
                        'amount' => $request->amount * 80,
                        'destination' => $user->account_id,
                    ],
                ]);

                return $payment_intent;
            } else {
                return response()->json([
                    'result' => 0,
                    'message' => 'User not found.'
                ], 500);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function gettoken(Request $request)
    {
        $user = Auth::user();


        if (!$user->braintree_customer_id) {
            $result = $this->gateway->customer()->create([
                'firstName' => $user->first_name,
                'lastName' => $user->surname,
                'email' => $user->email
            ]);
            if ($result->success) {
                $user->braintree_customer_id = $result->customer->id;
                $user->save();
            }
        }
        if ($user->braintree_customer_id) {

            $token = $this->gateway->clientToken()->generate(["customerId" => $user->braintree_customer_id, 'merchantAccountId' => 'Afrobizfind_Wiiliam']);
            return response()->json(['result' => 1, 'token' => $token]);
        }
        return response()->json(['result' => 0, 'message' => 'Something went wrong']);
    }

    public function maketree(Request $request)
    {
        try {

            $user = Auth::user();

            $result = $this->gateway->transaction()->sale([
                'amount' => $request->finalprice,
                'taxAmount' => $request->taxamount,
                'merchantAccountId' => 'Afrobizfind_Wiiliam',
                'customerId' => $user->braintree_customer_id,
                'paymentMethodNonce' => $request->nonce,
                'options' => ['submitForSettlement' => true]
            ]);

            // return response()->json($result);
            // die;
            //$result = (object)['success' => 1,'transaction' => (object)['id'=>1]];
            if ($result->success) {

                $company   = Company::find($request->id);
                $company->paypal_nonce = $request->nonce;
                $today = date('Y-m-d');
                if ($company->expiry_date && $company->expiry_date > $today) {
                    // $today = $company->expiry_date;
                    $today = date('Y-m-d', strtotime($company->expiry_date . ' + 1 days'));
                }
                $expiry_date = date('Y-m-d', strtotime($today . ' + 30 days'));
                $company->expiry_date = $expiry_date;
                $company->save();

                // PDF transaction save and send email
                $company->duration_start = $today;
                $company->duration_end =  $expiry_date;
                // $result->transaction->id
                $filepath = 'company_invoce/' . md5(time() . rand() . time()) . '.pdf';

                $invoice = new Invoice();
                $invoice->user_id = $company->user_id;
                $invoice->company_id = $company->id;
                $invoice->braintree_transaction_id = $result->transaction->id;
                $invoice->start_date = $today;
                $invoice->end_date = $expiry_date;
                $invoice->pdf = $filepath;
                $invoice->save();
                $filepath = public_path($filepath);
                $pdf = Pdf::loadView('emails.invoice', ['company' => $company])->save($filepath);


                $referral = Referral::where('company_number', $company->company_number)->first();
                if ($referral) {
                    $count = Invoice::where('company_id', $company->id)->count();
                    $referral->number_of_payments = $count;
                    $referral->first_payment = (new DateTime())->format('d-m-Y');
                    $referral->save();
                }


                Mail::to($company->user->email)->send(new InvoiceMail($company, $filepath));

                return response()->json(['result' => 1, "company" => $company]);
            } else if ($result->transaction) {
                Log::error('payment/make transaction Error Start ======================');
                Log::emergency($request->all());
                Log::emergency($result->transaction->processorResponseCode);
                Log::emergency($result->transaction->processorResponseText);
                Log::error('payment/make transaction Error End ======================');
                return response()->json(['result' => 0, "message" => "transaction Failed. Something went wrong"]);
                /* print_r("Error processing transaction:");
                print_r("\n  code: " . $result->transaction->processorResponseCode);
                print_r("\n  text: " . $result->transaction->processorResponseText);*/
            } else {
                Log::error('payment/make Validation Error Start ======================');
                Log::emergency($request->all());
                Log::emergency($result->errors->deepAll());
                Log::error('payment/make Validation Error End ======================');

                return response()->json(['result' => 0, "message" => "Validation Error. Something went wrong"]);
                /* print_r("Validation errors: \n");
                print_r($result->errors->deepAll());*/
            }
        } catch (Braintree\Exception\Authorization $e) {
            Log::error('payment/make Validation Error Start ======================');
            Log::emergency($request->all());
            Log::emergency('Braintree\Exception\Authorization');
            Log::error('payment/make Validation Error End ======================');
            return response()->json(['result' => 0, "message" => "Authorization Error. Something went wrong"]);
        }
    }
}
