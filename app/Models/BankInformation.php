<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankInformation extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'bank_name',
        'bank_account_no',
        'ifsc_code',
        'pan_no',
        'primary_bank_name',
        'primary_bank_account_no',
        'primary_ifsc_code',
        'primary_pan_no',
        'secondary_bank_name',
        'secondary_bank_account_no',
        'secondary_ifsc_code',
        'secondary_pan_no',
    ];
}
