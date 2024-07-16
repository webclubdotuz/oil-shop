<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstallmentMonth extends Model
{
    use HasFactory;

    protected $fillable = [
        'month',
        'percentage',
        'description'
    ];
}
