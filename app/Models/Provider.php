<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'user_id','name', 'code', 'address', 'phone', 'country', 'email', 'city',
    ];

    protected $casts = [
        'user_id' => 'integer',
    ];


    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
