<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Company;
use App\Models\Currency;
use App\Models\Event;
use App\Models\EventImage;
use App\Models\order;
use App\Models\Status;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Stripe;

class EventController extends Controller
{
    public function store(Request $request)
    {
        $eventname = $request->eventname;
        $company_id = $request->company_id;
        $price = $request->price;
        $currency_id = $request->currency_id;
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        // $location = $request->location;
        $termscondition = $request->termscondition;
        $organizer = $request->organizer;
        $contactno = $request->contactno;
        $maxnoticket = $request->maxnoticket;
        $flyerimage = $request->flyerimage;
        $eventimage = $request->eventimage;

        $building_number = $request->building_number;
        $address_line_1 = $request->address_line_1;
        $city = $request->city;
        $postcode = $request->postcode;
        $country = $request->country;

        if ($eventname == "" or $eventname == NULL) {
            return response()->json(['result' => 0, 'message' => 'eventname is required.']);
        }
        if ($company_id == "" or $company_id == NULL) {
            return response()->json(['result' => 0, 'message' => 'company_id is required.']);
        }
        if ($start_date == "" or $start_date == NULL) {
            return response()->json(['result' => 0, 'message' => 'start_date is required.']);
        }
        if ($end_date == "" or $end_date == NULL) {
            return response()->json(['result' => 0, 'message' => 'end_date is required.']);
        }
        if ($termscondition == "" or $termscondition == NULL) {
            return response()->json(['result' => 0, 'message' => 'termscondition is required.']);
        }
        if ($organizer == "" or $organizer == NULL) {
            return response()->json(['result' => 0, 'message' => 'organizer is required.']);
        }
        if ($contactno == "" or $contactno == NULL) {
            return response()->json(['result' => 0, 'message' => 'contactno is required.']);
        }
        if ($maxnoticket == "" or $maxnoticket == NULL) {
            return response()->json(['result' => 0, 'message' => 'maxnoticket is required.']);
        }
        if ($flyerimage == "" or $flyerimage == NULL) {
            return response()->json(['result' => 0, 'message' => 'flyerimage is required.']);
        }
        if ($building_number == "" or $building_number == NULL) {
            return response()->json(['result' => 0, 'message' => 'Building Number is required.']);
        }
        if ($address_line_1 == "" or $address_line_1 == NULL) {
            return response()->json(['result' => 0, 'message' => 'Address is required.']);
        }
        if ($city == "" or $city == NULL) {
            return response()->json(['result' => 0, 'message' => 'City is required.']);
        }
        if ($postcode == "" or $postcode == NULL) {
            return response()->json(['result' => 0, 'message' => 'Postcode is required.']);
        }
        if ($country == "" or $country == NULL) {
            return response()->json(['result' => 0, 'message' => 'country is required.']);
        }

        $eventdata = new Event();

        $flyername = strtolower(time() . $flyerimage->getClientOriginalName());
        $flyerpath = public_path() . '/mainflyer/';
        $flyerimage->move($flyerpath, $flyername);

        $eventdata->eventname = $eventname;
        $eventdata->company_id = $company_id;
        $eventdata->currency_id = $currency_id;
        $eventdata->start_date = $start_date;
        $eventdata->end_date = $end_date;
        // $eventdata->location = $location;
        $eventdata->termscondition = $termscondition;
        $eventdata->organizer = $organizer;
        $eventdata->contactno = $contactno;
        $eventdata->max_no_ticket = $maxnoticket;
        $eventdata->availableticket = $maxnoticket;
        $eventdata->flyerimage = $flyername;
        $eventdata->building_number = $building_number;
        $eventdata->address_line_1 = $address_line_1;
        $eventdata->city = $city;
        $eventdata->postcode = $postcode;
        $eventdata->country = $country;
        $eventdata->save();

        if ($eventimage != "" or $eventimage != NULL) {
            $imgcnt = count($request->eventimage);
            if ($imgcnt >= 10) {
                return response()->json(['result' => 0, 'message' => 'Please select events picture less than or equal to 10.']);
            } else {
                foreach ($request->eventimage as $key) {
                    $eventdataimages = new EventImage();
                    $image = $key;
                    $filename = strtolower(time() . $image->getClientOriginalName());
                    $dbfilename = '/events/' . strtolower(time() . $image->getClientOriginalName());
                    $path = public_path() . '/events/';
                    $image->move($path, $filename);

                    $eventdataimages->event_id = $eventdata->id;
                    $eventdataimages->eventimage = $dbfilename;
                    $eventdataimages->save();
                }
            }
        }


        if ($eventdata->save()) {
            $prefix = 'E';
            $lastinsertedid = $eventdata->id;
            $rand1 = mt_rand(100, 999);
            $rand2 = mt_rand(100, 999);
            $eventref = $prefix . $rand1 . $rand2;

            $setref = DB::table('events')
                ->where('id', $lastinsertedid)
                ->update(['eventref' => $eventref]);
            if ($setref == true) {
                return response()->json([
                    'result' => 1,
                    'message' => 'Event Added successfully.',
                    'data' => [
                        'event_id' => $eventdata->id
                    ]
                ]);
            } else {
                return response()->json(['result' => 0, 'message' => 'Something went wrong in Reference ID.']);
            }
        } else {
            return response()->json(['result' => 0, 'message' => 'Something went wrong please try again later.']);
        }
    }

