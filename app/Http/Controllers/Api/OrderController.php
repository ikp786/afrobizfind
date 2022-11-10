<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Currency;
use App\Models\Event;
use App\Models\EventImage;
use App\Models\order;
use App\Models\orderstatus;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function alleventprod(Request $request)
    {

        $getevents = false;
        $getproducts = false;

        $companyid = $request->companyid;
        $datenow = Carbon::now()->toDateString();
        $date = Carbon::createFromFormat('Y-m-d', $datenow);

        $eventdata = Event::where('company_id', $companyid)
            ->select(DB::raw('events.id,events.eventref,events.eventname,events.price,events.flyerimage,events.start_date,events.end_date,events.location,events.building_number,events.address_line_1,events.city,events.postcode,events.country,events.termscondition,events.organizer,events.contactno,events.max_no_ticket as ticket_amount,events.availableticket,events.currency_id,events.company_id,(events.max_no_ticket - events.availableticket) as ticket_sold'))
            ->orderby("events.eventname", "asc")
            ->get();

        if ($eventdata->toArray()) {
            foreach ($eventdata as $eventval) {
                $eventdataimages = EventImage::where('event_id', $eventval->id)->get();

                $eventval->flyerimage = url('public/mainflyer/' . $eventval->flyerimage);

                if ($eventval->start_date != "") {
                    $startdate = $eventval->start_date;
                    // $startdate = Carbon::createFromFormat('Y-m-d',$eventval->start_date);
                }

                if ($eventval->end_date != "") {
                    $enddate = $eventval->end_date;
                    // $enddate = Carbon::createFromFormat('Y-m-d',$eventval->end_date);
                }

                if ($date >= $startdate && $date <= $enddate) {
                    $eventval->status = "Going Ahead";
                } else if ($date->gt($enddate)) {
                    $eventval->status = "ended";
                } else if ($date->eq($startdate)) {
                    $eventval->status = "started";
                } else if ($date < $startdate && $date < $enddate) {
                    $eventval->status = "Not Started";
                }


                if ($eventdataimages->toArray()) {
                    $i = 0;
                    foreach ($eventdataimages as $imagesval) {
                        $imagesval->eventimage = url('public' . $imagesval->eventimage);
                        $data[$i] = $imagesval->eventimage;
                        $eventdataimages->eventimage = $data[$i];
                        $i++;
                    }

                    $eventval->eventimages = $eventdataimages;
                } else {
                    $eventval->eventimages = array();
                }
            }

            foreach ($eventdata as $eventcurrency) {
                $ecurrency = Currency::where('id', $eventcurrency->currency_id)->get();

                if ($ecurrency->toArray()) {
                    $eventcurrency->currency = $ecurrency;
                } else {
                    $eventcurrency->currency = array();
                }
            }
            $getevents = true;
            // return response()->json(['result'=>1,'eventdata'=>$eventdata]);
        }



        $products = Product::with('company')->where('company_id', $companyid)->orderby("product_name", "asc")->get();

        if (count($products) > 0) {
            $getproducts = true;
        }


        $alldata[] = $eventdata;
        $alldata[] = $products;


        // array_push($data, $products_array);


        return response()->json(['result' => 1, 'alldata' => $alldata]);
    }


    public function store(Request $request)
    {
        try {

            $error_message =     [
                'productid.required'             => 'Products Id should be required',
                'productid.exists'               => 'Products Id not found',
            ];
            $rules = [
                'productid'                      => 'required|exists:products,id',
                'quantity'                       => 'required|integer',
                'orderstatus'                    => 'required|integer',
                'paymentmethod'                  => 'required|integer',
            ];

            $validator = \Validator::make($request->all(), $rules, $error_message);

            if ($validator->fails()) {
                return response()->json(['result' => 0, 'message' => "Validation error", 'errors' => $validator->errors()->messages(), 'payment_url' => '']);
            }

            $price = DB::table('products')->where('id', $request->productid)->first();

            $mprice = $price->price;
            $finalprice = explode(' ', $mprice);
            $id = Auth::id();
            $qty = $request->quantity;
            $total = $finalprice[1] * $qty;
            $is_free = DB::table('settings')->value('on_off');
            if ($request->paymentmethod == 1) {
                if ($is_free == 0) {
                    $is_order = 1;
                } else {
                    $is_order = 0;
                }
            } else {
                $is_order = 1;
            }
            $orderno = $this->generateRandomString(6);
            $orderdata = new order;
            $orderdata['orderno'] = $orderno;
            $orderdata['company_id'] = $price->company_id;
            $orderdata['is_order'] = $is_order;
            $orderdata['productid'] = $request->productid;
            $orderdata['userid'] = $id;
            $orderdata['price'] = $finalprice[1];
            $orderdata['quantity'] = $request->quantity;
            $orderdata['totalprice'] = $total;
            $orderdata['orderstatus'] = $request->orderstatus;
            $orderdata['paymentmethod'] = $request->paymentmethod;
            if ($orderdata->save()) {
                if ($request->paymentmethod == 1 && $is_free == 1) {
                    $stripe   = new \Stripe\StripeClient(env('STRIPE_SECRET'));
                    $company  = Company::find($price->company_id);
                    $currency = Currency::find($company->currency_id);

                    $code = $currency->currency_code;
                    if ($code == 'BIF' || $code == 'CLP' || $code == 'DJF' || $code == 'GNF' || $code == 'JPY' || $code == 'KMF' || $code == 'KRW' || $code == 'MGA' || $code == 'PYG' || $code == 'RWF' || $code == 'UGX' || $code == 'VND' || $code == 'VUV' || $code == 'XAF' || $code == 'XOF' || $code == 'XPF' || $code == 'CFA') {
                    } else {
                        $total = $total * 100;
                    }

                    $price = $stripe->prices->create(
                        [
                            'unit_amount' => $total,
                            'currency' => $currency->currency_code,
                            'tax_behavior' => 'exclusive',
                            'product_data' => ['name' => $price->product_name],
                        ]
                    );
                    \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
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
                        'success_url' => url('product_order/payment/success?session_id={CHECKOUT_SESSION_ID}&&order_id=' . $orderdata->id),
                        'cancel_url' => route('product_order.payment.cancel'),
                    ]);

                    if (isset($session->url)) {
                        return response()->json(['result' => 1, 'message' => 'success', 'payment_url' => $session->url]);
                    }
                } else {
                    return response()->json(['result' => 1, 'message' => 'Order created successfully', 'payment_url' => '']);
                }
            } else {
                return response()->json(['result' => 0, 'message' => 'Something Went Wrong please try again later', 'payment_url' => '']);
            }
        } catch (\Throwable $e) {
            return response()->json(['result' => 0, 'message' => $e->getMessage() . ' on line ' . $e->getLine(), 'payment_url' => '']);
        }
    }


    public function success(Request $request)
    {
        try {
            $session_id =  $request->session_id;
            \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
            $result = \Stripe\Checkout\Session::retrieve(
                $session_id
            );
            $payment_id = $result->payment_intent;
            $order_id = $request->order_id;
            $order   = order::find($order_id);
            $order->txn_number = $payment_id;
            $order->is_order   = 1;
            $order->save();
            // TRANFER MERCHAT ACCOUNT
            //dd($order->company_id);
            $company       = Company::find($order->company_id);
            //dd($company);
            $merchant_data = User::find($company->user_id);
            // CHEK MERCHAT ACCOUNT CREATE ON STRIPE
            if ($merchant_data->stripe_account_id != '' || $merchant_data->stripe_account_id != null) {
                $currency = Currency::find($company->currency_id);
                $totalprice = $order->totalprice; // 100
                $processing_fee_per = $currency->processing_fee; // 10
                $per = $totalprice * ($processing_fee_per / 100);
                $merchant_amount =  $totalprice - $per;
                $merchant_amount_save_in_db = $merchant_amount;
                //$merchant_amount = (($totalprice / 100) * $processing_fee_per) - $totalprice;

                $code = $currency->currency_code;
                if ($code == 'BIF' || $code == 'CLP' || $code == 'DJF' || $code == 'GNF' || $code == 'JPY' || $code == 'KMF' || $code == 'KRW' || $code == 'MGA' || $code == 'PYG' || $code == 'RWF' || $code == 'UGX' || $code == 'VND' || $code == 'VUV' || $code == 'XAF' || $code == 'XOF' || $code == 'XPF' || $code == 'CFA') {
                } else {
                    $merchant_amount = $merchant_amount * 100;
                }
                \Stripe\Stripe::setApiKey(env('STRIPE_SECRET', 'sk_test_51JykQILs3ZmffSEzPVADK2R89DDuQUBX5yoSfsE9Y7wxprjGYFsXhFau6Gd8uRBJXcYEfNa1QZgt2RFQL7FJwmZT00ck1lqkdn'));

                $transfer_payment = \Stripe\PaymentIntent::create([
                    'amount' => $merchant_amount,
                    'currency' => $code,
                    'transfer_data' => [
                        'destination' => $merchant_data->stripe_account_id,
                    ],
                ]);

                $order->transfer_amount   = $merchant_amount_save_in_db;
                $order->transfer_id       = $transfer_payment->id;
                $order->save();
            }


            return redirect()->route('product_order.payment.success_call_back');
        } catch (\Throwable $e) {
            return response()->json(['result' => 0, 'message' => $e->getMessage() . ' on line ' . $e->getLine()]);
        }
    }


    public function storeOld(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'productid' => 'required',
            'quantity' => 'required',
            'orderstatus' => 'required',
            'paymentmethod' => 'required',
            'tablenumber' => 'integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['result' => 0, 'message' => "Validation error", 'errors' => $validator->errors()->messages()]);
        }

        $userid = Auth::id();

        $productid = $request->productid;
        $quantity = $request->quantity;

        $newproduct = explode(',', $productid);
        $newquantity = explode(',', $quantity);

        $prefix = 'O';
        $rand1 = mt_rand(100000, 999999);
        $orderno = $prefix . $rand1;

        foreach ($newproduct as $key => $productval) {

            $getproductdata = Product::select('price', 'company_id')->where('id', $productval)->first();

            $newprice = explode(' ', $getproductdata['price']);
            $productcompany = $getproductdata['company_id'];
            $newprice = $newprice[1];
            $newqty = $newquantity[$key];
            $total = $newprice * $newqty;

            $orderdata = new order;
            $orderdata['orderno'] = $orderno;
            $orderdata['company_id'] = $productcompany;
            $orderdata['productid'] = $productval;
            $orderdata['userid'] = $userid;
            $orderdata['price'] = $newprice;
            $orderdata['quantity'] = $newqty;
            $orderdata['totalprice'] = $total;
            $orderdata['orderstatus'] = $request->orderstatus;
            $orderdata['tablenumber'] = $request->tablenumber;
            $orderdata['paymentmethod'] = $request->paymentmethod;
            $orderdata->save();
        }
        if ($orderdata->save()) {
            return response()->json(['result' => 1, 'message' => 'Order created successfully']);
            die;
        } else {
            return response()->json(['error' => 'Something Went Wrong please try again later']);
            die;
        }
    }


    // public function store(Request $request)  //Create the New Order
    // {
    //     $price = DB::table('products')->where('id',$request->productid)->first();

    //     $prefix='O';
    //     $rand1 = mt_rand(100000, 999999);
    //     $orderno=$prefix.$rand1;


    //     $mprice=$price->price;
    //     $finalprice=explode(' ',$mprice);

    //     $orderdata= new order;
    //     $id = Auth::id();
    //     $qty=$request->quantity;
    //     $total=$finalprice[1]*$qty;

    //     $orderdata['orderno']=$orderno;
    //     $orderdata['productid']=$request->productid;
    //     $orderdata['userid']=$id;
    //     $orderdata['price']=$finalprice[1];
    //     $orderdata['quantity']=$request->quantity;
    //     $orderdata['totalprice']=$total;
    //     $orderdata['orderstatus']=$request->orderstatus;
    //     $orderdata['paymentmethod']=$request->paymentmethod;


    //     if($orderdata->save())
    //     {
    //         return response()->json(['result'=>1,'message'=>'Order created successfully']);
    //     }
    //     else
    //     {
    //         return response()->json(['error'=>'Something Went Wrong please try again later']);
    //     }
    // }


    public function editorderstatus(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'orderid' => 'required',
            'orderstatus' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['result' => 0, 'message' => "Validation error", 'errors' => $validator->errors()->messages()]);
        }

        $orderid = $request->orderid;
        $orderstatus = $request->orderstatus;

        $changeorderstatus = order::where('orderno', $orderid)->get();

        if ($changeorderstatus) {
            $changeorderstatus->orderstatus = $orderstatus;
            $changeorderstatus = order::where('orderno', $orderid)->update(['orderstatus' => $orderstatus]);

            if ($changeorderstatus == true) {
                return response()->json(['result' => 1, 'message' => 'Order Status Successfully changed.']);
                die;
            } else {
                return response()->json(['result' => 0, 'message' => 'Something went wrong please try again later.']);
                die;
            }
        } else {
            return response()->json(['result' => 0, 'message' => 'orderid doesn\'t exist.']);
            die;
        }
    }

    function success_call_back()
    {
        echo 'success';
    }

    public function cancel()
    {
        return redirect()->route('product_order.payment.failed_callback');
        dd('Your payment is canceled. You can create cancel page here.');
    }

    function failed_callback()
    {

        echo 'failed';
    }


    public function generateRandomString($length = 10)
    {
        $characters = '0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString . strtotime("now");
    }

    public function allorderhistory(Request $request)  //Getting all Orders by login user
    {
        $id = Auth::id();

        $datenow = Carbon::now()->toDateString();
        $date = Carbon::createFromFormat('Y-m-d', $datenow);

        // echo $id;
        // die;

        // $orderhistory = DB::table('orders')
        // ->join('users', 'users.id', '=', 'orders.userid')
        // ->leftjoin('products', 'products.id', '=', 'orders.productid')
        // ->leftjoin('orderstatus','orderstatus.id','orders.orderstatus')
        // ->join('companies','companies.id','products.company_id')
        // ->leftjoin('events','events.id','=','orders.event_id')
        // // ->join('product_images','product_images.product_id','products.id')
        // ->leftjoin('payment_method','payment_method.id','orders.paymentmethod')
        // ->select('orders.orderno',DB::raw('SUM(orders.totalprice) as ordertotal'),DB::raw('SUM(orders.quantity) as orderquantity'),'products.id as myid','users.username','orders.*','companies.company_name','companies.id as company_id','orderstatus.status','payment_method.method','companies.company_number','products.currency_id','events.currency_id as currency_id_ev')
        // ->groupBy('orders.orderno')
        // ->where('orders.userid', '=', $id)
        // ->orderby("orders.created_at","desc")
        // ->get();

        $orderhistory = DB::table('orders')->leftjoin('users', 'users.id', 'orders.userid')
            ->leftjoin('products', 'products.id', 'orders.productid')
            ->leftjoin('events', 'events.id', 'orders.event_id')
            ->leftjoin('orderstatus', 'orderstatus.id', 'orders.orderstatus')
            ->leftjoin('event_status', 'event_status.id', 'orders.event_status')
            ->leftjoin('payment_method', 'payment_method.id', 'orders.paymentmethod')
            ->leftjoin('companies as prodcomp', 'prodcomp.id', 'products.company_id')
            ->leftjoin('companies as evcomp', 'evcomp.id', 'events.company_id')
            ->leftjoin('currencies as pc', 'pc.id', 'prodcomp.currency_id')
            ->leftjoin('currencies as ec', 'ec.id', 'evcomp.currency_id')
            ->select(
                'orders.orderno',
                DB::raw('SUM(orders.totalprice) as ordertotal'),
                DB::raw('SUM(orders.quantity) as orderquantity'),
                'payment_method.method',
                'orderstatus.status as productorderstatus',
                'orders.event_status as eventstatus',
                'orders.tablenumber as tablenumber',
                'orders.created_at',
                'users.email',
                'prodcomp.company_name as productcompany',
                'evcomp.company_name as eventcompany',
                'prodcomp.image as productcompimage',
                'evcomp.image as eventcompimage',
                'events.eventname',
                'events.start_date',
                'events.end_date',
                'ec.currency_sign as eventcurrency',
                'pc.currency_sign as productcurrency',
                'events.flyerimage as flyerimage',
                'orders.productid as productid'
            )
            ->groupBy('orders.orderno')
            ->where('orders.userid', '=', $id)
            ->where('orders.is_order', 1)
            ->orderby("orders.created_at", "desc")
            ->get();

        if ($orderhistory == true) {
            foreach ($orderhistory as $order) {
                $order->created_at = date('d, M,Y h:i A', strtotime($order->created_at));
                $checkorder = order::where('event_id', '!=', null)->where('orderno', $order->orderno)->count();

                if ($checkorder != 0) //for events
                {
                    $company_image = URL::to('/') . '/storage/public/' . $order->eventcompimage;
                    $order->company_image = $company_image;
                    $order->flyerimage = url('mainflyer/' . $order->flyerimage);

                    if ($order->eventstatus == 1) {
                        if ($date >= $order->start_date && $date <= $order->end_date) {
                            $order->eventstatus = 'Going Ahead';
                        } else if ($date->gt($order->end_date)) {
                            $order->eventstatus = 'ended';
                        } else if ($date->eq($order->start_date)) {
                            $order->eventstatus = 'started';
                        } else if ($date < $order->start_date && $date < $order->end_date) {
                            $order->eventstatus = 'Not Started';
                        }
                    } else {
                        $order->eventstatus = 'Cancelled';
                    }
                } else //for products
                {
                    $company_image = URL::to('/') . '/storage/public/' . $order->productcompimage;
                    $order->company_image = $company_image;

                    $product = ProductImage::where('product_id', $order->productid)->first();
                    if ($product) {
                        $orderdata = order::join('products', 'products.id', 'orders.productid')
                            ->select('products.product_name')
                            ->where('orders.orderno', $order->orderno)
                            ->get();

                        $product_name = '';
                        if ($orderdata->toArray()) {
                            foreach ($orderdata as $prod_order) {
                                $product_name .= $prod_order->product_name . ',';
                            }

                            $product_name = trim($product_name, ',');
                        }

                        $order->product_name = $product_name;

                        $order->productimage = URL::to('/') . '/storage/public' . $product->image;
                    }
                }
            }

            // dd($orderhistory);

            return response()->json(['result' => 1, 'orderhistory' => $orderhistory]);
        } else {
            return response()->json(['result' => 0, 'orderhistory' => 'Something Went wrong Please try Again later.']);
        }

        // if($orderhistory==true)
        // {
        //     if($orderhistory->toArray())
        //     {
        //         foreach ($orderhistory as $orderimg)
        //         {
        //            $myimages = Productimage::where('product_id',$orderimg->myid)->get();

        //            if($myimages->toArray())
        //            {
        //                 $orderimg->image = $myimages;
        //            }
        //            else
        //            {
        //                $orderimg->image = array();
        //            }
        //         }
        //     }
        //     return response()->json(['result'=>1,'orderhistory'=>$orderhistory]);
        // }
        // else
        // {
        //     return response()->json(['result'=>0,'orderhistory'=>'Something Went wrong Please try Again later.']);
        // }
    }

    public function productorder(Request $request)
    {
        $userid = Auth::id();

        $totalorder = DB::table('orders')->join('users', 'users.id', 'orders.userid')
            ->join('products', 'products.id', 'orders.productid')
            ->join('orderstatus', 'orderstatus.id', 'orders.orderstatus')
            ->join('payment_method', 'payment_method.id', 'orders.paymentmethod')
            ->join('companies', 'companies.id', 'products.company_id')
            ->join('currencies', 'currencies.id', 'companies.currency_id')
            ->select('orders.orderno', DB::raw('SUM(orders.totalprice) as ordertotal'), DB::raw('SUM(orders.quantity) as orderquantity'), 'payment_method.method', 'orderstatus.status', 'orders.tablenumber as tablenumber', 'orders.created_at', 'users.email', 'companies.company_name', 'companies.image as company_image', 'currencies.currency_sign as currency', 'products.id as productid')
            ->groupBy('orders.orderno')
            ->where('orders.userid', '=', $userid)
            ->where('orders.is_order', 1)
            ->orderby("orders.created_at", "desc")
            ->get();

        // return($totalorder);
        if ($totalorder == true) {
            //if ($totalorder->toArray()) {
            if (true) {
                foreach ($totalorder as $orderimg) {
                    $orderimg->created_at = date('d, M,Y h:i A', strtotime($orderimg->created_at));
                    $company_image = URL::to('/') . '/storage/public/' . $orderimg->company_image;
                    $orderimg->company_image = $company_image;

                    // dd($orderimg);
                    $orderdata = order::join('products', 'products.id', 'orders.productid')
                        ->select('products.product_name')
                        ->where('orders.orderno', $orderimg->orderno)
                        ->get();

                    if ($orderdata->toArray()) {
                        $product_name = '';

                        foreach ($orderdata as $order) {
                            $product_name .= $order->product_name . ',';
                        }

                        $product_name = trim($product_name, ',');
                    }

                    $orderimg->product_name = $product_name;

                    $myimages = Productimage::where('product_id', $orderimg->productid)->first();

                    if ($myimages) {
                        $orderimg->image = $myimages;
                    } else {
                        $orderimg->image = array();
                    }
                    if (isset($orderimg->flyerimage)) {
                        if (isset($orderimg->flyerimage) && $orderimg->flyerimage != null || $orderimg->flyerimage != '') {
                            $orderimg->flyerimage = url('public/mainflyer/' . $orderimg->flyerimage);
                        }
                    }
                }
            }

            return response()->json(['result' => 1, 'totalorder' => $totalorder]);
        } else {
            return response()->json(['result' => 0, 'orderhistory' => 'Something Went wrong Please try Again later.']);
        }
    }

    public function ticketorder(Request $request)
    {
        $userid = Auth::id();

        $datenow = Carbon::now()->toDateString();
        $date = Carbon::createFromFormat('Y-m-d', $datenow);

        $ticketorder = order::join('users', 'users.id', 'orders.userid')
            ->with('ticket_type')
            ->join('events', 'events.id', 'orders.event_id')
            ->join('companies', 'companies.id', 'events.company_id')
            ->join('event_status', 'event_status.id', 'orders.event_status')
            ->leftjoin('payment_method', 'payment_method.id', 'orders.paymentmethod')
            ->leftjoin('currencies', 'currencies.id', 'companies.currency_id')
            ->select('orders.orderno', DB::raw('SUM(orders.totalprice) as ordertotal'), DB::raw('SUM(orders.quantity) as orderquantity'), 'orders.event_id', 'orders.ticket_type_id', 'orders.created_at', 'orders.event_status', 'payment_method.method', 'orders.event_status as eventstatus', 'companies.company_name', 'companies.image as company_image', 'events.eventname', 'events.start_date', 'events.end_date', 'users.email', 'users.username', 'currencies.currency_sign as currency', 'events.flyerimage as flyerimage')
            ->groupBy('orders.orderno')
            ->where('orders.userid', '=', $userid)
            ->where('orders.is_order', 1)
            ->orderby("orders.created_at", "desc")
            ->get();


        // return response()->json(['result'=>1,'ticketorder'=>$ticketorder]);

        if ($ticketorder == true) {
            if ($ticketorder->toArray()) {
                // return response()->json($ticketorder);
                foreach ($ticketorder as $ordervalue) {
                    //$created_at = \Carbon\Carbon::parse($ordervalue->created_at)->format('d, M,Y h:i A');
                    //date('d, M,Y h:i A',strtotime($ordervalue->created_at));
                    //$ordervalue->created_date = $created_at;


                    $eventObjStatus = 0;
                    $eventObj = Event::where('id', $ordervalue->event_id)->first();

                    if ($eventObj) {
                        $eventObjStatus = $eventObj->status;
                    }

                    if ($eventObjStatus == 1) {
                        if ($date >= $ordervalue->start_date && $date <= $ordervalue->end_date) {
                            $ordervalue->eventstatus = 'Started';
                            // $ordervalue->eventstatus = 'Going Ahead';
                        } else if ($date->gt($ordervalue->end_date)) {
                            $ordervalue->eventstatus = 'Ended';
                            // } else if ($date->eq($ordervalue->start_date)) {
                            //     $ordervalue->eventstatus = 'started';
                        } else if ($date < $ordervalue->start_date && $date < $ordervalue->end_date) {
                            $ordervalue->eventstatus = 'Going Ahead';
                        }
                    } else {
                        $ordervalue->eventstatus = 'Cancelled';
                    }

                    $flyerimage = URL::to('/') . '/mainflyer/' . $ordervalue->flyerimage;
                    $ordervalue->flyerimage = $flyerimage;

                    $company_image = URL::to('/') . '/storage/public/' . $ordervalue->company_image;
                    $ordervalue->company_image = $company_image;

                    $ticketorderlist = order::select("ticketrefno")->where('orderno', '=', $ordervalue->orderno)->get();

                    if ($ticketorder->toArray()) {
                        $ordervalue->ticketlist = $ticketorderlist;
                    } else {
                        $ordervalue->ticketlist = array();
                    }
                }
            }
            return response()->json(['result' => 1, 'ticketorder' => $ticketorder]);
        } else {
            return response()->json(['result' => 0, 'ticketorder' => 'Something Went wrong.']);
        }
    }

    public function companyorder(Request $request) //getting the Particular Company Order
    {
        $cmpid = $request->companyid;

        // $companyorder = order::leftjoin('users','users.id','orders.userid')
        // ->leftjoin('products','products.id','orders.productid')
        // ->leftjoin('events','events.id','orders.event_id')
        // ->leftjoin('orderstatus','orderstatus.id','orders.orderstatus')
        // ->leftjoin('event_status','event_status.id','orders.event_status')
        // ->leftjoin('payment_method','payment_method.id','orders.paymentmethod')
        // ->leftjoin('companies as prodcomp','prodcomp.id','products.company_id')
        // ->leftjoin('companies as evcomp','evcomp.id','events.company_id')
        // ->leftjoin('currencies as pc','pc.id','prodcomp.currency_id')
        // ->leftjoin('currencies as ec','ec.id','evcomp.currency_id')

        // ->select('orders.orderno',DB::raw('SUM(orders.totalprice) as ordertotal'),DB::raw('SUM(orders.quantity) as orderquantity'),'payment_method.method','orderstatus.status as productorderstatus','event_status.status as eventstatus','orders.created_at','users.email','prodcomp.company_name as productcompany','evcomp.company_name as eventcompany','events.eventname','pc.name as productcurrency','ec.name as eventcurrency')
        // ->groupBy('orders.orderno')
        // ->where('prodcomp.id','=',$cmpid)
        // ->orWhere('evcomp.id','=',$cmpid)
        // ->orderby("orders.created_at","desc")
        // ->get();


        $companyorder = DB::table('orders')->leftjoin('users', 'users.id', 'orders.userid')
            ->leftjoin('products', 'products.id', 'orders.productid')
            // ->leftjoin('events','events.id','orders.event_id')
            ->leftjoin('orderstatus', 'orderstatus.id', 'orders.orderstatus')
            // ->leftjoin('event_status','event_status.id','orders.event_status')
            ->leftjoin('payment_method', 'payment_method.id', 'orders.paymentmethod')
            ->leftjoin('companies as prodcomp', 'prodcomp.id', 'products.company_id')
            // ->leftjoin('companies as evcomp','evcomp.id','events.company_id')
            ->leftjoin('currencies as pc', 'pc.id', 'prodcomp.currency_id')
            // ->leftjoin('currencies as ec','ec.id','evcomp.currency_id')

            ->select('orders.orderno', DB::raw('SUM(orders.totalprice) as ordertotal'), DB::raw('SUM(orders.quantity) as orderquantity'), 'orders.tablenumber as tablenumber', 'payment_method.method', 'orderstatus.status as productorderstatus', 'orders.created_at', 'users.email', 'prodcomp.company_name as productcompany', 'pc.currency_sign as productcurrency', 'users.first_name', 'users.surname', 'users.postcode')
            ->groupBy('orders.orderno')
            ->where('prodcomp.id', '=', $cmpid)
            // ->orWhere('evcomp.id','=',$cmpid)
            ->orderby("orders.created_at", "desc")
            ->get();

        foreach ($companyorder as $eventcurrency) {
            $eventcurrency->created_at = date('d, M,Y h:i A', strtotime($eventcurrency->created_at));
        }


        // foreach($eventdata as $eventcurrency)
        // {
        //     $ecurrency = Currency::where('id',$eventcurrency->currency_id)->get();

        //     if($ecurrency->toArray())
        //     {
        //         $eventcurrency->currency = $ecurrency;
        //     }
        //     else
        //     {
        //        $eventcurrency->currency = array();
        //    }
        // }

        if ($companyorder == true) {
            return response()->json(['result' => 1, 'companyorder' => $companyorder]);
        } else {
            return response()->json(['result' => 0, 'companyorder' => 'Something Went Wrong']);
        }
    }

    public function singleorder(Request $request) //View only Single Order record  to display
    {
        $orderid = $request->orderid;

        $datenow = Carbon::now()->toDateString();
        $date = Carbon::createFromFormat('Y-m-d', $datenow);
        $checkorder = order::where('event_id', '!=', null)->where('orderno', $orderid)->count();

        if ($checkorder != 0) // For the Events
        {
            $singleorder = DB::table('orders')->leftjoin('events', 'events.id', 'orders.event_id')
                ->leftjoin('event_status', 'event_status.id', 'orders.event_status')
                ->leftjoin('companies', 'companies.id', 'events.company_id')
                ->leftjoin('currencies', 'currencies.id', 'companies.currency_id')
                ->select('orders.orderno', 'orders.userid', DB::raw('SUM(orders.totalprice) as ordertotal'), DB::raw('SUM(orders.quantity) as orderquantity'), 'orders.event_id', 'orders.created_at', 'orders.event_status', 'event_status.status as eventstatus', 'companies.id as company_id', 'companies.company_name', 'companies.company_number', 'companies.email as company_email', 'companies.lat', 'companies.long', 'companies.telephone', 'events.eventname', 'currencies.name as event_currency', 'companies.image as company_image')
                ->with("images")
                ->where('orders.orderno', '=', $orderid)
                ->where('orders.is_order', 1)
                ->groupBy('orders.orderno')
                ->get();

            if ($singleorder->toArray()) {
                foreach ($singleorder as $ordervalue) {

                    $company_image = URL::to('/') . '/storage/public/' . $ordervalue->company_image;

                    $download_event_pdf_link = URL::to('/') . '/eventpdf/' . $orderid;
                    $email_event_pdf_link = URL::to('/') . '/email_eventpdf/' . $orderid;

                    $ordervalue->company_image = $company_image;
                    $ordervalue->download_event_pdf_link = $download_event_pdf_link;
                    $ordervalue->email_event_pdf_link = $email_event_pdf_link;

                    if ($ordervalue->event_status == 1) {
                        if ($date >= $ordervalue->start_date && $date <= $ordervalue->end_date) {
                            $ordervalue->eventstatus = 'Going Ahead';
                        } else if ($date->gt($ordervalue->end_date)) {
                            $ordervalue->eventstatus = 'ended';
                        } else if ($date->eq($ordervalue->start_date)) {
                            $ordervalue->eventstatus = 'started';
                        } else if ($date < $ordervalue->start_date && $date < $ordervalue->end_date) {
                            $ordervalue->eventstatus = 'Not Started';
                        }
                    } else {
                        $ordervalue->eventstatus = 'Cancelled';
                    }

                    $ticketorder = order::select("ticketrefno")->where('orderno', '=', $ordervalue->orderno)->get();

                    if ($ticketorder->toArray()) {
                        $ordervalue->ticketlist = $ticketorder;
                    } else {
                        $ordervalue->ticketlist = array();
                    }
                }
            }
        } else    // for the Products
        {
            // $singleorder = order::leftjoin('products','products.id','orders.productid')
            // ->leftjoin('orderstatus','orderstatus.id','orders.orderstatus')
            // ->leftjoin('payment_method','payment_method.id','orders.paymentmethod')
            // ->leftjoin('companies','companies.id','products.company_id')
            // ->select('orders.orderno','orderstatus.status','orders.quantity','orders.totalprice','orders.price','products.product_name','products.description','products.id as productid','products.product_number','orders.created_at','payment_method.method','companies.company_name as productcompany','companies.company_name','companies.company_number','companies.lat','companies.long','companies.telephone')
            // ->where('orders.orderno','=',$orderid)
            // ->with("productimages")
            // ->get();

            $singleorder = order::leftjoin('products', 'products.id', 'orders.productid')
                ->leftjoin('orderstatus', 'orderstatus.id', 'orders.orderstatus')
                ->leftjoin('payment_method', 'payment_method.id', 'orders.paymentmethod')
                ->leftjoin('companies', 'companies.id', 'products.company_id')
                ->leftjoin('currencies', 'currencies.id', 'companies.currency_id')
                ->select('orders.orderno', 'orderstatus.status', 'orders.quantity as orderquantity', 'orders.totalprice as ordertotal', 'orders.price', 'products.product_name', 'products.description', 'products.id as productid', 'products.product_number', 'orders.created_at', 'payment_method.method', 'companies.company_name as productcompany', 'companies.id as company_id', 'companies.company_name', 'companies.company_number', 'companies.email as company_email', 'companies.lat', 'companies.long', 'companies.telephone', 'currencies.currency_sign as product_currency', 'companies.image as company_image')
                ->where('orders.orderno', '=', $orderid)
                ->with("productimages")
                ->get();

            if ($singleorder->toArray()) {
                foreach ($singleorder as $ordervalue) {
                    $download_product_pdf_link = URL::to('/') . '/productpdf/' . $orderid;
                    $ordervalue->download_product_pdf_link = $download_product_pdf_link;

                    $email_product_pdf_link = URL::to('/') . '/email_productpdf/' . $orderid;
                    $ordervalue->email_product_pdf_link = $email_product_pdf_link;

                    if ($ordervalue->company_image) {
                        $company_image = URL::to('/') . '/storage/public/' . $ordervalue->company_image;
                        $ordervalue->company_image = $company_image;
                    }
                }
            }
        }


        if ($singleorder == true) {
            return response()->json(['result' => 1, 'orderdetails' => $singleorder]);
        }
    }

    public function eventpdf($eid)
    {
        $event = order::leftjoin('events', 'events.id', 'orders.event_id')
            ->leftjoin('event_status', 'event_status.id', 'orders.event_status')
            ->leftjoin('companies', 'companies.id', 'events.company_id')
            ->leftjoin('users', 'users.id', 'orders.userid')
            ->leftjoin('currencies', 'currencies.id', 'companies.currency_id')
            ->select('orders.orderno', 'users.user_number', DB::raw('SUM(orders.totalprice) as ordertotal'), DB::raw('SUM(orders.quantity) as orderquantity'), 'orders.event_id', 'orders.created_at', 'event_status.status as eventstatus', 'companies.building_number', 'companies.address_line_1', 'companies.city', 'companies.country', 'companies.postcode', 'companies.email', 'companies.website', 'companies.company_name', 'companies.company_number', 'companies.lat', 'companies.long', 'companies.telephone', 'events.id', 'events.eventname', 'events.building_number as eventbuilding_number', 'events.address_line_1 as eventaddress_line_1', 'events.city as eventcity', 'events.country as eventcountry', 'events.postcode as eventpostcode', 'events.price', 'events.organizer', 'events.contactno', 'events.start_date', 'events.end_date', 'events.termscondition', 'events.status', 'currencies.name as event_currency', 'companies.image as company_image', 'users.first_name', 'users.surname')
            ->with("images")
            ->where('orders.orderno', '=', $eid)
            ->groupBy('orders.orderno')
            ->first();

        if ($event) {
            $company_image = URL::to('/') . '/storage/public/' . $event->company_image;

            $event_pdf_link = URL::to('/') . '/eventpdf/' . $eid;

            $event->company_image = $company_image;
            $event->event_pdf_link = $event_pdf_link;

            $ticketorder = order::join('event_status', 'event_status.id', 'orders.event_status')
                ->select("orders.ticketrefno", "event_status.status")
                ->where('orders.orderno', '=', $event->orderno)
                ->get();

            if ($ticketorder->toArray()) {
                $event->ticketlist = $ticketorder;
            } else {
                $event->ticketlist = array();
            }

            $pdf = PDF::loadView('pdf.eventpdf', compact('event'));
            return $pdf->stream('event.pdf', array('Attachment' => 0));
        }
    }

    public function email_eventpdf($eid)
    {
        $event = order::leftjoin('events', 'events.id', 'orders.event_id')
            ->leftjoin('event_status', 'event_status.id', 'orders.event_status')
            ->leftjoin('companies', 'companies.id', 'events.company_id')
            ->leftjoin('users', 'users.id', 'orders.userid')
            ->leftjoin('currencies', 'currencies.id', 'companies.currency_id')
            ->select('orders.orderno', 'users.user_number', DB::raw('SUM(orders.totalprice) as ordertotal'), DB::raw('SUM(orders.quantity) as orderquantity'), 'orders.event_id', 'orders.created_at', 'event_status.status as eventstatus', 'companies.building_number', 'companies.address_line_1', 'companies.city', 'companies.country', 'companies.postcode', 'companies.email', 'companies.website', 'companies.company_name', 'companies.company_number', 'companies.lat', 'companies.long', 'companies.telephone', 'events.id', 'events.eventname', 'events.building_number as eventbuilding_number', 'events.address_line_1 as eventaddress_line_1', 'events.city as eventcity', 'events.country as eventcountry', 'events.postcode as eventpostcode', 'events.price', 'events.organizer', 'events.contactno', 'events.start_date', 'events.end_date', 'events.termscondition', 'events.status', 'currencies.name as event_currency', 'companies.image as company_image', 'users.first_name', 'users.surname', 'users.email')
            ->with("images")
            ->where('orders.orderno', '=', $eid)
            ->groupBy('orders.orderno')
            ->first();

        if ($event) {
            $company_image = URL::to('/') . '/storage/public/' . $event->company_image;

            $event_pdf_link = URL::to('/') . '/eventpdf/' . $eid;

            $event->company_image = $company_image;
            $event->event_pdf_link = $event_pdf_link;

            $ticketorder = order::join('event_status', 'event_status.id', 'orders.event_status')
                ->select("orders.ticketrefno", "event_status.status")
                ->where('orders.orderno', '=', $event->orderno)
                ->get();

            if ($ticketorder->toArray()) {
                $event->ticketlist = $ticketorder;
            } else {
                $event->ticketlist = array();
            }

            $useremail = $event->email;
            $username = $event->first_name . ' ' . $event->last_name;

            $pdf = PDF::loadView('pdf.eventpdf', compact('event'));
            // return $pdf->stream('event.pdf', array('Attachment'=>0));

            Mail::send('pdf.eventpdf', compact('event'), function ($message) use ($pdf, $useremail, $username) {
                // $message->to( $data['email'], $data['name'])
                $message->from($_ENV['MAIL_USERNAME'], $_ENV['MAIL_FROM_NAME']);
                $message->to($useremail, $username)
                    ->subject('Order PDF')
                    ->attachData($pdf->output(), "order.pdf");
            });

            if (Mail::failures()) {
                return response()->json(['success' => 0, 'message' => 'Mail can\'t send.']);
            } else {
                return response()->json(['success' => 1, 'message' => 'Mail sent.']);
            }
        }
    }

    public function productpdf($pid)
    {
        $product = order::leftjoin('products', 'products.id', 'orders.productid')
            // ->leftjoin('product_images','product_images.product_id','products.id')
            ->leftjoin('orderstatus', 'orderstatus.id', 'orders.orderstatus')
            ->leftjoin('payment_method', 'payment_method.id', 'orders.paymentmethod')
            ->leftjoin('companies', 'companies.id', 'products.company_id')
            ->leftjoin('currencies', 'currencies.id', 'companies.currency_id')
            ->leftjoin('users', 'users.id', 'orders.userid')
            ->select('orders.orderno', 'users.user_number', 'orderstatus.status as orderstatus', 'orders.quantity as orderquantity', 'orders.totalprice as ordertotal', 'orders.price', 'products.product_name', 'products.description', 'products.id as productid', 'products.product_number', 'orders.created_at', 'companies.building_number', 'companies.address_line_1', 'companies.city', 'companies.country', 'companies.postcode', 'companies.email', 'companies.website', 'payment_method.method', 'companies.company_name as productcompany', 'companies.id as company_id', 'companies.company_name', 'companies.company_number', 'companies.email as company_email', 'companies.lat', 'companies.long', 'companies.telephone', 'currencies.name as product_currency', 'companies.image as company_image', 'users.first_name', 'users.surname')
            ->where('orders.orderno', '=', $pid)
            ->with("productimages")
            ->get();

        if ($product->toArray()) {
            foreach ($product as $ordervalue) {
                $product_pdf_link = URL::to('/') . '/productpdf/' . $pid;
                $ordervalue->product_pdf_link = $product_pdf_link;

                if ($ordervalue->company_image) {
                    $company_image = URL::to('/') . '/storage/public/' . $ordervalue->company_image;
                    $ordervalue->company_image = $company_image;
                }

                $product_image = ProductImage::select('image')->where('product_id', $ordervalue->productid)->first();

                if ($product_image) {
                    $product_image = URL::to('/') . '/storage/public/' . $product_image->image;
                    $ordervalue->product_image = $product_image;
                }
            }

            $pdf = PDF::loadView('pdf.productpdf', compact('product'));
            return $pdf->stream('product.pdf', array('Attachment' => 0));
        }
    }

    public function email_productpdf($pid)
    {
        $product = order::leftjoin('products', 'products.id', 'orders.productid')
            // ->leftjoin('product_images','product_images.product_id','products.id')
            ->leftjoin('orderstatus', 'orderstatus.id', 'orders.orderstatus')
            ->leftjoin('payment_method', 'payment_method.id', 'orders.paymentmethod')
            ->leftjoin('companies', 'companies.id', 'products.company_id')
            ->leftjoin('currencies', 'currencies.id', 'companies.currency_id')
            ->leftjoin('users', 'users.id', 'orders.userid')
            ->select('orders.orderno', 'users.user_number', 'orderstatus.status as orderstatus', 'orders.quantity as orderquantity', 'orders.totalprice as ordertotal', 'orders.price', 'products.product_name', 'products.description', 'products.id as productid', 'products.product_number', 'orders.created_at', 'companies.building_number', 'companies.address_line_1', 'companies.city', 'companies.country', 'companies.postcode', 'companies.email', 'companies.website', 'payment_method.method', 'companies.company_name as productcompany', 'companies.id as company_id', 'companies.company_name', 'companies.company_number', 'companies.email as company_email', 'companies.lat', 'companies.long', 'companies.telephone', 'currencies.name as product_currency', 'companies.image as company_image', 'users.first_name', 'users.surname', 'users.email')
            ->where('orders.orderno', '=', $pid)
            ->with("productimages")
            ->get();

        if ($product->toArray()) {
            foreach ($product as $ordervalue) {
                $product_pdf_link = URL::to('/') . '/productpdf/' . $pid;
                $ordervalue->product_pdf_link = $product_pdf_link;

                if ($ordervalue->company_image) {
                    $company_image = URL::to('/') . '/storage/public/' . $ordervalue->company_image;
                    $ordervalue->company_image = $company_image;
                }

                $product_image = ProductImage::select('image')->where('product_id', $ordervalue->productid)->first();

                if ($product_image) {
                    $product_image = URL::to('/') . '/storage/public/' . $product_image->image;
                    $ordervalue->product_image = $product_image;
                }

                $useremail = $ordervalue->email;
                $username = $ordervalue->first_name . ' ' . $ordervalue->last_name;
            }
            // dd($useremail);
            $pdf = PDF::loadView('pdf.productpdf', compact('product'));

            Mail::send('pdf.productpdf', compact('product'), function ($message) use ($pdf, $useremail, $username) {
                // $message->to( $data['email'], $data['name'])
                $message->from($_ENV['MAIL_USERNAME'], $_ENV['MAIL_FROM_NAME']);
                $message->to($useremail, $username)
                    ->subject('Order PDF')
                    ->attachData($pdf->output(), "order.pdf");
            });

            if (Mail::failures()) {
                return response()->json(['success' => 0, 'message' => 'Mail can\'t send.']);
            } else {
                return response()->json(['success' => 1, 'message' => 'Mail sent.']);
            }
            // return $pdf->stream('product.pdf', array('Attachment'=>0));
        }
    }

    public function orderstatus()  //getting the Order Status
    {
        $orderstatus = orderstatus::all();

        return response()->json(['result' => 1, 'orderstatus' => $orderstatus]);
    }

    public function IncomeByDay(Request $request)
    {
        $cmpid = $request->companyid;

        $incomeday = DB::table('orders')
            ->select('orders.created_at', DB::raw('SUM(orders.totalprice) as totaldayincome'), DB::raw('COUNT(orders.id) as totalorder'), DB::raw('SUM(orders.quantity) as totalproduct'))
            ->join('products', 'products.id', 'orders.productid')
            ->join('companies', 'companies.id', 'products.company_id')
            ->where('companies.id', '=', $cmpid)
            ->groupBy(DB::raw('CAST(orders.created_at AS DATE)'))
            ->get();

        if ($incomeday == true) {
            return response()->json(['result' => 1, 'incomebyday' => $incomeday]);
        } else {
            return response()->json(['result' => 0, 'incomebyday' => 'Something went wrong please try again later.']);
        }
    }

    public function IncomebyProduct(Request $request)
    {
        $cmpid = $request->companyid;

        $incomeproduct = DB::table('orders')
            ->join('products', 'products.id', 'orders.productid')
            ->join('companies', 'companies.id', 'products.company_id')
            // ->join('product_images','product_images.product_id','products.id')
            ->where('companies.id', '=', $cmpid)
            ->select('companies.company_name', 'products.id as myid', 'products.product_name', 'products.product_number', 'orders.quantity', DB::raw('SUM(orders.totalprice) as totalproductincome'))
            ->groupBy(DB::raw('orders.productid'))
            ->get();


        if ($incomeproduct == true) {
            if ($incomeproduct->toArray()) {
                foreach ($incomeproduct as $orderimg) {
                    $myimages = Productimage::where('product_id', $orderimg->myid)->get();

                    // print_r($myimages);
                    if ($myimages->toArray()) {
                        $orderimg->image = $myimages;
                        // print_r($orderimg);
                    } else {
                        $orderimg->image = array();
                    }
                }
            }

            return response()->json(['result' => 1, 'incomeproduct' => $incomeproduct]);
        } else {
            return response()->json(['result' => 0, 'incomeproduct' => 'Something went wrong please try again later.']);
        }
    }

    public function productIncomeByDate(Request $request)
    {
        $user_id = Auth::id();

        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $productid = $request->product_id;

        // if($start_date==''||$end_date==''|| $start_date==null||$end_date==null)
        // {
        //     $start_date='';
        //     $end_date='';
        // }
        // else
        // {
        //     $start_date = date('Y-m-d H:i:s',strtotime($start_date));
        //     $end_date = date('Y-m-d H:i:s',strtotime($end_date));
        // }
        $start_date = date('Y-m-d H:i:s', strtotime($start_date));
        $end_date = date('Y-m-d H:i:s', strtotime($end_date));


        if ($productid == '' || $productid == null) //Login User All Products Profit
        {


            $orderdata = order::join('products', 'products.id', 'orders.productid')
                ->select('products.id as product_id', DB::raw('SUM(orders.totalprice) as productincome'), 'products.product_name', 'products.price as wholesale_price')
                ->where('orders.userid', $user_id)
                ->where('orders.productid', '!=', null)
                ->groupBy('orders.productid')
                ->whereBetween('orders.created_at', [$start_date, $end_date])
                ->get();

            foreach ($orderdata as $orderval) {
                $income = $orderval->order_price;
                $orderqty = $orderval->quantity;
                $wholesale_price = $orderval->wholesale_price;
                $wholesale_price = explode(' ', $wholesale_price);
                $currencycode = $wholesale_price[0];
                $wholesale_price = $wholesale_price[1];

                // $orderval->profit = ($income - $wholesale_price) * $orderqty;
                $orderval->profit = $currencycode . ' ' . $orderval->productincome;
                $orderval->productincome = $currencycode . ' ' . $orderval->productincome;
            }
        } else {
            $orderdata = order::join('products', 'products.id', 'orders.productid')
                ->select('products.id as product_id', DB::raw('SUM(orders.totalprice) as productincome'), DB::raw('SUM(orders.quantity) as totalquantity'), 'products.product_name', 'products.price as wholesale_price')
                ->where('orders.productid', '=', $productid)
                ->where('orders.userid', $user_id)
                ->groupBy('orders.productid')
                ->whereBetween('orders.created_at', [$start_date, $end_date])
                ->first();

            if ($orderdata->toArray()) {
                $income = $orderdata->order_price;
                $orderqty = $orderdata->quantity;
                $wholesale_price = $orderdata->wholesale_price;
                $wholesale_price = explode(' ', $wholesale_price);
                $currencycode = $wholesale_price[0];
                $wholesale_price = $wholesale_price[1];

                // $orderval->profit = ($income - $wholesale_price) * $orderqty;
                $orderdata->profit = $currencycode . ' ' . $orderdata->productincome;
                $orderdata->productincome = $currencycode . ' ' . $orderdata->productincome;
            }
        }

        return response()->json(['result' => 1, 'incomeproduct1' => $orderdata]);
    }

    public function productOrderdaliyPayout()
    {
        try {
            $orders   = order::where(['is_order' => 1, 'paymentmethod' => 1])
                ->whereNull('transfer_id')
                ->whereNotNull('txn_number')
                ->get();
            foreach ($orders as $key => $order) :
                // TRANFER MERCHAT ACCOUNT
                $company       = Company::find($order->company_id);

                $merchant_data = User::find($company->user_id);
                // CHEK MERCHAT ACCOUNT CREATE ON STRIPE
                if ($merchant_data->stripe_account_id != '' || $merchant_data->stripe_account_id != null) {
                    $currency = Currency::find($company->currency_id);
                    $totalprice = $order->totalprice; // 100
                    $processing_fee_per = $currency->processing_fee; // 10
                    $per = $totalprice * ($processing_fee_per / 100);
                    $merchant_amount =  $totalprice - $per;
                    $merchant_amount_save_in_db = $merchant_amount;

                    $code = $currency->currency_code;
                    if ($code == 'BIF' || $code == 'CLP' || $code == 'DJF' || $code == 'GNF' || $code == 'JPY' || $code == 'KMF' || $code == 'KRW' || $code == 'MGA' || $code == 'PYG' || $code == 'RWF' || $code == 'UGX' || $code == 'VND' || $code == 'VUV' || $code == 'XAF' || $code == 'XOF' || $code == 'XPF' || $code == 'CFA') {
                    } else {
                        $merchant_amount = $merchant_amount * 100;
                    }
                    \Stripe\Stripe::setApiKey(env('STRIPE_SECRET', 'sk_test_51JykQILs3ZmffSEzPVADK2R89DDuQUBX5yoSfsE9Y7wxprjGYFsXhFau6Gd8uRBJXcYEfNa1QZgt2RFQL7FJwmZT00ck1lqkdn'));
                    $transfer_payment = \Stripe\PaymentIntent::create([
                        'amount' => $merchant_amount,
                        'currency' => $code,
                        'transfer_data' => [
                            'destination' => $merchant_data->stripe_account_id,
                        ],
                    ]);
                    $order->transfer_amount   = $merchant_amount_save_in_db;
                    $order->transfer_id       = $transfer_payment->id;
                    $order->save();
                }
            endforeach;
            return response()->json(['status' => true, 'message' => 'success']);
        } catch (\Throwable $e) {
            return response()->json(['result' => 0, 'message' => $e->getMessage() . ' on line ' . $e->getLine()]);
        }
    }

    function cancelSubscription($company_id)
    {
        try {
            $company = Company::find($company_id);
            if ($company->subscription_id != '') {
                if ($company->cancel_subscription == 1) {
                    return response()->json(['result' => false, 'message' => 'this company already cancel subscription.']);
                }
                $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET', 'sk_test_51JykQILs3ZmffSEzPVADK2R89DDuQUBX5yoSfsE9Y7wxprjGYFsXhFau6Gd8uRBJXcYEfNa1QZgt2RFQL7FJwmZT00ck1lqkdn'));
                $responce = $stripe->subscriptions->cancel(
                    $company->subscription_id,
                    []
                );
                $company->cancel_subscription = 1;
                $company->save();
                return response()->json(['result' => true,  'message' => 'success']);
            } else {
                return response()->json(['result' => false, 'message' => 'this company not any subscription.']);
            }
        } catch (\Throwable $e) {
            return response()->json(['result' => 0, 'message' => $e->getMessage() . ' on line ' . $e->getLine()]);
        }
    }
}
