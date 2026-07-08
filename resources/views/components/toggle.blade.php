@props([
    'label' => '',
    'model',
    'lazy' => false,
    'inline' => false,
    'size' => 'md', // sm, md.
    'align' => 'left',
    'onChange' => 'false',
    'index' => '',
])

@php
    if ($lazy) {
        $bind = '.lazy';
    } else {
        $bind = '.defer';
    }

    if (!in_array($size, ['sm', 'md'])) {
        $size = 'md';
    }

    $mb = $label != '' ? 'mb-3' : 'mb-0';

    $id = \Illuminate\Support\Str::replace('.', '-', $model);

    $align =
        $align == 'left'
            ? 'justify-content-start'
            : ($align == 'center'
                ? 'justify-content-center'
                : 'justify-content-end');

    $attributes = $attributes->class(['checkbox', 'is-invalid' => $errors->has($model)])->merge([
        'type' => 'checkbox',
        'id' => $id,
        'wire:model' . $bind => $model,
    ]);
@endphp

<div class="{{ $mb }} {{ $inline ? 'd-flex' : '' }} {{ $align }}" x-data="{}"
    x-init="() => {
        $('#{{ $id }}').on('change', function(e) {
            {{-- let elementName = $(this).attr('id').replaceAll('-', '.');
            @this.set(elementName, e.target.value); --}}
            if ('{{ $onChange }}' !== 'false')
                $wire.emit('{{ $onChange }}', '{{ $index }}');
        });
    }">
    <label for="{{ $model }}"
        class="form-check-label {{ $inline ? 'mr-2 mt-2' : ' mb-1' }} text-capitalize">{{ $label }}</label>
    <div class="toggle-button-cover">
        <div class="button-cover">
            <div class="button {{ $size }} r button-toggle">
                <input {{ $attributes }}>
                <div class="knobs"></div>
                <div class="layer"></div>
            </div>
        </div>
    </div>
    @error($model)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
