<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentSale extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'sale_id', 'montant', 'Ref','change', 'payment_method_id', 'user_id', 'notes','date','account_id', 'installment_id', // add this line
    ];

    protected $casts = [
        'montant' => 'double',
        'change'  => 'double',
        'sale_id' => 'integer',
        'user_id' => 'integer',
        'account_id' => 'integer',
        'installment_id' => 'integer', // add this line
        'payment_method_id' => 'integer',
    ];

    public function payment_method()
    {
        return $this->belongsTo('App\Models\PaymentMethod');
    }

    public function installment()
    {
        return $this->belongsTo('App\Models\Installment'); // add this line
    }

    public function account()
    {
        return $this->belongsTo('App\Models\Account');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function sale()
    {
        return $this->belongsTo('App\Models\Sale');
    }

}
