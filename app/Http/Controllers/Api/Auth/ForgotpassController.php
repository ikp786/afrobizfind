<?php

namespace App\Http\Controllers\Api\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Api\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ForgotpassController extends Controller
{

    public function forgotpassword(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if ($user) {
            if (!$user->otp) {
                $user->otp = rand(100000, 999999);
                $user->save();
            }
            $user->save();

            Mail::send('emails.forgotpassword', ['user' => $user], function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Reset Password Notification');
            });
            return response()->json(['result' => 1, "message" => "Email sent, Please check."]);
        }
        return response()->json(['result' => 0, "message" => "Email is not registered"]);
    }

    public function verifyopt(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if ($user && $user->otp &&  $user->otp == $request->otp) {

            if (!$userToken = \JWTAuth::fromUser($user)) {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }
            if ($request->fcmtoken) {
                $user->fcmtoken = $request->fcmtoken;
            }
            $user->otp = null;
            $user->save();
            $user->token = $userToken;

            return response()->json(['result' => 1, "message" => "OTP verified", 'user' => $user]);
        }
        return response()->json(['result' => 0, "message" => "Invalid OTP "]);
    }

    public function changepassword(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'password' => ['required', 'string', 'min:6',]
        ]);
        if ($validator->fails()) {
            return response()->json(['result' => 0, 'message' => 'Validation Error', 'errors' => $validator->errors()->messages()]);
        }

        $user = Auth::user();

        if ($user) {
            $user->password = Hash::make($request->password);
            $user->save();
        }
        return response()->json(['result' => 1, "message" => "Password changed successfully"]);
    }
}
