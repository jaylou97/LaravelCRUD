<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Controller;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// =======================dashboard route============================
Route::get('/', function () {
    return view('body');
});

// =========================================casheir transaction route===============================================
Route::get('cashier_transaction_route', [Controller::class, 'cashier_transaction_ctrl'])->name('cashier_transaction_route');
Route::get('get_cashier_transaction_route', [Controller::class, 'get_cashier_transaction_ctrl']);
Route::get('get_partial_den_route', [Controller::class, 'get_partial_den_ctrl']);
Route::post('update_partial_cd_route', [Controller::class, 'update_partial_cd_ctrl']);
Route::get('get_final_den_route', [Controller::class, 'get_final_den_ctrl']);
Route::post('update_final_cd_route', [Controller::class, 'update_final_cd_ctrl']);
Route::get('get_noncash_den_route', [Controller::class, 'get_noncash_den_ctrl']);
Route::post('update_noncash_route', [Controller::class, 'update_noncash_ctrl']);
Route::post('transfer_mop_route', [Controller::class, 'transfer_mop_ctrl']);
Route::get('get_terminal_route', [Controller::class, 'get_terminal_ctrl']);
Route::post('update_terminal_route', [Controller::class, 'update_terminal_ctrl']);
Route::post('update_sales_date_route', [Controller::class, 'update_sales_date_ctrl']);
Route::get('get_location_route', [Controller::class, 'get_location_ctrl']);
Route::get('get_section_route', [Controller::class, 'get_section_ctrl']);
Route::get('get_sub_section_route', [Controller::class, 'get_sub_section_ctrl']);
Route::post('update_location_route', [Controller::class, 'update_location_ctrl']);
Route::get('get_batch_remittance_route', [Controller::class, 'get_batch_remittance_ctrl']);
Route::post('update_batch_remittance_route', [Controller::class, 'update_batch_remittance_ctrl']);





