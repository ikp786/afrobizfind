<?php

namespace App\Http\Controllers\Api;

use App\Models\Company;
use App\Models\Currency;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Stripe;
use Throwable;
use Validator;
use PDF;
use Auth;
use URL;
use Session;
use App\Models\User;
use Log;
use App\Models\Invoice;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

class StripeController extends Controller
{

    function payment(Request $request)
    {

        try {
            $error_message =     [
                'company_id.required'             => 'Company Id should be required',
                'company_id.exists'               => 'Company Id not found',
            ];
            $rules = [
                'company_id'                      => 'required|exists:companies,id',
            ];

            $validator = Validator::make($request->all(), $rules, $error_message);

            if ($validator->fails()) {
                return response()->json(['result' => 0, 'message' => "Validation error", 'errors' => $validator->errors()->messages()]);
            }


            $company = Company::find($request->company_id);
            $currency = Currency::find($company->currency_id);


            $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET', 'sk_test_51LqDGEDgZHqv5TzUMHUSQILyRMzaLLQXQ8lzZZiHuSNF8UYPfDi2nsGMdqRPGhMPvwoQve6JcZEJCDNOOpaOpkn400gibP49JV'));
            $merchant_amount =  $currency->country_price;
            $code = $currency->currency_code;
                if ($code == 'BIF' || $code == 'CLP' || $code == 'DJF' || $code == 'GNF' || $code == 'JPY' || $code == 'KMF' || $code == 'KRW' || $code == 'MGA' || $code == 'PYG' || $code == 'RWF' || $code == 'UGX' || $code == 'VND' || $code == 'VUV' || $code == 'XAF' || $code == 'XOF' || $code == 'XPF' || $code == 'CFA') {
                } else {
                    $merchant_amount = $merchant_amount * 100;
                }

            $price = $stripe->prices->create(
                [
                    'unit_amount' => $merchant_amount,
                    'currency' => $currency->currency_code,
                    'tax_behavior' => 'exclusive',
                    'product_data' => ['name' => $company->company_name],
                ]
            );

            \Stripe\Stripe::setApiKey(env('STRIPE_SECRET', 'sk_test_51LqDGEDgZHqv5TzUMHUSQILyRMzaLLQXQ8lzZZiHuSNF8UYPfDi2nsGMdqRPGhMPvwoQve6JcZEJCDNOOpaOpkn400gibP49JV'));

            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price' => $price->id,
                    'quantity' => 1,
                ]],
                'automatic_tax' => [
                    'enabled' => false,
                ],
                'mode' => 'payment',
                'success_url' => url('payment/success?session_id={CHECKOUT_SESSION_ID}&&company_id=' . $request->company_id),
                'cancel_url' => route('payment.cancel'),
            ]);


            if (isset($session->url)) {
                return response()->json(['result' => 1, 'payment_url' => $session->url]);
            }
        } catch (\Throwable $e) {
            return response()->json(['result' => 0, 'message' => $e->getMessage() . ' on line ' . $e->getLine()]);
        }
    }



    public function cancel()
    {
        return redirect()->route('payment.failed_callback');
        dd('Your payment is canceled. You can create cancel page here.');
    }

    /**
     * Responds with a welcome message with instructions
     *
     * @return \Illuminate\Http\Response
     */

    public function success(Request $request)
    {

        $session_id =  $request->session_id;
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET', 'sk_test_51LqDGEDgZHqv5TzUMHUSQILyRMzaLLQXQ8lzZZiHuSNF8UYPfDi2nsGMdqRPGhMPvwoQve6JcZEJCDNOOpaOpkn400gibP49JV'));
        $result = \Stripe\Checkout\Session::retrieve(
            $session_id
        );

        $payment_id = $result->payment_intent;
        //echo $payment_id;die;
        $company_id = $request->company_id;
        // $price_id   = $request->price_id;

        if (true) {
            // if (in_array(strtoupper($response['ACK']), ['SUCCESS', 'SUCCESSWITHWARNING'])) {
            $company   = Company::find($company_id);
            $company->paypal_nonce = $payment_id;
            $today = date('Y-m-d');
            if ($company->expiry_date && $company->expiry_date > $today) {
                // $today = $company->expiry_date;
                $today = date('Y-m-d', strtotime($company->expiry_date . ' + 1 days'));
            }
            $expiry_date = date('Y-m-d', strtotime($today . ' + 30 days'));
            $company->expiry_date = $expiry_date;
            $company->status = 1;
            $company->save();

            // PDF transaction save and send email
            //$company->duration_start = $today;
            $company->expiry_date =  $expiry_date;
            // $result->transaction->id
            $filepath = 'company_invoce/' . md5(time() . rand() . time()) . '.pdf';

            $invoice = new Invoice();
            $invoice->user_id = $company->user_id;
            $invoice->company_id = $company->id;
            $invoice->braintree_transaction_id = $payment_id;
            $invoice->start_date = $today;
            $invoice->end_date = $expiry_date;
            $invoice->pdf = $filepath;
            $invoice->save();
            $filepath = public_path($filepath);
            $pdf = PDF::loadView('emails.invoice', ['company' => $company])->save($filepath);

            \Mail::to($company->user->email)->send(new \App\Mail\InvoiceMail($company, $filepath));

            $user  = User::find($company->user_id);
            // You already reached the limit of 16 test webhook endpoints.


            if(true){
            $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET', 'sk_test_51LqDGEDgZHqv5TzUMHUSQILyRMzaLLQXQ8lzZZiHuSNF8UYPfDi2nsGMdqRPGhMPvwoQve6JcZEJCDNOOpaOpkn400gibP49JV'));
            if ($company->stripe_customer_id == null) {

                //$customer_id = $user->createAsStripeCustomer();

                $customer = $stripe->customers->create([
                    'description' => $company_id,
                    'name' => ucfirst($company->company_name),
                        'id' => $company->id
                ]);
                $customer_id = $customer->id;

                $company->stripe_customer_id = $customer_id;
                $company->save();
            } else {
                $customer_id = $company->stripe_customer_id;
            }
            $company = Company::find($company_id);



            $currency = Currency::find($company->currency_id);

            $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET', 'sk_test_51LqDGEDgZHqv5TzUMHUSQILyRMzaLLQXQ8lzZZiHuSNF8UYPfDi2nsGMdqRPGhMPvwoQve6JcZEJCDNOOpaOpkn400gibP49JV'));

            $price = $stripe->prices->create(
                [
                    'unit_amount' => $currency->country_price * 100,
                    'currency' => $currency->currency_code,
                    'tax_behavior' => 'exclusive',
                    'product_data' => ['name' => $company->company_name],

                    'recurring' => ['interval' => 'month'],
                ]
            );

            $subscription = \Stripe\Subscription::create([
                'customer' => $customer_id,
                'items' => [[
                    'price' => $price->id,
                ]],
                'metadata' => [
                    'start_date' => date('Y-m-d', strtotime(date('Y-m-d') . ' + 30 days'))
                ],
                'description' => '12345678910',
                'payment_behavior' => 'default_incomplete',
                'expand' => ['latest_invoice.payment_intent'],
            ]);

            $endpoint = \Stripe\WebhookEndpoint::create([
                'url' => route('payment.stripe_subscriptions_callback')."?company_id=".$company_id,
                'enabled_events' => [
                    'charge.failed',
                    'charge.succeeded',
                ],
            ]);

            $company->subscription_id = $subscription->id;
            $company->cancel_subscription = 0;
            $company->save();
            $subscription_reterive = $stripe->subscriptions->retrieve(
                $company->subscription_id,
                []
            );
        }

            return redirect()->route('payment.success_call_back');
            return response()->json(['result' => 1, "company" => $company]);
        } else {

            \Log::error('payment/make Validation Error Start ======================');
            \Log::emergency($request->all());
            \Log::error('payment/make Validation Error End ======================');

            return response()->json(['result' => 0, "message" => "Validation Error. Something went wrong"]);
        }

        dd('Something is wrong.');
    }

    public function successOld(Request $request)
    {
        $session_id =  $request->session_id;
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET', 'sk_test_51LqDGEDgZHqv5TzUMHUSQILyRMzaLLQXQ8lzZZiHuSNF8UYPfDi2nsGMdqRPGhMPvwoQve6JcZEJCDNOOpaOpkn400gibP49JV'));
        $result = \Stripe\Checkout\Session::retrieve(
            $session_id
        );

        $payment_id = $result->payment_intent;
        //echo $payment_id;die;
        $company_id = $request->company_id;

        if (true) {
            // if (in_array(strtoupper($response['ACK']), ['SUCCESS', 'SUCCESSWITHWARNING'])) {

            $company   = Company::find($company_id);



            $company->paypal_nonce = $payment_id;
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
            $invoice->braintree_transaction_id = $payment_id;
            $invoice->start_date = $today;
            $invoice->end_date = $expiry_date;
            $invoice->pdf = $filepath;
            $invoice->save();
            $filepath = public_path($filepath);
            $pdf = PDF::loadView('emails.invoice', ['company' => $company])->save($filepath);



            \Mail::to($company->user->email)->send(new \App\Mail\InvoiceMail($company, $filepath));


            return redirect()->route('payment.success_call_back');
            return response()->json(['result' => 1, "company" => $company]);
        } else {

            \Log::error('payment/make Validation Error Start ======================');
            \Log::emergency($request->all());
            \Log::error('payment/make Validation Error End ======================');

            return response()->json(['result' => 0, "message" => "Validation Error. Something went wrong"]);
        }

        dd('Something is wrong.');
    }

    function success_call_back()
    {

        echo 'success';
    }



    function failed_callback()
    {

        echo 'failed';
    }


    public function stripeSubscriptionsCallback(Request $request)
    {
        $data = json_encode($request->all());
			//DB::table('tests')->insert(['log' => $data]);
        \Log::info(['request' => $request]);
        $event = $request->all();
        $eventType = '';
        if (!empty($event)) {
            if (isset($event['type'])) {
                $eventType = $event['type'];
                $email = $event['data']['object']['billing_details']['email'];
                $paymentIntentId = $event['data']['object']['payment_intent'];
                $amount = $event['data']['object']['amount'];
                $txn_id = $event['data']['object']['balance_transaction'];
                $user_id = $event['data']['object']['customer'];
                $company_id = $event['data']['object']['customer'];
                $stripePaymentStatus = $event['data']['object']['paid'];
            } else {
                if (isset($event->type)) {
                    $eventType = $event->type;
                    $email = $event->data->object->billing_details->email;
                    $paymentIntentId = $event->data->object->payment_intent;
                    $amount = $event->data->object->amount;
                    $txn_id = $event->data->object->balance_transaction;
                    $company_id = $event->data->object->customer;
                    $stripePaymentStatus = $event->data->object->paid;
                }
            }

            if ($eventType == "charge.payment_failed") {
                $orderStatus = 'Payement Failure';
                $paymentStatus = 'Unpaid';
                $amount = $amount / 100;
            }
            //if ($eventType == "payment_intent.succeeded") {
            if ($eventType == "charge.succeeded") {
                $orderStatus = 'Completed';
                $paymentStatus = 'Paid';
                $amount = $amount / 100;
                DB::table('tests')->insert(['log' => $request->company_id,'updated_at' => date('Y-m-d H:i:s')]);
                $company = Company::find($request->company_id);
                DB::table('tests')->insert(['log' => json_encode($company),'updated_at' => date('Y-m-d H:i:s')]);
                $days = 30;
                if ($company) {
                    $company->paypal_nonce = $txn_id;
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
                    $company->expiry_date =  $expiry_date;
                    // $result->transaction->id
                    $filepath = 'company_invoce/' . md5(time() . rand() . time()) . '.pdf';

                    $invoice = new Invoice();
                    $invoice->user_id = $company->user_id;
                    $invoice->company_id = $company->id;
                    $invoice->braintree_transaction_id = $txn_id;
                    $invoice->start_date = $today;
                    $invoice->expiry_date = $expiry_date;
                    $invoice->pdf = $filepath;
                    $invoice->save();
                    $filepath = public_path($filepath);
                    $pdf = PDF::loadView('emails.invoice', ['company' => $company])->save($filepath);

                    \Mail::to($company->user->email)->send(new \App\Mail\InvoiceMail($company, $filepath));






                    /**

                    if (!Invoice::where("braintree_transaction_id", $txn_id)->first()) {
                        $payment_method = 'stripe';
                        if ($plan = Plan::where("id", 1)->first()) {
                            $days = 30;
                            $user->plan_id = 1;
                            $user->plan_expire_time = date('Y-m-d', strtotime(date('Y-m-d') . ' + ' . $days . ' days'));
                            if ($user->save()) {
                                $array_old = array('status' => 'old');
                                UserPlan::where('user_id', $user->id)->update($array_old);
                                $user_plan = new UserPlan();
                                $user_plan->user_id = $user->id;
                                $user_plan->plan_id = $plan->id;
                                $user_plan->title = $plan->title;
                                $user_plan->amount = $amount;
                                $user_plan->duration_text = $plan->duration_text;
                                $user_plan->duration_month = $plan->duration_month;
                                $user_plan->plan_expire_time = date('Y-m-d', strtotime(date('Y-m-d') . ' + ' . $days . ' days'));
                                if ($user_plan->save()) {
                                    $transaction = new Transaction();
                                    $transaction->user_id = $user->id;
                                    $transaction->status = 'active';
                                    $transaction->transaction_type = 'plan';
                                    $transaction->item_id = $plan->id;
                                    $transaction->txn_id = $txn_id;
                                    $transaction->payment_method = $payment_method;
                                    $transaction->before_wallet_amount = '0.00';
                                    $transaction->after_wallet_amount = '0.00';
                                    $transaction->amount = $amount;
                                    $transaction->title = 'Recurring Plan Upgrade';
                                    $transaction->message = 'Recurring Plan Upgrade +' . BusRuleRef::where("rule_name", 'currency')->first()->rule_value . ' ' . $amount;
                                    $transaction->save();
                                }
                            }
                        }
                    }
                     */
                }
            }
            http_response_code(200);
        }
    }
    public function deletewebhook()
    {
        $stripe = new \Stripe\StripeClient(
        'sk_test_51LqDGEDgZHqv5TzUMHUSQILyRMzaLLQXQ8lzZZiHuSNF8UYPfDi2nsGMdqRPGhMPvwoQve6JcZEJCDNOOpaOpkn400gibP49JV'
        );
      $all=  $stripe->webhookEndpoints->all(['limit' => 3]);
      foreach($all->data as $val)
      {
        print_r($val);
        $stripe->webhookEndpoints->delete(
        $val->id,
        []
        );

      }
      print_r($all->data); exit;


    }
}
