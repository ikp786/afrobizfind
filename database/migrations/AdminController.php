<?php
namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Braintree;

class AdminController  extends Controller
{     
    public function index(){
        $res = new Braintree\Gateway([
            'environment' => 'sandbox',
            'merchantId' => '32k8xv7jrqxn7w7m',
            'publicKey' => 'j6djrrdnhq3hnc2x',
            'privateKey' => '16de826a0e5ad5b4d17b824d42982234'
        ]);

        dd($res);
        $users   = \App\User::count();
        $company = \App\Company::count();
        $product = \App\Product::count();
        $offers  = \App\Offer::count();
        return view('admin.home',compact('users','company','product','offers'));
    } 
}
