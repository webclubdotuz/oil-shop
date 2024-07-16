<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InstallmentMonthController extends Controller
{
    public function index()
    {
        $installment_months = \App\Models\InstallmentMonth::orderBy('month', 'asc')->get();
        return view('installment_months.index', compact('installment_months'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'month' => 'required|integer',
            'percentage' => 'required|integer',
        ]);

        \App\Models\InstallmentMonth::create($request->all());
        return back()->with('success', 'Installment month created successfully');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'month' => 'required|integer',
            'percentage' => 'required|integer',
        ]);

        $installment_month = \App\Models\InstallmentMonth::find($id);
        $installment_month->update($request->all());
        return back()->with('success', 'Installment month updated successfully');
    }

    public function destroy($id)
    {
        \App\Models\InstallmentMonth::destroy($id);
        return back()->with('success', 'Installment month deleted successfully');
    }
}
