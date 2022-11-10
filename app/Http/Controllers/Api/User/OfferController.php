<?php

namespace App\Http\Controllers\Api\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Api\Controller;
use Carbon\Carbon;
use App\Models\Currency;
use App\Models\Offer;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class OfferController extends Controller
{

    public function getall(Request $request)
    {

        $nowtime = Carbon::now();

        $offers = Offer::with('company')->where([['company_id', $request->id], ["active", "1"], ["end_date", '>=', $nowtime]])->get();

        if ($offers->toArray()) {
            foreach ($offers as $offervalue) {

                $productid = $offervalue->productid;
                $getproductprice = Product::select('price', 'currency_id')->where('id', $productid)->first();


                $oprice = $getproductprice['price'];
                $offervalue->originalprice = $oprice;

                $ecurrency = Currency::where('id', $getproductprice['currency_id'])->get();

                if ($ecurrency->toArray()) {
                    $offervalue->currency = $ecurrency;
                } else {
                    $offervalue->currency = array();
                }
            }
        }


        return response()->json(['result' => 1, "offers" => $offers]);
    }

    public function gethistory(Request $request)
    {
        $offers = Offer::with('company')->where('company_id', $request->id)->withTrashed()->get();


        if ($offers->toArray()) {
            foreach ($offers as $offervalue) {
                $productid = $offervalue->productid;
                $getproductprice = Product::select('price', 'currency_id')->where('id', $productid)->first();

                $oprice = $getproductprice['price'];
                $offervalue->originalprice = $oprice;

                $ecurrency = Currency::where('id', $getproductprice['currency_id'])->get();

                if ($ecurrency->toArray()) {
                    $offervalue->currency = $ecurrency;
                } else {
                    $offervalue->currency = array();
                }
            }
        }
        return response()->json(['result' => 1, "offers" => $offers]);
    }

    public function get(Request $request)
    {
        $id = $request->id;
        if ($id) {
            $offer = Offer::find($id);
            if ($offer) {

                $offer->company_name = $offer->company->company_name;

                return response()->json(['result' => 1, "offer" => $offer]);
            }
        }
        return response()->json(['result' => 0, 'message' => "Something went wrong"]);
    }


    public function save(Request $request)
    {


        $validator = Validator::make($request->all(), [
            'company_id'    => 'required',
            'name'          => 'required',
            'offer_code'    => 'required',
            'offer_details' => 'required',
            'discount'      => 'required',
            'productid'      => 'required',
            'price'      => 'required',
            'discount_type'      => 'required',
            'start_date'    => 'required',
            'end_date'      => 'required',
            'customer_only' => 'required',
            // 'mobile_number' => 'required',
            'active'        => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['result' => 0, 'message' => "Validation error", 'errors' => $validator->errors()->messages()]);
        }

        $user    = Auth()->user();
        $user_id = $user->id;
        if ($request->id) {
            $offer = Offer::find($request->id);;
        } else {
            $offer = new Offer();
            $offer->offer_number = $this->generateOfferNumber();
        }

        if (!isset($offer)) {
            return response()->json(['result' => 0, 'message' => "Something went wrong"]);
        }

        $datenow = Carbon::now()->toDateString();
        $date = Carbon::createFromFormat('Y-m-d', $datenow);

        $checkoffer = Offer::where('productid', $request->productid)->where('end_date', '>', $datenow)->count();

        $offer->company_id = $request->company_id;
        $offer->name       = $request->name;
        $offer->offer_code = $request->offer_code;
        $offer->offer_details = $request->offer_details;
        $offer->discount = $request->discount;
        $offer->productid = $request->productid;
        $offer->price = $request->price;
        $offer->discount_type = $request->discount_type;
        $offer->start_date = $request->start_date;
        $offer->end_date = $request->end_date;
        $offer->customer_only = $request->customer_only ? 1 : 0;
        // $offer->mobile_number = $request->mobile_number;
        $offer->active = 1; //$request->id?$request->active?1:0:1;


        if ($checkoffer == 0) {
            $offer->save();
        } else {
            return response()->json(['result' => 0, "message" => "Offer is already Running with same product"]);
        }

        $op = $request->id ? 'updated' : 'created';
        return response()->json(['result' => 1, "message" => "Offer $op successfully", "offer" => $offer]);
    }


    public function delete(Request $request)
    {
        $id = $request->id;
        if ($id) {
            $user_id   = Auth::user()->id;
            $company   = Offer::find($id);
            if ($company) {
                $company->delete();
                return response()->json(['result' => 1,  "message" => "Offer removed successfully"]);
            }
        }
        return response()->json(['result' => 0, 'message' => "Something went wrong"]);
    }

    function generateOfferNumber()
    {
        $number = 'O' . mt_rand(1000000, 9999999);

        if ($this->OfferNumberExists($number)) {
            return $this->generateOfferNumber();
        }
        return $number;
    }

    function OfferNumberExists($number)
    {
        return Offer::where('offer_number', $number)->count();
    }
}
