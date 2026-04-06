<x-filament-panels::page>
    <x-filament::section heading="Email Delivery">
        <div class="pc-filter-shell">
            <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-600">Mailer</label>
                <select wire:model="mail_mailer" class="fi-input block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="log">Log (No external delivery)</option>
                    <option value="smtp">SMTP</option>
                    <option value="ses">AWS SES</option>
                </select>
            </div>
            <label class="mt-7 flex items-center gap-2">
                <input type="checkbox" wire:model="ses_enabled" class="rounded border-slate-300">
                <span class="text-sm">AWS SES Enabled</span>
            </label>

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-600">SES Region</label>
                <x-filament::input.wrapper>
                    <x-filament::input type="text" wire:model="ses_region" placeholder="us-east-1" />
                </x-filament::input.wrapper>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-600">SES Access Key ID</label>
                <x-filament::input.wrapper>
                    <x-filament::input type="text" wire:model="ses_access_key_id" placeholder="AKIA..." />
                </x-filament::input.wrapper>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-600">SES Secret Access Key</label>
                <x-filament::input.wrapper>
                    <x-filament::input type="password" wire:model="ses_secret_access_key" placeholder="Secret key" />
                </x-filament::input.wrapper>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-600">SES Configuration Set (Optional)</label>
                <x-filament::input.wrapper>
                    <x-filament::input type="text" wire:model="ses_configuration_set" placeholder="transactional-mails" />
                </x-filament::input.wrapper>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-600">From Email</label>
                <x-filament::input.wrapper>
                    <x-filament::input type="email" wire:model="mail_from_address" />
                </x-filament::input.wrapper>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-600">From Name</label>
                <x-filament::input.wrapper>
                    <x-filament::input type="text" wire:model="mail_from_name" />
                </x-filament::input.wrapper>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-600">Reply-To Email (Optional)</label>
                <x-filament::input.wrapper>
                    <x-filament::input type="email" wire:model="mail_reply_to_address" />
                </x-filament::input.wrapper>
            </div>
            </div>
        </div>

        <div class="mt-4 pc-action-row right">
            <x-filament::button wire:click="save">Save Email Settings</x-filament::button>
        </div>
    </x-filament::section>

    <x-filament::section heading="Send Test Email">
        <div class="pc-filter-shell">
            <div class="grid gap-4 md:grid-cols-3">
            <div class="md:col-span-2">
                <x-filament::input.wrapper>
                    <x-filament::input type="email" wire:model="test_recipient" placeholder="recipient@example.com" />
                </x-filament::input.wrapper>
            </div>
            <div>
                <x-filament::button color="gray" wire:click="sendTest">Send Test Email</x-filament::button>
            </div>
            </div>
        </div>
        <p class="mt-4 text-sm text-slate-500">SES requires verified identities and IAM permissions (`ses:SendEmail` / `ses:SendRawEmail`) in the configured region.</p>
    </x-filament::section>
</x-filament-panels::page>
