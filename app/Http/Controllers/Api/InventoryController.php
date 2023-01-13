<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class InventoryController extends Controller
{
    public function getproducts(Request $request)
    {
        $products = DB::select(DB::raw("SELECT id,product_number,product_name FROM products"));
        return response()->json(['result' => 1, 'inventory' => $products]);
    }

    public function getbarcode(Request $request)
    {
        $productid = $request->product_id;

        // echo $productid;
        $getbarcode = Inventory::select('barcode', 'quantity', 'wholesale_price1', 'retail_price1')->where('product_id', $productid)->get();

        return response()->json(['barcodes' => $getbarcode]);
    }

    public function addproductqty(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'product_id'    => 'required',
            'barcode'    => 'required',
            'quantity'    => 'required',
            'wholesale_price1'    => 'required',
            'retail_price1'    => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['result' => 0, 'message' => "Validation error", 'errors' => $validator->errors()->messages()]);
        }

        $product_number = $request->product_id;
        $barcode = $request->barcode;
        $quantity = $request->quantity;
        $wholesale_price1 = $request->wholesale_price1;
        $retail_price1 = $request->retail_price1;

        $productcheck = Product::where('id', $product_number)->count();
        $verify = Inventory::where('product_id', $product_number)->first();
        $checkbarcode = Inventory::where('barcode', $barcode)->count();

        if ($productcheck != 0) {
            if ($checkbarcode == 0)  //barcode is not exists
            {
                if ($verify['barcode'] == null || $verify['barcode'] == '')  //Enters the first Record
                {
                    $inventory = Inventory::where('product_id', $product_number)->update(['quantity' => $quantity, 'barcode' => $barcode, 'wholesale_price1' => $wholesale_price1, 'retail_price1' => $retail_price1]);
                } else {
                    $checkqtywithproduct = Inventory::where('product_id', $product_number)->where('quantity', $quantity)->count();
                    if ($checkqtywithproduct == 0) {
                        $inventory = new Inventory;
                        $inventory->product_id = $product_number;
                        $inventory->barcode = $barcode;
                        $inventory->quantity = $quantity;
                        $inventory->wholesale_price1 = $wholesale_price1;
                        $inventory->retail_price1 = $retail_price1;
                        $inventory->save();
                    } else {
                        return response()->json(['result' => 0, 'message' => 'Barcode is Already Exists with same product and Quantity.']);
                    }
                }

                if ($inventory == true) {
                    return response()->json(['result' => 1, 'message' => 'Barcode Saved Successfully']);
                } else {
                    return response()->json(['result' => 0, 'message' => 'Something went wrong please try later1.']);
                }
            } else   //barcode is exists
            {
                $getqty = Inventory::where('barcode', $barcode)->first();

                $oldqty = $getqty['quantity'];
                $finalqty = $oldqty + $quantity;

                $inventory = Inventory::where('product_id', $product_number)->where('barcode', $barcode)->update(['quantity' => $finalqty, 'barcode' => $barcode, 'wholesale_price1' => $wholesale_price1, 'retail_price1' => $retail_price1]);


                if ($inventory == true) {
                    return response()->json(['result' => 1, 'message' => 'Quantity Added successfully.']);
                } else {
                    return response()->json(['result' => 0, 'message' => 'Something went wrong please try later11.']);
                }
            }
        } else {
            return response()->json(['result' => 0, 'message' => 'Product Not Found.']);
        }



        // if($productcheck!=0)
        // {
        //     if($check==0)
        //     {
        //         if($product_number == "" OR $product_number == NULL)
        //         {
        //             return response()->json(['result' => 0, 'message' => 'product_number is required.']);
        //         }

        //         if($quantity == "" OR $quantity == NULL)
        //         {
        //             return response()->json(['result' => 0, 'message' => 'quantity is required.']);
        //         }

        //         $inventory = new Inventory;
        //         $inventory->product_id = $product_number;
        //         $inventory->barcode = $barcode;
        //         $inventory->quantity = $quantity;
        //         $inventory->wholesale_price1 = $wholesale_price1;
        //         $inventory->retail_price1 = $retail_price1;
        //         $inventory->save();

        //         if($inventory==true)
        //         {
        //             $lastid=$inventory->id;

        //             $lastdata = DB::table('inventory')                        
        //             ->join('products', 'products.id', '=', 'inventory.product_id')
        //             ->select('inventory.*','products.product_name')
        //             ->where('inventory.barcode',$barcode)
        //             ->get();

        //             return response()->json(['result' => 1, 'message' => 'Inventory data is saved successfully.','inventorydata'=>$lastdata]);
        //         }
        //         else
        //         {
        //             return response()->json(['result' => 0, 'message' => 'Something went wrong.']);
        //         }
        //     }
        //     else
        //     {
        //         $getqty=Inventory::where('barcode',$barcode)->first();
        //         $oldqty=$getqty['quantity'];
        //         $finalqty=$oldqty+$quantity;
        //         $inventory=Inventory::where('barcode',$barcode)->update(['quantity' =>$finalqty,'barcode'=>$barcode,'wholesale_price1'=>$wholesale_price1,'retail_price1'=>$retail_price1]);
        //         if($inventory)
        //         {
        //             $lastdata = DB::table('inventory')
        //             ->join('products', 'products.id', '=', 'inventory.product_id')
        //             ->select('inventory.*','products.product_name')
        //             ->where('inventory.barcode',$barcode)
        //             ->get();                

        //             return response()->json(['result' => 1, 'message' => 'Inventory is Updated successfully.','inventorydata'=>$lastdata]);
        //         }
        //         else
        //         {
        //             return response()->json(['result' => 0, 'message' => 'Something went wrong.']);
        //         }
        //     }    
        // }
        // else
        // {
        //     return response()->json(['result' => 0, 'message' => 'Product is Not Exists.']);
        // }
    }

    public function addproductqty11(Request $request)
    {

        $product_number = $request->product_id;
        $barcode = $request->barcode;
        $quantity = $request->quantity;

        $check = Inventory::where('product_id', $product_number)->count();

        $productcheck = Product::where('id', $product_number)->count();

        if ($productcheck != 0) {
            if ($check == 0) {

                if ($product_number == "" or $product_number == NULL) {
                    return response()->json(['result' => 0, 'message' => 'product_number is required.']);
                }

                if ($quantity == "" or $quantity == NULL) {
                    return response()->json(['result' => 0, 'message' => 'quantity is required.']);
                }

                $inventory = new Inventory;
                $inventory->product_id = $product_number;
                $inventory->barcode = $barcode;
                $inventory->quantity = $quantity;
                $inventory->save();

                if ($inventory == true) {
                    $lastid = $inventory->id;

                    $lastdata = DB::table('inventory')
                        ->join('products', 'products.product_number', '=', 'inventory.product_id')
                        ->select('inventory.*', 'products.product_name')
                        ->where('products.product_number', $product_number)
                        ->first();

                    return response()->json(['result' => 1, 'message' => 'Inventory data is saved successfully.', 'inventorydata' => $lastdata]);
                } else {
                    return response()->json(['result' => 0, 'message' => 'Something went wrong.']);
                }
            } else {
                $getqty = Inventory::where('product_id', $product_number)->first();

                $oldqty = $getqty['quantity'];
                $finalqty = $oldqty + $quantity;

                $inventory = Inventory::where('product_id', $product_number)->update(['quantity' => $finalqty, 'barcode' => $barcode]);

                if ($inventory) {

                    $lastdata = DB::table('inventory')
                        ->join('products', 'products.id', '=', 'inventory.product_id')
                        ->select('inventory.*', 'products.product_name')
                        ->where('products.id', $product_number)
                        ->first();

                    return response()->json(['result' => 1, 'message' => 'Inventory is Updated successfully.', 'inventorydata' => $lastdata]);
                } else {
                    return response()->json(['result' => 0, 'message' => 'Something went wrong.']);
                }
            }
        } else {
            return response()->json(['result' => 0, 'message' => 'Product is Not Exists.']);
        }
    }

    public function editproductqty(Request $request)  //edit the product quantity
    {
        // $editid = $request->editid;

        // if($editid == "" OR $editid == NULL)
        // {
        // 	return response()->json(['result' => 0, 'inventory' => 'editid is required.']);
        // }

        $validator = Validator::make($request->all(), [
            'productid' => 'required',
            'barcode' => 'required',
            'quantity' => 'required',
            'wholesale_price' => 'required',
            'restrict_stock' => 'required',
            'wholesale_price1' => 'required',
            'retail_price1' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['result' => 0, 'message' => "Validation error", 'errors' => $validator->errors()->messages()]);
        }

        $productid = $request->productid;
        $barcode = $request->barcode;
        // $product_name = $request->product_name;
        $quantity = $request->quantity;
        $wholesale_price = $request->wholesale_price;
        $restrict_stock = $request->restrict_stock;
        $wholesale_price1 = $request->wholesale_price1;
        $retail_price1 = $request->retail_price1;

        $inventory = Inventory::where('product_id', $productid)->first();

        // $inventory->product_number = ($request->product_number) ? $product_number : $inventory->product_number;
        // $inventory->product_name = ($request->product_name) ? $product_name : $inventory->product_name;

        if ($inventory) {
            $inventory->barcode = ($request->barcode) ? $barcode : $inventory->barcode;
            $inventory->quantity = ($request->quantity) ? $quantity : $inventory->quantity;
            $inventory->wholesale_price = ($request->wholesale_price) ? $wholesale_price : $inventory->wholesale_price;
            $inventory->restrict_stock = ($request->restrict_stock) ? $restrict_stock : $inventory->restrict_stock;
            $inventory->wholesale_price1 = ($request->wholesale_price1) ? $wholesale_price1 : $inventory->wholesale_price1;
            $inventory->retail_price1 = ($request->retail_price1) ? $retail_price1 : $inventory->retail_price1;
            if ($inventory->update()) {
                return response()->json(['result' => 1, 'message' => 'Inventory data is updated successfully.']);
            } else {
                return response()->json(['result' => 0, 'message' => 'Something went wrong.']);
            }
        } else {
            return response()->json(['result' => 0, 'message' => 'No record found for this id.']);
        }
    }

    public function deleteproductqty(Request $request)   //delete the specific product stock
    {
        $deleteid = $request->deleteid;

        if ($deleteid == "" or $deleteid == NULL) {
            return response()->json(['result' => 0, 'message' => 'deleteid is required.']);
        }

        $inventory = Inventory::where('product_id', $deleteid)->first();

        if ($inventory) {
            if ($inventory->delete()) {
                return response()->json(['result' => 1, 'message' => 'Inventory data is deleted successfully.']);
            } else {
                return response()->json(['result' => 0, 'message' => 'Something went wrong.']);
            }
        } else {
            return response()->json(['result' => 0, 'message' => 'No record found for this id.']);
        }
    }

    public function showproductqty(Request $request)
    {
        $showid = $request->showid;

        if ($showid == "" or $showid == NULL) {
            return response()->json(['result' => 0, 'inventory' => 'showid is required.']);
        }

        $inventory = Inventory::join('products', 'products.id', '=', 'inventory.product_id')
            ->select('inventory.*', 'products.price', 'products.id as productid')
            ->where('inventory.id', $showid)
            ->first();

        if ($inventory) {
            $price = explode(' ', $inventory->price);
            $totalprice = $price[1] * $inventory->quantity;
            $inventory->totalprice = $price[0] . " " . $totalprice;

            $productimages = ProductImage::where('product_id', $inventory->productid)->get();

            if ($productimages->toArray()) {
                $inventory->images = $productimages;
            } else {
                $inventory->images = array();
            }

            return response()->json(['result' => 1, 'inventory' => $inventory]);
        } else {
            return response()->json(['result' => 0, 'inventory' => 'No record found for this product.']);
        }
    }

    public function showallproductqty(Request $request)
    {
        $inventory = Inventory::join('products', 'products.id', '=', 'inventory.product_id')
            ->select('inventory.*', 'products.price', 'products.product_name', 'products.product_number', DB::raw('(products.price * inventory.quantity) as totalprice'))
            ->get();

        // $inventory = Inventory::join('products','products.id','=','inventory.product_id')
        //         ->select('inventory.*','products.price','products.product_name','products.product_number')->get();


        if ($inventory->toArray()) {
            foreach ($inventory as $inventoryval) {
                $price = explode(' ', $inventoryval->price);
                $separateprice = $price[1];
                $qty = $inventoryval->quantity;
                // $finalprice =  $separateprice * $qty;                            
                // $inventoryval->totalprice = $separateprice;

                $productimages = ProductImage::where('product_id', $inventoryval->product_id)->get();

                if ($productimages->toArray()) {
                    $inventoryval->images = $productimages;
                } else {
                    $inventoryval->images = array();
                }
            }

            return response()->json(['result' => 1, 'inventory' => $inventory]);
        } else {
            return response()->json(['result' => 0, 'inventory' => 'No record found.']);
        }
    }

    public function getinventory(Request $request)
    {

        // $products = Product::where('company_id',$request->companyid)->get();
        $products = Product::leftjoin('inventory', 'inventory.product_id', '=', 'products.id')
            ->select('products.*', 'inventory.quantity')
            ->where('company_id', $request->companyid)
            ->get();

        if ($products) {

            return response()->json(['result' => 1, 'inventory' => $products]);
        } else {
            return response()->json(['result' => 0, 'inventory' => 'No record found.']);
        }
    }
}