    public function editevent(Request $request)
    {

        $validator = Validator::make($request->all(), [
            // 'flyerimage' => 'required',
            'eventname' => 'required',
            'company_id' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            'termscondition' => 'required',
            'organizer' => 'required',
            'contactno' => 'required',
            'maxnoticket' => 'required',
            'building_number' => 'required',
            'address_line_1' => 'required',
            'city' => 'required',
            'postcode' => 'required',
            'country' => 'required',
            // 'eventimage' => 'required',
            // 'deletedimages' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['result' => 0, 'message' => "Validation error", 'errors' => $validator->errors()->messages()]);
        }

        $eventid = $request->eventid;

        if ($eventid) {
            $eventdata = Event::where('id', $eventid)->first();

            if ($eventdata) {
                if ($request->flyerimage != '' || $request->flyerimage != null) {
                    $flyerimage = $request->flyerimage;
                    $flyername = strtolower(time() . $flyerimage->getClientOriginalName());
                    $flyerpath = public_path() . '/mainflyer/';
                    $flyerimage->move($flyerpath, $flyername);
                }

                $eventdata->eventname = ($request->eventname) ? $request->eventname : $eventdata->eventname;
                $eventdata->company_id = ($request->company_id) ? $request->company_id : $eventdata->company_id;
                // $eventdata->price = ($request->price) ? $request->price : $eventdata->price;
                $eventdata->start_date = ($request->start_date) ? $request->start_date : $eventdata->start_date;
                $eventdata->end_date = ($request->end_date) ? $request->end_date : $eventdata->end_date;
                // $eventdata->location = ($request->location) ? $request->location : $eventdata->location;
                $eventdata->termscondition = ($request->termscondition) ? $request->termscondition : $eventdata->termscondition;
                $eventdata->organizer = ($request->organizer) ? $request->organizer : $eventdata->organizer;
                $eventdata->contactno = ($request->contactno) ? $request->contactno : $eventdata->contactno;
                $eventdata->max_no_ticket = ($request->maxnoticket) ? $request->maxnoticket : $eventdata->maxnoticket;
                $eventdata->flyerimage = ($request->flyerimage) ? $flyername : $eventdata->flyerimage;

                $eventdata->building_number = ($request->building_number) ? $request->building_number : $eventdata->building_number;
                $eventdata->address_line_1 = ($request->address_line_1) ? $request->address_line_1 : $eventdata->address_line_1;
                $eventdata->city = ($request->city) ? $request->city : $eventdata->city;
                $eventdata->postcode = ($request->postcode) ? $request->postcode : $eventdata->postcode;
                $eventdata->country = ($request->country) ? $request->country : $eventdata->country;

                $eventdata->update();

                if ($request->eventimage) {
                    $imgcnt = count($request->eventimage);
                    // $modifyimage = EventImage::where('event_id',$eventid)->get();
                    if ($imgcnt <= 10) {
                        foreach ($request->eventimage as $key) {
                            $eventdataimages = new EventImage;

                            $image = $key;
                            $filename = strtolower(time() . $image->getClientOriginalName());
                            $dbfilename = '/events/' . strtolower(time() . $image->getClientOriginalName());
                            $path = public_path() . '/events/';
                            $image->move($path, $filename);

                            $eventdataimages->event_id = $eventdata->id;
                            $eventdataimages->eventimage = $dbfilename;
                            $eventdataimages->save();
                        }
                    } else {
                        return response()->json(['result' => 0, 'message' => 'Please select events picture less than or equal to 10.']);
                    }
                }

                if ($request->deletedimages) {
                    $imgs = explode(',', $request->deletedimages);

                    if (!empty($imgs)) {
                        foreach ($imgs as $img_id) {
                            $di = EventImage::find($img_id);

                            if ($di) {
                                $di->delete();
                            }
                        }
                    }
                }

                if ($eventdata->update() || $eventdataimages->save()) {
                    return response()->json(['result' => 1, 'message' => 'Event updated successfully.']);
                } else {
                    return response()->json(['result' => 0, 'message' => 'Something went wrong please try again later.']);
                }
            } else {
                return response()->json(['result' => 0, 'message' => 'No data found for this Event.']);
            }
        } else {
            return response()->json(['result' => 0, 'message' => 'eventid is required.']);
        }
    }

    public function delete(Request $request)
    {
        $eventid = $request->eventid;

        if ($eventid != null) {
            $event = Event::where('id', $eventid)->count();

            if ($event != 0) {
                $changestatus = DB::table('events')->where('id', $eventid)->update(['status' => '0']);
                if ($changestatus) {
                    return response()->json(['result' => 1, 'message' => 'Event Canceled successfully.']);
                } else {
                    return response()->json(['result' => 0, 'message' => 'Something Went wrong please try later']);
                }
            } else {
                return response()->json(['result' => 0, 'message' => 'This eventid is not available.']);
            }
        } else {
            return response()->json(['result' => 0, 'message' => 'eventid is required.']);
        }
    }

    public function cmpevent(Request $request)
    {
        $companyid = $request->companyid;
        $datenow = Carbon::now()->toDateString();
        $date = Carbon::createFromFormat('Y-m-d', $datenow);

        $eventdata = Event::where('company_id', $companyid)
            ->with('ticket_types')
            ->select(DB::raw('events.id,events.eventref,events.eventname,events.flyerimage,events.start_date,events.end_date,events.location,events.building_number,events.address_line_1,events.status,events.city,events.postcode,events.country,events.termscondition,events.organizer,events.contactno,events.max_no_ticket as ticket_amount,events.availableticket,events.currency_id,events.company_id,(events.max_no_ticket - events.availableticket) as ticket_sold'))
            ->orderby("events.created_at", "desc")
            ->get();
        if ($eventdata->toArray()) {
            foreach ($eventdata as $eventval) {
                $eventdataimages = EventImage::where('event_id', $eventval->id)->get();

                $eventval->flyerimage = url('public/mainflyer/' . $eventval->flyerimage);

                if ($eventval->start_date != "") {
                    $startdate = $eventval->start_date;
                }

                if ($eventval->end_date != "") {
                    $enddate = $eventval->end_date;
                }

                if ($eventval->status != 0) {
                    if ($date >= $startdate && $date <= $enddate) {
                        $eventval->status = "Started";
                    } else if ($date->gt($enddate)) {
                        $eventval->status = "Ended";
                    } else if ($date < $startdate && $date < $enddate) {
                        $eventval->status = "Going Ahead";
                    }
                } else {
                    $eventval->status = "Cancelled";
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

                if (count($eventcurrency->ticket_types) > 0) {
                    $eventcurrency->event_status = 1;
                } else {
                    $eventcurrency->event_status = 0;
                }
            }


            return response()->json(['result' => 1, 'eventdata' => $eventdata]);
        } else {
            return response()->json(['result' => 0, 'message' => 'No events found for this company.']);
        }
    }

    public function allevents(Request $request)
    {
        $userid = $request->userid;

        if ($userid == "" or $userid == NULL) {
            return response()->json(['result' => 0, 'message' => 'userid is required.']);
        }

        $alldata = Company::join('events', 'events.company_id', 'companies.id')->where('companies.user_id', $userid)->get();

        if ($alldata->toArray()) {
            foreach ($alldata as $alleventval) {
                $eventdataimages = EventImage::where('event_id', $alleventval->id)->get();

                $alleventval->flyerimage = url('public/mainflyer/' . $alleventval->flyerimage);

                if ($eventdataimages->toArray()) {
                    $i = 0;
                    foreach ($eventdataimages as $imagesval) {
                        $imagesval->eventimage = url('public' . $imagesval->eventimage);
                        $data[$i] = $imagesval->eventimage;
                        $eventdataimages->eventimage = $data[$i];
                        $i++;
                    }

                    $alleventval->eventimages = $eventdataimages;
                } else {
                    $alleventval->eventimages = array();
                }
            }

            return response()->json(['result' => 1, 'eventdata' => $alldata]);
        } else {
            return response()->json(['result' => 0, 'message' => 'No events found for this user.']);
        }
    }


    public function singleevent(Request $request)
    {

        $datenow = Carbon::now()->toDateString();
        $date = Carbon::createFromFormat('Y-m-d', $datenow);


        $eventid = $request->eventid;

        if ($eventid) {
            $eventdata = Event::where('id', $eventid)->first();

            if ($eventdata) {

                if ($eventdata->status != 0) {
                    if ($date >= $eventdata->start_date && $date <= $eventdata->end_date) {
                        $eventdata->status = "Going Ahead";
                    } else if ($date->gt($eventdata->end_date)) {
                        $eventdata->status = "ended";
                    } else if ($date->eq($eventdata->start_date)) {
                        $eventdata->status = "started";
                    } else if ($date < $eventdata->start_date && $date < $eventdata->end_date) {
                        $eventdata->status = "Not Started";
                    }
                } else {
                    $eventdata->status = 'Cancelled';
                }


                $eventdata->flyerimage = url('public/mainflyer/' . $eventdata->flyerimage);

                $eventdataimages = EventImage::where('event_id', $eventid)->get();

                if ($eventdataimages->toArray()) {
                    $i = 0;
                    foreach ($eventdataimages as $imagesval) {
                        $imagesval->eventimage = url('public' . $imagesval->eventimage);
                        $data[$i] = $imagesval->eventimage;
                        $eventdataimages->eventimage = $data[$i];
                        $i++;
                    }

                    $eventdata->eventimages = $eventdataimages;
                } else {
                    $eventdata->eventimages = array();
                }

                return response()->json(['result' => 1, 'event' => $eventdata]);
            } else {
                return response()->json(['result' => 0, 'message' => 'No event found for this eventid.']);
            }
        } else {
            return response()->json(['result' => 0, 'message' => 'eventid is required.']);
        }
    }

    public function eventdetails(Request $request)
    {

        $datenow = Carbon::now()->toDateString();
        $date = Carbon::createFromFormat('Y-m-d', $datenow);

        $eventid = $request->eventid;

        if ($eventid == "" or $eventid == NULL) {
            return response()->json(['result' => 0, 'message' => 'eventid is required.']);
        }

        $eventinfo = Event::where('id', $eventid)->first();

        if ($eventinfo) {
            if ($eventinfo->status != 0) {
                if ($date >= $eventinfo->start_date && $date <= $eventinfo->end_date) {
                    $eventinfo->status = "Going Ahead";
                } else if ($date->gt($eventinfo->end_date)) {
                    $eventinfo->status = "ended";
                } else if ($date->eq($eventinfo->start_date)) {
                    $eventinfo->status = "started";
                } else if ($date < $eventinfo->start_date && $date < $eventinfo->end_date) {
                    $eventinfo->status = "Not Started";
                }
            } else {
                $eventinfo->status = 'Cancelled';
            }

            $eventinfo->flyerimage = url('public/mainflyer/' . $eventinfo->flyerimage);

            $eventdataimages = EventImage::where('event_id', $eventid)->get();

            $soldtickets = order::where('event_id', $eventid)
                ->select(DB::raw('SUM(quantity) as tickets'))
                ->first();


            $soldtickets = $soldtickets->tickets;
            $totaltickets = $eventinfo->max_no_ticket;
            $availabletickets = $totaltickets - $soldtickets;

            $eventinfo->ticket_amount = $eventinfo->max_no_ticket;
            $eventinfo->ticket_sold = $soldtickets;
            // $eventinfo->ticket_available = $availabletickets;

            if ($eventdataimages->toArray()) {
                $i = 0;
                foreach ($eventdataimages as $imagesval) {
                    $imagesval->eventimage = url('public' . $imagesval->eventimage);
                    $data[$i] = $imagesval->eventimage;
                    $eventdataimages->eventimage = $data[$i];
                    $i++;
                }

                $eventinfo->eventimages = $eventdataimages;
            } else {
                $eventinfo->eventimages = array();
            }



            return response()->json(['result' => 1, 'eventinfo' => $eventinfo]);
        } else {
            return response()->json(['result' => 0, 'message' => 'No record found for this event id.']);
        }
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

    public function ticketpurchase(Request $request)
    {
        Log::info(['request' => $request]);
        $userid = $request->userid;
        $eventid = $request->eventid;
        $payment_method = $request->payment_method;

        $ticket_data = $request->ticket_data;

        if ($userid == "" or $userid == NULL) {
            return response()->json(['result' => 0, 'message' => 'userid is required.']);
        }
        if ($eventid == "" or $eventid == NULL) {
            return response()->json(['result' => 0, 'message' => 'eventid is required.']);
        }

        if ($ticket_data == [] or $ticket_data == NULL) {
            return response()->json(['result' => 0, 'message' => 'ticket_data is required.']);
        }

        $totalorderprice = 0;
        $ordernotemt = $this->generateRandomString(6);
        foreach ($ticket_data as $ticketFromLoop) {
            $qty = $ticketFromLoop['qty'];
            $ticket_type_id = $ticketFromLoop['ticket_type_id'];

            if ($qty == "" or $qty == NULL) {
                return response()->json(['result' => 0, 'message' => 'qty is required.']);
            }

            if ($ticket_type_id == "" or $ticket_type_id == NULL) {
                return response()->json(['result' => 0, 'message' => 'ticket_type_id is required.']);
            }
        }

        $event = Event::where('id', $eventid)->first();
        $outerComplete = 0;

        $prefix = 'O';
        $rand1 = mt_rand(100000, 999999);
        $orderno = $prefix . $rand1;

        foreach ($ticket_data as $ticketFromLoop) {

            $eventcompany = $event->company_id;
            $availableticket = $event->availableticket;

            $qty = $ticketFromLoop['qty'];
            $ticket_type_id = $ticketFromLoop['ticket_type_id'];

            $ticketType = TicketType::where('id', $ticket_type_id)->first();
            $price = $ticketType->price;
            $price = explode(' ', $price);
            $singleprice = $price[1];
            $totalprice = $price[1] * $qty;
            $finalprice = $price[0] . " " . $totalprice;

            if ($qty > $availableticket) {
                return response()->json(['result' => 0, 'message' => 'Tickets are Not available. Please try with different quantity.']);
            } else {
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

                for ($i = 0; $i < $qty; $i++) {

                    $totalorderprice =  +$totalorderprice + $singleprice;
                    $prefix = 'T';
                    $rand1 = mt_rand(100, 999);
                    $rand2 = mt_rand(100000, 999999);
                    $ref = $prefix . $rand1 . $rand2;

                    $ticket = new order;
                    $ticket->userid = $userid;
                    $ticket->event_id = $eventid;
                    $ticket->ticket_type_id = $ticket_type_id;
                    $ticket->company_id = $eventcompany;
                    $ticket->quantity = 1;  //for the data management store according to ticketreference
                    $ticket->price = $singleprice;
                    $ticket->totalprice = $singleprice;
                    $ticket->event_status = 1;
                    $ticket->ticketrefno = $ref;
                    $ticket->orderno = $orderno;
                    $ticket->ordernotemt = $ordernotemt;
                    $ticket->paymentmethod = $payment_method;
                    $ticket->is_order = $is_order;
                    $ticket->save();
                }

                $finalticketqty = $availableticket - $qty;
                $event->availableticket = $finalticketqty;
                $modifynewqty = $event->save();

                if ($modifynewqty == true) {
                    $outerComplete = $outerComplete + 1;
                }
            }
        }

        if ($outerComplete == count($ticket_data)) {

            $company = Company::find($event->company_id);
            $currency = Currency::find($event->currency_id);

            //$stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
            //dd($totalorderprice);

            if ($request->payment_method == 1 && $is_free == 1) {

                $stripe = new \Stripe\StripeClient("sk_test_51LqDGEDgZHqv5TzUMHUSQILyRMzaLLQXQ8lzZZiHuSNF8UYPfDi2nsGMdqRPGhMPvwoQve6JcZEJCDNOOpaOpkn400gibP49JV");
                $code = $currency->currency_code;
                if ($code == 'BIF' || $code == 'CLP' || $code == 'DJF' || $code == 'GNF' || $code == 'JPY' || $code == 'KMF' || $code == 'KRW' || $code == 'MGA' || $code == 'PYG' || $code == 'RWF' || $code == 'UGX' || $code == 'VND' || $code == 'VUV' || $code == 'XAF' || $code == 'XOF' || $code == 'XPF') {
                } else {
                    $totalorderprice = $totalorderprice * 100;
                }
                $price = $stripe->prices->create(
                    [
                        'unit_amount' => $totalorderprice,
                        'currency' => $currency->currency_code,
                        'tax_behavior' => 'exclusive',
                        'product_data' => ['name' => $company->company_name],
                    ]
                );

                //\Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
                \Stripe\Stripe::setApiKey('sk_test_51LqDGEDgZHqv5TzUMHUSQILyRMzaLLQXQ8lzZZiHuSNF8UYPfDi2nsGMdqRPGhMPvwoQve6JcZEJCDNOOpaOpkn400gibP49JV');

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
                    'success_url' => url('event_order/payment/success?session_id={CHECKOUT_SESSION_ID}&&ordernotemt=' . $ordernotemt),
                    'cancel_url' => route('event_order.payment.cancel'),
                ]);

                if (isset($session->url)) {
                    return response()->json(['result' => 1, 'message' => 'success', 'payment_url' => $session->url]);
                }
            } else {
                return response()->json(['result' => 1, 'message' => 'Order created successfully', 'payment_url' => '']);
            }

            return response()->json(['result' => 1, 'message' => 'Data saved successfully.']);
        } else {
            return response()->json(['result' => 0, 'message' => 'Something went wrong.']);
        }
    }


    public function success(Request $request)
    {
        try {
            $session_id =  $request->session_id;
            //\Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
            \Stripe\Stripe::setApiKey("sk_test_51LqDGEDgZHqv5TzUMHUSQILyRMzaLLQXQ8lzZZiHuSNF8UYPfDi2nsGMdqRPGhMPvwoQve6JcZEJCDNOOpaOpkn400gibP49JV");
            $result = \Stripe\Checkout\Session::retrieve(
                $session_id
            );
            $payment_id = $result->payment_intent;
            $ordernotemt = $request->ordernotemt;
            $orders   = order::where('ordernotemt', $ordernotemt)->get();
            foreach ($orders as $key => $order) :
                $order->txn_number = $payment_id;
                $order->is_order   = 1;
                $order->orderstatus   = 1;
                $order->save();
            endforeach;

 // TRANFER MERCHAT ACCOUNT
            //dd($order->company_id);
            $company       = Company::find($orders[0]->company_id);
            //dd($company);
            $merchant_data = User::find($company->user_id);
            // CHEK MERCHAT ACCOUNT CREATE ON STRIPE
            if ($merchant_data->stripe_account_id != '' || $merchant_data->stripe_account_id != null) {
                $currency = Currency::find($company->currency_id);
                $totalprice = $orders[0]->totalprice; // 100
                $processing_fee_per = $currency->processing_fee; // 10
                $per = $totalprice * ($processing_fee_per / 100);
                $merchant_amount =  $totalprice - $per;
                $merchant_amount =  $merchant_amount - $currency->price_per_ticket;
                //$merchant_amount = (($totalprice / 100) * $processing_fee_per) - $totalprice;

                $code = $currency->currency_code;

                if ($code == 'BIF' || $code == 'CLP' || $code == 'DJF' || $code == 'GNF' || $code == 'JPY' || $code == 'KMF' || $code == 'KRW' || $code == 'MGA' || $code == 'PYG' || $code == 'RWF' || $code == 'UGX' || $code == 'VND' || $code == 'VUV' || $code == 'XAF' || $code == 'XOF' || $code == 'XPF') {
                } else {
                    $merchant_amount = $merchant_amount * 100;
                }
                \Stripe\Stripe::setApiKey(env('STRIPE_SECRET', 'sk_test_51JykQILs3ZmffSEzPVADK2R89DDuQUBX5yoSfsE9Y7wxprjGYFsXhFau6Gd8uRBJXcYEfNa1QZgt2RFQL7FJwmZT00ck1lqkdn'));

                $transfer_payment = \Stripe\PaymentIntent::create([
                    'amount' => $merchant_amount * 100,
                    'currency' => $code,
                    'transfer_data' => [
                        'destination' => $merchant_data->stripe_account_id,
                    ],
                ]);

            foreach ($orders as $key => $order) :
                $order->transfer_amount   = $merchant_amount;
                $order->transfer_id       = $transfer_payment->id;
                $order->save();
            endforeach;
            }

            return redirect()->route('product_order.payment.success_call_back');
        } catch (\Throwable $e) {
            return response()->json(['result' => 0, 'message' => $e->getMessage() . ' on line ' . $e->getLine()]);
        }
    }

    public function cancel()
    {
        return redirect()->route('product_order.payment.failed_callback');
        dd('Your payment is canceled. You can create cancel page here.');
    }


    public function ticketinfo(Request $request)
    {
        $ticket_id = $request->ticket_id;

        $user = order::findOrFail($ticket_id);

        if ($ticket_id == "" or $ticket_id == NULL) {
            return response()->json(['result' => 0, 'message' => 'ticket id is required.']);
        }

        $ticketinfo = User::join('orders', 'orders.userid', '=', 'users.id')
            ->join('events', 'events.id', '=', 'orders.event_id')
            ->join('statuses', 'statuses.id', '=', 'orders.event_status')
            ->select(DB::raw('orders.id,users.user_number as customer_number,users.email,users.first_name,users.surname,users.username,events.price,orders.quantity,orders.totalprice,orders.orderno,orders.ticketrefno,orders.created_at as purchased_date,orders.updated_at as last_updated,statuses.status,(events.max_no_ticket - events.availableticket) as usedticket'))
            ->where('orders.id', $ticket_id)
            ->first();



        $newlist = order::select("ticketrefno", "event_status")->where('orderno', $ticketinfo->orderno)->get()->toArray();
        $ticketinfo->orderinfo = $newlist;
        // ->select('ticket_purchase.id','users.user_number as customer_number','users.email','users.first_name','users.surname','users.username','events.price','events.max_no_ticket','events.availableticket','ticket_purchase.quantity','ticket_purchase.total_price','ticket_purchase.ticketref','ticket_purchase.created_at as purchased_date','ticket_purchase.updated_at as last_updated','statuses.status')
        return response()->json(['result' => 1, 'ticketinfo' => $ticketinfo]);
    }

    public function ticketbroughtlist(Request $request)
    {
        $eventid = $request->eventid;

        $tickets = order::join('users', 'users.id', '=', 'orders.userid')
            ->with('ticket_type')
            ->join('statuses', 'statuses.id', '=', 'orders.event_status')
            ->select(
                "orders.orderno",
                "orders.event_id",
                "orders.ticket_type_id",
                "orders.quantity",
                "orders.totalprice",
                "users.email",
                "users.user_number as customer_number",
                "users.first_name",
                "users.surname",
                DB::raw('SUM(orders.quantity) as total_quantity'),
                // DB::raw("SUM(orders.quantity) as total_quantity")
            )
            ->where('orders.event_id', '=', $eventid)
            ->groupBy('orders.orderno')
            ->distinct()
            ->get();

        if ($tickets->toArray()) {
            foreach ($tickets as $ticketval) {
                $ticketval->name = $ticketval->first_name . ' ' . $ticketval->surname;
                $ticketval->ticket_used_date = 1;
                $ticketval->ticket_type = $ticketval->ticket_type;

                $ticketorder = order::select("ticketrefno", "event_status", "orderno")->where('event_id', '=', $ticketval->event_id)
                    ->where('orderno', '=', $ticketval->orderno)
                    ->get();

                if ($ticketorder->toArray()) {
                    $ticketval->orderinfo = $ticketorder;
                } else {
                    $ticketval->orderinfo = array();
                }
            }

            return response()->json(['result' => 1, 'ticketlist' => $tickets]);
        } else {
            return response()->json(['result' => 0, 'message' => 'No record found.']);
        }
    }

    public function statusadmitted(Request $request)
    {
        $ticked_purchase_id = $request->ticked_purchase_id;

        if ($ticked_purchase_id == "" or $ticked_purchase_id == NULL) {
            return response()->json(['result' => 0, 'message' => 'ticked_purchase_id is required.']);
        }

        $ticket = order::where('id', $ticked_purchase_id)->first();

        if ($ticket) {
            $ticket->status = 2;

            if ($ticket->update()) {
                return response()->json(['result' => 1, 'ticketstatus' => 'Status is changed to ADMITTED.']);
            } else {
                return response()->json(['result' => 0, 'message' => 'Something went wrong.']);
            }
        } else {
            return response()->json(['result' => 0, 'statuses' => 'No record found for this id.']);
        }
    }

    public function statusrefunded(Request $request)
    {
        $ticked_purchase_id = $request->ticked_purchase_id;

        if ($ticked_purchase_id == "" or $ticked_purchase_id == NULL) {
            return response()->json(['result' => 0, 'message' => 'ticked_purchase_id is required.']);
        }

        $ticket = order::where('id', $ticked_purchase_id)->first();

        if ($ticket) {
            $ticket->event_status = 3;

            if ($ticket->update()) {
                return response()->json(['result' => 1, 'ticketstatus' => 'Status is changed to REFUNDED.']);
            } else {
                return response()->json(['result' => 0, 'message' => 'Something went wrong.']);
            }
        } else {
            return response()->json(['result' => 0, 'statuses' => 'No record found for this id.']);
        }
    }

    public function getstatus(Request $request)
    {
        $statuses = Status::get();

        if ($statuses->toArray()) {
            return response()->json(['result' => 1, 'statuses' => $statuses]);
        } else {
            return response()->json(['result' => 0, 'statuses' => 'No record found.']);
        }
    }


    public function gettickets(Request $request)
    {
        $orderno = $request->orderno;

        $datenow = Carbon::now()->toDateString();
        $date = Carbon::createFromFormat('Y-m-d', $datenow);

        // return order::with('belongsto_event.ticket_types')->
        // join('users', 'users.id', '=', 'orders.userid')
        // ->where('orderno', '=', $orderno)->select('orders.id', 'orders.event_id')->get();

        $getticket = order::join('users', 'users.id', '=', 'orders.userid')
            ->with('ticket_type')
            ->join('statuses', 'statuses.id', '=', 'orders.event_status')
            ->leftjoin('events', 'events.id', '=', 'orders.event_id')
            ->join('companies', 'companies.id', '=', 'events.company_id')
            ->join('currencies', 'currencies.id', '=', 'events.currency_id')
            ->select(
                "orders.orderno",
                DB::raw('SUM(orders.quantity) as orderquantity'),
                "orders.created_at",
                DB::raw('SUM(orders.totalprice) as ordertotalprice'),
                "users.email as useremail",
                "users.first_name",
                "users.surname",
                "users.mobile_number",
                "events.eventname",
                "events.*",
                "events.id as eid",
                "events.status as eventstatus",
                "events.currency_id as crid",
                "currencies.name as currency_name",
                // "events.price as eprice",
                "events.building_number as ebuilding",
                "events.address_line_1 as eaddressline1",
                "events.city as ecity",
                "events.postcode as epostcode",
                "events.country as ecountry",
                'orders.event_id',
                'orders.ticket_type_id',
                "companies.*",
            )
            ->distinct()
            ->where('orders.orderno', '=', $orderno)
            ->get();


        if ($getticket->toArray()) {
            foreach ($getticket as $key) {
                // dd($key);

                $ticdata["order"]["orderno"] = $key->orderno;
                $ticdata["order"]["date"] = date('Y-m-d H:i:s', strtotime($key->created_at));
                $ticdata["order"]["quantity"] = $key->orderquantity;
                $ticdata["order"]["email"] = $key->useremail;
                $ticdata["order"]["customername"] = $key->first_name . ' ' . $key->surname;
                $ticdata["order"]["total_price"] = $key->ordertotalprice;
                $ticdata["order"]["payment_method"] = "COD";
                $ticdata["order"]["customer_mobile"] = $key->mobile_number;

                $download_event_pdf_link = URL::to('/') . '/eventpdf/' . $orderno;
                $ticdata["order"]["download_event_pdf_link"] = $download_event_pdf_link;
                $email_event_pdf_link = URL::to('/') . '/email_eventpdf/' . $orderno;
                $ticdata["order"]["email_event_pdf_link"] = $email_event_pdf_link;
                // $ticdata["ticketref"]["ticketrefno"] = $key->ticketrefno;
                // $ticdata["ticketref"]["status"] = $key->status;
                $ticdata["events"]["event_id"] = $key->eid;
                $ticdata["events"]["email"] = $key->email;
                $ticdata["events"]["eventref"] = $key->eventref;
                $ticdata["events"]["eventname"] = $key->eventname;
                $ticdata["events"]["start_date"] = $key->start_date;
                $ticdata["events"]["end_date"] = $key->end_date;
                $ticdata["events"]["mobile_number"] = $key->mobile_number;
                $ticdata["events"]["termscondition"] = $key->termscondition;
                $ticdata["events"]["location"] = $key->location;
                $ticdata["events"]["organizer"] = $key->organizer;
                $ticdata["events"]["contactno"] = $key->contactno;
                // $ticdata["events"]["price"] = $key->eprice;
                $ticdata["events"]["currency_id"] = $key->crid;
                // $ticdata["events"]["currency___id"] = $key->currency___id;
                $ticdata["events"]["currency_name"] = $key->currency_name;

                $ticdata["events"]["building_number"] = $key->ebuilding;
                $ticdata["events"]["address_line_1"] = $key->eaddressline1;
                $ticdata["events"]["city"] = $key->ecity;
                $ticdata["events"]["postcode"] = $key->epostcode;
                $ticdata["events"]["country"] = $key->ecountry;

                $e_data = order::where('orderno', '=', $orderno)
                    ->with('ticket_type')
                    ->select('id', 'ticket_type_id')
                    ->get();

                $types = [];
                foreach ($e_data as $i => $e) {
                    if (!in_array($e->ticket_type, $types)) {
                        $e->ticket_type->count = 1;
                    } else {
                        $e->ticket_type->count = $e->ticket_type->count + 1;
                    }
                    $types[] = $e->ticket_type;
                }
                $ticdata["events"]["ticket_type"] = array_values(array_unique($types));

                $ticdata["events"]["flyerimage"] = url("mainflyer/" . $key->flyerimage);
                $ticdata["company"]["company_id"] = $key->company_id;
                $ticdata["company"]["company_number"] = $key->company_number;
                $ticdata["company"]["company_name"] = $key->company_name;
                $ticdata["company"]["telephone"] = $key->telephone;
                $ticdata["company"]["email"] = $key->email;
                $ticdata["company"]["image"] = URL::to('/') . '/storage/public/' . $key->image;
                $ticdata["company"]["lat"] = $key->lat;
                $ticdata["company"]["long"] = $key->long;
                // $ticdata["currency_id"] = $key->currency_id;
                $ticdata["images"] = $key->images;

                if ($key->start_date != "") {
                    $startdate = $key->start_date;
                }
                if ($key->end_date != "") {
                    // $enddate = Carbon::createFromFormat('Y-m-d',$key->end_date);
                    $enddate = $key->end_date;
                }

                if ($key->eventstatus != 0)  //event is Not Canceled
                {
                    if ($date >= $startdate && $date <= $enddate) {
                        $ticdata['events']['status'] = "Started";
                    } else if ($date->gt($enddate)) {
                        $ticdata['events']['status'] = "Ended";
                        // } else if ($date->eq($startdate)) {
                        //     $ticdata['events']['status'] = "started";
                    } else if ($date < $startdate && $date < $enddate) {
                        $ticdata['events']['status'] = "Going Ahead";
                    }
                } else {
                    $ticdata['events']['status'] = "Cancelled";
                }

                $eventdataimages = EventImage::select('eventimage')->where('event_id', $key->eid)->get();
                if ($eventdataimages->toArray()) {
                    $i = 0;
                    foreach ($eventdataimages as $imagesval) {
                        $imagesval->eventimage = url('public' . $imagesval->eventimage);
                        $data[$i] = $imagesval->eventimage;
                        $eventdataimages->eventimage = $data[$i];
                        $i++;
                    }
                    $ticdata["events"]["eventimages"] = $eventdataimages;
                } else {
                    $ticdata["events"]["eventimages"] = array();
                }


                $newlist = order::with('ticket_type')->select("ticketrefno", "event_status as status", "ticket_type_id")->where('orderno', $key->orderno)->get()->toArray();

                $ticdata["orderinfo"] = $newlist;

                $ticketlist[] = $ticdata;
            }

            return response()->json(['result' => 1, 'ticketlist' => $ticketlist]);
        } else {
            return response()->json(['result' => 0, 'message' => 'No record found.']);
        }
    }

    public function ticketpurchaselist(Request $request)
    {

        $customerid = Auth::id();   //Login User ticket List

        $list = order::join('users', 'users.id', '=', 'orders.userid')
            ->join('statuses', 'statuses.id', '=', 'orders.event_status')

            ->leftjoin('events', 'events.id', '=', 'orders.event_id')
            ->join('companies', 'companies.id', '=', 'events.company_id')
            ->select("orders.orderno", "orders.quantity", "orders.created_at", "orders.totalprice", "users.email", "users.first_name", "users.surname", "events.eventname", "events.flyerimage", "companies.company_name", 'events.currency_id')
            ->distinct()
            ->where('orders.userid', '=', $customerid)
            ->with("images")
            ->orderby("orders.created_at", "desc")
            ->get();

        if ($list->toArray()) {
            foreach ($list as $value) {
                $value->flyerimage = url('public/mainflyer/' . $value->flyerimage);
            }
            return response()->json(['result' => 1, 'ticketlist' => $list]);
        }
    }

    public function ticketrefstatus(Request $request)
    {
        if ($request->ticketrefno != "") {
            $cond = "ticketrefno";
            $val = $request->ticketrefno;
        } else if ($request->orderno != '') {
            $cond = "orderno";
            $val = $request->orderno;
        }

        $updateref = order::where($cond, $val)->update(['event_status' => $request->status]);

        if ($updateref == true) {
            return response()->json(['result' => 1, 'message' => 'Ticket status updated successfully.']);
        } else {
            return response()->json(['result' => 0, 'message' => 'There is something wrong please try again later.']);
        }
    }
}
