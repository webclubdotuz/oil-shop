<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InstallmentInfo extends Model
{
    use HasFactory,  SoftDeletes;

    protected $fillable = [
        'client_id',
        'sale_id',
        'percentage',
        'months',
        'status',
        'notes'
    ];

    protected $appends = ['amount', 'total', 'due', 'montant', 'fact_due', 'fact_due_month_count'];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function installments()
    {
        return $this->hasMany(Installment::class);
    }

    public function getAmountAttribute()
    {
        return $this->sale->total * $this->percentage;
    }

    public function getTotalAttribute()
    {
        return $this->sale->GrandTotal;
    }

    public function getDueAttribute()
    {
        return $this->total - $this->sale->facture->sum('montant');
    }

    public function getMontantAttribute()
    {
        return $this->sale->facture->sum('montant');
    }

    public function getFactDueAttribute()
    {

        $summa = 0;

        foreach ($this->installments as $installment) {
            if (date('Y-m-d') >= $installment->date && $installment->due > 0)
            {
                $summa += $installment->due;
            }
        }

        return $summa;
    }

    public function getFactDueMonthCountAttribute()
    {
        $count = 0;

        foreach ($this->installments as $installment) {
            if (date('Y-m-d') >= $installment->date && $installment->due > 0)
            {
                $count++;
            }
        }

        return $count;
    }




}
