@section('title', 'Cabecera Facturas')

<div wire:init="init">
    <h1 class="fs-1 mb-2">@yield('title')</h1>

    <ul class="nav nav-tabs" id="myTab" role="tablist">
        @foreach ($sucursales as $index => $sucursal)
            <li class="nav-item" role="presentation">
                <button wire:ignore.self class="nav-link @if ($index == 0) active @endif"
                    id="sucursal-{{ $index }}-tab" data-bs-toggle="tab"
                    data-bs-target="#sucursal-{{ $index }}-tab-pane" type="button" role="tab"
                    aria-controls="sucursal-{{ $index }}-tab-pane"
                    aria-selected="true">{{ $sucursal['nombre_comercial'] }}</button>
            </li>
        @endforeach
    </ul>
    <div class="tab-content" id="myTabContent">
        @foreach ($sucursales as $index => $sucursal)
            <div wire:ignore.self class="tab-pane fade @if ($index == 0) show active @endif"
                id="sucursal-{{ $index }}-tab-pane" role="tabpanel"
                aria-labelledby="sucursal-{{ $index }}-tab" tabindex="0">
                <div class="row py-2">
                    <div class="col-12 col-md-8">
                        <div class="card mb-3">
                            <div class="card-header">
                                <h3 class="fs-5 fw-bold">Datos Generales</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-sm-5">
                                        <x-input label="Nombre Comercial" type="text"
                                            model="sucursales.{{ $index }}.nombre_comercial" />
                                    </div>
                                    <div class="col-sm-5">
                                        <x-input label="Razón Social" type="text"
                                            model="sucursales.{{ $index }}.razon_social" />
                                    </div>
                                    <div class="col-sm-2">
                                        <x-input label="RFC" type="text"
                                            model="sucursales.{{ $index }}.rfc" class="text-uppercase" />
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-4">
                                        <x-select2 :label="'Régimen Fiscal'" :placeholder="'Seleccione...'" :options="$regimenesFiscales"
                                            model="sucursales.{{ $index }}.regimen_fiscal_id"
                                            class="form-control" />
                                    </div>
                                    <div class="col-sm-4">
                                        <x-input label="Correo" type="email"
                                            model="sucursales.{{ $index }}.correo" />
                                    </div>
                                    <div class="col-sm-2">
                                        <x-input label="Teléfono" type="text"
                                            model="sucursales.{{ $index }}.telefono" />
                                    </div>
                                    <div class="col-sm-2">
                                        <x-select2 :label="'Moneda facturación'" :placeholder="'Seleccione...'" :options="$monedas"
                                            model="sucursales.{{ $index }}.moneda_facturacion_id"
                                            class="form-control" :dynamic="true" />
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="mb-1">
                                            <label for="">Portal del PAC</label>
                                            <textarea rows="2" class="form-control @error("sucursales.$index.portal_pac") is-invalid @enderror"
                                                wire:model="sucursales.{{ $index }}.portal_pac"></textarea>
                                            @error("sucursales.$index.portal_pac")
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <x-input label="Usuario Integrador SAT" type="text"
                                            model="sucursales.{{ $index }}.usuario_integrador_sat" />
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="text-center">
                                    <button class="btn btn-primary w-auto float-end"
                                        wire:click="saveDatosGenerales('{{ $index }}')">Guardar
                                        Datos
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header">
                                <h3 class="fs-5 fw-bold">Dirección Fiscal</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-sm-3">
                                        <x-input label="Calle"
                                            model="sucursales.{{ $index }}.direccion.calle" />
                                    </div>
                                    <div class="col-sm-3">
                                        <x-input label="No. Exterior"
                                            model="sucursales.{{ $index }}.direccion.no_exterior" />
                                    </div>
                                    <div class="col-sm-3">
                                        <x-input label="No. Interior"
                                            model="sucursales.{{ $index }}.direccion.no_interior" />
                                    </div>
                                    <div class="col-sm-3">
                                        <x-input label="Código Postal"
                                            model="sucursales.{{ $index }}.direccion.codigo_postal" />
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-3">
                                        <x-input label="Colonia"
                                            model="sucursales.{{ $index }}.direccion.colonia" />
                                    </div>
                                    <div class="col-sm-3">
                                        <x-select2-ajax label="Estado" placeholder="Seleccione..." class="form-control"
                                            url="{{ route('estados.load-estados') }}"
                                            model="sucursales.{{ $index }}.direccion.estado_id"
                                            :dynamic="true" />
                                    </div>
                                    <div class="col-sm-3">
                                        <x-select2-ajax label="Localidad" placeholder="Seleccione..."
                                            class="form-control"
                                            url="{{ route('localidades.load-localidades', ['estado_id' => $sucursales[$index]['direccion']['estado_id']]) }}"
                                            model="sucursales.{{ $index }}.direccion.localidad_id"
                                            :dynamic="true" />
                                    </div>
                                    <div class="col-sm-3">
                                        <x-select2-ajax label="Municipio" placeholder="Seleccione..."
                                            class="form-control"
                                            url="{{ route('municipios.load-municipios', ['estado_id' => $sucursales[$index]['direccion']['estado_id']]) }}"
                                            model="sucursales.{{ $index }}.direccion.municipio_id"
                                            :dynamic="true" />
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <x-input label="Referencia"
                                            model="sucursales.{{ $index }}.direccion.referencia" />
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="text-center">
                                    <button class="btn btn-primary w-auto float-end"
                                        wire:click="saveDireccion('{{ $index }}')">Guardar
                                        Datos
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="fs-5 fw-bold">Formas de Pago Facturación</h3>
                            </div>
                            <div class="card-body table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Forma de Pago SAT</th>
                                            <th class="text-center">Moneda</th>
                                            <th class="text-center">Activa</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($sucursal['formas_pago'] as $index_fp => $forma_pago)
                                            <tr>
                                                <td>{{ $forma_pago['nombre'] }}</td>
                                                <td>
                                                    <x-select2 class="form-control"
                                                        model="sucursales.{{ $index }}.formas_pago.{{ $index_fp }}.forma_pago_id"
                                                        :options="$formasPagoOptions" :dynamic="true" />
                                                </td>
                                                <td class="text-center">
                                                    <span
                                                        class="badge bg-primary-subtle text-primary">{{ $forma_pago['moneda'] }}</span>
                                                </td>
                                                <td class="text-center">
                                                    @if ($forma_pago['deleted_at'])
                                                        <span class="text-danger">NO</span>
                                                    @else
                                                        <span class="text-success">SI</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td class="text-center" colspan="5">
                                                    Sin datos que mostrar
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
