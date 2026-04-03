<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExportAudit extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_email',
        'report_name',
        'format',
        'filename',
        'report_date',
        'employee_search',
        'ip_address',
        'user_agent',
        'exported_at',
    ];
}
