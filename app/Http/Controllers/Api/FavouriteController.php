<?php

namespace App\Http\Controllers\Api;

use App\Models\Company;
use App\Models\Favourite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FavouriteController extends Controller
{
    public function addtofavourite(Request $request)
    {
        $user = Auth::user();
        $fav = Favourite::updateOrCreate(
            ['company_id' => $request->id, 'user_id' => $user->id],
            ['company_id' => $request->id, 'user_id' => $user->id]
        );
        if ($fav->wasRecentlyCreated) {
            $company = Company::find($request->id);
            if ($company) {
                $company->totalfavorite = ($company->totalfavorite ?? 0) + 1;
                $company->save();
            }
        }
        return response()->json(['result' => 1, "message" => "Added to favourite"]);
    }

    public function removefavourite(Request $request)
    {
        $user = Auth::user();
        $fav = Favourite::where(
            ['company_id' => $request->id, 'user_id' => $user->id]
        )->first();

        if ($fav) {
            $fav->delete();
        }

        $company = Company::find($request->id);
        if ($company) {
            $total = (($company->totalfavorite ?? 0) - 1);
            $total = ($total < 0) ? 0 : $total;
            $company->totalfavorite =  $total;
            $company->save();
        }
        return response()->json(['result' => 1, "message" => "Removed from favourite"]);
    }

    public function getfavouriteCompanies(Request $request)
    {
        $now = \Carbon\Carbon::now();
        $favoriteCompanies  = Auth()->user()->favoriteCompanies;
        $ispaymenton = $this->ispaymenton();
        if ($ispaymenton) {
            $favoriteCompanies = $favoriteCompanies->where('expiry_date', '>', $now->toDateString());
        }
        return response()->json(['result' => 1, "companies" => $favoriteCompanies]);
    }

    protected function ispaymenton()
    {
        $cs = DB::table('settings')->where('control_settings', 'PAYMENT MANDATORY')->first();
        if ($cs) {
            return $cs->on_off;
        }
        return false;
    }


    /*  public function getStatuses() {
        $statuses = \App\Status::select("id","title","description")->get();
       return response()->json(['result' => 1,"statuses" => $statuses ]);
    }

    public function getReasons() {
        $reasons = \App\Reason::select("id","title")->get();
       return response()->json(['result' => 1,"reasons" => $reasons ]);
    }
    
    public function getFaq() {
        $faqs = \App\Faq::select("question","answer")->get();
       return response()->json(['result' => 1,"faqs" => $faqs ]);
    }

    public function getAlluserNotifications() {
        $user = Auth::user();
        return response()->json(['result' => 1,"notifications" => $user->notifications ]);
    }

    public function contactus(Request $request) {
        $validator=Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required',
            'message' => 'required',
            ]);
      
        if ($validator->fails()){
              return response()->json(['result' => '0',"message"=>"Validation error",'errors' => $validator->errors()->messages()]);
        }


        $contact = new  \App\Contactus();
        $contact->name  = $request->name;
        $contact->email = $request->email;
        $contact->message   = $request->message;
        $contact->save();
        
        \Mail::send('emails.contactus',['contact'=>$contact] , function($message) {
           $message->to('mainz@kanzlei-scheruebel.de', 'Mister Parking')
           ->subject('Kontaktformular Misterparking.de');
        });
        return response()->json(['result' => 1,"message" => "Vielen Dank für deine Kontaktanfrage. Wir melden uns in Kürze bei dir."]);
    }*/
}
