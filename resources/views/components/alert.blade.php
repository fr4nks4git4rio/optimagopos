@props([
    'alert' => 'info',
    'icon' => null
])
<div>
    <div class="alert alert-{{$alert}}">
        @if($icon)
            <x-icon class="fs-4 align-middle" name="{{$icon}}"></x-icon> &nbsp;
        @endif
        {{ $slot }}
    </div>
</div>
