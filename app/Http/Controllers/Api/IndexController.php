<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Company;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Event;
use App\Models\EventImage;
use App\Models\Favourite;
use App\Models\Notification;
use App\Models\Offer;
use App\Models\order;
use App\Models\Product;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class IndexController extends Controller
{

    public function homepage(Request $request)
    {

        $now = \Carbon\Carbon::now();
        $lat  = $request->lat;
        $long = $request->long;
        $ispaymenton = $this->ispaymenton();

        $nearbyCompanies = Company::select(
            "*",
            DB::raw("6371 * acos(cos(radians(" . $lat . "))
                           * cos(radians(companies.lat))
                           * cos(radians(companies.long) - radians(" . $long . "))
                           + sin(radians(" . $lat . "))
                           * sin(radians(companies.lat))) AS distance")
        )
            ->where('status', 0)
            ->orderby("distance", 'asc')
            ->having("distance", "<=", 500);

        if ($ispaymenton) {
            $nearbyCompanies = $nearbyCompanies->whereDate('expiry_date', '>', $now->toDateString());
        }

        $nearbyCompanies = $nearbyCompanies->get();

        // use($allowedRoles)
        $categories = Category::with(['companies' => function ($query) use ($now, $ispaymenton) {
            if ($ispaymenton) {
                $query->with(['products' => function ($pquery) {
                    $pquery->whereHas('company', function ($query) {
                        $query->where('package', 'Platinum E-Service Package');
                    });
                }])
                    ->select('companies.id', 'companies.company_name', 'companies.image', 'companies.category_id')
                    ->whereDate('expiry_date', '>', $now->toDateString())
                    ->where('companies.status', 0)
                    ->limit(50)->orderBy('id', 'desc');
            } else {
                $query->with(['products' => function ($pquery) {
                    $pquery->whereHas('company', function ($query) {
                        $query->where('package', 'Platinum E-Service Package');
                    });
                }])
                    ->select('companies.id', 'companies.company_name', 'companies.image', 'companies.category_id')
                    ->where('companies.status', 0)
                    ->limit(50)->orderBy('id', 'desc');
            }
        }])->get();


        $lfcompanies = Company::limit(50)->orderBy('id', 'desc')->where('status', 0);
        if ($ispaymenton) {
            $lfcompanies = $lfcompanies->whereDate('expiry_date', '>', $now->toDateString());
        }

        $lfcompanies = $lfcompanies->get();

        $lfc = new  Category();
        $lfc->id   = '0';
        $lfc->name = '50 newest companies';
        $lfc->image = 'category/50fnc1.png';
        $lfc->created_at =  null;
        $lfc->updated_at    =  null;
        $lfc->companies =  $lfcompanies;
        $categories->prepend($lfc);

        if ($ispaymenton) {
            $products = Product::limit(50)->orderBy('id', 'desc')
                ->whereHas('company', function ($query) use ($now) {
                    $query->where('package', 'Platinum E-Service Package');
                    $query->whereDate('expiry_date', '>', $now->toDateString());
                })
                ->get();
        } else {
            $products = Product::whereHas('company', function ($query) use ($now) {
                $query->where('package', 'Platinum E-Service Package');
            })
                ->limit(50)
                ->orderBy('id', 'desc')->get();
        }

        $nowtime = Carbon::now();

        $events = Event::with('event_currency')
            ->with('ticket_types')
            ->whereDate('events.end_date', '>', $nowtime)
            ->orderby("events.start_date", "asc")
            ->get();

        $spareData = [];

        if ($events->toArray()) {
            foreach ($events as $eventsval) {
                if (count($eventsval->ticket_types) > 0) {
                    $eventimages = EventImage::where('event_id', $eventsval->id)->get();

                    if ($eventimages->toArray()) {
                        $eventsval->eventimages = $eventimages;
                    } else {
                        $eventsval->eventimages = array();
                    }
                    $spareData[] = $eventsval;
                }
            }
        }

        $events = $spareData;

        $totalcustomers = Customer::count();
        $user = Auth::user();
        $totalunreadnotification = 0;
        if ($user) {
            $totalunreadnotification = Notification::where('user_id', $user->id)->where('isread', '0')->count();
        }
        return response()->json(['result' => 1, "nearbyCompanies" => $nearbyCompanies, "categories" => $categories, 'products' => $products, 'totalcustomers' => $totalcustomers, 'totalunreadnotification' => $totalunreadnotification, 'eventdata' => $events]);
    }

    public function neabycompanies(Request $request)
    {

        $lat = $request->lat;
        $long = $request->long;
        $now = \Carbon\Carbon::now();
        $ispaymenton = $this->ispaymenton();
        $maxdistance = 500;
        if ($request->unit == 'miles') {
            $maxdistance =  805;
        }
        $nearbyCompanies = Company::select(
            "*",
            DB::raw("6371 * acos(cos(radians(" . $lat . "))
                         * cos(radians(companies.lat))
                         * cos(radians(companies.long) - radians(" . $long . "))
                         + sin(radians(" . $lat . "))
                         * sin(radians(companies.lat))) AS distance")
        )
            ->where('status', 0)
            ->orderby("distance", 'asc')
            ->having("distance", "<=", $maxdistance);
        if ($ispaymenton) {
            $nearbyCompanies = $nearbyCompanies->whereDate('expiry_date', '>', $now->toDateString());
        }

        $nearbyCompanies = $nearbyCompanies->get();



        return response()->json(['result' => 1, "nearbyCompanies" => $nearbyCompanies]);
    }

    public function getmoreproducts(Request $request)
    {
        $companies = Company::limit(50)->whereDate('expiry_date', '>', $now->toDateString())->orderBy('id', 'desc');

        if ($request->category_id) {
            $companies = $companies->where('category_id', $request->category_id);
        }

        if ($request->last_id) {
            $companies = $companies->where('id', '>', $request->last_id);
        }
        $companies = $companies->get();
        $ismore = ($product->count() < 10) ? 0 : 1;
        return response()->json(['result' => 1, "products" => $companies]);
    }

    public function getcompanydetail(Request $request)
    {

        $nowtime = Carbon::now();

        $company = Company::with(['activeoffers.company', 'products.company'])->findorfail($request->id);

        if ($company->products->isEmpty()) {
            $offervalid = 0;
        } else {
            $offervalid = 1;
        }


        if ($company) {

            if ($offervalid == 1) {
                foreach ($company->products as $myval) {
                    $offerdata = Offer::select('discount', 'discount_type', 'price')->where('productid', $myval->id)->first();
                    $myval->offerdetails = $offerdata;
                }
            }

            /**
            if ($company->currency_id != null || $company->currency_id != '') {
                $ecurrency = Currency::where('id', $company->currency_id)->first();
                if ($ecurrency->toArray()) {
                    $company->currency = $ecurrency;
                } else {
                    $company->currency = array();
                }
            } else {
                $company->currency = array();
            }
             */

            // if (isset($company->currency->currency->name)) {
            $company->currency->newname = $company->currency->name . ' (' . $company->currency->currency_sign . ')';
            //}

            foreach ($company->products as $keey => $currency_value) {
                $currency_value->currency->newname = $currency_value->currency->name . ' (' . $currency_value->currency_sign . ')';
            }

            $eventcmp = Event::join('companies', 'companies.id', '=', 'events.company_id')
                ->with('ticket_types')
                ->select('events.*')
                ->where('events.company_id', '=', $request->id)
                ->whereDate('events.end_date', '>', $nowtime)
                ->orderby("events.start_date", "asc")
                ->get();

            $spareData = [];

            if ($eventcmp->toArray()) {
                foreach ($eventcmp as $eventsval) {
                    if (count($eventsval->ticket_types) > 0) {

                        $eventimages = EventImage::where('event_id', $eventsval->id)->get();

                        if ($eventimages->toArray()) {
                            $eventsval->eventimages = $eventimages;
                        } else {
                            $eventsval->eventimages = array();
                        }

                        $spareData[] = $eventsval;
                    }
                }
            }

            $eventcmp = $spareData;

            $user = Auth::user();

            $company->is_favourite = 0;
            if ($user) {
                $fav = Favourite::where(['user_id' => $user->id, 'company_id' => $company->id])->first();

                if ($fav) {
                    $company->is_favourite = 1;
                }
            }

            $company->is_customer = 0;
            if ($user) {
                $cus = Customer::where(['user_id' => $user->id, 'company_id' => $company->id])->first();
                if ($cus) {
                    $company->is_customer = 1;
                }
            }

            $active = 1;
            if ($company->expiry_date && $company->expiry_date > date('Y-m-d')) {
                $active = 0;
            }
            $company->is_expiry = $active;

            // ========== order history =================

            $download_order_history = URL::to('/') . '/companyorderpdf/' . $company->id;
            $company->download_order_history = $download_order_history;

            $email_order_history = URL::to('/') . '/email_companyorderpdf/' . $company->id;
            $company->email_order_history = $email_order_history;

            // ========== company product listing =================

            $download_company_product_list = URL::to('/') . '/companyproducts/' . $company->id;
            $company->download_company_product_list = $download_company_product_list;

            $email_company_product_list = URL::to('/') . '/email_companyproducts/' . $company->id . '/' . $user->id;
            $company->email_company_product_list = $email_company_product_list;

            // ========== company offer history =================

            $download_company_offer_history = URL::to('/') . '/companyoffer/' . $company->id;
            $company->download_company_offer_history = $download_company_offer_history;

            $email_company_offer_history = URL::to('/') . '/email_companyoffer/' . $company->id . '/' . $user->id;
            $company->email_company_offer_history = $email_company_offer_history;

            $stripe_account_id = Auth::user()->stripe_account_id;
            if ($stripe_account_id == null) {
                $stripe_status = 0;
            } else {
                $stripe_status = 1;
            }
            return response()->json(['result' => 1, "company" => $company, 'eventdata' => $eventcmp, 'stripe_status' =>  $stripe_status]);
        }
    }

    public function search(Request $request)
    {
        $now = \Carbon\Carbon::now();
        $ispaymenton = $this->ispaymenton();
        $compnies = Company::limit(20)->offset(($request->page - 1) * 20)->where('status', 0)->orderby("company_name", "desc");

        if ($ispaymenton) {
            $compnies = $compnies->whereDate('expiry_date', '>', $now->toDateString());
        }

        if ($request->company_name) {
            $compnies->where('company_name', 'LIKE', '%' . $request->company_name . '%');
        }

        if ($request->city) {
            $compnies->where('company_name', 'LIKE', $request->city);
        }

        if ($request->postcode) {
            $compnies->where('postcode', $request->postcode);
        }

        $compnies = $compnies->get();

        return response()->json(['result' => 1, "companies" => $compnies]);
    }

    public function companyorderpdf($companyid)
    {
        // dd($companyid);

        // $user=Auth::user();


        // $cmpproductorder=order::leftjoin('companies','companies.id','orders.company_id')
        // ->leftjoin('users','users.id','orders.userid')
        // ->leftjoin('products','products.id','orders.productid')
        // ->leftjoin('currencies','currencies.id','companies.currency_id')
        // ->select('orders.*','currencies.name as currency','products.product_name','users.user_number',DB::raw('SUM(orders.totalprice) as ordertotal'),DB::raw('SUM(orders.quantity) as orderquantity'))
        // ->groupBy('orders.orderno')
        // ->where('orders.company_id','=',$companyid)
        // ->where('orders.event_id','=',null)
        // ->orderby("orders.created_at","desc")
        // ->get();

        $cmpproductorder = order::join('companies', 'companies.id', 'orders.company_id')
            ->join('users', 'users.id', 'orders.userid')
            ->join('products', 'products.id', 'orders.productid')
            ->join('currencies', 'currencies.id', 'companies.currency_id')
            ->select('orders.*', 'currencies.name as currency', 'products.product_name', 'users.user_number', 'orders.totalprice', 'orders.quantity')
            // ->groupBy('orders.orderno')
            ->where('orders.company_id', '=', $companyid)
            ->where('orders.event_id', '=', null)
            ->orderby("orders.created_at", "desc")
            ->get();

        // dd($cmpproductorder);
        // dd($cmpproductorder);

        $getcompany = Company::where('id', $companyid)->first();

        if ($getcompany->toArray()) {
            $company_image = URL::to('/') . '/storage/public/' . $getcompany->image;
            $getcompany->image = $company_image;
        }

        // $cmpeventorder=order::join('companies','companies.id','orders.company_id')
        // ->leftjoin('users','users.id','orders.userid')
        // ->leftjoin('events','events.id','orders.event_id')
        // ->leftjoin('currencies','currencies.id','companies.currency_id')
        // ->select('orders.*','currencies.name as currency','events.eventname','users.user_number',DB::raw('SUM(orders.totalprice) as ordertotal'),DB::raw('SUM(orders.quantity) as orderquantity'))
        // // ->groupBy('orders.orderno')
        // ->where('orders.company_id','=',$companyid)
        // ->where('orders.productid','=',null)
        // ->orderby("orders.created_at","desc")
        // ->get();

        $cmpeventorder = order::join('companies', 'companies.id', 'orders.company_id')
            ->join('users', 'users.id', 'orders.userid')
            ->join('events', 'events.id', 'orders.event_id')
            ->join('currencies', 'currencies.id', 'companies.currency_id')
            ->select('orders.*', 'currencies.name as currency', 'events.eventname', 'users.user_number', 'orders.totalprice', 'orders.quantity')
            // ->groupBy('orders.orderno')
            ->where('orders.company_id', '=', $companyid)
            ->where('orders.productid', '=', null)
            ->orderby("orders.created_at", "desc")
            ->get();
        // dd($cmpeventorder);

        if ($cmpeventorder->toArray()) {
            foreach ($cmpeventorder as $eventvalue) {

                $ticketorderlist = order::select("ticketrefno")->where('orderno', '=', $eventvalue->orderno)->get();

                if ($ticketorderlist->toArray()) {
                    $eventvalue->ticketlist = $ticketorderlist;
                } else {
                    $eventvalue->ticketlist = array();
                }
            }
        }


        // return response()->json($cmpproductorder);

        $pdf = Pdf::loadView('pdf.companyorderpdf', compact('cmpproductorder', 'getcompany', 'cmpeventorder'));
        return $pdf->stream('newevent.pdf', array('Attachment' => 0));
    }

    public function email_companyorderpdf($companyid)
    {
        $user = Auth::user();

        $cmpproductorder = order::leftjoin('companies', 'companies.id', 'orders.company_id')
            ->leftjoin('users', 'users.id', 'orders.userid')
            ->leftjoin('products', 'products.id', 'orders.productid')
            ->leftjoin('currencies', 'currencies.id', 'companies.currency_id')
            ->select('orders.*', 'currencies.name as currency', 'products.product_name', 'users.user_number', 'users.first_name', 'users.surname', 'users.email', DB::raw('SUM(orders.totalprice) as ordertotal'), DB::raw('SUM(orders.quantity) as orderquantity'))
            ->groupBy('orders.orderno')
            ->where('orders.company_id', '=', $companyid)
            ->where('orders.event_id', '=', null)
            ->orderby("orders.created_at", "desc")
            ->get();

        $getcompany = Company::where('id', $companyid)->first();

        if ($getcompany->toArray()) {
            $company_image = URL::to('/') . '/storage/public/' . $getcompany->image;
            $getcompany->image = $company_image;
        }

        $cmpeventorder = order::leftjoin('companies', 'companies.id', 'orders.company_id')
            ->leftjoin('users', 'users.id', 'orders.userid')
            ->leftjoin('events', 'events.id', 'orders.event_id')
            ->leftjoin('currencies', 'currencies.id', 'companies.currency_id')
            ->select('orders.*', 'currencies.name as currency', 'events.eventname', 'users.user_number', 'users.first_name', 'users.surname', 'users.email', DB::raw('SUM(orders.totalprice) as ordertotal'), DB::raw('SUM(orders.quantity) as orderquantity'))
            ->groupBy('orders.orderno')
            ->where('orders.company_id', '=', $companyid)
            ->where('orders.productid', '=', null)
            ->orderby("orders.created_at", "desc")
            ->get();
        // dd($cmpeventorder);

        if ($cmpeventorder->toArray()) {
            foreach ($cmpeventorder as $eventvalue) {

                $ticketorderlist = order::select("ticketrefno")->where('orderno', '=', $eventvalue->orderno)->get();

                if ($ticketorderlist->toArray()) {
                    $eventvalue->ticketlist = $ticketorderlist;
                } else {
                    $eventvalue->ticketlist = array();
                }

                $useremail = $eventvalue->email;
                $username = $eventvalue->first_name . ' ' . $eventvalue->last_name;
            }

            // $pdf = PDF::loadView('pdf.companyorderpdf',compact('cmpproductorder','getcompany','cmpeventorder'));
            // // return $pdf->stream('newevent.pdf', array('Attachment'=>0));

            // Mail::send('pdf.companyorderpdf', compact('cmpproductorder','getcompany','cmpeventorder'), function($message) use ($pdf,$useremail,$username) {
            //     // $message->to( $data['email'], $data['name'])
            //     $message->from($_ENV['MAIL_USERNAME'],$_ENV['MAIL_FROM_NAME']);
            //     $message->to($useremail,$username)
            //             ->subject('Company Orders PDF')
            //             ->attachData($pdf->output(), "orderhistory.pdf");
            // });

            // if(Mail::failures())
            // {
            //     return response()->json(['success' => 0, 'message' => 'Mail can\'t send.']);
            // }
            // else
            // {
            //     return response()->json(['success' => 1, 'message' => 'Mail sent.']);
            // }
        }

        $pdf = PDF::loadView('pdf.companyorderpdf', compact('cmpproductorder', 'getcompany', 'cmpeventorder'));
        // return $pdf->stream('newevent.pdf', array('Attachment'=>0));

        Mail::send('pdf.companyorderpdf', compact('cmpproductorder', 'getcompany', 'cmpeventorder'), function ($message) use ($pdf, $useremail, $username) {
            // $message->to( $data['email'], $data['name'])
            $message->from($_ENV['MAIL_USERNAME'], $_ENV['MAIL_FROM_NAME']);
            $message->to($useremail, $username)
                ->subject('Company Orders PDF')
                ->attachData($pdf->output(), "orderhistory.pdf");
        });

        if (Mail::failures()) {
            return response()->json(['success' => 0, 'message' => 'Mail can\'t send.']);
        } else {
            return response()->json(['success' => 1, 'message' => 'Mail sent.']);
        }
        // return response()->json($cmpproductorder);

        // $pdf = PDF::loadView('pdf.companyorderpdf',compact('cmpproductorder','getcompany','cmpeventorder'));
        // return $pdf->stream('newevent.pdf', array('Attachment'=>0));

    }

    public function companyproductspdf($companyid)
    {
        $getproducts = Product::leftjoin('currencies', 'currencies.id', 'products.currency_id')
            ->where('company_id', $companyid)
            ->get();

        $getcompany = Company::where('id', $companyid)->first();

        if ($getcompany->toArray()) {
            $company_image = URL::to('/') . '/storage/public/' . $getcompany->image;
            $getcompany->image = $company_image;
        }

        // return response()->json($getproducts);

        $pdf = PDF::loadView('pdf.companyproductspdf', compact('getproducts', 'getcompany'));
        return $pdf->stream('productlist.pdf', array('Attachment' => 0));
    }

    public function email_companyproductspdf($companyid, $userid)
    {
        $user = User::where('id', $userid)->first();
        $useremail = $user->email;
        $username = $user->first_name . ' ' . $user->surname;

        $getproducts = Product::leftjoin('currencies', 'currencies.id', 'products.currency_id')
            ->where('company_id', $companyid)
            ->get();

        $getcompany = Company::where('id', $companyid)->first();

        if ($getcompany->toArray()) {
            $company_image = URL::to('/') . '/storage/public/' . $getcompany->image;
            $getcompany->image = $company_image;
        }

        // return response()->json($getproducts);

        $pdf = PDF::loadView('pdf.companyproductspdf', compact('getproducts', 'getcompany'));

        // return $pdf->stream('newevent.pdf', array('Attachment'=>0));

        Mail::send('pdf.companyproductspdf', compact('getproducts', 'getcompany'), function ($message) use ($pdf, $useremail, $username) {
            // $message->to( $data['email'], $data['name'])
            $message->from($_ENV['MAIL_USERNAME'], $_ENV['MAIL_FROM_NAME']);
            $message->to($useremail, $username)
                ->subject('Product List PDF')
                ->attachData($pdf->output(), "productlist.pdf");
        });

        if (Mail::failures()) {
            return response()->json(['success' => 0, 'message' => 'Mail can\'t send.']);
        } else {
            return response()->json(['success' => 1, 'message' => 'Mail sent.']);
        }

        // return $pdf->stream('productlist.pdf', array('Attachment'=>0));
    }

    public function companyofferpdf($companyid)
    {
        $getoffer = Offer::leftjoin('companies', 'companies.id', 'offers.company_id')
            ->leftjoin('currencies', 'currencies.id', 'companies.currency_id')
            ->select('offers.*', 'currencies.name as currency')
            ->where('offers.company_id', $companyid)
            ->get();

        $getcompany = Company::where('id', $companyid)->first();

        if ($getcompany->toArray()) {
            $company_image = URL::to('/') . '/storage/public/' . $getcompany->image;
            $getcompany->image = $company_image;
        }

        // return response()->json($getoffer);

        $pdf = PDF::loadView('pdf.companyoffer', compact('getoffer', 'getcompany'));
        return $pdf->stream('offerlist.pdf', array('Attachment' => 0));
    }

    public function email_companyofferpdf($companyid, $userid)
    {
        $user = User::where('id', $userid)->first();
        $useremail = $user->email;
        $username = $user->first_name . ' ' . $user->surname;

        $getoffer = Offer::leftjoin('companies', 'companies.id', 'offers.company_id')
            ->leftjoin('currencies', 'currencies.id', 'companies.currency_id')
            ->select('offers.*', 'currencies.name as currency')
            ->where('offers.company_id', $companyid)
            ->get();

        $getcompany = Company::where('id', $companyid)->first();

        if ($getcompany->toArray()) {
            $company_image = URL::to('/') . '/storage/public/' . $getcompany->image;
            $getcompany->image = $company_image;
        }

        // return response()->json($getoffer);

        $pdf = PDF::loadView('pdf.companyoffer', compact('getoffer', 'getcompany'));

        Mail::send('pdf.companyoffer', compact('getoffer', 'getcompany'), function ($message) use ($pdf, $useremail, $username) {
            // $message->to( $data['email'], $data['name'])
            $message->from($_ENV['MAIL_USERNAME'], $_ENV['MAIL_FROM_NAME']);
            $message->to($useremail, $username)
                ->subject('Company Offers PDF')
                ->attachData($pdf->output(), "offerhistory.pdf");
        });

        if (Mail::failures()) {
            return response()->json(['success' => 0, 'message' => 'Mail can\'t send.']);
        } else {
            return response()->json(['success' => 1, 'message' => 'Mail sent.']);
        }
        // return $pdf->stream('offerlist.pdf', array('Attachment'=>0));
    }

    public function GetCartegoryProducts(Request $request)
    {
        $now = \Carbon\Carbon::now();
        $ispaymenton = $this->ispaymenton();
        //Last 50 notification thing
        if ($request->id == 0) {
            $compnies = Company::limit(50)->where('status', 0);

            if ($ispaymenton) {
                $compnies = $compnies->whereDate('expiry_date', '>', $now->toDateString());
            }

            if ($request->sortby == 'popular') {
                $compnies->orderby("totalfavorite", "desc")->latest();
            } else if ($request->sortby == 'latest') {
                $compnies->orderby("created_at", "desc")->latest();
            } else {
                $compnies->latest();
            }
            $compnies = $compnies->get();
            return response()->json(['result' => 1, "companies" => $compnies, 'last50' => true]);
        } else {

            $compnies = Company::where("category_id", $request->id)->where('status', 0);

            if ($ispaymenton) {
                $compnies = $compnies->whereDate('expiry_date', '>', $now->toDateString());
            }

            if ($request->sortby == 'popular') {
                $compnies->orderby("totalfavorite", "desc");
            }

            if ($request->sortby == 'latest') {
                $compnies->orderby("created_at", "desc");
            }
            $compnies = $compnies->get();

            return response()->json(['result' => 1, "companies" => $compnies]);
        }
    }

    protected function ispaymenton()
    {
        $cs = DB::table('settings')->where('control_settings', 'PAYMENT MANDATORY')->first();
        if ($cs) {
            return $cs->on_off;
        }
        return false;
    }
}
