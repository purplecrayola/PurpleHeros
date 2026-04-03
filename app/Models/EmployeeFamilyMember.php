<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeFamilyMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'relationship',
        'date_of_birth',
        'phone',
        'is_next_of_kin',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'is_next_of_kin' => 'boolean',
    ];
}

