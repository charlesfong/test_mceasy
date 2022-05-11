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


class ReportController extends Controller
{
    public function index()
    {
        $customers = customer::all();
        return view('admin.report', [
            'nav_tab' => 'report',
            'customers' => $customers
        ]);
    }
}
