@props([
    'name',
    'style' => config('ui.font_awesome_style'),
])

@php
    $attributes = $attributes->class([
        'bi' . \Illuminate\Support\Str::limit($style, 1, null) . ' bi-' . $name,
    ])->merge([
        //
    ]);
@endphp

<i {{ $attributes }}></i>
