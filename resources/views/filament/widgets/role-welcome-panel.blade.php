<x-filament-widgets::widget>
    <div class="ax-hero">
        <div class="ax-hero-copy">
            <p class="ax-hero-kicker">{{ $greeting }}</p>
            <h2 class="ax-hero-title">Welcome to Purple HR</h2>
            <p class="ax-hero-subtitle">
                Active profile: <strong>{{ $roleLabel }}</strong>. Use quick actions to jump directly into today’s priorities.
            </p>
        </div>

        <div class="ax-hero-actions">
            @foreach ($actionItems as $item)
                <a href="{{ $item['url'] }}" class="ax-hero-chip">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </div>
    </div>
</x-filament-widgets::widget>
