<?php

namespace App\Http\Controllers;

use App\utils\helpers;
use Illuminate\Http\Request;
use App\Models\InstallmentInfo;
use App\Models\Sale;

class InstallmentInfoController extends Controller
{
    protected $currency;
    protected $symbol_placement;

    public function __construct()
    {
        $helpers = new helpers();
        $this->currency = $helpers->Get_Currency();
        $this->symbol_placement = $helpers->get_symbol_placement();

    }


    public function index(Request $request)
    {

        $fact_due_month_count = $request->fact_due_month_count;

        $installment_infos = InstallmentInfo::where('status', 'partial')->get();

        $fact_due_month_counts = $installment_infos->pluck('fact_due_month_count')->unique();

        if ($fact_due_month_count) {
            $installment_infos = $installment_infos->where('fact_due_month_count', $fact_due_month_count);
        }

        return view('installment_infos.index', compact('installment_infos', 'fact_due_month_counts'));

    }


    public function Print_Contract(InstallmentInfo $installment_info)
    {

        $sale = Sale::find($installment_info->sale_id);

        return view('installment_infos.print_contract', compact('installment_info', 'sale'));
    }


}
