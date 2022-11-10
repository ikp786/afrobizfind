<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PDF;
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\ExecutePayment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\Transaction;
use PayPal\Api\Payout;
use PayPal\Api\PayoutSenderBatchHeader;
use PayPal\Api\PayoutItem;
use Auth;
use URL;
use Session;
use Validator;
use App\Company;
use App\Currency;
use Srmklive\PayPal\Services\ExpressCheckout;

class PayPalController extends Controller
{
    private $_api_context;

    public function __construct()
    {
        $paypal_configuration = \Config::get('paypal');
        $this->_api_context = new ApiContext(new OAuthTokenCredential($paypal_configuration['client_id'], $paypal_configuration['secret']));
        $this->_api_context->setConfig($paypal_configuration['settings']);
    }

    /**
     * Responds with a welcome message with instructions
     *
     * @return \Illuminate\Http\Response
     */

     function payment(Request $request){
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
            return response()->json(['result' => 0, 'message' => "Validation error",'errors' => $validator->errors()->messages()]);
		}


        $company = Company::find($request->company_id);
       // dd($company);
        $currency = Currency::find($company->currency_id);
        $payer = new Payer();
                    $payer->setPaymentMethod('paypal');
                    $item = new Item();
                    $item->setName($company->company_name)
                        ->setCurrency($currency->currency_code)
                        ->setQuantity(1)
                        ->setPrice($currency->country_price);
                    $item_list = new ItemList();
                    $item_list->setItems(array($item));

                    $amount = new Amount();
                    $amount->setCurrency($currency->currency_code)
                        ->setTotal($currency->country_price);

                    $transaction = new Transaction();
                    $transaction->setAmount($amount)
                        ->setItemList($item_list)
                        ->setDescription($request->company_id);

                    $redirect_urls = new RedirectUrls();
                    $redirect_urls->setReturnUrl(URL::route('payment.success'))
                        ->setCancelUrl(URL::route('payment.cancel'));

