@section('title', 'Reporte de Ingresos')

<div>
    <h1 class="h2 fw-bold">@yield('title')</h1>

    <div class="row mb-3">
        <div class="col-2">
            <label for="">Fecha Inicio</label>
            <input type="date" class="form-control" wire:model="fechaInicio">
        </div>
        <div class="col-2">
            <label for="">Fecha Fin</label>
            <input type="date" class="form-control" wire:model="fechaFin">
        </div>
        <div class="col-4">
            <x-select2-ajax class="form-control" :label="'Cliente'" :placeholder="'Seleccione...'"
                url="{{ route('clientes.load-clientes', ['is_filter' => 1]) }}" model="cliente" />
        </div>
        <div class="col-2">
            <label for="">Moneda:</label>
            <select class="form-control " wire:model="moneda">
                <option value="">Todas</option>
                @foreach ($monedas as $moneda)
                    <option value="{{ $moneda }}">{{ $moneda }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-2">
            <label for="">Importe</label>
            <input type="text" class="form-control" wire:model="importe">
        </div>
    </div>
    <div class="row justify-content-end">
        <div class="col-lg-auto mb-3">
            <button type="button" class="btn btn-primary" wire:click="imprimirListadoIngresos">Imprimir</button>
            <button type="button" class="btn btn-primary" wire:click="exportarExcelListadoIngresos">Exportar
                EXCEL</button>
            <x-dropdown icon="eye" :label="__($perPage)">
                @foreach ($perPages as $perPage)
                    @if ($this->perPage === $perPage)
                        <x-dropdown-item class="active opacity-50" label="{{ $perPage }}"
                            click="$set('perPage', '{{ $perPage }}')" />
                    @else
                        <x-dropdown-item label="{{ $perPage }}" click="$set('perPage', '{{ $perPage }}')" />
                    @endif
                @endforeach
            </x-dropdown>
            <x-dropdown icon="sort-down-alt" :label="__($sort)">
                @foreach ($sorts as $sort)
                    @if ($this->sort === $sort)
                        <x-dropdown-item class="active opacity-50" :label="__($sort)"
                            click="$set('sort', '{{ $sort }}')" />
                    @else
                        <x-dropdown-item :label="__($sort)" click="$set('sort', '{{ $sort }}')" />
                    @endif
                @endforeach
            </x-dropdown>
        </div>
    </div>

    <div class="list-group mb-3 table-responsive">
        <table class="table table-responsive table-striped">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Folio Interno</th>
                    <th>Cliente</th>
                    <th>Póliza</th>
                    <th>Folio UUID</th>
                    <th>Moneda</th>
                    <th>Importe</th>
                    <th class="text-center" style="width: 150px">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total_mxn = 0;
                $total_usd = 0;
                ?>
                @forelse($ingresos as $ingreso)
                    <?php
                    if ($ingreso->moneda === 'MXN') {
                        $total_mxn += $ingreso->monto;
                    } elseif ($ingreso->moneda === 'USD') {
                        $total_usd += $ingreso->monto;
                    }
                    ?>
                    <tr>
                        <td>{{ $ingreso->fecha_str }}</td>
                        <td>{{ $ingreso->folio_interno }}</td>
                        <td>{{ $ingreso->razon_social }}</td>
                        <td>{{ $ingreso->nombre_poliza }}</td>
                        <td>{{ $ingreso->uuid }}</td>
                        <td>{{ $ingreso->moneda }}</td>
                        <td>${{ number_format($ingreso->monto, 2) }}</td>
                        <td class="text-center">
                            <ul class="list-unstyled mb-0">
                                <li class="list-inline-item">
                                    <x-action icon="file-pdf" title="Imprimir Factura"
                                        click="imprimirFactura({{ $ingreso->factura_id }})" />
                                </li>
                                <li class="list-inline-item">
                                    <x-action icon="eye" title="Detalles Ingreso"
                                        click="$emit('openModal', 'facturacion.ingresos.show', {ingreso_id: '{{ $ingreso->id }}', factura_id: '{{ $ingreso->factura_id }}'})" />
                                </li>
                                <li class="list-inline-item">
                                    <x-action icon="file-earmark-post" title="Imprimir Ingreso"
                                        click="imprimirIngresoPdf({{ $ingreso->id }})" />
                                </li>
                            </ul>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <div class="list-group-item">
                                No se encontraron resultados...
                            </div>
                        </td>
                    </tr>
                @endforelse
                @if (count($ingresos) > 0)
                    <tr>
                        <td colspan="6" class="text-end fw-bold">
                            Total MXN:
                        </td>
                        <td class="fw-bold">${{ number_format($total_mxn, 2) }}</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="6" class="text-end fw-bold">
                            Total USD:
                        </td>
                        <td class="fw-bold">${{ number_format($total_usd, 2) }}</td>
                        <td></td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    <x-pagination :links="$ingresos" :count="true" />

    <div class="modal {{ $iframeContainerClass }}">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">PDF</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        wire:click="$set('iframeContainerClass', '')"></button>
                </div>
                <div class="modal-body pb-0 text-center">
                    <div class="row">
                        <iframe src="{{ $iframeSrc }}" frameborder="0" id="frame-death-file"
                            style="height: 80dvh"></iframe>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                        wire:click="$set('iframeContainerClass', '')">{{ __('Cerrar') }}</button>
                </div>
            </div>
        </div>
    </div>
</div>
