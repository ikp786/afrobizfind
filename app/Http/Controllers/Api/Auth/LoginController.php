<?php

namespace App\Http\Controllers\Api\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Api\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class LoginController extends Controller
{

    public function logout(Request $request)
    {
        $user = Auth()->user();
        $user->fcmtoken = '';
        $user->save();
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json(['result' => 1, "message" => "Ausloggen erfolgreich"]);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => 0, 'message' => 'Validation Error', 'error' => $validator->messages()]);
        }
        try {
            // attempt to verify the credentials and create a token for the user
            //if (!$token = JWTAuth::attempt($credentials)) {
            if (!$token = JWTAuth::attempt($credentials, ['exp' => Carbon::now()->addDays(7)->timestamp])){

                return response()->json(['result' => 0, 'message' => 'Invalid email or password']);
            }
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return response()->json(['result' => 0, 'message' => 'Invalid email or password']);
        }
        $user = Auth::user();
        /* if(!$user->email_verified_at){
            return response()->json(['result' => 0, 'message' => 'Bitte verifiziere zuerst deine E-Mail und klicke den Aktivierungslink in unserer gesendeten E-Mail.','token'=> $token]);
        }*/

        if ($request->fcmtoken) {
            $user->fcmtoken = $request->fcmtoken;
            $user->save();
        }
        $user->token = $token;
        return response()->json(['result' => 1, 'data' => ['user' => $user]]);
    }

    function generateUserNumber()
    {
        $number = mt_rand(10000000, 99999999);

        // call the same function if the barcode exists already
        if ($this->UserNumberExists($number)) {
            return $this->generateUserNumber();
        }

        // otherwise, it's valid and can be used
        return $number;
    }

    function UserNumberExists($number)
    {
        // query the database and return a boolean
        // for instance, it might look like this in Laravel
        return User::where('user_number', $number)->count();
    }


    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => ['required', 'max:50'],
            'surname' => ['required', 'max:50'],
            //'username' => ['required', 'string', 'max:50', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:50', 'unique:users'],
            'username' => ['required', 'string', 'max:50', 'unique:users'],
            'password' => ['required', 'string', 'min:6',],
        ], [
            'email.unique' => 'Email is already registered',
            //'username.unique' => 'Username is already in use.'
        ]);
        if ($validator->fails()) {
            return response()->json(['result' => 0, 'message' => 'Validation Error', 'errors' => $validator->errors()->messages()]);
        }

        $user = new User();
        $user->first_name = $request->first_name;
        $user->surname = $request->surname;
        $user->username = $request->username;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->home_number = $request->home_number;
        $user->address_line_1 = $request->address_line_1;
        $user->city = $request->city;
        $user->postcode = $request->postcode;
        $user->mobile_number = $request->mobile_number;
        $user->user_number = $this->generateUserNumber();
        $user->save();
        // $user->sendEmailVerificationNotification();
        $user->token = JWTAuth::attempt(['email' =>  $request->email, "password" =>  $request->password]);

        return response()->json(['result' => 1, "message" => "Account created successfully.", 'user' => $user]);
    }

    /* public function resendemailverification(Request $request) {
        $user = \Auth::user();
        $user->sendEmailVerificationNotification();
        return response()->json(['result' => 1,"message"=>"E-Mail wurde erfolgreich Versendet." ]);

    }*/
}
