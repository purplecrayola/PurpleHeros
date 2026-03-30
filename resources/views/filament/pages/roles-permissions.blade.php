<x-filament-panels::page>
    <x-filament::section heading="Create Role">
        <div class="flex gap-3">
            <div class="w-full max-w-md">
                <x-filament::input.wrapper>
                    <x-filament::input type="text" wire:model="newRoleName" placeholder="Role name" />
                </x-filament::input.wrapper>
            </div>
            <x-filament::button wire:click="createRole">Add Role</x-filament::button>
        </div>
    </x-filament::section>

    <x-filament::section heading="Configured Roles">
        @php($roles = $this->getRoles())
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left">
                    <tr>
                        <th class="px-3 py-2">Role</th>
                        <th class="px-3 py-2">Assigned Users</th>
                        <th class="px-3 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach($roles as $role)
                        <tr>
                            <td class="px-3 py-2">
                                @if($editingRoleId === (int) $role->id)
                                    <x-filament::input.wrapper>
                                        <x-filament::input type="text" wire:model="editingRoleName" />
                                    </x-filament::input.wrapper>
                                @else
                                    <span>{{ $role->role_type }}</span>
                                @endif
                            </td>
                            <td class="px-3 py-2">{{ $role->assigned_users }}</td>
                            <td class="px-3 py-2">
                                <div class="flex gap-2">
                                    @if($editingRoleId === (int) $role->id)
                                        <x-filament::button size="sm" wire:click="saveEdit">Save</x-filament::button>
                                        <x-filament::button size="sm" color="gray" wire:click="cancelEdit">Cancel</x-filament::button>
                                    @else
                                        <x-filament::button size="sm" color="gray" wire:click="startEdit({{ $role->id }})">Edit</x-filament::button>
                                        <x-filament::button size="sm" color="danger" wire:click="deleteRole({{ $role->id }})">Delete</x-filament::button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-panels::page>