                    $payment = new Payment();
                    $payment->setIntent('Sale')
                        ->setPayer($payer)
                        ->setRedirectUrls($redirect_urls)
                        ->setTransactions(array($transaction));
                    try {
                        $payment->create($this->_api_context);
                    } catch (\PayPal\Exception\PPConnectionException $ex) {
                        if (\Config::get('app.debug')) {
                            return response()->json(['result' => 0, 'payment_url' => "Connection Timeout"]);
                        } else {
                            return response()->json(['result' => 0, 'payment_url' => "Some error occur, sorry for inconvenient."]);
                        }
                    } catch (\PayPal\Exception\PayPalConnectionException $ex) {
                       // echo 'aaaaaaaaaaaaaaa<br>';die;
                       //dd(json_decode($ex->getData())->message);die;
                   //echo json_decode($ex->getData())->details[0]->issue;die;
                   if(isset(json_decode($ex->getData())->message)){
                    return response()->json(['result' => 0, 'payment_url' => "Currency is not supported.."]);

                   }


                        if (\Config::get('app.debug')) {
                          //  return response()->json(['result' => 0, 'payment_url' => $ex->getMessage()]);
                            return response()->json(['result' => 0, 'payment_url' => "Currency is not supported"]);
                        } else {
                            return response()->json(['result' => 0, 'payment_url' => "Some error occur, sorry for inconvenient."]);
                        }
                    }
                    foreach ($payment->getLinks() as $link) {
                        if ($link->getRel() == 'approval_url') {
                            $redirect_url = $link->getHref();
                            break;
                        }
                    }
                    Session::put('paypal_payment_id', $payment->getId());
                    if (isset($redirect_url)) {
                        return response()->json(['result'=>1,'payment_url'=>$redirect_url]);
                }

            } catch (\Throwable $e) {
                return response()->json(['result' => 0, 'message' => $e->getMessage() . ' on line ' . $e->getLine()]);
		}
            }

     function payment2(){

            $payer = new Payer();
            $payer->setPaymentMethod('paypal');
            $item = new Item();
            $item->setName('test')
                ->setCurrency('USD')
                ->setQuantity(1)
                ->setPrice(10);
            $item_list = new ItemList();
            $item_list->setItems(array($item));
            $amount = new Amount();
            $amount->setCurrency('USD')
                ->setTotal(10);
            $transaction = new Transaction();
            $transaction->setAmount($amount)
                ->setItemList($item_list)
                ->setDescription('Pay for Prime Reel');

            $redirect_urls = new RedirectUrls();
            $redirect_urls->setReturnUrl(URL::route('payment.success'))
                ->setCancelUrl(URL::route('payment.cancel'));

            $payment = new Payment();
            $payment->setIntent('Sale')
                ->setPayer($payer)
                ->setRedirectUrls($redirect_urls)
                ->setTransactions(array($transaction));
            try {
                $payment->create($this->_api_context);
            } catch (\PayPal\Exception\PPConnectionException $ex) {
                if (\Config::get('app.debug')) {
                    return redirect()->back()->withError('Connection Timeout');
                } else {
                    return redirect()->back()->withError('Some error occur, sorry for inconvenient.');
                }
            } catch (\PayPal\Exception\PayPalConnectionException $ex) {
                dd($ex);
                if (\Config::get('app.debug')) {
                    return redirect()->back()->withError('Connection Timeout');
                } else {
                    return redirect()->back()->withError('Some error occur, sorry for inconvenient.');
                }
            }

            foreach ($payment->getLinks() as $link) {
                if ($link->getRel() == 'approval_url') {
                    $redirect_url = $link->getHref();
                    break;
                }
            }

            Session::put('paypal_payment_id', $payment->getId());

            if (isset($redirect_url)) {
return redirect($redirect_url.'?company_id=23454');
}

}


    /**
     * Responds with a welcome message with instructions
     *
     * @return \Illuminate\Http\Response
     */
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

        $payment_id = $request->paymentId;

        //Session::get('paypal_payment_id');
        $payment = Payment::get($payment_id, $this->_api_context);
        $execution = new PaymentExecution();
        $execution->setPayerId($request->input('PayerID'));
        $result = $payment->execute($execution, $this->_api_context);
        $ddd = $result->getTransactions();
        $company_id = $ddd[0]->description;
       // $response = $provider->getExpressCheckoutDetails($request->token);

       if (true) {
       // if (in_array(strtoupper($response['ACK']), ['SUCCESS', 'SUCCESSWITHWARNING'])) {

                $company   = Company::find($company_id);
                $company->paypal_nonce = $payment_id;
                $today = date('Y-m-d');
                if($company->expiry_date && $company->expiry_date > $today ) {
                    // $today = $company->expiry_date;
                    $today = date('Y-m-d', strtotime($company->expiry_date. ' + 1 days'));
                }
                $expiry_date = date('Y-m-d', strtotime($today. ' + 30 days'));
                $company->expiry_date = $expiry_date;
                $company->save();

                // PDF transaction save and send email
                $company->duration_start = $today;
                $company->duration_end =  $expiry_date;
                // $result->transaction->id
                $filepath = 'company_invoce/'.md5(time().rand().time()).'.pdf';

                $invoice = new \App\Invoice();
                $invoice->user_id = $company->user_id;
                $invoice->company_id = $company->id;
                $invoice->braintree_transaction_id = $payment_id;
                $invoice->start_date = $today;
                $invoice->end_date = $expiry_date;
                $invoice->pdf = $filepath;
                $invoice->save();
                $filepath = public_path($filepath);
                $pdf = PDF::loadView('emails.invoice', ['company' =>$company])->save($filepath);



                \Mail::to($company->user->email)->send(new \App\Mail\InvoiceMail($company,$filepath));


return redirect()->route('payment.success_call_back');
                return response()->json(['result' => 1, "company" => $company]);


        }else{

            \Log::error( 'payment/make Validation Error Start ======================');
                 \Log::emergency( $request->all() );
                 \Log::emergency( $result->errors->deepAll() );
                  \Log::error( 'payment/make Validation Error End ======================');

                 return response()->json(['result' => 0,"message" => "Validation Error. Something went wrong" ]);

        }

        dd('Something is wrong.');
    }

    function success_call_back(){

        echo 'success';
    }


    function failed_callback(){

echo 'failed';
}
}
