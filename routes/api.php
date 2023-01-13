<?php

use App\Http\Controllers\Api\Auth\ForgotpassController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\FavouriteController;
use App\Http\Controllers\Api\IndexController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\OtherController;
use App\Http\Controllers\Api\Payments\DirectPaymentController;
use App\Http\Controllers\Api\Payments\StripePaymentMethodController;
use App\Http\Controllers\Api\PaymentsController;
use App\Http\Controllers\Api\ProductController as ApiProductController;
use App\Http\Controllers\Api\ReferralController;
use App\Http\Controllers\Api\StripeController;
use App\Http\Controllers\Api\TicketTypeController;
use App\Http\Controllers\Api\User\CompanyController as UserCompanyController;
use App\Http\Controllers\Api\User\OfferController;
use App\Http\Controllers\Api\User\ProductController;
use App\Http\Controllers\Api\User\TableServiceController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('jwt.auth')->controller(PaymentsController::class)->group(function () {
    Route::get('/stripe-test', 'testStripe');
});

Route::middleware('jwt.auth')->prefix('stripe')->controller(StripePaymentMethodController::class)->group(function () {
    Route::get('/get-customer', 'getCustomer')->name('get-customer');
    Route::post('/store-payment_method', 'storePaymentMethod')->name('get-customer');
    Route::get('/get-payment_methods', 'index')->name('get-payment_methods');
});

Route::middleware('jwt.auth')->controller(DirectPaymentController::class)->group(function () {
    // Route::get('/stripe-test', 'testStripe');
    Route::post('/store-connected-account', 'makeAccount');
    Route::post('/transfer', 'transferPayment');
});


Route::post('login', [LoginController::class, 'login']);
Route::post('register', [LoginController::class, 'register']);
Route::get('deletewebhook', [StripeController::class, 'deletewebhook']);
// Route::post('forgotpassword', [ForgotpassController::class,'sendResetLinkEmail']);
Route::get('getpaymentstatus',  [OtherController::class, 'getpaymentstatus']);
Route::post('contactus',  [OtherController::class, 'contactus']);
Route::get('getversionhistiry',  [OtherController::class, 'getversionhistiry']);

Route::post('forgotpassword', [ForgotpassController::class, 'forgotpassword']);
Route::post('verifyopt', [ForgotpassController::class, 'verifyopt']);


Route::post('gethomepagedata', [IndexController::class, 'homepage']);
Route::post('getcompany', [CompanyController::class, 'getcompany']);
Route::post('search', [IndexController::class, 'search']);
Route::post('GetCartegoryProducts', [IndexController::class, 'GetCartegoryProducts']);
Route::get('getallcurrencies',  [OtherController::class, 'getallcurrencies']);

Route::post('getmoreproducts', [IndexController::class, 'getmoreproducts']); //Not in use
Route::post('neabycompanies', [IndexController::class, 'neabycompanies']); //Not in use


