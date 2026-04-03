<?php

namespace App\Filament\Pages;

use App\Models\roleTypeUser;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class RolesPermissions extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-key';
    protected static ?string $navigationGroup = 'Administration';
    protected static ?int $navigationSort = 20;
    protected static ?string $title = 'Roles & Permissions';
    protected static ?string $slug = 'roles-permissions';

    protected static string $view = 'filament.pages.roles-permissions';

    public string $newRoleName = '';
    public ?int $editingRoleId = null;
    public string $editingRoleName = '';

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user && $user->canManageSettings();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public function getRoles()
    {
        return roleTypeUser::query()
            ->leftJoin('users', 'users.role_name', '=', 'role_type_users.role_type')
            ->select(
                'role_type_users.id',
                'role_type_users.role_type',
                DB::raw('COUNT(users.id) as assigned_users')
            )
            ->groupBy('role_type_users.id', 'role_type_users.role_type')
            ->orderBy('role_type_users.role_type')
            ->get();
    }

    public function createRole(): void
    {
        $this->validate([
            'newRoleName' => 'required|string|max:255',
        ]);

        $roleName = trim($this->newRoleName);
        if ($roleName === '') {
            return;
        }

        $exists = roleTypeUser::query()
            ->whereRaw('LOWER(role_type) = ?', [strtolower($roleName)])
            ->exists();

        if ($exists) {
            Notification::make()->title('Role already exists')->danger()->send();
            return;
        }

        roleTypeUser::query()->create(['role_type' => $roleName]);
        $this->newRoleName = '';

        Notification::make()->title('Role created')->success()->send();
    }

    public function startEdit(int $roleId): void
    {
        $role = roleTypeUser::query()->findOrFail($roleId);
        $this->editingRoleId = $role->id;
        $this->editingRoleName = (string) $role->role_type;
    }

    public function cancelEdit(): void
    {
        $this->editingRoleId = null;
        $this->editingRoleName = '';
    }

    public function saveEdit(): void
    {
        if (! $this->editingRoleId) {
            return;
        }

        $this->validate([
            'editingRoleName' => 'required|string|max:255',
        ]);

        $role = roleTypeUser::query()->findOrFail($this->editingRoleId);
        $newName = trim($this->editingRoleName);

        $duplicate = roleTypeUser::query()
            ->where('id', '!=', $role->id)
            ->whereRaw('LOWER(role_type) = ?', [strtolower($newName)])
            ->exists();

        if ($duplicate) {
            Notification::make()->title('Role already exists')->danger()->send();
            return;
        }

        $oldName = $role->role_type;
        $role->update(['role_type' => $newName]);
        DB::table('users')->where('role_name', $oldName)->update(['role_name' => $newName]);

        $this->cancelEdit();
        Notification::make()->title('Role updated')->success()->send();
    }

    public function deleteRole(int $roleId): void
    {
        $role = roleTypeUser::query()->findOrFail($roleId);
        $assignedUsers = DB::table('users')->where('role_name', $role->role_type)->count();

        if ($assignedUsers > 0) {
            Notification::make()
                ->title('Cannot delete role')
                ->body('This role is assigned to existing users.')
                ->danger()
                ->send();
            return;
        }

        $role->delete();
        Notification::make()->title('Role deleted')->success()->send();
    }
}
