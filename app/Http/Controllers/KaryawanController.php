<?php

namespace App\Http\Controllers;
 
use Illuminate\Http\Request;
use App\Models\karyawan;
use App\Models\cuti;
use Illuminate\Support\Facades\Auth;
use DB;

class KaryawanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.index', [
            'nav_tab' => 'dashboard'
        ]);
    }

    public function list_karyawan() {
        $karyawan   = karyawan::all();
        return view('admin.customer', [
            'nav_tab'   => 'list_karyawan',
            'karyawan'  =>  $karyawan
        ]);
    }

    public function list_cuti() {
        $cuti   = cuti::all();
        return view('admin.courier', [
            'nav_tab'   => 'list_cuti',
            'cuti'  =>  $cuti
        ]);
    }

    public function show_profile_info() {
        return view('admin.profile_info', [
            'nav_tab'   => 'profile-info'
        ]);
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
            $get_last_id = karyawan::select('nomor_induk')->orderBy('nomor_induk','desc')->first();
            $nomor_induk = 'IP'.str_pad(str_replace('IP','',$get_last_id->nomor_induk) + 1, 5, '0', STR_PAD_LEFT);
            $karyawan = new karyawan($request->all());
            $karyawan->tanggal_lahir = $request->year_input.'-'.str_pad($request->month_input,2,'0',STR_PAD_LEFT).'-'.str_pad($request->date_input,2,'0',STR_PAD_LEFT);
            $karyawan->nomor_induk=$nomor_induk;
            $karyawan->saveOrFail();
            $cuti      = cuti::select('nomor_induk',DB::raw("sum(lama_cuti) as total_cuti"))->groupBy('nomor_induk')->get();
            DB::commit();
            $karyawans = karyawan::select('*',DB::raw("DATE_FORMAT(tanggal_lahir, '%d-%b-%y') as tanggal_lahir_format"),DB::raw("DATE_FORMAT(tanggal_bergabung, '%d-%b-%y') as tanggal_bergabung_format"))->orderBy('nomor_induk')->get();
            return response()->json(['success'=>true, 'data'=>$karyawans,'cuti'=>$cuti],200);
        } catch(\Exception $e) {
            DB::rollback();
            return response()->json(
                ['error'=>'Something went wrong, please try later.'],
                $e->getCode()
            );
        }
    }

    public function edit_karyawan(Request $request)
    {
        DB::beginTransaction();
        try{

            $karyawan = karyawan::find($request->edit_id_karyawan);
           
            $karyawan->nama             = $request->edit_name;
            $karyawan->alamat           = $request->edit_address;
            $karyawan->tanggal_lahir    = $request->year_input.'-'.str_pad($request->month_input,2,'0',STR_PAD_LEFT).'-'.str_pad($request->date_input,2,'0',STR_PAD_LEFT);

            $karyawan->save();
            $cuti      = cuti::select('nomor_induk',DB::raw("sum(lama_cuti) as total_cuti"))->groupBy('nomor_induk')->get();
            DB::commit();
            $karyawans = karyawan::select('*',DB::raw("DATE_FORMAT(tanggal_lahir, '%d-%b-%y') as tanggal_lahir_format"),DB::raw("DATE_FORMAT(tanggal_bergabung, '%d-%b-%y') as tanggal_bergabung_format"))->orderBy('nomor_induk')->get();
            return response()->json(['success'=>true, 'data'=>$karyawans,'cuti'=>$cuti],200);
        } catch(\Exception $e) {
            DB::rollback();
            return response()->json(
                ['error'=>'Something went wrong, please try later.'],
                $e->getMessage()
            );
        }
    }

    public function show_karyawan(Request $request)
    {
        // $karyawan = karyawan::all();
        $karyawan = karyawan::where('nomor_induk',$request->id)->first();
        $data = ['result' => 1,
            'data' => $karyawan
        ];
        return response()->json($data, 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        DB::beginTransaction();
        try{
            // $karyawan = karyawan::find($request->id);
            $karyawan = karyawan::where('nomor_induk',$request->id)->first();
            $karyawan->delete();

            DB::commit();
            $karyawans = karyawan::select('*',DB::raw("DATE_FORMAT(tanggal_lahir, '%d-%b-%y') as tanggal_lahir_format"),DB::raw("DATE_FORMAT(tanggal_bergabung, '%d-%b-%y') as tanggal_bergabung_format"))->orderBy('nomor_induk')->get();
            $cuti      = cuti::select('nomor_induk',DB::raw("sum(lama_cuti) as total_cuti"))->groupBy('nomor_induk')->get();
            return response()->json(['success'=>true, 'data'=>$karyawans,'cuti'=>$cuti],200);
        } catch(\Exception $e) {
            DB::rollback();
            return response()->json(
                ['error'=>'Something went wrong, please try later.'],
                $e->getMessage()
            );
        }
    }

}
