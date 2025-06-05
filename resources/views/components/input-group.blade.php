@props([
    'label' => null,
    'type',
    'icon',
    'front' => true,
    'model',
    'lazy' => false,
    'debounce' => false,
])

@php
    if ($type == 'number') $inputmode = 'decimal';
    else if (in_array($type, ['tel', 'search', 'email', 'url'])) $inputmode = $type;
    else $inputmode = 'text';

    if ($lazy) $bind = '.lazy';
    else if (ctype_digit($debounce)) $bind = '.debounce.' . $debounce . 'ms';
    else if ($debounce) $bind = '';
    else $bind = '.defer';

    $attributes = $attributes->class([
        'form-control',
        'is-invalid' => $errors->has($model),
    ])->merge([
        'type' => $type,
        'inputmode' => $inputmode,
        'id' => $model,
        'wire:model' . $bind => $model,
    ]);
@endphp

<div class="mb-3">
    @if($label)
        <label for="{{ $model }}" class="form-label">{{ $label }}</label>
    @endif

    <div class="input-group mb-3">
        @if($front)
            <span class="input-group-text {{$icon}}" id="basic-addon1"></span>
            <input {{ $attributes }}>
        @else
            <input {{ $attributes }}>
            <span class="input-group-text {{$icon}}" id="basic-addon1"></span>
        @endif
    </div>

    @error($model)
    <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>
