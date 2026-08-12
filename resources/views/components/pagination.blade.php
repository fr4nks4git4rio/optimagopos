@props([
    'links',
    'count' => false,
    'justify' => null,
])

@php
    $justify = $justify ?? ($count ? 'between' : 'end');

    $attributes = $attributes->class([
        'row align-items-baseline justify-content-' . $justify,
    ])->merge([]);
@endphp

<div {{ $attributes }}>
    {{-- Contador de registros (opcional) --}}
    @if($links && $links->total() > 0 && $count)
        <div class="col-auto text-muted">
            {{ __('site.paginator.showing') }}: {{ $links->firstItem() ?? 0 }}
            {{ __('site.paginator.to') }} {{ $links->lastItem() ?? 0 }}
            {{ __('site.paginator.of') }} {{ $links->total() }}
        </div>
    @endif

    {{-- Botones del paginador (Navegación reactiva) --}}
    <div class="col-auto mb-n3">
        @if($links && $links->hasPages())
            {{-- En pantallas pequeñas (móviles) --}}
            <div class="d-block d-lg-none">
                {{ $links->links('livewire::simple-bootstrap') }}
            </div>

            {{-- En pantallas grandes (escritorio) --}}
            <div class="d-none d-lg-block">
                {{ $links->links('livewire::bootstrap') }}
            </div>
        @endif
    </div>
</div>
