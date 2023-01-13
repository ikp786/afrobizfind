<?php

namespace App\Http\Controllers\Api;

use App\Models\Customer;
use App\Models\Favourite;
use App\Models\Notification;
use App\Models\Offer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use JWTAuth;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public function saveUser(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'first_name' => 'required',
            'surname'    => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['result' => '0', "message" => "Validation error", 'errors' => $validator->errors()->messages()]);
        }

        $u = Auth()->user()->id;

        $user = User::find($u);
        $user->first_name       = $request->first_name;
        $user->surname          = $request->surname;
        $user->username          = $request->username;
        $user->home_number      = $request->home_number;
        $user->address_line_1   = $request->address_line_1;
        $user->city             = $request->city;
        $user->postcode         = $request->postcode;
        $user->mobile_number    = $request->mobile_number;


        /*if ($request->hasFile('image')) {
            $image     = $request->image;
            $extension = $image->getClientOriginalExtension();
            $filename  = 'users/' . md5(rand() . time() . rand()) . "." . $extension;
            \Storage::disk('public_uploads')->put($filename, \File::get($image));
            $user->image = $filename;
        }*/
        $user->save();
        //$user = \App\User::find($user->id);

        return response()->json(['result' => 1, "message" => "User detail updated successfully", "user" => $user]);
    }


    public function removeuser(Request $request)
    {

        $user =  Auth()->user();
        if ($user) {
            $id = $user->id;
            if ($user->companies->count()) {
                foreach ($user->companies as $key => $company) {
                    Product::where('company_id', $company->id)->delete();
                    Offer::where('company_id', $company->id)->delete();
                    Favourite::where('company_id', $company->id)->delete();
                    Customer::where('company_id', $company->id)->delete();

                    $company->delete();
                }
            }
            $user->delete();
            return response()->json(['result' => 1, "message" => "User deleted successfully"]);
        }
    }

    public function logout(Request $request)
    {
        $user = Auth()->user();
        $user->fcmtoken = '';
        $user->save();
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json(['result' => 1, "message" => "Logout successfully"]);
    }

    public function usernotifications(Request $request)
    {
        $user = Auth()->user();
        $offset = 10 * (($request->page ?? 1) - 1);
        $notifications = Notification::where('user_id', $user->id)
            ->limit(10)
            ->offset($offset)
            ->orderby('id', 'desc')
            ->get();


        $totalnotifications = Notification::where('user_id', $user->id)->count();
        $total_pages = ceil($totalnotifications / 10);


        Notification::where('user_id', $user->id)->where('isread', '0')->update(array('isread' => 1));

        return response()->json(['result' => 1, "notifications" => $notifications, 'totalnotifications' => $totalnotifications, 'total_pages' => $total_pages]);
    }
}
