<?php

namespace App\Http\Controllers\Api\User;

use App\Models\Offer;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\Controller;
use App\Models\Inventory;
use App\Models\ProductImage;
use Milon\Barcode\DNS1D;
use Intervention\Image\Facades\Image;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{

    /**
    public function getallcompanyproduct(Request $request)
    {


        $products = Product::with('company')->where('company_id', $request->id)->orderby("created_at", "desc")->get();

        if ($products->toArray()) {
            foreach ($products as $productval) {
                $getstock = Inventory::select('quantity')->where('product_id', $productval->id)->first();


                if ($getstock) {
                    $qty = $getstock->quantity;
                    $productval->quantity = $qty;

                    $getoffer = Offer::select('price', 'discount', 'discount_type')->where('productid', $productval->id)->first();

                    if ($getoffer) {
                        $ofprice = $getoffer->price;

                        $productval->offerprice = $getoffer->price;
                        $productval->discount = $getoffer->discount;
                        $productval->discount_type = $getoffer->discount_type;
                    } else {
                        $productval->offerprice = null;
                        $productval->discount = null;
                        $productval->discount_type = null;
                    }
                } else {
                    $productval->quantity = array();
                }
            }
        }

        return response()->json(['result' => 1, "products" => $products]);
    }

     */

    public function getallcompanyproduct(Request $request)
    {
        $products = Product::with('company')->where('company_id', $request->id)->get();

        foreach ($products as $key => $val) {

            $currency_sign = '';

          //  echo  $val->currency->name."<br>";
            $namew = $val->currency->name;

            $currency_sign = ' (' . $val->currency->currency_sign . ')';
            $result[$key]=  $val;
            $result[$key]->currency->newname= $namew . $currency_sign;
        }
        return response()->json(['result' => 1, "products" => $result]);
    }

    public function get(Request $request)
    {
        $id = null;
        if (isset($request->id)) {
            $id = $request->id;
        }
        if (isset($request->barcode_no)) {
            $id = $request->barcode_no;
        }

        if ($id) {
            $product = Product::find($id);
            if ($product) {
                return response()->json(['result' => 1, "product" => $product]);
            }
        }
        return response()->json(['result' => 0, 'message' => "Something went wrong"]);
    }


    public function save(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'company_id'    => 'required',
            'product_name'  => 'required',
            'description'   => 'required',
            'price'         => 'required',
            'images'        => 'required_without:id',
        ]);

        if ($validator->fails()) {
            return response()->json(['result' => 0, 'message' => "Validation error", 'errors' => $validator->errors()->messages()]);
        }

        $user    = Auth()->user();
        $user_id = $user->id;
        if ($request->id) {
            $product = Product::find($request->id);
        } else {
            $product = new Product();
            $product->barcode_no  = Carbon::now()->timestamp;

            $product->product_number = $this->generateProductNumber();

            $statement = DB::select("show table status like 'products'");
            $nextid = $statement[0]->Auto_increment;

            $inventory = new Inventory;
            $inventory->product_id = $nextid;
            $inventory->save();
        }

        if (!isset($product)) {
            return response()->json(['result' => 0, 'message' => "Something went wrong"]);
        }
        $product->company_id   = $request->company_id;
        $product->product_name = $request->product_name;
        $product->description  = $request->description;
        $product->price        = str_replace(",","",$request->price);
        $product->currency_id  = $request->currency_id;
        $product->save();

        $images = [];
        if ($request->hasfile('images')) {
            foreach ($request->file('images') as $image) {
                $extension = $image->getClientOriginalExtension();
                $filename = '/product/' . md5(rand() . time() . rand()) . "." . $extension;
                $images[] = $filename;
                Storage::disk('public')->put($filename,  File::get($image));
            }
        }

        if (!empty($images)) {
            foreach ($images as $key => $img) {
                $image = new  ProductImage();
                $image->product_id = $product->id;
                $image->image = $img;
                $image->save();
            }
        }

        if ($request->deletedimages) {
            $diary = explode(',', $request->deletedimages);
            if (!empty($diary)) {
                foreach ($diary as  $di_id) {
                    $di = ProductImage::find($di_id);
                    if ($di) {
                        $di->delete();
                    }
                }
            }
        }

        $product = Product::find($product->id);
        if ($product) {
            $product->barcode_url = env('APP_URL') . '/api/product/barcode/' . $product->barcode_no;
        }

        $op = $request->id ? 'updated' : 'created';
        return response()->json(['result' => 1, "message" => "Product $op successfully", "product" => $product]);
    }


    public function barcode($barcode_no)
    {
        try {
            $product = Product::where('barcode_no', $barcode_no)->first();
            if ($product) {
                $milon = new DNS1D();
                $file = ($milon->getBarcodePNG($barcode_no, 'EAN13', 3, 80));
                $img = Image::make($file);
                return $img->response('jpg');
            }

            return response()->json([
                "success" => false,
                "message" => "Invalid Barcode Number."
            ], 422);
        } catch (\Throwable $th) {
            // throw $th;
            return response()->json([
                "success" => false,
                "message" => "Something went wrong."
            ], 422);
        }
    }

    public function delete(Request $request)
    {
        $id = $request->id;
        if ($id) {
            $user_id = Auth::user()->id;
            $company   = Product::find($id);
            if ($company) {
                $company->delete();
                return response()->json(['result' => 1,  "message" => "Product removed successfully"]);
            }
        }
        return response()->json(['result' => 0, 'message' => "Something went wrong"]);
    }

    function generateProductNumber()
    {
        $number = 'P' . mt_rand(1000000, 9999999);

        if ($this->ProductNumberExists($number)) {
            return $this->generateProductNumber();
        }
        return $number;
    }

    function ProductNumberExists($number)
    {
        return Product::where('product_number', $number)->count();
    }
}
