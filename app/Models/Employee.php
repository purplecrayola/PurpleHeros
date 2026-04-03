<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'birth_date',
        'gender',
        'employee_id',
        'company',
        'holidays',
        'leaves',
        'clients',
        'projects',
        'tasks',
        'assets',
        'timing_sheets',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $model): void {
            $first = trim((string) ($model->first_name ?? ''));
            $last = trim((string) ($model->last_name ?? ''));
            $full = trim($first . ' ' . $last);

            if ($full !== '') {
                $model->name = $full;
                return;
            }

            $name = trim((string) ($model->name ?? ''));
            if ($name === '') {
                return;
            }

            $parts = preg_split('/\s+/', $name) ?: [];
            if ($first === '' && isset($parts[0])) {
                $model->first_name = $parts[0];
            }
            if ($last === '' && count($parts) > 1) {
                $model->last_name = implode(' ', array_slice($parts, 1));
            }
        });
    }
}
