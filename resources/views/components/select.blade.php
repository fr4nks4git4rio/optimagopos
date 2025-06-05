@props([
    'label' => null,
    'options' => [],
    'model',
    'lazy' => false,
    'placeholder' => ''
])

@php
    $options = Arr::isAssoc($options) ? $options : array_combine($options, $options);

     $label = $label ? "$label:" : null;
    if ($lazy) $bind = '.lazy';
    else $bind = '.defer';

    $attributes = $attributes->class([
        'form-select',
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

    <select {{ $attributes }}>
        <option value="">{{$placeholder}}</option>

        @foreach($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}">{{ $optionLabel }}</option>
        @endforeach
    </select>

    @error($model)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
