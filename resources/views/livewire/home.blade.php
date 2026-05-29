@section('title', 'Dashboard')
<div wire:poll.3000ms="loadData" wire:init="init" class="row">
    {{-- <div wire:loading.delay.longer>
        <div class="loading">
            <img src="{{ asset('img/loading.gif') }}" />
        </div>
    </div> --}}

    <h1 class="fs-1 mb-2">@yield('title')</h1>
    <div class="col-12">
        <div class="row justify-content-start gap-3 px-3 mb-3">
            <a href="{{ route('home', ['seccion' => 'resumen']) }}"
                class="btn btn-outline-site-primary bt-lg w-auto @if ($seccion == 'resumen') active @endif">
                Resumen
            </a>
            <a href="{{ route('home', ['seccion' => 'operaciones']) }}"
                class="btn btn-outline-site-primary bt-lg w-auto @if ($seccion == 'operaciones') active @endif">
                Operaciones
            </a>
            <a href="{{ route('home', ['seccion' => 'productos']) }}"
                class="btn btn-outline-site-primary bt-lg w-auto @if ($seccion == 'productos') active @endif">
                Productos
            </a>
            <a href="{{ route('home', ['seccion' => 'pagos']) }}"
                class="btn btn-outline-site-primary bt-lg w-auto @if ($seccion == 'pagos') active @endif">
                Pagos
            </a>
            <a href="{{ route('home', ['seccion' => 'correcciones']) }}"
                class="btn btn-outline-site-primary bt-lg w-auto @if ($seccion == 'correcciones') active @endif">
                Correcciones
            </a>
            {{-- <a href="{{ route('home', ['seccion' => 'cuarentena']) }}"
                class="btn btn-outline-site-primary bt-lg w-auto @if ($seccion == 'cuarentena') active @endif">
                Cuarentena
            </a> --}}
        </div>
        @if ($seccion)
            @include("livewire.secciones-dashboard.$seccion")
        @endif
    </div>
</div>
