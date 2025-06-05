@props([
    'label' => null,
    'model',
    'lazy' => false,
    'debounce' => false,
])

@php
    $label = $label ? "$label:" : null;
    if ($lazy) $bind = '.lazy';
    else if (ctype_digit($debounce)) $bind = '.debounce.' . $debounce . 'ms';
    else if ($debounce) $bind = '';
    else $bind = '.defer';

    $attributes = $attributes->class([
        'form-control',
        'is-invalid' => $errors->has($model),
    ])->merge([
        'id' => $model,
        'wire:model' . $bind => $model,
    ]);
@endphp

<div class="mb-1">
    @if($label)
        <label for="{{ $model }}" class="">{{ $label }}</label>
    @endif

    <textarea {{ $attributes }}></textarea>

    @error($model)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
