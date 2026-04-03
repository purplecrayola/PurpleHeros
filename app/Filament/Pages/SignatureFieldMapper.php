<?php

namespace App\Filament\Pages;

use App\Models\EmployeeOnboarding;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class SignatureFieldMapper extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-pencil-square';
    protected static ?string $title = 'Signature Field Mapper';
    protected static ?string $slug = 'signature-field-mapper';
    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.pages.signature-field-mapper';

    public ?EmployeeOnboarding $onboarding = null;
    public int $onboardingId = 0;
    public string $documentType = 'offer';
    public string $documentPath = '';

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $actors = [];

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->canManagePeopleOps();
    }

    public function mount(): void
    {
        $this->onboardingId = (int) request()->query('onboarding', 0);
        $this->documentType = in_array((string) request()->query('document', 'offer'), ['offer', 'contract'], true)
            ? (string) request()->query('document', 'offer')
            : 'offer';

        $this->onboarding = EmployeeOnboarding::query()->with('user:user_id,name,email')->find($this->onboardingId);

        abort_unless($this->onboarding, 404);

        $this->documentPath = (string) ($this->documentType === 'offer'
            ? ($this->onboarding->offer_document_path ?? '')
            : ($this->onboarding->contract_document_path ?? ''));

        $rawActors = $this->documentType === 'offer'
            ? ($this->onboarding->offer_signers_json ?? [])
            : ($this->onboarding->contract_signers_json ?? []);

        if (! is_array($rawActors) || $rawActors === []) {
            $rawActors = [[
                'role_label' => 'Employee',
                'signer_name' => (string) ($this->onboarding->user?->name ?: $this->onboarding->user_id),
                'signer_email' => (string) ($this->onboarding->user?->email ?: ''),
                'sign_order' => 1,
                'signature_field_key' => 'EMPLOYEE_SIGN',
                'page_number' => 1,
                'position_x' => 80,
                'position_y' => 160,
                'field_width' => 200,
                'field_height' => 60,
            ]];
        }

        $this->actors = collect($rawActors)
            ->filter(fn ($actor) => is_array($actor))
            ->map(function (array $actor, int $index): array {
                return [
                    'role_label' => trim((string) ($actor['role_label'] ?? 'Signer ' . ($index + 1))),
                    'signer_name' => trim((string) ($actor['signer_name'] ?? '')),
                    'signer_email' => trim((string) ($actor['signer_email'] ?? '')),
                    'sign_order' => max(1, (int) ($actor['sign_order'] ?? ($index + 1))),
                    'signature_field_key' => trim((string) ($actor['signature_field_key'] ?? ('SIGNATURE_' . ($index + 1)))),
                    'page_number' => max(1, (int) ($actor['page_number'] ?? 1)),
                    'position_x' => $this->toFloatOrDefault($actor['position_x'] ?? null, 60 + ($index * 20)),
                    'position_y' => $this->toFloatOrDefault($actor['position_y'] ?? null, 120 + ($index * 20)),
                    'field_width' => max(80, $this->toFloatOrDefault($actor['field_width'] ?? null, 200)),
                    'field_height' => max(40, $this->toFloatOrDefault($actor['field_height'] ?? null, 60)),
                ];
            })
            ->values()
            ->all();
    }

    public function addActor(): void
    {
        $next = count($this->actors) + 1;
        $this->actors[] = [
            'role_label' => 'Signer ' . $next,
            'signer_name' => '',
            'signer_email' => '',
            'sign_order' => $next,
            'signature_field_key' => 'SIGNATURE_' . $next,
            'page_number' => 1,
            'position_x' => 80 + ($next * 10),
            'position_y' => 140 + ($next * 10),
            'field_width' => 200,
            'field_height' => 60,
        ];
    }

    public function removeActor(int $index): void
    {
        if (! isset($this->actors[$index])) {
            return;
        }

        unset($this->actors[$index]);
        $this->actors = array_values($this->actors);
    }

    public function saveMappings(): void
    {
        if (! $this->onboarding) {
            return;
        }

        $normalized = collect($this->actors)
            ->filter(fn ($actor) => is_array($actor))
            ->map(function (array $actor, int $index): array {
                return [
                    'role_label' => trim((string) ($actor['role_label'] ?? 'Signer ' . ($index + 1))),
                    'signer_name' => trim((string) ($actor['signer_name'] ?? '')),
                    'signer_email' => trim((string) ($actor['signer_email'] ?? '')),
                    'sign_order' => max(1, (int) ($actor['sign_order'] ?? ($index + 1))),
                    'signature_field_key' => trim((string) ($actor['signature_field_key'] ?? ('SIGNATURE_' . ($index + 1)))),
                    'page_number' => max(1, (int) ($actor['page_number'] ?? 1)),
                    'position_x' => max(0, $this->toFloatOrDefault($actor['position_x'] ?? null, 0)),
                    'position_y' => max(0, $this->toFloatOrDefault($actor['position_y'] ?? null, 0)),
                    'field_width' => max(80, $this->toFloatOrDefault($actor['field_width'] ?? null, 200)),
                    'field_height' => max(40, $this->toFloatOrDefault($actor['field_height'] ?? null, 60)),
                ];
            })
            ->sortBy('sign_order')
            ->values()
            ->all();

        if ($this->documentType === 'offer') {
            $this->onboarding->offer_signers_json = $normalized;
            $this->onboarding->offer_document_path = trim($this->documentPath) !== '' ? trim($this->documentPath) : $this->onboarding->offer_document_path;
        } else {
            $this->onboarding->contract_signers_json = $normalized;
            $this->onboarding->contract_document_path = trim($this->documentPath) !== '' ? trim($this->documentPath) : $this->onboarding->contract_document_path;
        }

        $this->onboarding->updated_by_user_id = auth()->user()?->user_id;
        $this->onboarding->save();

        $this->actors = $normalized;

        Notification::make()->title('Signature field mapping saved')->success()->send();
    }

    private function toFloatOrDefault(mixed $value, float $default): float
    {
        if ($value === null || $value === '') {
            return $default;
        }

        return (float) $value;
    }
}
