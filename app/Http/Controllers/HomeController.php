<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    function stripeSetup($id)
    {

        Auth::logout();
        if (Auth::loginUsingId($id)) {
            if (auth()->user()->email == '') {
                return response()->json(['result' => 0, 'message' => 'YOUR EMAIL NOT UPDATE IN PROFILE']);
            }
            //$url = 'https://connect.stripe.com/express/oauth/authorize?client_id=ca_KjmI7Aksr9OnOgRIghEiAxTMvzlKbnB0&state=AU&stripe_user[email]=' . auth()->user()->email . '#/';
                    $url = 'https://connect.stripe.com/express/oauth/authorize?client_id=ca_MdrylXaW5WEZcL9bAGUqx5gMGgqtI8ZT&state=AU&stripe_user[email]=' . auth()->user()->email . '#/';

            return redirect($url);
        }
        return response()->json(['result' => 0, 'message' => 'Invalid detail.']);
    }


    public function stripe_callback(Request $request)
	{

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, "https://connect.stripe.com/oauth/token");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt(
			$ch,
			CURLOPT_POSTFIELDS,
			"client_secret=" . env('STRIPE_SECRET','sk_test_51LqDGEDgZHqv5TzUMHUSQILyRMzaLLQXQ8lzZZiHuSNF8UYPfDi2nsGMdqRPGhMPvwoQve6JcZEJCDNOOpaOpkn400gibP49JV') . "&grant_type=authorization_code&code=" . $request->code . ""
		);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$server_output = curl_exec($ch);

		curl_close($ch);
		$server_output = json_decode($server_output, true);

		$user_data = Auth::user();
		$user_data->stripe_account_id = $server_output['stripe_user_id'];
		$user_data->save();
        return redirect('stripe-account-create-thank-you');
	}


    public function stripe_callbackOld(Request $request)
    {

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://connect.stripe.com/oauth/token");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt(
            $ch,
            CURLOPT_POSTFIELDS,
            "client_secret=" . env('STRIPE_SECRET') . "&grant_type=authorization_code&code=" . $request->code . ""
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);
        curl_close($ch);
        $server_output = json_decode($server_output, true);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/accounts/' . $server_output['stripe_user_id']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_USERPWD,  env('STRIPE_SECRET') . ':' . '');
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        //$stripe =  Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
        $stripe->accounts->update(
            $server_output['stripe_user_id'],
            ['capabilities' => [
                'card_payments' => ['requested' => true],
                'transfers' => ['requested' => true],
            ]]
        );

        $user_data = Auth::user();
        $user_data->stripe_account_id = $server_output['stripe_user_id'];
        $user_data->save();

        return redirect('/');
    }

    function stripeAccountCreateThankYou(){
        return view('stripe-account-create-thank-you');
    }

}
