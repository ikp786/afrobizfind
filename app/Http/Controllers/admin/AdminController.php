<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Offer;
use App\Models\Product;
use App\Models\User;

class AdminController  extends Controller
{
    public function index()
    {
        $users   = User::count();
        $company = Company::count();
        $product = Product::count();
        $offers  = Offer::count();
        return view('admin.home', compact('users', 'company', 'product', 'offers'));
    }
}
