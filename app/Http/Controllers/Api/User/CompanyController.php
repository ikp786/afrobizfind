<?php

namespace App\Http\Controllers\Api\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Api\Controller;
use App\Models\Company;
use App\Models\CompanyImage;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Favourite;
use App\Models\Notification;
use App\Models\Offer;
use App\Models\order;
use App\Models\Product;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CompanyController extends Controller
{
    public function getall(Request $request)
    {
        $user_id = Auth::user()->id;
        $coms = Company::where("user_id", $user_id)->latest()->get();
        $companies = $coms->map(function ($com, $key) {
            $active = 1;
            if ($com['expiry_date'] && $com['expiry_date'] > date('Y-m-d')) {
                $active = 0;
            }
            $com['is_expiry'] = $active;
            $currency = Currency::where('id', $com['currency_id'])->first();

            if ($currency) {
                $com['currency'] = $currency;
            }
            return  $com;
        });

        $stripe_account_id = Auth::user()->stripe_account_id;
        if($stripe_account_id == null){
        $stripe_status = 0;
        }else{
        $stripe_status = 1;

        }
        return response()->json(['result' => 1, "companies" => $companies,'stripe_status' =>  $stripe_status]);
    }

    public function get(Request $request)
    {
        $id = $request->id;
        if ($id) {
            $user_id = Auth::user()->id;
            $company   = Company::find($id);
            // $company   = Company::where("user_id",$user_id)->find($id);

            if ($company) {

                $active = 1;
                if ($company->expiry_date && $company->expiry_date > date('Y-m-d')) {
                    $active = 0;
                }
                $company->is_expiry = $active;

                $currency = Currency::where('id', $company->currency_id)->first();

                if ($currency) {
                    $company->currency = $currency;
                }

                return response()->json(['result' => 1, "company" => $company]);
            }
        }
        return response()->json(['result' => 0, 'message' => "Something went wrong"]);
    }

    public function save(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id'     => 'required',
            'company_name'    => 'required',
            'building_number' => 'required',
            'address_line_1'  => 'required',
            'city'            => 'required',
            'postcode'        => 'required',
            'email'           => 'required',
            'lat'             => 'required',
            'long'            => 'required',
            'telephone'       => 'required',
            'package'       => 'required',
            'website'         => 'required',
            'currency_id'     => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['result' => 0, 'message' => "Validation error", 'errors' => $validator->errors()->messages()]);
        }

        $newCompany = false;

        $user = Auth()->user();
        $user_id = $user->id;
        if ($request->id) {
            // $company = Company::where("user_id",$user_id)->find($request->id);
            $company = Company::find($request->id);
            $company->price = $request->price;
            $company->currency_id = $request->currency_id;
        } else {
            $company = new Company();
            $company->company_number = $this->generateCompanyNumber();
            $company->price = $request->price;
            $company->currency_id = $request->currency_id;
        }
        $company->package = $request->package;

        if (!isset($company)) {
            return response()->json(['result' => 0, 'message' => "Something went wrong"]);
        }
        if ($request->has('referrer') && $request->referrer != '' && $request->referrer != null) {
            $userFound = User::where('user_number', $request->referrer)->first();
            if (!$userFound) {
                return response()->json(['result' => 0, 'message' => "Refrrer not found."]);
            }
        }

        $company->user_id         = Auth::user()->id;
        $company->category_id     = $request->category_id;
        $company->country         = $request->country;
        $company->company_name    = $request->company_name;
        $company->building_number = $request->building_number;
        $company->address_line_1  = $request->address_line_1;
        $company->city            = $request->city;
        $company->postcode        = $request->postcode;
        $company->monday_opening  = $request->monday_opening;
        $company->monday_closing  = $request->monday_closing;
        $company->tuesday_opening = $request->tuesday_opening;
        $company->tuesday_closing = $request->tuesday_closing;
        $company->wednesday_opening  = $request->wednesday_opening;
        $company->wednesday_closing  = $request->wednesday_closing;
        $company->thursday_opening   = $request->thursday_opening;
        $company->thursday_closing   = $request->thursday_closing;
        $company->friday_opening     = $request->friday_opening;
        $company->friday_closing     = $request->friday_closing;
        $company->saturday_opening   = $request->saturday_opening;
        $company->saturday_closing   = $request->saturday_closing;
        $company->sunday_opening     = $request->sunday_opening;
        $company->sunday_closing     = $request->sunday_closing;
        $company->email      = $request->email;
        $company->telephone  = $request->telephone;
        $company->website    = $request->website;
        $company->lat        = $request->lat;
        $company->long       = $request->long;
        $company->ethos       = $request->ethos;
        $company->applink       = $request->applink;
        if ($request->has('referrer')) {
            $company->referrer      = $request->referrer;
        }

        if ($request->hasFile('image')) {
            $image     = $request->image;
            $extension = $image->getClientOriginalExtension();
            $filename  = 'company/' . md5(rand() . time() . rand()) . "." . $extension;
            Storage::disk('public')->put($filename, File::get($image));
            $company->image = $filename;
        }
        $company->save();

        if ($request->has('referrer') && $request->referrer != '' && $request->referrer != null) {
            $referral = new Referral();
            $referral->company_number =  $company->company_number;
            $referral->referrer =  $request->referrer;
            $referral->number_of_payments =  0;
            $referral->subscription_status =  'inactive';

            $referral->company_name = $request->company_name;
            $referral->first_line_address = $request->address_line_1;
            $referral->postcode = $request->postcode;
            $referral->company_contact_number = $request->telephone;
            $referral->customer_number = Auth::user()->user_number;
            $referral->save();
        }


        $images = [];
        if ($request->hasfile('images')) {
            foreach ($request->file('images') as $image) {
                $extension = $image->getClientOriginalExtension();
                $filename = 'company/' . md5(rand() . time() . rand()) . "." . $extension;
                $images[] = $filename;
                Storage::disk('public')->put($filename,  File::get($image));
            }
        }
        if (!empty($images)) {
            foreach ($images as $key => $img) {
                $image = new  CompanyImage();
                $image->company_id = $company->id;
                $image->image = $img;
                $image->save();
            }
        }

        if ($request->deletedimages) {
            $diary = explode(',', $request->deletedimages);
            if (!empty($diary)) {
                foreach ($diary as  $di_id) {
                    $di = CompanyImage::find($di_id);
                    if ($di) {
                        $di->delete();
                    }
                }
            }
        }
        $op = $request->id ? 'updated' : 'created';

        if (!$request->id) {
            $count = Company::count();

            // if($count && !($count%50)) {
            if ($count >= 10) {
                $title = '50 new companies, have a look!';
                $this->sendnotification($title, $company->id, 'company');
            }
        }

        return response()->json(['result' => 1, "message" => "Company $op successfully", "company" => $company]);
    }

    public function delete(Request $request)
    {
        $id = $request->id;

        if ($id) {
            $user_id = Auth::user()->id;

            $company   = Company::where("user_id", $user_id)->find($id);

            if ($company) {
                Product::where('company_id', $id)->delete();
                Offer::where('company_id', $id)->delete();
                Favourite::where('company_id', $id)->delete();
                Customer::where('company_id', $id)->delete();
                order::where('company_id', $id)->delete();
                Notification::where('type_id', $id)->where('type', 'company')->delete();
                $company->delete();
                return response()->json(['result' => 1,  "message" => "Company removed successfully"]);
            }
        }
        return response()->json(['result' => 0, 'message' => "Something went wrong"]);
    }


    public function changestatus(Request $request)
    {
        $user_id = Auth::user()->id;
        $company   = Company::where("user_id", $user_id)->find($request->id);
        if ($company) {
            $company->status = $request->status ? 1 : 0;
            $company->save();

            $o = $request->status ? 'Suspeded' : 'Activated';
            return response()->json(['result' => 1,  "message" => "Company $o successfully"]);
        }

        return response()->json(['result' => 0, 'message' => "Something went wrong"]);
    }


    public function sendnotification($title, $type_id, $type)
    {
        $users = User::get();
        foreach ($users as $key => $user) {
            if (!empty($user->fcmtoken)) {
                $this->notification($user->fcmtoken, $title, $type_id);
            }
            $notification = new Notification();
            $notification->user_id = $user->id;
            $notification->title = $title;
            $notification->type_id = $type_id;
            $notification->type = $type;
            $notification->save();
        }
    }

    public function notification($token, $title, $company_id)
    {
        $fcmUrl = 'https://fcm.googleapis.com/fcm/send';
        $token = $token;
        $server_key = env('FIREBASE_SERVER_KEY');

        //Please enable sound after in production
        //'sound' => true,
        $notification = [
            'title' => $title,
            'sound' => false,
        ];

        $extraNotificationData = ["message" => $notification, "data" => ['company_id' => (string)$company_id, 'last50' =>  "1"]];

        $fcmNotification = [
            //'registration_ids' => $tokenList, //multple token array
            'to'        => "$token", //single token
            'notification' => $notification,
            'data' => ['company_id' => (string)$company_id, 'last50' =>  "1"]
            // 'data' => $extraNotificationData
        ];

        $headers = [
            'Authorization: key=' . $server_key,
            'Content-Type: application/json'
        ];


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $fcmUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmNotification));
        $result = curl_exec($ch);
        curl_close($ch);
        /* echo "<pre>";
        print_r($result);*/
        return true;
    }

    function generateCompanyNumber()
    {
        $number = 'C' . mt_rand(1000000, 9999999);

        if ($this->CompanyNumberExists($number)) {
            return $this->generateCompanyNumber();
        }
        return $number;
    }

    function CompanyNumberExists($number)
    {
        return Company::where('company_number', $number)->count();
    }


    public function companyPay(Request $request)
    {
        $user_id = Auth::user()->id;
        $company   = Company::where("user_id", $user_id)->find($request->id);
        if ($company) {
            $company->paypal_nonce = $request->paypal_nonce;

            $today = date('Y-m-d');
            if ($company->expiry_date && $company->expiry_date > $today) {
                $today = $company->expiry_date;
            }
            $expiry_date = date('Y-m-d', strtotime($today . ' + 30 days'));
            $company->expiry_date = $expiry_date;
            $company->save();
            return response()->json(['result' => 1, "company" => $company]);
        }
        return response()->json(['result' => 0, "message" => 'Something went wrong']);
    }
}
