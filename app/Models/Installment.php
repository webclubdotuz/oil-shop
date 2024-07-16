<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Installment extends Model
{
    use HasFactory, SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'client_id',
        'sale_id',
        'installment_info_id',
        'amount',
        'date',
        'status',
        'notes',
    ];

    protected $appends = ['due', 'status_html'];

    protected $casts = [
        'client_id' => 'integer',
        'sale_id' => 'integer',
        'amount' => 'double',
    ];

    public function client()
    {
        return $this->belongsTo('App\Models\Client');
    }

    public function sale()
    {
        return $this->belongsTo('App\Models\Sale');
    }

    public function installment_info()
    {
        return $this->belongsTo('App\Models\InstallmentInfo');
    }

    public function payment_sales()
    {
        return $this->hasMany('App\Models\PaymentSale');
    }

    public function getDueAttribute()
    {
        return $this->amount - $this->payment_sales->sum('montant');
    }

    public function getStatusHtmlAttribute()
    {
        if ($this->status == 'unpaid')
        {
            return '<span class="badge badge-danger">' . __('translate.Unpaid');
        }

        if ($this->status == 'paid')
        {
            return '<span class="badge badge-success">' . __('translate.Paid');
        }

        if ($this->status == 'partial')
        {
            return '<span class="badge badge-warning">' . __('translate.Partial');
        }
    }
}
