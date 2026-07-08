@section('title', 'Dashboard')
<div wire:init="init" class="row" x-data="{
    mostrarFiltros: true,
    init() {
        this.loadData();
    },
    loadData() {
        @this.loadData();
        let el = this;
        setTimeout(() => {
            el.loadData();
        }, 5000);
    }
}">
    <div class="col-12">
        <h1 class="fs-1 mb-3">@yield('title')</h1>

        <div class="d-flex align-items-start">

            {{-- COLUMNA DE FILTROS --}}
            <div x-show="mostrarFiltros" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0" x-cloak class="flex-shrink-0 me-3 mb-3" style="width: 260px;">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center">
                        <i class="bi bi-funnel-fill me-2 text-primary"></i>
                        <h6 class="mb-0 fw-bold text-dark">Filtros</h6>
                    </div>
                    <div class="card-body p-3">
                        <x-input :label="'Fecha inicio'" type="date" :debounce="200" :lazy="true"
                            model="fecha_inicio" />
                        <x-input :label="'Fecha fin'" type="date" :debounce="200" :lazy="true"
                            model="fecha_fin" />
                        <x-select2-multiple class="form-control" :label="'Sucursales'" :lazy="true" model="sucursales"
                            :options="$sucursalesDisponibles" />
                        <x-select2-multiple class="form-control" :label="'Terminales'" :dynamic="true" :lazy="true"
                            model="terminales" :options="$terminalesDisponibles" />
                        @if ($terminales && count($terminales) > 0)
                            <a href="{{ route('cliente.reportes.historico-operaciones', ['fecha_inicio' => $fecha_inicio, 'fecha_fin' => $fecha_fin, 'sucursales_query' => implode(',', $sucursales), 'terminales_query' => implode(',', $terminales)]) }}"
                                class="btn btn-primary w-100 mt-2">
                                Ver Detalles
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- PESTAÑA/MANIJA: siempre visible, pegada al costado del panel --}}
            <button type="button"
                class="btn btn-primary d-flex align-items-center justify-content-center shadow-sm flex-shrink-0 me-3 mb-3"
                @click="mostrarFiltros = !mostrarFiltros"
                :title="mostrarFiltros ? 'Ocultar filtros' : 'Mostrar filtros'"
                style="width: 24px; height: 60px; padding: 0; border-radius: 6px; align-self: flex-start; margin-top: 42px;">
                <i class="bi" :class="mostrarFiltros ? 'bi-chevron-left' : 'bi-chevron-right'"></i>
            </button>

            {{-- CONTENIDO PRINCIPAL --}}
            <div class="flex-grow-1" style="min-width: 0;">

                <div class="row justify-content-start gap-3 px-3 mb-3">
                    <a href="{{ route('home', ['seccion' => 'resumen', 'fecha_inicio' => $fecha_inicio, 'fecha_fin' => $fecha_fin, 'sucursales_query' => implode(',', $sucursales), 'terminales_query' => implode(',', $terminales)]) }}"
                        class="btn btn-outline-site-primary bt-lg w-auto @if ($seccion == 'resumen') active @endif">
                        Resumen
                    </a>
                    <a href="{{ route('home', ['seccion' => 'operaciones', 'fecha_inicio' => $fecha_inicio, 'fecha_fin' => $fecha_fin, 'sucursales_query' => implode(',', $sucursales), 'terminales_query' => implode(',', $terminales)]) }}"
                        class="btn btn-outline-site-primary bt-lg w-auto @if ($seccion == 'operaciones') active @endif">
                        Operaciones
                    </a>
                    <a href="{{ route('home', ['seccion' => 'productos', 'fecha_inicio' => $fecha_inicio, 'fecha_fin' => $fecha_fin, 'sucursales_query' => implode(',', $sucursales), 'terminales_query' => implode(',', $terminales)]) }}"
                        class="btn btn-outline-site-primary bt-lg w-auto @if ($seccion == 'productos') active @endif">
                        Productos
                    </a>
                    <a href="{{ route('home', ['seccion' => 'pagos', 'fecha_inicio' => $fecha_inicio, 'fecha_fin' => $fecha_fin, 'sucursales_query' => implode(',', $sucursales), 'terminales_query' => implode(',', $terminales)]) }}"
                        class="btn btn-outline-site-primary bt-lg w-auto @if ($seccion == 'pagos') active @endif">
                        Pagos
                    </a>
                    <a href="{{ route('home', ['seccion' => 'correcciones', 'fecha_inicio' => $fecha_inicio, 'fecha_fin' => $fecha_fin, 'sucursales_query' => implode(',', $sucursales), 'terminales_query' => implode(',', $terminales)]) }}"
                        class="btn btn-outline-site-primary bt-lg w-auto @if ($seccion == 'correcciones') active @endif">
                        Correcciones
                    </a>
                    {{-- <a href="{{ route('home', ['seccion' => 'cuarentena', 'fecha_inicio' => $fecha_inicio, 'fecha_fin' => $fecha_fin, 'sucursales_query' => implode(',', $sucursales), 'terminales_query' => implode(',', $terminales)]) }}"
                        class="btn btn-outline-site-primary bt-lg w-auto @if ($seccion == 'cuarentena') active @endif">
                        Cuarentena
                    </a> --}}
                </div>

                @if ($seccion)
                    @include("livewire.secciones-dashboard.$seccion")
                @endif
            </div>

        </div>
    </div>
</div>
