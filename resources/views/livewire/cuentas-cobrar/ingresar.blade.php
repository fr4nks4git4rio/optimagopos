<x-modal form-action="save">
    <div wire:loading.delay.longer>
        <div class="loading">
            <img src="{{ asset('img/loading.gif') }}" />
        </div>
    </div>
    <x-slot:title>
        Ingresar
    </x-slot:title>

    <x-slot:content>
        <div class="card shadow-sm border-0 mb-4 bg-light-subtle">
            <div class="card-body py-3">
                <div class="row g-3 align-items-center">
                    <div class="col-md-4 col-12">
                        <x-input type="date" label="Fecha de Ingreso" model="fecha" class="form-control-sm" />
                    </div>
                    <div class="col-md-4 col-6">
                        <div class="form-check form-switch">
                            <label for="check-con_nota_credito" class="form-label">Con nota de crédito</label><br>
                            <input class="form-check-input m-auto" type="checkbox" role="switch"
                                id="check-con_nota_credito" wire:model="con_nota_credito">
                        </div>
                    </div>
                    <div class="col-md-4 col-6">
                        <div class="form-check form-switch">
                            <label for="check-con_diferente_moneda" class="form-label">Con diferente moneda</label><br>
                            <input class="form-check-input m-auto" type="checkbox" role="switch"
                                id="check-con_diferente_moneda" wire:model="con_diferente_moneda">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-secondary small text-uppercase">
                        <tr>
                            @if ($this->con_nota_credito)
                                <th class="py-3 ps-3" style="min-width: 200px;">Nota de Crédito</th>
                                <th class="py-3 text-center" style="width: 160px;">Monto Ingresado NC</th>
                            @endif

                            <th class="py-3 text-center" style="width: 150px;">Pendiente</th>

                            @if ($this->con_diferente_moneda)
                                <th class="py-3 text-center" style="width: 120px;">Moneda</th>
                                <th class="py-3 text-end" style="width: 160px;">Monto Moneda Orig.</th>
                            @endif

                            <th class="py-3 text-end pe-3" style="width: 180px;">Monto a Ingresar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($facturas as $index => $factura)
                            <tr wire:key="row-factura-{{ $index }}">
                                {{-- Bloque dinámico: Nota de Crédito --}}
                                @if ($this->con_nota_credito)
                                    <td class="ps-3">
                                        <x-select2-component-modals class="form-control form-control-sm"
                                            :placeholder="'Seleccione...'" :initAll="true" :options="$factura['notasCredito']"
                                            model="facturas.{{ $index }}.nota_credito_id"
                                            index="{{ $index }}" onChange="updatedNotaCredito" />
                                    </td>
                                    <td class="text-center fw-medium text-secondary">
                                        @if ($factura['nota_credito_id'])
                                            <span
                                                class="text-dark">${{ number_format($factura['total_nota_credito'], 2) }}</span>
                                            <span
                                                class="badge bg-secondary-subtle text-secondary small ms-1">{{ $factura['moneda_referencia'] }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                @endif

                                {{-- Columna Fija: Pendiente --}}
                                <td class="text-center fw-semibold text-dark">
                                    ${{ number_format($factura['limite'], 2) }}
                                    <span class="text-muted small fw-normal">{{ $factura['moneda_referencia'] }}</span>
                                </td>

                                {{-- Bloque dinámico: Diferente Moneda --}}
                                @if ($this->con_diferente_moneda)
                                    <td class="text-center">
                                        <select class="form-select form-select-sm mx-auto" style="max-width: 90px;"
                                            wire:model="facturas.{{ $index }}.moneda">
                                            @foreach ($monedas as $moneda)
                                                <option value="{{ $moneda }}">{{ $moneda }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-inline-block text-end" style="max-width: 140px;">
                                            <x-input type="number" step="0.01"
                                                class="form-control form-control-sm text-end mb-1"
                                                model="facturas.{{ $index }}.monto_moneda_original"
                                                :lazy="true" />
                                            <small
                                                class="text-muted d-block pe-1"><i>${{ number_format(max($facturas[$index]['monto_moneda_original'], 0), 2) }}</i></small>
                                        </div>
                                    </td>
                                @endif

                                {{-- Columna Fija: Monto a Ingresar --}}
                                <td class="text-end pe-3">
                                    <div class="d-inline-block text-end" style="max-width: 160px;">
                                        <x-input type="number" step="0.01"
                                            class="form-control form-control-sm text-end fw-bold mb-1 border-primary-subtle"
                                            model="facturas.{{ $index }}.monto" :lazy="true" />
                                        <small
                                            class="text-muted d-block pe-1"><i>${{ number_format(max($facturas[$index]['monto'], 0), 2) }}</i></small>
                                    </div>
                                </td>
                            </tr>
                        @endforeach

                        {{-- Totales de la Tabla --}}
                        @if ($this->totales['total_usd'] > 0 || $this->totales['total_mxn'] > 0)
                            @php
                                $total_columns =
                                    1 + ($this->con_nota_credito ? 2 : 0) + ($this->con_diferente_moneda ? 2 : 0);
                            @endphp

                            @if ($this->totales['total_usd'] > 0)
                                <tr class="table-light border-top">
                                    <td colspan="{{ $total_columns }}" class="text-end text-muted fw-bold small py-3">
                                        TOTAL USD:</td>
                                    <td class="text-end pe-3 fw-bold text-primary py-3 fs-6">
                                        ${{ number_format($this->totales['total_usd'], 2) }}</td>
                                </tr>
                            @endif

                            @if ($this->totales['total_mxn'] > 0)
                                <tr class="table-light border-top">
                                    <td colspan="{{ $total_columns }}" class="text-end text-muted fw-bold small py-3">
                                        TOTAL MXN:</td>
                                    <td class="text-end pe-3 fw-bold text-success py-3 fs-6">
                                        ${{ number_format($this->totales['total_mxn'], 2) }}</td>
                                </tr>
                            @endif
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <x-textarea label="Comentarios u Observaciones" rows="3" model="comentarios"
                    placeholder="Escriba aquí los detalles o notas adicionales referentes a este ingreso..." />
            </div>
        </div>
    </x-slot:content>

    <x-slot:buttons>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
            wire:click="$emit('show-modal-confirm', '', '', 'facturacion.cuentas-cobrar.ingresar')">
            Cerrar
        </button>
        <button type="submit" class="btn btn-primary">Ingresar</button>
    </x-slot:buttons>
</x-modal>
