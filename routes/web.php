<?php

use App\Http\Controllers\admin\AdminController;
use App\Http\Controllers\admin\CurrencyController;
use App\Http\Controllers\admin\VersionController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\IndexController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\StripeController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// require __DIR__ . '/auth.php';

Route::get('/', function () {
    return view('home');
});


Route::controller(HomeController::class)->group(function () {

Route::get('stripe_callback',  'stripe_callback');
Route::get('stripe_setup/{id}', 'stripeSetup');
Route::get('stripe-account-create-thank-you', 'stripeAccountCreateThankYou');

});

Route::get('/privacypolicy', function () {
    return view('tearms');
});


Auth::routes(['register' => false, 'reset' => false]);

// STRIPE PAYMENT ROUTE
Route::controller(StripeController::class)->group(function () {
    Route::get('payment', 'payment')->name('payment');
    Route::get('cancel', 'cancel')->name('payment.cancel');
    Route::get('payment/success', 'success')->name('payment.success');
    Route::post('payment/stripe_subscriptions_callback', 'stripeSubscriptionsCallback')->name('payment.stripe_subscriptions_callback');
    Route::get('payment/success_callback', 'success_call_back')->name('payment.success_call_back');
    Route::get('payment/failed_callback', 'failed_callback')->name('payment.failed_callback');
});

// PRODUCT ORDER START
Route::controller(OrderController::class)->group(function () {

    Route::get('product_order/cancel', 'cancel')->name('product_order.payment.cancel');
    Route::get('product_order/payment/success', 'success')->name('product_order.payment.success');
    Route::get('product_order/success_callback', 'success_call_back')->name('product_order.payment.success_call_back');
    Route::get('product_order/failed_callback', 'failed_callback')->name('product_order.payment.failed_callback');

/*
|--------------------------------------------------------------------------
| PAYOUT ROUTE (CRON)
|--------------------------------------------------------------------------
*/

    Route::get('product_order_daily_pryout', 'productOrderdaliyPayout');
    Route::get('ticket_order_daily_pryout', 'ticketOrderdaliyPayout');


});

// PRODUCT ORDER END


// EVENT ORDER START
Route::controller(EventController::class)->group(function () {

    Route::get('event_order/cancel', 'cancel')->name('event_order.payment.cancel');
    Route::get('event_order/payment/success', 'success')->name('event_order.payment.success');
    Route::get('event_order/success_callback', 'success_call_back')->name('event_order.payment.success_call_back');
    Route::get('event_order/failed_callback', 'failed_callback')->name('event_order.payment.failed_callback');
});

// EVENT ORDER END






// Download PDF
Route::get('eventpdf/{eid}', [OrderController::class, 'eventpdf']);
Route::get('productpdf/{pid}', [OrderController::class, 'productpdf']);
Route::get('companyorderpdf/{companyid}', [IndexController::class, 'companyorderpdf']);
Route::get('companyproducts/{companyid}', [IndexController::class, 'companyproductspdf']);
Route::get('companyoffer/{companyid}', [IndexController::class, 'companyofferpdf']);

// Email PDF
Route::get('email_eventpdf/{eid}', [OrderController::class, 'email_eventpdf']);
Route::get('email_productpdf/{pid}', [OrderController::class, 'email_productpdf']);
Route::get('email_companyorderpdf/{companyid}', [IndexController::class, 'email_companyorderpdf']);
Route::get('email_companyproducts/{companyid}/{userid}', [IndexController::class, 'email_companyproductspdf']);
Route::get('email_companyoffer/{companyid}/{userid}', [IndexController::class, 'email_companyofferpdf']);


Route::get('cmptest', function () {
    return view('pdf.testcmporder');
});

Route::group(['prefix' => 'admin', 'middleware' => 'admin'], function () {

    Route::get('/', [AdminController::class, 'index'])->name('admin.home');

    Route::get('/versions', [VersionController::class, 'list'])->name('admin.versions');
    Route::post('/version/getall', [VersionController::class, 'getallversions']);

    Route::get('/version/add', [VersionController::class, 'add'])->name('version.add');
    Route::get('/version/edit/{id}', [VersionController::class, 'edit'])->name('version.edit');
    Route::get('/version/show/{id}', [VersionController::class, 'show'])->name('version.show');
    Route::post('/version/store', [VersionController::class, 'store'])->name('version.store');
    Route::get('/version/delete/{id}', [VersionController::class, 'delete'])->name('version.delete');

    Route::get('/currencies', [CurrencyController::class, 'list'])->name('admin.currencies');
    Route::post('/currencies/getall', [CurrencyController::class, 'getallcurrencies']);
    Route::get('/currencies/create', [CurrencyController::class, 'create'])->name('currencies.create');
    Route::get('/currencies/edit/{id}', [CurrencyController::class, 'edit'])->name('admin.currencies.edit');
    Route::get('/currencies/show/{id}', [CurrencyController::class, 'show']);
    Route::post('/currencies/store', [CurrencyController::class, 'store'])->name('currencies.store');
    Route::get('/currencies/delete/{id}', [CurrencyController::class, 'delete'])->name('admin.currencies.delete');
});

Route::get('clear_cache', function () {
    $exitCode = Artisan::call('storage:link', []);
    echo $exitCode;
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

