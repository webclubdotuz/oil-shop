<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone', 'message', 'sms_id', 'status'
    ];

}
