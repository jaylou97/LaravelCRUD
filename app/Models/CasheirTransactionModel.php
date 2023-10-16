<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CasheirTransactionModel extends Model
{
    use HasFactory;

    public function update_partial_cd_model($id,$tr_no,$emp_id,$data){
         DB::connection('ebs')->table('cs_cashier_cashdenomination')
            ->where('id', $id)
            ->where('tr_no', $tr_no)
            ->where('emp_id', $emp_id)
            ->update($data);
    }

    public function get_cs_den_model($tr_no,$emp_id,$sales_date){
        $cash_den = DB::connection('ebs')->table('cebo_cs_denomination')
                        ->select('total_denomination','registered_sales','discount','tr_count')
                        ->where('tr_no',$tr_no)
                        ->where('emp_id',$emp_id)
                        ->where('date_shrt',$sales_date)
                        ->where('delete_status', '<>', 'deleted')
                        ->get();
        return $cash_den;
    }

    public function update_cs_data_model($tr_no,$emp_id,$sales_date,$data){
        DB::connection('ebs')->table('cebo_cs_data')
            ->where('tr_no',$tr_no)
            ->where('emp_id',$emp_id)
            ->where('date_shrt',$sales_date)
            ->update($data);
    }

    public function get_cutoff_model($cc,$bc){
       $data = DB::connection('pis')->table('cut_off')
            ->where('cc',$cc)
            ->where('bc',$bc)
            ->get()->toArray();
        return $data;
    }

    public function update_cs_den_model($tr_no,$emp_id,$sales_date,$data){
        DB::connection('ebs')->table('cebo_cs_denomination')
            ->where('tr_no',$tr_no)
            ->where('emp_id',$emp_id)
            ->where('date_shrt',$sales_date)
            ->update($data);
    }

    public function get_final_den_model($tr_no,$emp_id,$terminal_no){
        $data = DB::connection('ebs')->table('cs_cashier_cashdenomination')
                    ->where('tr_no',$tr_no)
                    ->where('emp_id',$emp_id)
                    ->where('pos_name',$terminal_no)
                    ->where('remit_type','FINAL')
                    ->where('delete_status','<>','DELETED')
                    ->get();
        return $data;
    }

    public function update_final_cd_model($id,$tr_no,$emp_id,$terminal_no,$data){
        DB::connection('ebs')->table('cs_cashier_cashdenomination')
            ->where('id',$id)
            ->where('tr_no',$tr_no)
            ->where('emp_id',$emp_id)
            ->where('pos_name',$terminal_no)
            ->update($data);
    }

    public function get_noncash_den_model($tr_no,$emp_id,$terminal_no,$sales_date){
        $data = DB::connection('ebs')->table('cs_cashier_noncashdenomination')
                    ->select('id','emp_name','mop_name','noncash_qty','noncash_amount',DB::raw('concat(company_code,bunit_code,dep_code,section_code,sub_section_code) as location'))
                    ->where('tr_no',$tr_no)
                    ->where('emp_id',$emp_id)
                    ->where('pos_name',$terminal_no)
                    ->whereDate('date_submit',$sales_date)
                    ->where('delete_status','<>','DELETED')
                    ->orderBy('mop_name','asc')
                    ->get()->toArray();
        return $data;
    }

    public function get_mop_name_model($mop_name,$dcode){
        $data = DB::connection('ebs')->table('cs_bu_mode_of_payment')
                    ->select('mop_name', DB::raw('MIN(id) as min_id'))
                    ->whereNotIn('mop_name',$mop_name)
                    ->where('dcode',$dcode)
                    ->whereNotIn('mop_code',array(1,9,50))
                    ->groupBy('mop_name')
                    ->orderBy('mop_name','asc')
                    ->get()->toArray();
        return $data;
    }

    public function update_noncash_model($id,$emp_id,$data){
         DB::connection('ebs')->table('cs_cashier_noncashdenomination')
            ->where('id', $id)
            ->where('emp_id', $emp_id)
            ->update($data);
    }

    public function get_mop_data_model($id){
        $data = DB::connection('ebs')->table('cs_cashier_noncashdenomination')
                    ->where('id', $id)
                    ->get();
        return $data;
    }

    public function update_mop_data_model($id,$data){
        DB::connection('ebs')->table('cs_cashier_noncashdenomination')
            ->where('id', $id)
            ->update($data);    
    }

    public function insert_mop_data_model($data){
        DB::connection('ebs')->table('cs_cashier_noncashdenomination')
            ->insert($data);    
    }

    public function get_terminal_data_model($dcode,$terminal_no){
        $data = DB::connection('ebs')->table('cs_store_pos_counter_no')
                    ->select('pos_name', 'counter_no')
                    ->where('dcode', $dcode)
                    ->where('pos_name', '<>', $terminal_no)
                    ->orderBy('pos_name', 'asc')
                    ->get()->toArray();
        return $data;
    }

    public function get_sales_data_model($tr_no,$emp_id,$location,$sales_date){
        $data = DB::connection('ebs')->table('cebo_cs_denomination')
                    ->select('total_denomination','registered_sales','discount','tr_count')
                    ->where('tr_no', $tr_no)
                    ->where('emp_id', $emp_id)
                    ->where('date_shrt', $sales_date)
                    ->where('delete_status','<>', 'deleted')
                    ->get();
        return $data;
    }

    public function update_cash_terminal_model($tr_no,$emp_id,$sales_date,$data){
        DB::connection('ebs')->table('cs_cashier_cashdenomination')
            ->where('tr_no', $tr_no)
            ->where('emp_id', $emp_id)
            ->whereDate('date_submit', $sales_date)
            ->update($data);    
    }

    public function update_noncash_terminal_model($tr_no,$emp_id,$sales_date,$data){
        DB::connection('ebs')->table('cs_cashier_noncashdenomination')
            ->where('tr_no', $tr_no)
            ->where('emp_id', $emp_id)
            ->whereDate('date_submit', $sales_date)
            ->update($data);    
    }

    public function update_cash_date_model($tr_no,$emp_id,$old_date,$new_date){
        DB::connection('ebs')->table('cs_cashier_cashdenomination')
            ->where('tr_no', $tr_no)
            ->where('emp_id', $emp_id)
            ->whereDate('date_submit', $old_date)
            ->update(['date_submit' => $new_date]);    
    }

    public function update_noncash_date_model($tr_no,$emp_id,$old_date,$new_date){
        DB::connection('ebs')->table('cs_cashier_noncashdenomination')
            ->where('tr_no', $tr_no)
            ->where('emp_id', $emp_id)
            ->whereDate('date_submit', $old_date)
            ->update(['date_submit' => $new_date]);    
    }

    public function update_csdata_date_model($tr_no,$emp_id,$old_date,$new_date){
        DB::connection('ebs')->table('cebo_cs_data')
            ->where('tr_no', $tr_no)
            ->where('emp_id', $emp_id)
            ->where('date_shrt', $old_date)
            ->update(['date_shrt' => $new_date]);    
    }

    public function update_csden_date_model($tr_no,$emp_id,$old_date,$new_date){
        DB::connection('ebs')->table('cebo_cs_denomination')
            ->where('tr_no', $tr_no)
            ->where('emp_id', $emp_id)
            ->where('date_shrt', $old_date)
            ->update(['date_shrt' => $new_date]);    
    }

    public function update_remitted_date_model($tr_no,$emp_id,$old_date,$new_date){
        DB::connection('ebs')->table('cs_liq_remitted_cash')
            ->where('tr_no', $tr_no)
            ->where('emp_id', $emp_id)
            ->whereDate('date_remitted', $old_date)
            ->update(['date_remitted' => $new_date]);    
    }

    public function get_deptname_model($bcode){
        $data = DB::connection('pis')->table('locate_department')
                ->select('dcode','dept_name')
                ->where(DB::raw("SUBSTRING(dcode, 1, 4)"), $bcode)
                ->orderBy('dept_name', 'asc')
                ->get()->toArray();
        return $data;
    }

    public function get_section_model($dcode){
        $data = DB::connection('pis')->table('locate_section')
                ->select('scode','section_name')
                ->where(DB::raw("SUBSTRING(scode, 1, 6)"), $dcode)
                ->orderBy('section_name', 'asc')
                ->get()->toArray();
        return $data;
    }

    public function get_sub_section_model($scode){
        $data = DB::connection('pis')->table('locate_sub_section')
                ->select('sscode','sub_section_name')
                ->where(DB::raw("SUBSTRING(sscode, 1, 8)"), $scode)
                ->orderBy('sub_section_name', 'asc')
                ->get()->toArray();
        return $data;
    }

    public function get_cashier_info_model($emp_id){
        $data = DB::connection('pis')->table('employee3')
                ->select(DB::raw('concat(company_code,bunit_code,dept_code,section_code,sub_section_code) as location'))
                ->where('emp_id', $emp_id)
                ->get();
        return $data;
    }

    public function update_location_model($tr_no,$emp_id,$sales_date,$cashier_data,$csdata,$csden,$remitted_data){
        // =========update cash==================
        DB::connection('ebs')->table('cs_cashier_cashdenomination')
            ->where('tr_no', $tr_no)
            ->where('emp_id', $emp_id)
            ->whereDate('date_submit', $sales_date)
            ->update($cashier_data);
        // =========update noncash==================
        DB::connection('ebs')->table('cs_cashier_noncashdenomination')
            ->where('tr_no', $tr_no)
            ->where('emp_id', $emp_id)
            ->whereDate('date_submit', $sales_date)
            ->update($cashier_data);
        // =========update csdata==================
        DB::connection('ebs')->table('cebo_cs_data')
            ->where('tr_no', $tr_no)
            ->where('emp_id', $emp_id)
            ->where('date_shrt', $sales_date)
            ->update($csdata);
        // =========update csden==================
        DB::connection('ebs')->table('cebo_cs_denomination')
            ->where('tr_no', $tr_no)
            ->where('emp_id', $emp_id)
            ->where('date_shrt', $sales_date)
            ->update($csden);
        // =========update remitted==================
        DB::connection('ebs')->table('cs_liq_remitted_cash')
            ->where('tr_no', $tr_no)
            ->where('emp_id', $emp_id)
            ->whereDate('date_remitted', $sales_date)
            ->update($remitted_data);
    }

    public function get_batch_remittance_model($tr_no,$emp_id,$sales_date){
        $data = DB::connection('ebs')->table('cs_liq_remitted_cash')
                ->select('id','cash_id','dcode','batch_remit')
                ->where('tr_no', $tr_no)
                ->where('emp_id', $emp_id)
                ->whereDate('date_remitted', $sales_date)
                ->get()->toArray();
        return $data;
    }

    public function get_batch_cash_data_model($id){
        $data = DB::connection('ebs')->table('cs_cashier_cashdenomination')
                ->select('total_cash')
                ->where('id', $id)
                ->get();
        return $data;
    }

    public function get_batch_counter($dcode,$batch_date){
        $data = DB::connection('ebs')->table('cs_batch_remit_counter')
                ->select('batch_remit')
                ->where('dcode', $dcode)
                ->where('batch_date', $batch_date)
                ->get();
        return $data;
    }

    public function update_batch_counter($dcode,$batch_date,$data){
        DB::connection('ebs')->table('cs_batch_remit_counter')
            ->where('dcode', $dcode)
            ->where('batch_date', $batch_date)
            ->update($data);
    }

    public function update_batch_remittance_model($id,$data){
        DB::connection('ebs')->table('cs_liq_remitted_cash')
            ->where('id', $id)
            ->update($data);
    }




}
