<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\CasheirTransactionModel;
use Illuminate\Http\Request;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function cashier_transaction_ctrl()
    {
        $html = '<div class="container-fluid">
                   <p><span style="font-weight: bold;">BUSINESS UNIT:</span> ALTURAS TALIBON</p>
                   <form class="form-inline"><span style="color: black; font-weight: bold;">Department: &nbsp;</span>
                   <select class="form-control" id="department" onchange="get_cashier_transaction_js()">';
  
            $department = DB::connection('pis')->table('locate_department')
                            ->select('dcode', 'dept_name')
                            ->whereRaw("LEFT(dcode, 4) = '0202'")
                            ->orderBy('dept_name', 'asc')
                            ->get()->toArray();
           
            foreach($department as $dept){
                $html.='<option value="'.$dept->dcode.'">'.$dept->dept_name.'</option>';
            }

           $html.='</select>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                   <span style="color: black; font-weight: bold;">Sales Date: &nbsp;</span>
                   <input class="form-control" id="sales_date" type="date" onchange="get_cashier_transaction_js()" value="'.date('Y-m-d').'" max="'.date('Y-m-d').'">
                  </form></div><br>';

        $html.='<div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Cashier Transaction Table</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive" id="div_ct_table">
                        
                        </div>
                    </div>
                </div>';

        $data['html'] = $html;
        echo json_encode($data);
    }

    public function get_cashier_transaction_ctrl(){
        // ==========cash data================
        $cash_data = DB::connection('ebs')->table('cs_cashier_cashdenomination')
                            ->select('tr_no', 'emp_id', 
                                DB::raw('MAX(emp_name) as emp_name'), 
                                DB::raw('MAX(concat(company_code, bunit_code, dep_code, section_code, sub_section_code)) as location'), 
                                DB::raw('MAX(remit_type) as remit_type'), 
                                DB::raw('MAX(pos_name) as pos_name'), 
                                DB::raw('MAX(borrowed) as borrowed')
                            )
                            ->where(DB::raw("concat(company_code,bunit_code,dep_code)"), $_GET['dcode'])
                            ->where(DB::raw("delete_status"), '<>', 'DELETED')
                            ->whereDate('date_submit', $_GET['date'])
                            ->groupBy('tr_no', 'emp_id') // if using group by you need to max or min the ungroup column to avoid error
                            ->get()->toArray();
        // =========noncash data=================
        $noncash_data = DB::connection('ebs')->table('cs_cashier_noncashdenomination as n')
                            ->leftJoin('cs_cashier_cashdenomination as c', function ($join) {
                                $join->on('n.tr_no', '=', 'c.tr_no')
                                     ->on('n.emp_id', '=', 'c.emp_id')
                                     ->on(DB::raw('date(n.date_submit)'), '=', DB::raw('date(c.date_submit)'));
                            })
                            ->select('n.tr_no', 'n.emp_id', 
                                DB::raw('MAX(n.emp_name) as emp_name'), 
                                DB::raw('MAX(concat(n.company_code, n.bunit_code, n.dep_code, n.section_code, n.sub_section_code)) as location'), 
                                DB::raw('MAX(n.remit_type) as remit_type'), 
                                DB::raw('MAX(n.pos_name) as pos_name'), 
                                DB::raw('MAX(n.borrowed) as borrowed')
                            )
                            ->whereDate('n.date_submit', $_GET['date'])
                            ->whereNull('c.tr_no')
                            ->whereNull('c.emp_id')
                            ->groupBy('n.tr_no', 'n.emp_id') // if using group by you need to max or min the ungroup column to avoid error
                            ->get()->toArray();
        // ==========merge cash and noncash===================
        $cashier_data = array_merge($cash_data, $noncash_data);
        // ========html table==============
        $html='<table class="table table-hover table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead style="background: #36b9cc;">
                    <tr>
                        <th style="text-align: center; color: white;">Cashier\'s Name</th>
                        <th style="text-align: center; color: white;">Location</th>
                        <th style="text-align: center; color: white;">Terminal No.</th>
                        <th style="text-align: center; color: white;">Action</th>
                    </tr>
                </thead>
                <tbody>';
        foreach($cashier_data as $data){
                $location = '';
                if(strlen($data->location) >= 6){
                    $dcode = substr($data->location, 0, 6);
                    $location_data = DB::connection('pis')->table('locate_department')
                                        ->select('dept_name')
                                        ->where('dcode', $dcode)
                                        ->get()->toArray();
                    $location = $location_data[0]->dept_name;
                }
                if(strlen($data->location) >= 8){
                    $scode = substr($data->location, 0, 8);
                    $location_data = DB::connection('pis')->table('locate_section')
                                        ->select('section_name')
                                        ->where('scode', $scode)
                                        ->get()->toArray();
                    $location .= ' / '.$location_data[0]->section_name;
                }
                if(strlen($data->location) == 10){
                    $sscode = substr($data->location, 0, 10);
                    $location_data = DB::connection('pis')->table('locate_sub_section')
                                        ->select('sub_section_name')
                                        ->where('sscode', $sscode)
                                        ->get()->toArray();
                    $location .= ' / '.$location_data[0]->sub_section_name;
                }
                $html.='<tr>
                            <td style="vertical-align: middle;">'.$data->emp_name.'</td>
                            <td style="text-align: center; vertical-align: middle;">'.$location.'</td>
                            <td style="text-align: center; vertical-align: middle;">'.$data->pos_name.'</td>
                            <td style="text-align: center; vertical-align: middle;">
                                <div class="dropdown">
                                    <button class="btn btn-info dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        Action
                                    </button>
                                    <div class="dropdown-menu animated--fade-in" aria-labelledby="dropdownMenuButton" style="background-color: lightcyan; cursor: pointer;">
                                        <a class="dropdown-item" data-toggle="modal" data-target="#cashDenModal" onclick="cash_den_js('."'".$data->tr_no."','".$data->emp_id."','".$data->pos_name."'".')">💰 Cash Denomination</a>
                                        <a class="dropdown-item" data-toggle="modal" data-target="#nonCashDenModal" onclick="noncash_den_js('."'".$data->tr_no."','".$data->emp_id."','".$data->pos_name."'".')">💳 NonCash Denomination</a>
                                        <a class="dropdown-item" data-toggle="modal" data-target="#terminalModal" onclick="view_terminal_js('."'".$data->tr_no."','".$data->emp_id."','".$data->location."','".$_GET['date']."','".$data->emp_name."','".$data->pos_name."'".')">🖥️ Termina No. / Registered Sales  /<br>Transaction Count</a>
                                        <a class="dropdown-item" data-toggle="modal" data-target="#salesDateModal" onclick="view_sales_date_js('."'".$data->tr_no."','".$data->emp_id."','".$data->location."','".$_GET['date']."'".')">📆 Sales Date</a>
                                        <a class="dropdown-item" data-toggle="modal" data-target="#locationModal" onclick="view_location_js('."'".$data->tr_no."','".$data->emp_id."','".$data->location."','".$_GET['date']."','".$data->borrowed."'".')"><i class="fas fa-map-marker" style="color: red;"></i> Location</a>
                                        <a class="dropdown-item" data-toggle="modal" data-target="#batch_remittanceModal" onclick="view_batch_remittance_js('."'".$data->tr_no."','".$data->emp_id."','".$data->location."','".$_GET['date']."','".$data->emp_name."','".$data->pos_name."'".')">💸 Batch Remittance</a>
                                    </div>
                                </div>
                            </td>
                        </tr>';
        }
        $html.=' </tbody>
                </table>
                <script>
                   $(function(){
                    $("#dataTable").DataTable({
                        searching: true,
                        paging: true
                    });
                   });
                </script>';
       
        $response = ['html' => $html];
        return response()->json($response);
    }

    public function get_partial_den_ctrl(Request $request){
        $partial_den = DB::connection('ebs')->table('cs_cashier_cashdenomination')
                            ->select('id','emp_name','onek','fiveh','twoh','oneh','fifty','twenty','date_submit','total_cash')
                            ->where('tr_no', $request->input('tr_no'))
                            ->where('emp_id', $request->input('emp_id'))
                            ->where('pos_name', $request->input('terminal_no'))
                            ->where('remit_type', 'PARTIAL')
                            ->where('delete_status', '<>', 'DELETED')
                            ->get()->toArray();
        $html='';
        $counter = 0;
        foreach($partial_den as $den){
            $counter += 1;
            $html.='<label>Date/Time Remitted: <span>'.$den->date_submit.'</span></label>
                    <form>
                        <table>
                            <tr>
                                <td style="text-align: right; color: black;">1,000:</td>
                                <td><input type="number" class="form-control" id="onek'.$counter.'" value="'.$den->onek.'" min="0" style="width: 100%; text-align: right;" onkeydown="validate_input_js(event)" onkeyup="calculate_ptotal_js('.$counter.')" onclick="arrow_updown_js(this,'.$counter.')"></input></td>
                                <td style="text-align: right; color: black;">500:</td>
                                <td><input type="number" class="form-control" id="fiveh'.$counter.'" value="'.$den->fiveh.'" min="0" style="width: 100%; text-align: right;" onkeydown="validate_input_js(event)" onkeyup="calculate_ptotal_js('.$counter.')" onclick="arrow_updown_js(this,'.$counter.')"></input></td>
                                <td style="text-align: right; color: black;">200:</td>
                                <td><input type="number" class="form-control" id="twoh'.$counter.'" value="'.$den->twoh.'" min="0" style="width: 100%; text-align: right;" onkeydown="validate_input_js(event)" onkeyup="calculate_ptotal_js('.$counter.')" onclick="arrow_updown_js(this,'.$counter.')"></input></td>
                            </tr>
                            <tr>
                                <td style="text-align: right; color: black;">100:</td>
                                <td><input type="number" class="form-control" id="oneh'.$counter.'" value="'.$den->oneh.'" min="0" style="width: 100%; text-align: right;" onkeydown="validate_input_js(event)" onkeyup="calculate_ptotal_js('.$counter.')" onclick="arrow_updown_js(this,'.$counter.')"></input></td>
                                <td style="text-align: right; color: black;">50:</td>
                                <td><input type="number" class="form-control" id="fifty'.$counter.'" value="'.$den->fifty.'" min="0" style="width: 100%; text-align: right;" onkeydown="validate_input_js(event)" onkeyup="calculate_ptotal_js('.$counter.')" onclick="arrow_updown_js(this,'.$counter.')"></input></td>
                                <td style="text-align: right; color: black;">20:</td>
                                <td><input type="number" class="form-control" id="twenty'.$counter.'" value="'.$den->twenty.'" min="0" style="width: 100%; text-align: right;" onkeydown="validate_input_js(event)" onkeyup="calculate_ptotal_js('.$counter.')" onclick="arrow_updown_js(this,'.$counter.')"></input></td>
                            </tr>
                            <tr>
                                <td colspan="6" style="text-align: right;">Total: <span id="ptotal'.$counter.'">'.number_format($den->total_cash).'</span> &nbsp;&nbsp;&nbsp;<button class="btn btn-success" type="button" onclick="update_partial_cd_js('.$counter.",".$den->id.",'".$request->input('tr_no')."','".$request->input('emp_id')."','".$den->total_cash."'".')">Update ✔️</button></td>
                            </tr>
                        </table>
                    </form></br>';
        }
        
        $name = '';
        if(count($partial_den) === 0){
            $html = '<h1>No Partial Data</h1>';
        }else{
            $name = $partial_den[0]->emp_name;
        }

        $response = ['html' => $html, 'emp_name' => $name];
        return response()->json($response);
    }

    public function update_partial_cd_ctrl(Request $request){
        $data = array('onek' => $request->input('onek'),
                      'fiveh' => $request->input('fiveh'),
                      'twoh' => $request->input('twoh'),
                      'oneh' => $request->input('oneh'),
                      'fifty' => $request->input('fifty'),
                      'twenty' => $request->input('twenty'),
                      'total_cash' => $request->input('total')
                    );
        $message = 'updated';
        (new CasheirTransactionModel)->update_partial_cd_model($request->input('id'),$request->input('tr_no'),$request->input('emp_id'),$data);
        // ===============validate final sales=======================
        if($request->input('variance') != 0) {
            $cs_den_data = (new CasheirTransactionModel)->get_cs_den_model($request->input('tr_no'),$request->input('emp_id'),$request->input('sales_date'));
            if($cs_den_data->isNotEmpty()){
                $row = $cs_den_data->first();
                $new_total_amount = $row->total_denomination + $row->discount + $request->input('variance');
                $new_total_amount2 = $row->total_denomination + $request->input('variance');
                $registered_sales = $row->registered_sales;
                $variance_amount = bcsub($new_total_amount, $registered_sales, 2);
                // ================================================================
                $variance_text = 'PF';
                if($variance_amount < 0)
                {
                    $variance_text = 'S';
                    $variance_amount = preg_replace('/-/', '', $variance_amount);
                }
                else if($variance_amount > 0)
                {
                    $variance_text = 'O';
                }
                // ================================================================
                $deduction_date = '1970-01-01';
                if($variance_text == 'S' && $variance_amount >= 10)
                {
                    $bcode = substr($request->input('dcode'), 0, 4);
                    $start_fc = 6;
                    $end_fc = 20;
                    $pay_day_fc = 0;
                    $pay_day_sc = 0;
                    // ==========================================================================================================================
                    if($bcode == '0201' || $bcode == '0301')
                    {
                        $pay_day_fc = 30;
                        $pay_day_sc = 15;
                    }
                    else if($bcode == '0203' || $bcode == '0223' || $bcode == '0202')
                    {
                        $pay_day_fc = 5;
                        $pay_day_sc = 20;
                    }
                    // ========================================================================================================================
                    $date_exploded = explode("-", $request->input('sales_date'));
                    $year = date($date_exploded[0]);
                    $year2 = date($date_exploded[0])+1;
                    $month = date($date_exploded[1]);
                    $month2 = date($date_exploded[1])+1;
                    $day = date($date_exploded[2]);
                    $last_day = date('t', strtotime($request->input('sales_date')));
                    if($month2 > 12)
                    {
                        $year = $year2;
                        $month2 = 1;
                    }
                    // ============================================================================
                    if($pay_day_fc == 30)
                    {
                        if($month == '02')
                        {
                            $pay_day_fc = $last_day;
                        }
                        // ==========================================================================
                        if($day >= $start_fc && $day <= $end_fc)
                        {
                            $deduction_date = $year.'-'.$month.'-'.$pay_day_fc;
                        }
                        else if($day < $start_fc)
                        {
                            $deduction_date = $year.'-'.$month.'-'.$pay_day_sc;
                        }
                        else if($day > $end_fc)
                        {
                            $deduction_date = $year.'-'.$month2.'-'.$pay_day_sc;
                        }
                    }
                    else
                    {
                        if($day >= $start_fc && $day <= $end_fc)
                        {
                            $deduction_date = $year.'-'.$month2.'-'.$pay_day_fc;
                        }
                        else if($day < $start_fc)
                        {
                            $deduction_date = $year.'-'.$month.'-'.$pay_day_sc;
                        }
                        else if($day > $end_fc)
                        {
                            $deduction_date = $year.'-'.$month2.'-'.$pay_day_sc;
                        }
                    }
                }
                // =======================================================================================
                $vms_cutoff_date = '';
                if($variance_amount >= 30)
                {
                    $company_code = substr($request->input('dcode'), 0, 2);
                    $bunit_code = substr($request->input('dcode'), 2, 2);
                    $cutoff_data = (new CasheirTransactionModel)->get_cutoff_model($company_code,$bunit_code);
                    $start_fc = 0;
                    $end_fc = '';
                    $start_sc = '';
                    $end_sc = '';
                    foreach($cutoff_data as $cutoff)
                    {
                        $start_fc = $cutoff->startFC;
                        $end_fc = $cutoff->endFC;
                        $start_sc = $cutoff->startSC;
                        $end_sc = $cutoff->endSC;
                    }
                    // =====================================================================================
                    $date_exploded = explode("-", $request->input('sales_date'));
                    $year = date($date_exploded[0]);
                    $year2 = date($date_exploded[0]) - 1;
                    $month = date($date_exploded[1]);
                    $month2 = date($date_exploded[1]) + 1;
                    $day = date($date_exploded[2]);
                    $last_day = date('t', strtotime($request->input('sales_date')));
                    if(!empty($cutoff_data))
                    {
                        $day = $day * 1;
                        if($end_fc == 15)
                        {
                            if($day <= 15)
                            {
                                $vms_cutoff_date = $month.'-'.'1'.'-'.$year.' / '.$month.'-'.$end_fc.'-'.$year;
                            }
                            else
                            {
                                $vms_cutoff_date = $month.'-'.$start_sc.'-'.$year.' / '.$month.'-'.$last_day.'-'.$year;
                            }
                        }
                        else
                        {
                            $start_fc = $start_fc * 1;
                            if($day >= $start_fc || $day <= $end_fc)
                            {
                                $vms_cutoff_date = $month.'-'.$start_fc.'-'.$year.' / '.$month.'-'.$end_fc.'-'.$year;
                            }
                            else
                            {
                                $vms_cutoff_date = $month.'-'.$start_sc.'-'.$year.' / '.$month.'-'.$end_sc.'-'.$year;
                            }
                        }
                    }
                    else
                    {
                        $day = $day * 1;
                        if($day >= 24 || $day <= 8)
                        {
                            if($month == '01')
                            {
                                $vms_cutoff_date = '12-'.'24'.'-'.$year2.' / '.$month.'-'.'8'.'-'.$year;
                            }
                            else
                            {
                                $month3 = $month2 * 1;
                                if($month3 < 10){
                                    $month3 = '0'.$month3;
                                }
                                $vms_cutoff_date = $month.'-'.'24'.'-'.$year.' / '.$month3.'-'.'8'.'-'.$year;
                            }
                        }
                        else
                        {
                            $vms_cutoff_date = $month.'-'.'9'.'-'.$year.' / '.$month.'-'.'23'.'-'.$year;
                        }
                    }
                }
                // ==============================update cebo_cs_data and cebo_cs_denomination===================================
                $cs_data = array(
                    'amount_shrt'     => $variance_amount,
                    'type'            => $variance_text,
                    'cut_off_date'    => $deduction_date,
                    'vms_cutoff_date' => $vms_cutoff_date
                );
                $csden = array(
                    'total_denomination' => $new_total_amount2
                );
                (new CasheirTransactionModel)->update_cs_data_model($request->input('tr_no'),$request->input('emp_id'),$request->input('sales_date'),$cs_data);
                (new CasheirTransactionModel)->update_cs_den_model($request->input('tr_no'),$request->input('emp_id'),$request->input('sales_date'),$csden);
            }
        }

        return response()->json($message);
    }

    public function get_final_den_ctrl(Request $request){
        $final_data = (new CasheirTransactionModel)->get_final_den_model($request->input('tr_no'),$request->input('emp_id'),$request->input('terminal_no'));
        $html = '<h1>No Final Data</h1>';
        $name = '';
        if(count($final_data) > 0){
            $row = $final_data->first();
            $name = $row->emp_name;
            $html = '
                <form>
                    <table>
                        <tr>
                            <td style="text-align: right; color: black;">1,000:</td>
                            <td><input type="number" class="form-control" id="onek" value="'.$row->onek.'" min="0" style="width: 100%; text-align: right;" onkeydown="validate_input_js(event)" onkeyup="calculate_ftotal_js()" onclick="final_arrow_updown_js(this)"></input></td>
                            <td style="text-align: right; color: black;">500:</td>
                            <td><input type="number" class="form-control" id="fiveh" value="'.$row->fiveh.'" min="0" style="width: 100%; text-align: right;" onkeydown="validate_input_js(event)" onkeyup="calculate_ftotal_js()" onclick="final_arrow_updown_js(this)"></input></td>
                            <td style="text-align: right; color: black;">200:</td>
                            <td><input type="number" class="form-control" id="twoh" value="'.$row->twoh.'" min="0" style="width: 100%; text-align: right;" onkeydown="validate_input_js(event)" onkeyup="calculate_ftotal_js()" onclick="final_arrow_updown_js(this)"></input></td>
                        </tr>
                        <tr>
                            <td style="text-align: right; color: black;">100:</td>
                            <td><input type="number" class="form-control" id="oneh" value="'.$row->oneh.'" min="0" style="width: 100%; text-align: right;" onkeydown="validate_input_js(event)" onkeyup="calculate_ftotal_js()" onclick="final_arrow_updown_js(this)"></input></td>
                            <td style="text-align: right; color: black;">50:</td>
                            <td><input type="number" class="form-control" id="fifty" value="'.$row->fifty.'" min="0" style="width: 100%; text-align: right;" onkeydown="validate_input_js(event)" onkeyup="calculate_ftotal_js()" onclick="final_arrow_updown_js(this)"></input></td>
                            <td style="text-align: right; color: black;">20:</td>
                            <td><input type="number" class="form-control" id="twenty" value="'.$row->twenty.'" min="0" style="width: 100%; text-align: right;" onkeydown="validate_input_js(event)" onkeyup="calculate_ftotal_js()" onclick="final_arrow_updown_js(this)"></input></td>
                        </tr>
                        <tr>
                            <td style="text-align: right; color: black;">10:</td>
                            <td><input type="number" class="form-control" id="ten" value="'.$row->ten.'" min="0" style="width: 100%; text-align: right;" onkeydown="validate_input_js(event)" onkeyup="calculate_ftotal_js()" onclick="final_arrow_updown_js(this)"></input></td>
                            <td style="text-align: right; color: black;">5:</td>
                            <td><input type="number" class="form-control" id="five" value="'.$row->five.'" min="0" style="width: 100%; text-align: right;" onkeydown="validate_input_js(event)" onkeyup="calculate_ftotal_js()" onclick="final_arrow_updown_js(this)"></input></td>
                            <td style="text-align: right; color: black;">1:</td>
                            <td><input type="number" class="form-control" id="one" value="'.$row->one.'" min="0" style="width: 100%; text-align: right;" onkeydown="validate_input_js(event)" onkeyup="calculate_ftotal_js()" onclick="final_arrow_updown_js(this)"></input></td>
                        </tr>
                        <tr>
                            <td style="text-align: right; color: black;">0.25:</td>
                            <td><input type="number" class="form-control" id="twentyfive_cents" value="'.$row->twentyfive_cents.'" min="0" style="width: 100%; text-align: right;" onkeydown="validate_input_js(event)" onkeyup="calculate_ftotal_js()" onclick="final_arrow_updown_js(this)"></input></td>
                            <td style="text-align: right; color: black;">0.10:</td>
                            <td><input type="number" class="form-control" id="ten_cents" value="'.$row->ten_cents.'" min="0" style="width: 100%; text-align: right;" onkeydown="validate_input_js(event)" onkeyup="calculate_ftotal_js()" onclick="final_arrow_updown_js(this)"></input></td>
                            <td style="text-align: right; color: black;">0.05:</td>
                            <td><input type="number" class="form-control" id="five_cents" value="'.$row->five_cents.'" min="0" style="width: 100%; text-align: right;" onkeydown="validate_input_js(event)" onkeyup="calculate_ftotal_js()" onclick="final_arrow_updown_js(this)"></input></td>
                        </tr>
                        <tr>
                            <td style="text-align: right; color: black;">0.01:</td>
                            <td><input type="number" class="form-control" id="one_cents" value="'.$row->one_cents.'" min="0" style="width: 100%; text-align: right;" onkeydown="validate_input_js(event)" onkeyup="calculate_ftotal_js()" onclick="final_arrow_updown_js(this)"></input></td>
                            <td colspan="4" style="text-align: center;"><span style="color: black;">Total:</span> <span id="ftotal">'.number_format($row->total_cash, 2).'</span><span hidden id="final_info">'.$row->total_cash.",".$row->id.'</span></td>
                        </tr>
                    </table>            
                </form>
            ';
        }

        $response = ['html' => $html, 'emp_name' => $name];
        return response()->json($response);
    }

    public function update_final_cd_ctrl(Request $request){
        $data = array(
            'onek' => $request->input('onek'),
            'fiveh' => $request->input('fiveh'),
            'twoh' => $request->input('twoh'),
            'oneh' => $request->input('oneh'),
            'fifty' => $request->input('fifty'),
            'twenty' => $request->input('twenty'),
            'ten' => $request->input('ten'),
            'five' => $request->input('five'),
            'one' => $request->input('one'),
            'twentyfive_cents' => $request->input('twentyfive_cents'),
            'ten_cents' => $request->input('ten_cents'),
            'five_cents' => $request->input('five_cents'),
            'one_cents' => $request->input('one_cents'),
            'total_cash' => $request->input('total')
        );
        $message = 'updated';
        (new CasheirTransactionModel)->update_final_cd_model($request->input('id'),$request->input('tr_no'),$request->input('emp_id'),$request->input('terminal_no'),$data);

        if($request->input('variance') != 0){
            $cs_den_data = (new CasheirTransactionModel)->get_cs_den_model($request->input('tr_no'),$request->input('emp_id'),$request->input('sales_date'));
            if($cs_den_data->isNotEmpty()){
                $row = $cs_den_data->first();
                $new_total_amount = $row->total_denomination + $row->discount + $request->input('variance');
                $new_total_amount2 = $row->total_denomination + $request->input('variance');
                $registered_sales = $row->registered_sales;
                $variance_amount = bcsub($new_total_amount, $registered_sales, 2);
                // ================================================================
                $variance_text = 'PF';
                if($variance_amount < 0)
                {
                    $variance_text = 'S';
                    $variance_amount = preg_replace('/-/', '', $variance_amount);
                }
                else if($variance_amount > 0)
                {
                    $variance_text = 'O';
                }
                // ================================================================
                $deduction_date = '1970-01-01';
                if($variance_text == 'S' && $variance_amount >= 10)
                {
                    $bcode = substr($request->input('dcode'), 0, 4);
                    $start_fc = 6;
                    $end_fc = 20;
                    $pay_day_fc = 0;
                    $pay_day_sc = 0;
                    // ==========================================================================================================================
                    if($bcode == '0201' || $bcode == '0301')
                    {
                        $pay_day_fc = 30;
                        $pay_day_sc = 15;
                    }
                    else if($bcode == '0203' || $bcode == '0223' || $bcode == '0202')
                    {
                        $pay_day_fc = 5;
                        $pay_day_sc = 20;
                    }
                    // ========================================================================================================================
                    $date_exploded = explode("-", $request->input('sales_date'));
                    $year = date($date_exploded[0]);
                    $year2 = date($date_exploded[0])+1;
                    $month = date($date_exploded[1]);
                    $month2 = date($date_exploded[1])+1;
                    $day = date($date_exploded[2]);
                    $last_day = date('t', strtotime($request->input('sales_date')));
                    if($month2 > 12)
                    {
                        $year = $year2;
                        $month2 = 1;
                    }
                    // ============================================================================
                    if($pay_day_fc == 30)
                    {
                        if($month == '02')
                        {
                            $pay_day_fc = $last_day;
                        }
                        // ==========================================================================
                        if($day >= $start_fc && $day <= $end_fc)
                        {
                            $deduction_date = $year.'-'.$month.'-'.$pay_day_fc;
                        }
                        else if($day < $start_fc)
                        {
                            $deduction_date = $year.'-'.$month.'-'.$pay_day_sc;
                        }
                        else if($day > $end_fc)
                        {
                            $deduction_date = $year.'-'.$month2.'-'.$pay_day_sc;
                        }
                    }
                    else
                    {
                        if($day >= $start_fc && $day <= $end_fc)
                        {
                            $deduction_date = $year.'-'.$month2.'-'.$pay_day_fc;
                        }
                        else if($day < $start_fc)
                        {
                            $deduction_date = $year.'-'.$month.'-'.$pay_day_sc;
                        }
                        else if($day > $end_fc)
                        {
                            $deduction_date = $year.'-'.$month2.'-'.$pay_day_sc;
                        }
                    }
                }
                // =======================================================================================
                $vms_cutoff_date = '';
                if($variance_amount >= 30)
                {
                    $company_code = substr($request->input('dcode'), 0, 2);
                    $bunit_code = substr($request->input('dcode'), 2, 2);
                    $cutoff_data = (new CasheirTransactionModel)->get_cutoff_model($company_code,$bunit_code);
                    $start_fc = 0;
                    $end_fc = '';
                    $start_sc = '';
                    $end_sc = '';
                    foreach($cutoff_data as $cutoff)
                    {
                        $start_fc = $cutoff->startFC;
                        $end_fc = $cutoff->endFC;
                        $start_sc = $cutoff->startSC;
                        $end_sc = $cutoff->endSC;
                    }
                    // =====================================================================================
                    $date_exploded = explode("-", $request->input('sales_date'));
                    $year = date($date_exploded[0]);
                    $year2 = date($date_exploded[0]) - 1;
                    $month = date($date_exploded[1]);
                    $month2 = date($date_exploded[1]) + 1;
                    $day = date($date_exploded[2]);
                    $last_day = date('t', strtotime($request->input('sales_date')));
                    if(!empty($cutoff_data))
                    {
                        $day = $day * 1;
                        if($end_fc == 15)
                        {
                            if($day <= 15)
                            {
                                $vms_cutoff_date = $month.'-'.'1'.'-'.$year.' / '.$month.'-'.$end_fc.'-'.$year;
                            }
                            else
                            {
                                $vms_cutoff_date = $month.'-'.$start_sc.'-'.$year.' / '.$month.'-'.$last_day.'-'.$year;
                            }
                        }
                        else
                        {
                            $start_fc = $start_fc * 1;
                            if($day >= $start_fc || $day <= $end_fc)
                            {
                                $vms_cutoff_date = $month.'-'.$start_fc.'-'.$year.' / '.$month.'-'.$end_fc.'-'.$year;
                            }
                            else
                            {
                                $vms_cutoff_date = $month.'-'.$start_sc.'-'.$year.' / '.$month.'-'.$end_sc.'-'.$year;
                            }
                        }
                    }
                    else
                    {
                        $day = $day * 1;
                        if($day >= 24 || $day <= 8)
                        {
                            if($month == '01')
                            {
                                $vms_cutoff_date = '12-'.'24'.'-'.$year2.' / '.$month.'-'.'8'.'-'.$year;
                            }
                            else
                            {
                                $month3 = $month2 * 1;
                                if($month3 < 10){
                                    $month3 = '0'.$month3;
                                }
                                $vms_cutoff_date = $month.'-'.'24'.'-'.$year.' / '.$month3.'-'.'8'.'-'.$year;
                            }
                        }
                        else
                        {
                            $vms_cutoff_date = $month.'-'.'9'.'-'.$year.' / '.$month.'-'.'23'.'-'.$year;
                        }
                    }
                }
                // ==============================update cebo_cs_data and cebo_cs_denomination===================================
                $cs_data = array(
                    'amount_shrt'     => $variance_amount,
                    'type'            => $variance_text,
                    'cut_off_date'    => $deduction_date,
                    'vms_cutoff_date' => $vms_cutoff_date
                );
                $csden = array(
                    'total_denomination' => $new_total_amount2
                );
                (new CasheirTransactionModel)->update_cs_data_model($request->input('tr_no'),$request->input('emp_id'),$request->input('sales_date'),$cs_data);
                (new CasheirTransactionModel)->update_cs_den_model($request->input('tr_no'),$request->input('emp_id'),$request->input('sales_date'),$csden);
            }
        }

        return response()->json($message);
    }

    public function get_noncash_den_ctrl(Request $request){
        $nocash_data = (new CasheirTransactionModel)->get_noncash_den_model($request->input('tr_no'),$request->input('emp_id'),$request->input('terminal_no'),$request->input('sales_date'));
        $html = '';
        $emp_name = '';
        $mop_array_html = array();
        if(count($nocash_data) > 0){
            $html .= '
                    <form>
                        <table class="table table-hover table-bordered" id="noncashTable" width="100%" cellspacing="0">
                            <thead style="background: #36b9cc;">
                                <tr>
                                    <th style="vertical-align: middle; width: 40%; text-align: center; color: white; font-size: 12px;">MODE OF PAYMENT</th>
                                    <th style="vertical-align: middle; width: 15%; text-align: center; color: white;">QUANTITY</th>
                                    <th style="vertical-align: middle; width: 30%; text-align: center; color: white;">AMOUNT</th>
                                    <th style="vertical-align: middle; width: 15%; text-align: center; color: white;">ACTION</th>
                                </tr>
                            </thead>
                            <tbody>';
                    $mop_array = array();
                    foreach($nocash_data as $mop){
                        array_push($mop_array, $mop->mop_name);
                    }
                    foreach($nocash_data as $noncash){    
                        $emp_name = $noncash->emp_name;
                        $dcode = substr($noncash->location, 0, 6);
                        $mop_name_data = (new CasheirTransactionModel)->get_mop_name_model($mop_array,$dcode);
                        $mop_name_html = '<option>'.$noncash->mop_name.'</option>';
                        foreach($mop_name_data as $mop)
                        {
                            $mop_name_html .= '<option>'.$mop->mop_name.'</option>';
                            if(!in_array('<option>'.$mop->mop_name.'</option>', $mop_array_html)){
                                array_push($mop_array_html, '<option>'.$mop->mop_name.'</option>');
                            }
                        }
                            
                        $html.='<tr>
                                    <td><select id="mop_name'.$noncash->id.'" class="form-control">'.$mop_name_html.'</select></td>
                                    <td> <input style="text-align: right;" class="form-control" type="number" id="noncash_qty'.$noncash->id.'" min="1" value="'.$noncash->noncash_qty.'" onclick="moveCursorToEndNumber(this)"></input></td>
                                    <td><input style="text-align: right;" type="text" class="form-control" id="noncash_amount'.$noncash->id.'" oninput="formatCurrency(this)" onclick="moveCursorToEndText(this)" value="'.number_format($noncash->noncash_amount, 2).'" /></td>
                                    <td>
                                        <a title="UPDATE" style="cursor: pointer;" onclick="update_noncash_js('.$noncash->id.",'".$noncash->noncash_amount."'".')">✔️</a>
                                        &nbsp;|&nbsp;
                                        <a title="TRANSFER" data-toggle="modal" data-target="#transferNonCashDenModal" style="cursor: pointer;" onclick="transfer_noncash_js('.$noncash->id.",'".implode("|", $mop_array)."','".$noncash->mop_name."',".$noncash->noncash_qty.",".$noncash->noncash_amount.')"><i class="fas fa-exchange-alt" style="color: red;"></i></a>
                                    </td>
                                </tr>';
                    }
                    $html.='</tbody>
                        </table>
                    </form>
                    <script>
                        $("input[type=number]").keydown(function(event) {
                            if (event.key === "e" || event.key === "E" || event.key === "+" || event.key === "-" || event.key === ".") {
                            event.preventDefault();
                            }
                        });
                    </script>
                    ';
        }else{
            $html = '<h1>No Noncash Data</h1>';
        }
        
        $response = ['html' => $html, 'emp_name' => $emp_name, 'mop_array_html' => $mop_array_html];
        return response()->json($response);
    }

    public function update_noncash_ctrl(Request $request){
        if($request->input('variance') != 0){
            $cs_den_data = (new CasheirTransactionModel)->get_cs_den_model($request->input('tr_no'),$request->input('emp_id'),$request->input('sales_date'));
            if($cs_den_data->isNotEmpty()){
                $row = $cs_den_data->first();
                $new_total_amount = $row->total_denomination + $row->discount + $request->input('variance');
                $new_total_amount2 = $row->total_denomination + $request->input('variance');
                $registered_sales = $row->registered_sales;
                $variance_amount = bcsub($new_total_amount, $registered_sales, 2);
                // ================================================================
                $variance_text = 'PF';
                if($variance_amount < 0)
                {
                    $variance_text = 'S';
                    $variance_amount = preg_replace('/-/', '', $variance_amount);
                }
                else if($variance_amount > 0)
                {
                    $variance_text = 'O';
                }
                // ================================================================
                $deduction_date = '1970-01-01';
                if($variance_text == 'S' && $variance_amount >= 10)
                {
                    $bcode = substr($request->input('dcode'), 0, 4);
                    $start_fc = 6;
                    $end_fc = 20;
                    $pay_day_fc = 0;
                    $pay_day_sc = 0;
                    // ==========================================================================================================================
                    if($bcode == '0201' || $bcode == '0301')
                    {
                        $pay_day_fc = 30;
                        $pay_day_sc = 15;
                    }
                    else if($bcode == '0203' || $bcode == '0223' || $bcode == '0202')
                    {
                        $pay_day_fc = 5;
                        $pay_day_sc = 20;
                    }
                    // ========================================================================================================================
                    $date_exploded = explode("-", $request->input('sales_date'));
                    $year = date($date_exploded[0]);
                    $year2 = date($date_exploded[0])+1;
                    $month = date($date_exploded[1]);
                    $month2 = date($date_exploded[1])+1;
                    $day = date($date_exploded[2]);
                    $last_day = date('t', strtotime($request->input('sales_date')));
                    if($month2 > 12)
                    {
                        $year = $year2;
                        $month2 = 1;
                    }
                    // ============================================================================
                    if($pay_day_fc == 30)
                    {
                        if($month == '02')
                        {
                            $pay_day_fc = $last_day;
                        }
                        // ==========================================================================
                        if($day >= $start_fc && $day <= $end_fc)
                        {
                            $deduction_date = $year.'-'.$month.'-'.$pay_day_fc;
                        }
                        else if($day < $start_fc)
                        {
                            $deduction_date = $year.'-'.$month.'-'.$pay_day_sc;
                        }
                        else if($day > $end_fc)
                        {
                            $deduction_date = $year.'-'.$month2.'-'.$pay_day_sc;
                        }
                    }
                    else
                    {
                        if($day >= $start_fc && $day <= $end_fc)
                        {
                            $deduction_date = $year.'-'.$month2.'-'.$pay_day_fc;
                        }
                        else if($day < $start_fc)
                        {
                            $deduction_date = $year.'-'.$month.'-'.$pay_day_sc;
                        }
                        else if($day > $end_fc)
                        {
                            $deduction_date = $year.'-'.$month2.'-'.$pay_day_sc;
                        }
                    }
                }
                // =======================================================================================
                $vms_cutoff_date = '';
                if($variance_amount >= 30)
                {
                    $company_code = substr($request->input('dcode'), 0, 2);
                    $bunit_code = substr($request->input('dcode'), 2, 2);
                    $cutoff_data = (new CasheirTransactionModel)->get_cutoff_model($company_code,$bunit_code);
                    $start_fc = 0;
                    $end_fc = '';
                    $start_sc = '';
                    $end_sc = '';
                    foreach($cutoff_data as $cutoff)
                    {
                        $start_fc = $cutoff->startFC;
                        $end_fc = $cutoff->endFC;
                        $start_sc = $cutoff->startSC;
                        $end_sc = $cutoff->endSC;
                    }
                    // =====================================================================================
                    $date_exploded = explode("-", $request->input('sales_date'));
                    $year = date($date_exploded[0]);
                    $year2 = date($date_exploded[0]) - 1;
                    $month = date($date_exploded[1]);
                    $month2 = date($date_exploded[1]) + 1;
                    $day = date($date_exploded[2]);
                    $last_day = date('t', strtotime($request->input('sales_date')));
                    if(!empty($cutoff_data))
                    {
                        $day = $day * 1;
                        if($end_fc == 15)
                        {
                            if($day <= 15)
                            {
                                $vms_cutoff_date = $month.'-'.'1'.'-'.$year.' / '.$month.'-'.$end_fc.'-'.$year;
                            }
                            else
                            {
                                $vms_cutoff_date = $month.'-'.$start_sc.'-'.$year.' / '.$month.'-'.$last_day.'-'.$year;
                            }
                        }
                        else
                        {
                            $start_fc = $start_fc * 1;
                            if($day >= $start_fc || $day <= $end_fc)
                            {
                                $vms_cutoff_date = $month.'-'.$start_fc.'-'.$year.' / '.$month.'-'.$end_fc.'-'.$year;
                            }
                            else
                            {
                                $vms_cutoff_date = $month.'-'.$start_sc.'-'.$year.' / '.$month.'-'.$end_sc.'-'.$year;
                            }
                        }
                    }
                    else
                    {
                        $day = $day * 1;
                        if($day >= 24 || $day <= 8)
                        {
                            if($month == '01')
                            {
                                $vms_cutoff_date = '12-'.'24'.'-'.$year2.' / '.$month.'-'.'8'.'-'.$year;
                            }
                            else
                            {
                                $month3 = $month2 * 1;
                                if($month3 < 10){
                                    $month3 = '0'.$month3;
                                }
                                $vms_cutoff_date = $month.'-'.'24'.'-'.$year.' / '.$month3.'-'.'8'.'-'.$year;
                            }
                        }
                        else
                        {
                            $vms_cutoff_date = $month.'-'.'9'.'-'.$year.' / '.$month.'-'.'23'.'-'.$year;
                        }
                    }
                }
                // ==============================update cebo_cs_data and cebo_cs_denomination===================================
                $cs_data = array(
                    'amount_shrt'     => $variance_amount,
                    'type'            => $variance_text,
                    'cut_off_date'    => $deduction_date,
                    'vms_cutoff_date' => $vms_cutoff_date
                );
                $csden = array(
                    'total_denomination' => $new_total_amount2
                );
                (new CasheirTransactionModel)->update_cs_data_model($request->input('tr_no'),$request->input('emp_id'),$request->input('sales_date'),$cs_data);
                (new CasheirTransactionModel)->update_cs_den_model($request->input('tr_no'),$request->input('emp_id'),$request->input('sales_date'),$csden);
            }
        }
        
        $data = array(
            'mop_name' => $request->input('mop_name'),
            'noncash_qty' => $request->input('noncash_qty'),
            'noncash_amount' => $request->input('noncash_amount')
        );
        $message = 'updated';
        (new CasheirTransactionModel)->update_noncash_model($request->input('id'),$request->input('emp_id'),$data);

        $response = ['message' => $message];
        return response()->json($response);
    }

    public function transfer_mop_ctrl(Request $request){
        $mop_data = (new CasheirTransactionModel)->get_mop_data_model($request->input('id'));
        $message = 'updating';
        if(count($mop_data) > 0){
            $row = $mop_data->first();

            $update_data = array(
              'noncash_qty' => $request->input('old_qty'),
              'noncash_amount' => $request->input('old_amount')
            );
            $message = 'updated';
            (new CasheirTransactionModel)->update_mop_data_model($request->input('id'),$update_data);

            // ====================insert new mop===============================
            $insert_data = array(
                'tr_no' => $row->tr_no,
                'emp_id' => $row->emp_id,
                'sal_no' => $row->sal_no,
                'emp_name' => $row->emp_name,
                'emp_type' => $row->emp_type,
                'company_code' => $row->company_code,
                'bunit_code' => $row->bunit_code,
                'dep_code' => $row->dep_code,
                'section_code' => $row->section_code,
                'sub_section_code' => $row->sub_section_code,
                'borrowed' => $row->borrowed,
                'pos_name' => $row->pos_name,
                'counter_no' => $row->counter_no,
                'mop_name' => $request->input('new_mop'),
                'noncash_qty' => $request->input('new_qty'),
                'noncash_amount' => $request->input('new_amount'),
                'remit_type' => $row->remit_type,
                'status' => $row->status,
                'date_submit' => $row->date_submit
            );
           
            $message = 'updated';
            (new CasheirTransactionModel)->insert_mop_data_model($insert_data);
        }

        $response = ['message' => $message];
        return response()->json($response);
    }

    public function get_terminal_ctrl(Request $request){
        $dcode = substr($request->input('location'), 0, 6);
        $terminal_data = (new CasheirTransactionModel)->get_terminal_data_model($dcode,$request->input('terminal_no'));
        $terminal_html = '<option value="DEFAULT">'.$request->input('terminal_no').'</option>';
        foreach($terminal_data as $terminal)
        {
            $terminal_html .= '<option value="'.$terminal->counter_no.'">'.$terminal->pos_name.'</option>';
        }
        // ======================================================
        $sales_data = (new CasheirTransactionModel)->get_sales_data_model($request->input('tr_no'),$request->input('emp_id'),$request->input('location'),$request->input('sales_date'));
        $old_terminal_data = '';
        $total_sales = 0;
        $registered_sales = 0;
        $discount = 0;
        $tr_count = 0;
        if(count($sales_data) > 0){
            $row = $sales_data->first();
            $old_terminal_data = $row->total_denomination.'|'.$row->registered_sales.'|'.$row->discount.'|'.$row->tr_count;
            $registered_sales = number_format($row->registered_sales, 2);
            $discount = number_format($row->discount, 2);
            $tr_count = $row->tr_count;
        }

        $response = ['terminal_html' => $terminal_html, 'old_terminal_data' => $old_terminal_data, 'registered_sales' => $registered_sales, 'discount' => $discount, 'tr_count' => $tr_count];
        return response()->json($response);
    }

    public function update_terminal_ctrl(Request $request){
        if(trim($request->input('old_rs')) != trim($request->input('new_rs')) || trim($request->input('old_discount')) != trim($request->input('new_discount')) || trim($request->input('old_tc')) != trim($request->input('new_tc')))
        {
            $new_total_amount = $request->input('total_sales') + $request->input('new_discount');
            $registered_sales = $request->input('new_rs');
            $variance_amount = bcsub($new_total_amount, $registered_sales, 2);
            // ===========================================
            $variance_text = 'PF';
            if($variance_amount < 0)
            {
                $variance_text = 'S';
                $variance_amount = preg_replace('/-/', '', $variance_amount);
            }
            else if($variance_amount > 0)
            {
                $variance_text = 'O';
            }
            // ================================================================
            $deduction_date = '1970-01-01';
            if($variance_text == 'S' && $variance_amount >= 10)
            {
                $bcode = substr($request->input('location'), 0, 4);
                $start_fc = 6;
                $end_fc = 20;
                $pay_day_fc = 0;
                $pay_day_sc = 0;
                // ==========================================================================================================================
                if($bcode == '0201' || $bcode == '0301')
                {
                    $pay_day_fc = 30;
                    $pay_day_sc = 15;
                }
                else if($bcode == '0203' || $bcode == '0223' || $bcode == '0202')
                {
                    $pay_day_fc = 5;
                    $pay_day_sc = 20;
                }
                // ========================================================================================================================
                $date_exploded = explode("-", $request->input('sales_date'));
                $year = date($date_exploded[0]);
                $year2 = date($date_exploded[0])+1;
                $month = date($date_exploded[1]);
                $month2 = date($date_exploded[1])+1;
                $day = date($date_exploded[2]);
                $last_day = date('t', strtotime($request->input('sales_date')));
                if($month2 > 12)
                {
                    $year = $year2;
                    $month2 = 1;
                }
                // ============================================================================
                if($pay_day_fc == 30)
                {
                    if($month == '02')
                    {
                        $pay_day_fc = $last_day;
                    }
                    // ==========================================================================
                    if($day >= $start_fc && $day <= $end_fc)
                    {
                        $deduction_date = $year.'-'.$month.'-'.$pay_day_fc;
                    }
                    else if($day < $start_fc)
                    {
                        $deduction_date = $year.'-'.$month.'-'.$pay_day_sc;
                    }
                    else if($day > $end_fc)
                    {
                        $deduction_date = $year.'-'.$month2.'-'.$pay_day_sc;
                    }
                }
                else
                {
                    if($day >= $start_fc && $day <= $end_fc)
                    {
                        $deduction_date = $year.'-'.$month2.'-'.$pay_day_fc;
                    }
                    else if($day < $start_fc)
                    {
                        $deduction_date = $year.'-'.$month.'-'.$pay_day_sc;
                    }
                    else if($day > $end_fc)
                    {
                        $deduction_date = $year.'-'.$month2.'-'.$pay_day_sc;
                    }
                }
            }
            // =======================================================================================
            $vms_cutoff_date = '';
            if($variance_amount >= 30)
            {
                $company_code = substr($request->input('location'), 0, 2);
                $bunit_code = substr($request->input('location'), 2, 2);
                $cutoff_data = (new CasheirTransactionModel)->get_cutoff_model($company_code,$bunit_code);
                $start_fc = 0;
                $end_fc = '';
                $start_sc = '';
                $end_sc = '';
                foreach($cutoff_data as $cutoff)
                {
                    $start_fc = $cutoff->startFC;
                    $end_fc = $cutoff->endFC;
                    $start_sc = $cutoff->startSC;
                    $end_sc = $cutoff->endSC;
                }
                // =====================================================================================
                $date_exploded = explode("-", $request->input('sales_date'));
                $year = date($date_exploded[0]);
                $year2 = date($date_exploded[0]) - 1;
                $month = date($date_exploded[1]);
                $month2 = date($date_exploded[1]) + 1;
                $day = date($date_exploded[2]);
                $last_day = date('t', strtotime($request->input('sales_date')));
                if(!empty($cutoff_data))
                {
                    $day = $day * 1;
                    if($end_fc == 15)
                    {
                        if($day <= 15)
                        {
                            $vms_cutoff_date = $month.'-'.'1'.'-'.$year.' / '.$month.'-'.$end_fc.'-'.$year;
                        }
                        else
                        {
                            $vms_cutoff_date = $month.'-'.$start_sc.'-'.$year.' / '.$month.'-'.$last_day.'-'.$year;
                        }
                    }
                    else
                    {
                        $start_fc = $start_fc * 1;
                        if($day >= $start_fc || $day <= $end_fc)
                        {
                            $vms_cutoff_date = $month.'-'.$start_fc.'-'.$year.' / '.$month.'-'.$end_fc.'-'.$year;
                        }
                        else
                        {
                            $vms_cutoff_date = $month.'-'.$start_sc.'-'.$year.' / '.$month.'-'.$end_sc.'-'.$year;
                        }
                    }
                }
                else
                {
                    $day = $day * 1;
                    if($day >= 24 || $day <= 8)
                    {
                        if($month == '01')
                        {
                            $vms_cutoff_date = '12-'.'24'.'-'.$year2.' / '.$month.'-'.'8'.'-'.$year;
                        }
                        else
                        {
                            $month3 = $month2 * 1;
                            if($month3 < 10){
                                $month3 = '0'.$month3;
                            }
                            $vms_cutoff_date = $month.'-'.'24'.'-'.$year.' / '.$month3.'-'.'8'.'-'.$year;
                        }
                    }
                    else
                    {
                        $vms_cutoff_date = $month.'-'.'9'.'-'.$year.' / '.$month.'-'.'23'.'-'.$year;
                    }
                }
            }
            $cs_den_data = array(
                'registered_sales' => $request->input('new_rs'),
                'discount'         => $request->input('new_discount'),
                'tr_count'         => $request->input('new_tc')
            );
            (new CasheirTransactionModel)->update_cs_den_model($request->input('tr_no'),$request->input('emp_id'),$request->input('sales_date'),$cs_den_data);
            // ==========================================
            $cs_data = array(
                'amount_shrt'     => $variance_amount,
                'type'            => $variance_text,
                'cut_off_date'    => $deduction_date,
                'vms_cutoff_date' => $vms_cutoff_date
            );
            (new CasheirTransactionModel)->update_cs_data_model($request->input('tr_no'),$request->input('emp_id'),$request->input('sales_date'),$cs_data);
        }
        
        $message = 'updated';
        if($request->input('new_counter') != 'DEFAULT')
        {
            $data = array(
                'pos_name' => $request->input('new_terminal'),
                'counter_no' => $request->input('new_counter')
            );
            (new CasheirTransactionModel)->update_cash_terminal_model($request->input('tr_no'),$request->input('emp_id'),$request->input('sales_date'),$data);
            (new CasheirTransactionModel)->update_noncash_terminal_model($request->input('tr_no'),$request->input('emp_id'),$request->input('sales_date'),$data);
        }

        $response = ['message' => $message];
        return response()->json($response);
    }

    public function update_sales_date_ctrl(Request $request){
        $message = 'updated';
        (new CasheirTransactionModel)->update_cash_date_model($request->input('tr_no'),$request->input('emp_id'),$request->input('old_date'),$request->input('new_date'));
        (new CasheirTransactionModel)->update_noncash_date_model($request->input('tr_no'),$request->input('emp_id'),$request->input('old_date'),$request->input('new_date'));
        (new CasheirTransactionModel)->update_csdata_date_model($request->input('tr_no'),$request->input('emp_id'),$request->input('old_date'),$request->input('new_date'));
        (new CasheirTransactionModel)->update_csden_date_model($request->input('tr_no'),$request->input('emp_id'),$request->input('old_date'),$request->input('new_date'));
        (new CasheirTransactionModel)->update_remitted_date_model($request->input('tr_no'),$request->input('emp_id'),$request->input('old_date'),$request->input('new_date'));

        $response = ['message' => $message];
        return response()->json($response);
    }

    public function get_location_ctrl(Request $request){
        $bcode = substr($request->input('location'), 0, 4);
        $dcode = substr($request->input('location'), 0, 6);
        $scode = substr($request->input('location'), 0, 8);
        $sscode = substr($request->input('location'), 0, 10);
        $location_name = '';
        // ==================department code======================
        $dept_name = '';
        if(strlen($dcode) == 6){
            $dept_data = (new CasheirTransactionModel)->get_deptname_model($bcode);
            foreach($dept_data as $dept){
                $selected = '';
                if($dept->dcode == $dcode){
                    $selected = 'selected';
                    $location_name .= $dept->dept_name;
                }
                $dept_name .= '<option '.$selected.' value="'.$dept->dcode.'">'.$dept->dept_name.'</option>';
            }
        }
        // ==================section code======================
        $section_name = '';
        if(strlen($scode) == 8){
            $section_data = (new CasheirTransactionModel)->get_section_model($dcode);
            foreach($section_data as $section){
                $selected = '';
                if($section->scode == $scode){
                    $selected = 'selected';
                    $location_name .= ' - '.$section->section_name;
                }
                $section_name .= '<option '.$selected.' value="'.$section->scode.'">'.$section->section_name.'</option>';
            }
        }
        // // ==================sub section code======================
        $sub_section_name = '';
        if(strlen($sscode) == 10){
            $sub_section_data = (new CasheirTransactionModel)->get_sub_section_model($scode);
            foreach($sub_section_data as $sub_section){
                $selected = '';
                if($sub_section->sscode == $sscode){
                    $selected = 'selected';
                    $location_name .= ' - '.$sub_section->sub_section_name;
                }
                $sub_section_name .= '<option '.$selected.' value="'.$sub_section->sscode.'">'.$sub_section->sub_section_name.'</option>';
            }
        }

        $response = ['location_name' => $location_name, 'dept_name' => $dept_name, 'section_name' => $section_name, 'sub_section_name' => $sub_section_name];
        return response()->json($response);
    }

    public function get_section_ctrl(Request $request){
        $section_data = (new CasheirTransactionModel)->get_section_model($request->input('dcode'));
        $section_html = '';
        foreach($section_data as $section){
            $section_html .= '<option value="'.$section->scode.'">'.$section->section_name.'</option>';
        }

        $response = ['section_html' => $section_html];
        return response()->json($response);
    }

    public function get_sub_section_ctrl(Request $request){
        $sub_section_data = (new CasheirTransactionModel)->get_sub_section_model($request->input('scode'));
        $sub_section_html = '';
        foreach($sub_section_data as $sub_section){
            $sub_section_html .= '<option value="'.$sub_section->sscode.'">'.$sub_section->sub_section_name.'</option>';
        }

        $response = ['sub_section_html' => $sub_section_html];
        return response()->json($response);
    }

    public function update_location_ctrl(Request $request){
        $message = 'updated';
        $current_location = '';
        if($request->input('borrowed') == 'YES'){
            $cashier_info = (new CasheirTransactionModel)->get_cashier_info_model($request->input('emp_id'));
            if(count($cashier_info) > 0){
                $row = $cashier_info->first();
                $current_location = $row->location;
            }
            // ======================================
            if($current_location == $request->input('location')){
                $message = 'invalid';
            }
        }
        // ==========================================
        if($message == 'updated'){
            $remitted_dcode = substr($request->input('location'), 0, 6);
            $dcode = substr($request->input('location'), 4, 2);
            $scode = substr($request->input('location'), 6, 2);
            if($scode === false){
                $scode = '';
            }
            $sscode = substr($request->input('location'), 8, 2);
            if($sscode === false){
                $sscode = '';
            }
            // ===========================================================================
            $cashier_data = array(
                'dep_code' => $dcode,
                'section_code' => $scode,
                'sub_section_code' => $sscode,
                'borrowed' => $request->input('borrowed')
            );
            $csdata = array(
                'dept_code' => $dcode,
                'section_code' => $scode,
                'sub_section_code' => $sscode
            );
            $csden = array(
                'dept_code' => $dcode,
                'section_code' => $scode,
                'sub_sec_code' => $sscode
            );
            $remitted_data = array(
                'dcode' => $remitted_dcode,
                'sscode' => $request->input('location')
            );
            (new CasheirTransactionModel)->update_location_model($request->input('tr_no'),$request->input('emp_id'),$request->input('sales_date'),$cashier_data,$csdata,$csden,$remitted_data);
        }
        
        $response = ['message' => $message];
        return response()->json($response);
    }

    public function get_batch_remittance_ctrl(Request $request){
        $batch_data = (new CasheirTransactionModel)->get_batch_remittance_model($request->input('tr_no'),$request->input('emp_id'),$request->input('sales_date'));
        $html = '<table class="table table-hover table-bordered" id="batch_remittanceTable" width="100%" cellspacing="0">
                    <thead style="background: #36b9cc;">
                        <tr>
                            <th style="vertical-align: middle; width: 20%; text-align: center; color: white;">Terminal No.</th>
                            <th style="vertical-align: middle; width: 20%; text-align: center; color: white;">Cash Remitted</th>
                            <th style="vertical-align: middle; width: 20%; text-align: center; color: white;">Batch No.</th>
                            <th style="vertical-align: middle; width: 30%; text-align: center; color: white;">Date Remitted</th>
                            <th style="vertical-align: middle; width: 10%; text-align: center; color: white;">Action</th>
                        </tr>
                    </thead>
                    <tbody>';
        foreach($batch_data as $batch){
              $cash_data = (new CasheirTransactionModel)->get_batch_cash_data_model($batch->cash_id);
              $cash_remitted = '';
              if(count($cash_data) > 0){
                  $row = $cash_data->first();
                  $cash_remitted = $row->total_cash;
              }
              $html .= '<tr>
                            <td style="text-align: center; vertical-align: middle;"><span id="batch_terminal">'.$request->input('terminal_no').'</span></td>
                            <td style="text-align: right; vertical-align: middle;"><span id="batch_cash">'.number_format($cash_remitted, 2).'</span></td>
                            <td><input type="number" class="form-control" id="batch_no'.$batch->id.'" min="1" style="text-align: right;" onkeydown="validate_input_js(event)" value="'.$batch->batch_remit.'"></td>
                            <td><input type="date" class="form-control" id="batch_date'.$batch->id.'" max="'.date('Y-m-d').'" value="'.$request->input('sales_date').'"></td>
                            <td style="text-align: center; vertical-align: middle;"><a style="cursor: pointer; font-size: x-large;" onclick="update_batch_remittance_js('.$batch->id.",".$batch->batch_remit.')">✔️</a></td>
                        </tr>';
        }
          $html .= '</tbody>
                </table>';

        $response = ['html' => $html];
        return response()->json($response);
    }

    public function update_batch_remittance_ctrl(Request $request){
        $dcode = substr($request->input('dcode'), 0, 6);
        $batch_counter = (new CasheirTransactionModel)->get_batch_counter($dcode,$request->input('new_date'));
        $old_batch_counter = 0;
        if(count($batch_counter) > 0){
            $row = $batch_counter->first();
            $old_batch_counter = $row->batch_remit;
        }
        // ====================================================
        if($request->input('new_batch') >= $old_batch_counter){
            $new_batch_counter = $request->input('new_batch') + 1;
            $counter_data = array('batch_remit' => $new_batch_counter);
            (new CasheirTransactionModel)->update_batch_counter($dcode,$request->input('new_date'),$counter_data);
        }
        // ====================================================
        $batch_data = array('batch_remit' => $request->input('new_batch'), 'date_remitted' => $request->input('new_date'));
        $message = 'updated';
        (new CasheirTransactionModel)->update_batch_remittance_model($request->input('id'),$batch_data);

        $response = ['message' => $message];
        return response()->json($response);
    }

    




}
