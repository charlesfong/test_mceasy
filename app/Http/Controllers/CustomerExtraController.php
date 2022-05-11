<?php

namespace App\Http\Controllers;

use App\Models\customer;
use App\Models\customer_extra;
use Illuminate\Http\Request;
use DB;

class CustomerExtraController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\customer_extra  $customer_extra
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        // $customer = customer::all();
        $customer = customer_extra::where('id_customer',$request->id)->select('customers_extras.*')
        ->selectRaw(DB::raw("COALESCE(phone1, '-') AS phone_1,COALESCE(phone2, '-') AS phone_2,COALESCE(phone3, '-') AS phone_3,COALESCE(email, '-') AS email_"))->get();
        // dd($customer);
        $data = ['result' => 1,
            'data' => $customer
        ];
        return response()->json($data, 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\customer_extra  $customer_extra
     * @return \Illuminate\Http\Response
     */
    public function edit(customer_extra $customer_extra)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\customer_extra  $customer_extra
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, customer_extra $customer_extra)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\customer_extra  $customer_extra
     * @return \Illuminate\Http\Response
     */
    public function destroy(customer_extra $customer_extra)
    {
        //
    }
}