Route::group(['middleware' => 'jwt.auth'], function () {
    Route::get('/test', function () {
        dd(Auth::user());
    });
    Route::post('payment', [StripeController::class,'payment'])->name('payment');
    Route::get('getallcategories',  [OtherController::class, 'getallcategories']);
    Route::post('changepassword', [ForgotpassController::class, 'changepassword']);
    Route::post('getcompanydetail', [IndexController::class, 'getcompanydetail']);

    //events
    Route::post('addevent', [EventController::class, 'store']); //store the new event.
    Route::post('editevent', [EventController::class, 'editevent']); //edit event.
    Route::post('delevent', [EventController::class, 'delete']);  //Delete the Events.
    Route::post('cmpevent', [EventController::class, 'cmpevent']);  //fetching the events data by particular company.
    Route::post('allevents', [EventController::class, 'allevents']); //users all events having all company
    Route::post('singleevent', [EventController::class, 'singleevent']); //get particular event
    Route::post('eventdetails', [EventController::class, 'eventdetails']); //get particular event

    Route::post('ticket-types/store ', [TicketTypeController::class, 'store']); //get particular event
    Route::get('ticket-types/event/{eventId} ', [TicketTypeController::class, 'eventWise']); //get particular event
    Route::get('ticket-types/{typeId} ', [TicketTypeController::class, 'oneType']); //get particular event

    //Ticket purchase
    Route::post('ticketpurchase', [EventController::class, 'ticketpurchase']); //ticket purchase
    Route::post('ticketinfo', [EventController::class, 'ticketinfo']); //ticket info
    Route::post('ticketbroughtlist', [EventController::class, 'ticketbroughtlist']); //brought ticket list
    Route::post('statusadmitted', [EventController::class, 'statusadmitted']); //change status
    Route::post('statusrefunded', [EventController::class, 'statusrefunded']); //change status
    Route::post('ticketpurchaselist', [EventController::class, 'ticketpurchaselist']); //change status
    Route::post('ticketrefstatus', [EventController::class, 'ticketrefstatus']); //change status
    Route::post('gettickets', [EventController::class, 'gettickets']); //Get tickets from OrderNumber
    //Status
    Route::post('getstatus', [EventController::class, 'getstatus']); //get statuses

    //Inventory
    Route::post('getproducts', [InventoryController::class, 'getproducts']); //get products
    Route::post('addproductqty', [InventoryController::class, 'addproductqty']); //add product with qty
    Route::post('editproductqty', [InventoryController::class, 'editproductqty']); //edit product with qty
    Route::post('deleteproductqty', [InventoryController::class, 'deleteproductqty']); //delete specific product stock
    Route::post('showproductqty', [InventoryController::class, 'showproductqty']); //show specific product with qty
    Route::post('showallproductqty', [InventoryController::class, 'showallproductqty']); //show all product with qty
    Route::post('getinventory', [InventoryController::class, 'getinventory']); //show all product with qty
    Route::post('getbarcode', [InventoryController::class, 'getbarcode']); //show all barcode with product

    //Order
    Route::post('addorder', [OrderController::class, 'store']);
    Route::post('allorderhistory', [OrderController::class, 'allorderhistory']);  //users multiple companies order
    Route::get('fetchorderstatus', [OrderController::class, 'orderstatus']);  //order status
    Route::get('companyorder', [OrderController::class, 'companyorder']);  //particular company order individual
    Route::get('singleorder', [OrderController::class, 'singleorder']); //get the single order details
    Route::post('editorderstatus', [OrderController::class, 'editorderstatus']); // For updating the order status.

    //modify order details.
    // Route::post('updateorder',[OrderController::class,'updateorder']);

    Route::post('productorder', [OrderController::class, 'productorder']);  //get all product orders with login users.
    Route::post('ticketorder', [OrderController::class, 'ticketorder']);  //get all event tickets orders with login users.


    Route::post('alleventprod', [OrderController::class, 'alleventprod']);


    //Income By day Particular Wise
    Route::get('incomebyday', [OrderController::class, 'IncomeByDay']);

    //Income by product particular company wise
    Route::get('incomebyproduct', [OrderController::class, 'IncomebyProduct']);

    //Get profit of products
    Route::post('productprofit', [OrderController::class, 'productIncomeByDate']);

    // SUBSCRIPTION CANCEL
    Route::get('company_subscription_cancel/{company_id}', [OrderController::class, 'cancelSubscription']);

    /*User Companies CRUD start*/
    Route::group(['prefix' => 'user/company'], function () {
        Route::get('getall', [UserCompanyController::class, 'getall']);
        Route::post('get', [UserCompanyController::class, 'get']);
        Route::post('create', [UserCompanyController::class, 'save']);
        Route::post('update', [UserCompanyController::class, 'save']);
        Route::post('changestatus', [UserCompanyController::class, 'changestatus']);
        Route::post('delete', [UserCompanyController::class, 'delete']);
        Route::post('pay', [UserCompanyController::class, 'companyPay']);
    });

    // Table - Service Routes
    Route::group(['prefix' => '/table-service'], function () {
        Route::post('/get-all', [TableServiceController::class, 'getTableServices']);
        Route::post('/get-status', [TableServiceController::class, 'getStatus']);
        Route::post('/change-status', [TableServiceController::class, 'statusChange']);
        Route::post('/add-range', [TableServiceController::class, 'addRange']);
        Route::post('/table/status-change', [TableServiceController::class, 'tableStatusChange']);
    });

    Route::get('/get-referrals', [ReferralController::class, 'index']);

    Route::get('/payment/gettoken', [PaymentsController::class, 'gettoken']);
    Route::post('/payment/maketree', [PaymentsController::class, 'maketree']);  //payment

    Route::group(['prefix' => 'user/company/product'], function () {
        Route::post('getall', [ProductController::class, 'getallcompanyproduct']);
        Route::post('get', [ProductController::class, 'get']);
        Route::post('create', [ProductController::class, 'save']);
        Route::post('update', [ProductController::class, 'save']);
        Route::post('delete', [ProductController::class, 'delete']);
    });


    Route::group(['prefix' => 'user/company/offer'], function () {
        Route::post('getall', [OfferController::class, 'getall']);
        Route::post('gethistory', [OfferController::class, 'gethistory']);
        Route::post('get', [OfferController::class, 'get']);
        Route::post('create', [OfferController::class, 'save']);
        // Route::post('update', [OfferController::class,'save']);
        Route::post('delete', [OfferController::class, 'delete']);
    });


    /*User Companies CRUD END*/

    /*Customer*/
    // Route::group(['prefix' => 'customer'], function () {
    //     Route::get('add', [CustomerController::class, 'add']);
    //     Route::post('remove', [CustomerController::class, 'remove']);
    // });

    /*Front side*/
    /*Route::post('gethomepagedata', [IndexController::class,'homepage']);
    Route::post('getmoreproducts', [IndexController::class,'getmoreproducts']);

    Route::post('getcompanydetail', [IndexController::class,'getcompanydetail']);*/


    Route::post('saveuser', [UserController::class, 'saveUser']);
    Route::get('removeuser', [UserController::class, 'removeuser']);
    Route::get('logout', [UserController::class, 'logout']);
    Route::post('usernotifications', [UserController::class, 'usernotifications']);

    Route::post('getfavouritecompanies', [FavouriteController::class, 'getfavouriteCompanies']);
    Route::post('addtofavourite', [FavouriteController::class, 'addtofavourite']);
    Route::post('removefavourite', [FavouriteController::class, 'removefavourite']);

    Route::post('getcompanycustomers', [CustomerController::class, 'getcompanycustomers']);
    Route::post('addtocustomers', [CustomerController::class, 'addtocustomers']);
    Route::post('removecustomer', [CustomerController::class, 'removecustomer']);

    Route::post('getallproducts', [ApiProductController::class, 'getproductwithqty']);
});

Route::get('/product/barcode/{barcode_no}', [ProductController::class, 'barcode']);
