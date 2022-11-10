<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Referral;
use Illuminate\Support\Facades\Auth;

class ReferralController extends Controller
{
    public function index()
    {
        try {

            $user = Auth::user();
            $referrals = Referral::where('referrer', $user->user_number)
                ->select('id', 'company_number','company_name', 'referrer', 'first_payment', 'number_of_payments', 'subscription_status','created_at')
                ->orderBy('created_at','DESC')
                ->get();

            if (($referrals)) {
                return response()->json([
                    'result' => 1,
                    'message' => 'Referrals found',
                    'data' => $referrals
                ]);
            }
            return response()->json([
                'result' => 0,
                'message' => 'Referrals not found',
                'data' => []
            ]);
        } catch (\Throwable $th) {

            return response()->json([
                'result' => 0,
                'message' => 'Something went wrong',
            ]);
        }
    }
}
