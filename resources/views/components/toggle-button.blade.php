@props([
    'label',
    'model',
    'lazy' => false,
    'inline' => false
])

@php
    if ($lazy) $bind = '.lazy';
    else $bind = '.defer';

    $attributes = $attributes->class([
        'checkbox',
        'is-invalid' => $errors->has($model),
    ])->merge([
        'type' => 'checkbox',
        'id' => $model,
        'wire:model' . $bind =>  $model,
    ]);
@endphp

<style>
    .toggle-button-cover {
        display: table-cell;
        position: relative;
        width: 70px;
        height: 30px;
        box-sizing: border-box;
    }

    .button-cover {
    /*/ / height: 100 px;*/
    /*/ / margin: 20 px;*/
        background-color: transparent;
        /*box-shadow: 0 10px 20px -8px #c5d6d6;*/
        /*border-radius: 4px;*/
    }

    .button-cover:before {
        counter-increment: button-counter;
    / / content: counter(button-counter);
        position: absolute;
        right: 0;
        bottom: 0;
        color: #d7e3e3;
        font-size: 12px;
        line-height: 1;
        padding: 5px;
    }

    .button-cover,
    .knobs,
    .layer {
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
    }

    .button {
        position: relative;
        top: 50%;
        width: 74px;
        height: 35px;
        margin: -20px auto 0 auto;
        overflow: hidden;
    }

    .button.r,
    .button.r .layer {
        border-radius: 100px;
    }

    .button.b2 {
        border-radius: 2px;
    }

    .checkbox {
        position: relative;
        width: 100%;
        height: 100%;
        padding: 0;
        margin: 0;
        opacity: 0;
        cursor: pointer;
        z-index: 3;
    }

    .checkbox:disabled {
        cursor: not-allowed;
    }

    .knobs {
        z-index: 2;
        background-color: lightgrey;
    }

    .layer {
        width: 100%;
        background-color: lightgrey;
        transition: 0.3s ease all;
        z-index: 1;
    }

    .button-toggle .knobs:before {
        content: "NO";
        position: absolute;
        top: 0;
        left: 0;
        width: 35px;
        height: 35px;
        color: #fff;
        font-size: 16px;
        font-weight: bold;
        text-align: center;
        line-height: 1;
        padding: 10px 4px;
        background-color: gray;
        border-radius: 50%;
        transition: 0.3s cubic-bezier(0.18, 0.89, 0.35, 1.15) all;
    }

    .button-toggle .checkbox:checked + .knobs:before {
        content: "SI";
        left: 38px;
        background-color: #ce5124;
    }

    .button-toggle .checkbox:disabled + .knobs:before {
        opacity: 0.4;
    }

    .button-toggle .checkbox:checked ~ .layer {
        background-color: #fcebeb;
    }

    .button-toggle .knobs,
    .button-toggle .knobs:before,
    .button-toggle .layer {
        transition: 0.3s ease all;
    }
</style>

<div class="mb-3 {{ $inline ? 'd-flex' : '' }}">
    <label for="{{ $model }}" class="form-check-label {{ $inline ? 'mr-2' : ' mb-1' }}">{{ $label }}</label>
    <div class="toggle-button-cover">
        <div class="button-cover">
            <div class="button r button-toggle">
                <input {{ $attributes }} >
                <div class="knobs"></div>
                <div class="layer"></div>
            </div>
        </div>
    </div>
    @error($model)
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
