<?php

namespace App\Http\Controllers;

use App\Models\supplier;
use Illuminate\Http\Request;
use DB;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $suppliers = supplier::all();
        // $cust = array();
        // foreach ($customers as $key => $value) {
        //     $customers_extra = customer_extra::where('id_customer',$value->id)->get();
        //     foreach ($customers_extra as $key => $value) {
        //         $cust[] = $value;
        //     }
            
        // }
        // dd($cust);
        $nav_tab   = 'supplier';

        
        return view('admin.supplier', compact('suppliers','nav_tab'));
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
            $supplier = new supplier($request->all());
            $supplier->saveOrFail();
            
            DB::commit();
            $suppliers = supplier::all();
            return response()->json(['success'=>true, 'data'=>$suppliers],200);
        } catch(\Exception $e) {
            DB::rollback();
            return response()->json(
                ['error'=>'Something went wrong, please try later.'],
                $e->getCode()
            );
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\supplier  $supplier
     * @return \Illuminate\Http\Response
     */
    public function show(supplier $supplier)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\supplier  $supplier
     * @return \Illuminate\Http\Response
     */
    public function edit(supplier $supplier)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\supplier  $supplier
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, supplier $supplier)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\supplier  $supplier
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        DB::beginTransaction();
        try{
            $supplier = supplier::find($request->id);
            $supplier->delete();
            
            DB::commit();
            $suppliers = supplier::orderBy('id')->get();
            return response()->json(['success'=>true, 'data'=>$suppliers],200);
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
        $supplier = supplier::where('id',$id)->first();
        return $supplier;
    }
}
