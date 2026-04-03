<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfileInformation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'user_id',
        'email',
        'birth_date',
        'gender',
        'address',
        'state',
        'country',
        'pin_code',
        'phone_number',
        'department',
        'designation',
        'reports_to',
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
