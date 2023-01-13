<?php

namespace App\Http\Controllers\admin;

use App\Models\Currency;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function list()
    {
        return view('admin.currencies.list');
    }


    public function getallcurrencies(Request $request)
    {
        $columns = array(
            0 => 'id',
            1 => 'name',
            2 => 'currency_code',
            3 => 'currency_sign',
            4 => 'country_name',
            5 => 'country_price',
            6 => 'price_in_uk',
            7 => 'price_per_ticket',
            8 => 'processing_fee',
            9 => 'stripe_support',
            // 2 => 'created_at',
        );

        $filters = [
            'search' => $request->input('search.value'),
            'start'   => $request->input('start'),
            'limit'   => $request->input('length'),
            'orderby' => $columns[$request->input('order.0.column')],
            'dir'     => $request->input('order.0.dir')
        ];
        $totalData     = 50;
        $totalData = Currency::count();

        $alldata       = Currency::GetData($filters);
        $totalFiltered = Currency::GetCount($filters['search']);

        $responsedata = array();
        if (!empty($alldata)) {
            $i = $filters['start'] + 1;
            foreach ($alldata as $row) {
                $actionhtml  =  '<a href="' . url('admin/currencies/edit/' . $row->id) . '" title="Edit" class="btn btn-primary font-15 view" > <i class="fa fa-pencil"></i></a>
                <a href="' . url('admin/currencies/delete/' . $row->id) . '" title="View" class="btn btn-primary font-15 view" > <i class="fa fa-pencil"></i></a>';
                $nestedData['id']               =  $row->id;
                $nestedData['name']             =  $row->name;
                $nestedData['currency_code']    =  $row->currency_code;
                $nestedData['currency_sign']    =  $row->currency_sign;
                $nestedData['country_name']     =  $row->country_name;
                $nestedData['country_price']    =  $row->currency_code . ' ' . $row->country_price;
                $nestedData['price_in_uk']      =  '£' . $row->price_in_uk;
                $nestedData['price_per_ticket'] =  $row->price_per_ticket;
                $nestedData['processing_fee']   =  $row->processing_fee;
                $nestedData['stripe_support']   =  $row->stripe_support;
                $nestedData['options']          =  $actionhtml;
                $responsedata[]                 =  $nestedData;
            }
        }
        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $responsedata,
        );
        echo json_encode($json_data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $currency = array();
        return view('admin.currencies.form', compact('currency'));
    }




    public function edit(Request $request, $id)
    {
        $currency = Currency::find($id);

        if ($currency) {

            return view('admin.currencies.form', compact('currency'));
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $this->validate(
            $request,
            [
                'name'            => 'required',
                'currency_code'   => 'required',
                'country_name'    => 'required',
                'currency_sign'    => 'required',
                'country_price'   => 'required',
                'price_in_uk'     => 'required'
            ]
        );

        if ($request->id) {
            $currencies = Currency::find($request->id);
        } else {
            $currencies = new Currency();
        }
        $currencies->fill($request->all());
        $currencies->save();

        $msg = 'Currency created successfully';
        if ($request->id) {
            $msg = 'Currency updated successfully';
        }
        return redirect('/admin/currencies')->with('success', $msg);
    }


    public function delete(Request $request, $id)
    {
        $Currency = Currency::find($id);
        if ($Currency) {
            $Currency->delete();
        }
        return redirect('/admin/currencies')->with('message', 'Currency deleted successfully');
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Currency  $currency
     * @return \Illuminate\Http\Response
     */
    public function show(Currency $currency)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Currency  $currency
     * @return \Illuminate\Http\Response
     */

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Currency  $currency
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Currency $currency)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Currency  $currency
     * @return \Illuminate\Http\Response
     */
    public function destroy(Currency $currency)
    {
        //
    }
}
