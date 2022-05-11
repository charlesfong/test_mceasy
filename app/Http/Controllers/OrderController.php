<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\customer;
use App\Models\customer_address;
use App\Models\supplier;
use App\Models\Product;
use App\Models\courier;
use App\Models\shipment;
use App\Models\order;
use App\Models\order_detail;
use App\Models\order_log;
use Illuminate\Support\Facades\Auth;
use DB;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.index', [
            'nav_tab' => 'order'
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create_order()
    {
        $customers = customer::all();
        $suppliers = supplier::all();

        return view('admin.create_order', [
            'nav_tab'   => 'create_order',
            'customers' => $customers,
            'suppliers' => $suppliers
        ]);
    }

    public function show_product(Request $request){
        $products = product::where('id_supplier',$request->id)->where('status',true)->get();
        return response()->json(['success'=>true, 'data'=>$products],200);
    }

    public function list_orders(Request $request)
    {
        $orders = array();
        if ($request->date1!=null){
            $where_status   = $request->status;
            if ($where_status!='9'){
                $orders         = order::where('status','=',$where_status);
            } else {
                $orders         = order::where('status','!=','5');
            }
            $orders         = $orders->whereDate('CREATED_AT','>=',$request->date1)->whereDate('CREATED_AT','<=',$request->date2);
        } else {
            $orders         = order::where('status','!=','5');
        }
        
        $orders = $orders->orderBy('CREATED_AT','desc')->get();
        
        $order_details  = order_detail::orderBy('id_product','ASC')->get();

        return view('admin.list_orders', [
            'nav_tab'           => 'list_orders',
            'orders'            => $orders,
            'order_details'     => $order_details
        ]);
    }

    public function create_po()
    {
        return view('admin.create_po', [
            'nav_tab' => 'create_po'
        ]);
    }

    public function list_po()
    {
        return view('admin.list_po', [
            'nav_tab' => 'list_po'
        ]);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // FORMULA ID
        // ID = TWO CHARACTER OF CUSTOMER NAME + ID CUSTOMER + '-' + DATE(Y-M-D) + COUNTER OF THAT CUSTOMER ON CURRENT DATE
        DB::beginTransaction();
        try{
            $customer       = customer::where('id',$request->id_customer)->first();
            $company        = customer_address::find($request->id_address)->first();
            $split_name     = explode(" ",$customer->name);
            if (count($split_name)==1){
                $split_name[1]="Z";
            }
            if ($split_name[1]==null || $split_name[1]==""){
                $split_name[1]="Z";
            }
            $check_current = order::whereYear('CREATED_AT',"=",date("Y"))->whereMonth('CREATED_AT',"=",date("m"))->count();
            // $check_current = order::where('id_customer',$request->id_customer)->whereDate('CREATED_AT',">=",date("Y-m-d"))->whereDate('CREATED_AT',"<=",date("Y-m-d"))->count();
            $check_current = str_pad($check_current+1, 4, "0", STR_PAD_LEFT);
            $trcd          = strtoupper(substr($split_name[0],0,1)).strtoupper(substr($split_name[1],0,1)).$request->id_customer."-".date("Ymd").$check_current;

            
            $order = new order();
            $order->id                  =   $trcd;
            $order->id_customer         =   $request->id_customer;
            $order->id_address          =   $request->id_address;
            $order->name_address        =   $company->name;
            $order->name_customer       =   $customer->name;
            $order->shipping_address    =   $request->shipping_address;
            $order->total_pay           =   $request->total_pay;
            $order->status              =   0;
            $order->remark              =   nl2br($request->remark);
            $order->created_by          =   Auth::user()->name;
            $order->updated_by          =   Auth::user()->name;
            $order->saveOrFail();
            $carts = json_decode($request->cart);
            foreach ($carts as $key => $item) {
                $order_detail = new order_detail();
                $order_detail->id_order     =   $trcd;
                
                $order_detail->id_product   =   $item->id;
                $order_detail->name_product =   $item->name;
                $order_detail->qty          =   (int) $item->qty;
                $order_detail->price        =   (int) $item->price;
                $order_detail->d_price      =   (int) $item->dprice;
                $order_detail->profit       =   $item->price-$item->dprice;
                $order_detail->saveOrFail();
            }
            
            
            
            DB::commit();
            return response()->json(['success'=>true, 'data'=>'ok'],200);
        } catch (Throwable $e) {
            report($request);
            report($e);
            return response()->json(['success'=>false, 'data'=>'not ok'],200);
        }
        
        // catch(\Exception $e) {
        //     DB::rollback();
        //     return response()->json(
        //         ['error'=>'Something went wrong, please try later.'],
        //         $e->getCode()
        //     );
        // }
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
            $order = order::find($request->id);
            $order->status = 5;
            $order->save();
            
            DB::commit();
            $orders         = order::where('status','!=','5')->get();
            $order_details  = order_detail::orderBy('id_product','ASC')->get();

            $data['orders'] = $orders;
            $data['details']= $order_details;

            return response()->json(['success'=>true, 'data'=>$data],200);
        } catch(\Exception $e) {
            DB::rollback();
            return response()->json(
                ['error'=>'Something went wrong, please try later.'],
                $e->getCode()
            );
        }
    }

    public function process_order(Request $request) {
        DB::beginTransaction();
        try{
            $trcd = "";
            if ($request->array_invoices!=null && $request->array_invoices!=''){

                $order = order::find($request->id);
                $order->status      = 1;
                $order->UPDATED_BY  = Auth::user()->name;
                $order->UPDATED_AT  = date('Y-m-d H:m:s');
                $order->save();

                $array_invoices = json_decode($request->array_invoices);
                foreach ($array_invoices as $key => $detail) {
                    $order_detail = order_detail::where('id_order',$detail->orderid)->where('id_product',$detail->code)->first();
                    $order_detail->id_invoices_details = $detail->invoice;
                    $order_detail->d_price             = $detail->d_price;
                    $order_detail->profit              = $order_detail->price-$detail->d_price;
                    $order_detail->UPDATED_AT          = date('Y-m-d H:m:s');
                    $order_detail->save();
                }
            }
            
            // CHECK IS IT SHIPMENT SELF PICK UP
            $self_pickup = false;
            $shipment = shipment::where("id_order",$request->id)->first();
            if ($shipment!=null){
                if ($shipment->id_courier=='self'){
                    $self_pickup = true;
                }
            }

            if ($request->courier!=''){
                $shipment = new shipment();
                $shipment->id_order     = $request->id;
                $shipment->id_courier   = $request->courier;
                $shipment->shipping_cost= (int) $request->shipping_fee;
                $shipment->status       = "Shipping inputed";
                $shipment->created_by   = Auth::user()->name;
                $shipment->updated_by   = Auth::user()->name;
                $shipment->save();

                $order = order::find($request->id);
                $order->status      = 2;
                $order->UPDATED_BY  = Auth::user()->name;
                $order->UPDATED_AT  = date('Y-m-d H:m:s');
                $order->save();
            }

            if ($request->tracking_no!='' || $self_pickup){
                $shipment = shipment::where('id_order',$request->id)->first();
                if (!$self_pickup) {
                    $shipment->tracking_no  = $request->tracking_no;
                }
                $shipment->status       = "Delivery";
                $shipment->UPDATED_AT   = date('Y-m-d H:m:s');
                $shipment->updated_by   = Auth::user()->name;
                $shipment->save();

                $order = order::find($request->id);
                $order->status      = 3;
                $order->UPDATED_BY  = Auth::user()->name;
                $order->UPDATED_AT  = date('Y-m-d H:m:s');
                $order->save();
            }

            if ($request->done==true){
                $shipment = shipment::where('id_order',$request->id)->first();
                $shipment->status       = "Sent";
                $shipment->UPDATED_AT   = date('Y-m-d H:m:s');
                $shipment->updated_by   = Auth::user()->name;
                $shipment->save();

                $order = order::find($request->id);
                $order->status      = 4;
                $order->UPDATED_BY  = Auth::user()->name;
                $order->UPDATED_AT  = date('Y-m-d H:m:s');
                $order->save();
            }
            
            // var_dump($request);
            DB::commit();
            return response()->json(['success'=>true],200);
        } catch(\Exception $e) {
            DB::rollback();
            return response()->json(
                ['error'=>'Something went wrong, please try later.'],
                $e->getCode()
            );
        }
    }

    public function find_byId(Request $request)
    {
        $id = $request->id;
        // $order = order::where('id',$id)->first();
        $order              = order::leftJoin('customers as c', 'orders.id_customer', '=', 'c.id')->where('orders.id',$id)->select(["orders.*","c.name"])->first();
        $order_details      = order_detail::where('id_order',$id)->orderBy('id_product','ASC')->get();
        $done_date          = order_log::where('id_order',$id)->where('new_status','4')->first();
        $delivering_date    = order_log::where('id_order',$id)->where('new_status','3')->first();
        $ready_date         = order_log::where('id_order',$id)->where('new_status','2')->first();
        $paid_date          = order_log::where('id_order',$id)->where('new_status','1')->first();
        $draft_date         = order_log::where('id_order',$id)->where('new_status','0')->first();
        $shipment           = shipment::where('id_order',$id)->first();
        $couriers           = courier::all();

        $not_yet_sent       = true;
        if ($shipment==null || empty($shipment) || $shipment==''){
            //NOT YET SENT
            $not_yet_sent   = false;
        }
        $data['order']              = $order;
        $data['detail']             = $order_details;
        $data['done_date']          = $done_date;
        $data['delivering_date']    = $delivering_date;
        $data['ready_date']         = $ready_date;
        $data['delivering_date']    = $delivering_date;
        $data['paid_date']          = $paid_date;
        $data['draft_date']         = $draft_date;
        $data['couriers']           = $couriers;
        $data['shipment']           = $shipment;
        $data['has_sent']           = $not_yet_sent;
        return json_encode($data);
    }

    public function show_receipt($id){
        $id = explode("=",$id);
        $id = $id[1];

        $order          = order::find($id);
        $order_detail   = order_detail::where("id_order",$id)->get();
        $customer       = customer::find($order->id_customer);
        $shipment       = shipment::leftjoin('couriers','couriers.id','=','shipments.id_courier')->where("id_order",$id)->select("shipments.*","couriers.name as courier_name")->first();
        
        return view('admin.receipt', [
            'order'         => $order,
            'order_detail'  => $order_detail,
            'customer'      => $customer,
            'shipment'      => $shipment
        ]);
    }

    public function show_suratjalan($id){
        $id = explode("=",$id);
        $id = $id[1];
        
        $order          = order::find($id);
        $order_detail   = order_detail::where("id_order",$order->id)->get();
        $customer       = customer::find($order->id_customer);
        $shipment       = shipment::leftjoin('couriers','couriers.id','=','shipments.id_courier')->where("id_order",$order->id)->select("shipments.*","couriers.name as courier_name")->first();
        
        return view('admin.surat_jalan', [
            'order'         => $order,
            'order_detail'  => $order_detail,
            'customer'      => $customer,
            'shipment'      => $shipment
        ]);
    }
}
