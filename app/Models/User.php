<?php

namespace App\Models;

use App\Support\MediaStorageManager;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'user_id',
        'email',
        'join_date',
        'last_login',
        'phone_number',
        'status',
        'role_name',
        'email',
        'role_name',
        'avatar',
        'position',
        'department',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $appends = [
        'avatar_url',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->canAccessHrPanel();
    }

    public function getAvatarUrlAttribute(): string
    {
        return MediaStorageManager::publicUrl(
            $this->avatar,
            'assets/img/user.jpg',
            'assets/images',
        );
    }


    public function hasRole(array|string $roles): bool
    {
        $roles = is_array($roles) ? $roles : [$roles];

        return in_array($this->role_name, $roles, true);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(['Admin', 'Super Admin']);
    }

    public function canAccessHrPanel(): bool
    {
        return $this->canManagePeopleOps()
            || $this->canManageTimeAttendance()
            || $this->canManagePayroll()
            || $this->canViewReports()
            || $this->canManageSettings()
            || $this->canManageUsers();
    }

    public function canManageOrganization(): bool
    {
        return $this->hasRole(['Super Admin', 'Admin', 'HR Manager']);
    }

    public function canManagePeopleOps(): bool
    {
        return $this->hasRole(['Super Admin', 'Admin', 'HR Manager']);
    }

    public function canManageTimeAttendance(): bool
    {
        return $this->hasRole(['Super Admin', 'Admin', 'HR Manager', 'Operations Manager']);
    }

    public function canManagePayroll(): bool
    {
        return $this->hasRole(['Super Admin', 'Admin', 'Payroll Admin']);
    }

    public function canViewReports(): bool
    {
        return $this->hasRole(['Super Admin', 'Admin', 'HR Manager', 'Payroll Admin', 'Reports Analyst']);
    }

    public function canManageSettings(): bool
    {
        return $this->hasRole(['Super Admin', 'Admin']);
    }

    public function canManageUsers(): bool
    {
        return $this->hasRole(['Super Admin', 'Admin']);
    }

    public function canAccessUserId(?string $userId): bool
    {
        return $this->isAdmin() || $this->user_id === $userId;
    }

    public function payslips()
    {
        return $this->hasMany(Payslip::class, 'user_id', 'user_id');
    }

    public function statutoryProfile(): HasOne
    {
        return $this->hasOne(EmployeeStatutoryProfile::class, 'user_id', 'user_id');
    }

    protected static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $getUser = self::orderBy('user_id', 'desc')->first();

            if ($getUser) {
                $latestID = intval(substr($getUser->user_id, 4));
                $nextID = $latestID + 1;
            } else {
                $nextID = 1;
            }
            $model->user_id = 'KH_' . sprintf("%04s", $nextID);
            while (self::where('user_id', $model->user_id)->exists()) {
                $nextID++;
                $model->user_id = 'KH_' . sprintf("%04s", $nextID);
            }
        });

        self::saving(function ($model) {
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
