<?php

namespace App\Models;

use App\Support\MediaStorageManager;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeDocument extends Model
{
    use HasFactory;

    protected $table = 'employee_documents';

    protected $fillable = [
        'user_id',
        'document_type',
        'title',
        'file_path',
        'is_verified',
        'verification_feedback',
        'verified_by_user_id',
        'verified_at',
        'uploaded_by_user_id',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];

    protected $appends = [
        'file_url',
    ];

    public function getFileUrlAttribute(): string
    {
        return MediaStorageManager::publicUrl($this->file_path);
    }
}
