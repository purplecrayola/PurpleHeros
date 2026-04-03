<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffSalary extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'user_id',
        'salary',
        'tax_station',
        'worked_days',
        'total_working_days',
        'unpaid_days',
        'salary_advance',
        'kpi_other_deductions',
        'annual_rent',
        'non_taxable_reimbursement',
        'basic',
        'da',
        'hra',
        'conveyance',
        'allowance',
        'medical_allowance',
        'tds',
        'esi',
        'pf',
        'leave',
        'prof_tax',
        'labour_welfare',
    ];

    protected $casts = [
        'salary' => 'float',
        'worked_days' => 'integer',
        'total_working_days' => 'integer',
        'unpaid_days' => 'float',
        'salary_advance' => 'float',
        'kpi_other_deductions' => 'float',
        'annual_rent' => 'float',
        'non_taxable_reimbursement' => 'float',
    ];
}
