<?php

namespace App\Http\Controllers;

use App\Models\courier;
use Illuminate\Http\Request;
use DB;

class CourierController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $couriers = courier::all();
        $nav_tab   = 'courier';

        return view('admin.courier', compact('couriers','nav_tab'));
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
        
        DB::beginTransaction();
        try{
            $courier = new courier($request->all());
            // var_dump($courier);
            $courier->saveOrFail();
            
            DB::commit();
            $couriers = courier::all();
            return response()->json(['success'=>true, 'data'=>$couriers],200);
        } catch(\Exception $e) {
            DB::rollback();
            return response()->json(
                ['error'=>'Something went wrong, please try later.'],
                $e->getMessage()
            );
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\courier  $courier
     * @return \Illuminate\Http\Response
     */
    public function show(courier $courier)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\courier  $courier
     * @return \Illuminate\Http\Response
     */
    public function edit(courier $courier)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\courier  $courier
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, courier $courier)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\courier  $courier
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        DB::beginTransaction();
        try{
            $courier = courier::find($request->id);
            $courier->delete();
            
            DB::commit();
            $couriers = courier::orderBy('id')->get();
            return response()->json(['success'=>true, 'data'=>$couriers],200);
        } catch(\Exception $e) {
            DB::rollback();
            return response()->json(
                ['error'=>'Something went wrong, please try later.'],
                $e->getCode()
            );
        }
    }

    public function find_byId($id)
    {
        $courier = courier::where('id',$id)->first();
        return $courier;
    }
}
