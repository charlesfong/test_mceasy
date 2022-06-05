<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KaryawanController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

Route::post('/delete_karyawan',   [KaryawanController::class, 'destroy'])->name('delete_karyawan');
Route::post('/show_karyawan/',          [KaryawanController::class,'show_karyawan'])->name('show_karyawan');
Route::post('/karyawan/edit_karyawan',       [KaryawanController::class, 'edit_karyawan'])->name('update_karyawan');
Route::post('/karyawan/store',       [KaryawanController::class, 'store'])->name('save_karyawan');
Route::get('/karyawan/list_cuti',       [KaryawanController::class, 'list_cuti'])->name('list_cuti');
Route::get('/karyawan/list_karyawan',       [KaryawanController::class, 'list_karyawan'])->name('list_karyawan');
Route::get('/karyawan/list_cuti',       [KaryawanController::class, 'list_cuti'])->name('list_cuti');
Route::get('/karyawan',                    [KaryawanController::class, 'index'])->name('karyawan');


Route::get('/', function () {
    // if (Auth::check()) {
        //if have dashboard goes to dashboard
        return redirect()->route('karyawan');
    // } else {
    //     return redirect()->route('login');
    // }
});