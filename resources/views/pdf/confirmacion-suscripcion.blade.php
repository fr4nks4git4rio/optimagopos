<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Confirmación de Suscripción Contractual</title>
    <style>
        @page {
            margin: 1.5cm;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333333;
            font-size: 12px;
            line-height: 1.5;
        }

        /* Colores corporativos */
        .text-primary {
            color: #0d6efd;
        }

        .text-success {
            color: #198754;
        }

        .text-danger {
            color: #dc3545;
        }

        .text-muted {
            color: #6c757d;
        }

        .bg-light {
            background-color: #f8f9fa;
        }

        .bg-dark {
            background-color: #212529;
            color: #ffffff;
        }

        /* Estructura */
        .w-100 {
            width: 100%;
        }

        .mb-4 {
            margin-bottom: 20px;
        }

        .mb-2 {
            margin-bottom: 10px;
        }

        .mt-4 {
            margin-top: 20px;
        }

        .p-3 {
            padding: 15px;
        }

        .text-end {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        /* Tablas */
        table {
            border-collapse: collapse;
            width: 100%;
        }

        .table-bordered th,
        .table-bordered td {
            border: 1px solid #dee2e6;
            padding: 8px 12px;
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0, 0, 0, 0.02);
        }

        /* Componentes específicos */
        .header-table td {
            vertical-align: top;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            font-size: 10px;
            font-weight: bold;
            border-radius: 4px;
            text-transform: uppercase;
        }

        .badge-success {
            background-color: #d1e7dd;
            color: #0f5132;
        }

        .badge-primary {
            background-color: #cfe2ff;
            color: #084298;
        }

        .badge-warning {
            background-color: #fff3cd;
            color: #664d03;
        }

        .badge-danger {
            background-color: #f8d7da;
            color: #842029;
        }

        .card-resumen {
            border: 1px dashed #6c757d;
            border-radius: 6px;
            padding: 15px;
            background-color: #f8f9fa;
        }

        .total-display {
            font-size: 24px;
            font-weight: 900;
            color: #0d6efd;
            margin-top: 5px;
        }

        .footer-legal {
            font-size: 10px;
            color: #6c757d;
            border-top: 1px solid #dee2e6;
            padding-top: 15px;
            margin-top: 40px;
        }
    </style>
</head>

<body>

    <table class="header-table w-100 mb-4">
        <tr>
            <td>
                <h2 style="margin: 0; font-weight: 900;" class="text-primary">{{ config('app.name') }}</h2>
                <p class="text-muted" style="margin: 5px 0 0 0;">Asignación Contractual de Recursos Digitales</p>
            </td>
            <td class="text-end">
                <h3 style="margin: 0;">CONFIRMACIÓN DE SUSCRIPCIÓN</h3>
                <p style="margin: 5px 0 0 0;">
                    <strong>Folio:</strong>
                    #{{ $id ?? 'A-' . str_pad($cliente_id, 4, '0', STR_PAD_LEFT) }}<br>
                    <strong>Fecha Emisión:</strong> {{ $created_at->format('d/m/Y H:i') }}<br>
                    <strong>Estado:</strong>
                    @php
                        $badgeClass = 'badge-primary';
                        if ($estado === 'ACTIVA') {
                            $badgeClass = 'badge-success';
                        }
                        if ($estado === 'VENCIDA') {
                            $badgeClass = 'badge-warning';
                        }
                        if ($estado === 'INACTIVA') {
                            $badgeClass = 'badge-danger';
                        }
                    @endphp
                    <span class="badge {{ $badgeClass }}" style="margin-bottom: -8px">{{ $estado }}</span>
                </p>
            </td>
        </tr>
    </table>

    <hr style="border: 0; border-top: 1px solid #dee2e6;" class="mb-4">

    <div class="bg-light p-3 mb-4" style="border-radius: 6px; border-left: 4px solid #0d6efd;">
        <h4 style="margin: 0 0 10px 0;" class="text-muted">DATOS DEL TITULAR</h4>
        <table class="w-100">
            <tr>
                <td style="width: 50%;">
                    <strong>Cliente / Razón Social:</strong><br>
                    <span style="font-size: 14px; font-weight: bold;">
                        {{ $cliente }}
                    </span>
                </td>
                <td style="width: 50%;">
                    <strong>RFC:</strong> {{ $rfc }}<br>
                    @if ($es_cliente_fiel)
                        <span class="badge badge-warning" style="margin-top: 5px;">Cliente Fiel Preferencial</span>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <h4 class="text-primary mb-2" style="text-transform: uppercase;">1. Estructura e Infraestructura Contratada</h4>
    <table class="table-bordered w-100 mb-4">
        <thead>
            <tr class="bg-dark">
                <th colspan="2" class="text-center" style="font-size: 11px;">DETALLES DEL PLAN BASE</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="width: 30%;"><strong>Plan / Paquete:</strong></td>
                <td>{{ $paquete ? $paquete->nombre : 'Plan a la Medida (Personalizado)' }}
                </td>
            </tr>
            <tr>
                <td><strong>Descripción:</strong></td>
                <td class="text-muted">
                    {{ $paquete ? $paquete->descripcion ?? 'Estructura operativa estándar.' : 'Estructura diseñada a medida activando módulos y capacidades de manera independiente.' }}
                </td>
            </tr>
        </tbody>
    </table>

    <table class="table-bordered w-100 mb-4 text-center">
        <thead>
            <tr style="background-color: #f1f3f5;">
                <th>Sucursales Autorizadas</th>
                <th>Terminales Base (APOS)</th>
                <th>Usuarios Cloud Permitidos</th>
            </tr>
        </thead>
        <tbody>
            <tr style="font-size: 14px; font-weight: bold;">
                <td>{{ $cant_sucursales }}</td>
                <td>{{ $cant_terminales }}</td>
                <td>{{ $cant_usuarios }}</td>
            </tr>
        </tbody>
    </table>

    <h4 class="text-primary mb-2" style="text-transform: uppercase;">2. Cronograma de Vigencias y Facturación</h4>
    <table class="table-bordered w-100 mb-4">
        <tr style="background-color: #f1f3f5;">
            <td><strong>Inicio de Operaciones:</strong></td>
            <td><strong>Próxima Fecha de Cobro:</strong></td>
            <td><strong>Periodicidad del Servicio:</strong></td>
        </tr>
        <tr>
            <td>{{ \Carbon\Carbon::parse($fecha_inicio_operaciones)->format('d/m/Y') }}</td>
            <td class="text-primary" style="font-weight: bold;">
                {{ $fecha_inicio_pagos ? \Carbon\Carbon::parse($fecha_inicio_pagos)->format('d/m/Y') : '— / — / —' }}
            </td>
            <td style="font-weight: bold;">{{ $periodicidad_pagos }}</td>
        </tr>
    </table>

    <h4 class="text-primary mb-2" style="text-transform: uppercase;">3. Módulos y Ecosistema de Software Activos</h4>
    <table class="table-bordered table-striped w-100 mb-4">
        <thead>
            <tr class="bg-dark">
                <th style="width: 70%;">Nombre del Módulo</th>
                <th style="width: 30%;" class="text-end">Costo en Contrato</th>
            </tr>
        </thead>
        <tbody>
            @php $tieneModulos = false; @endphp
            @foreach ($modulos as $module)
                @php $tieneModulos = true; @endphp
                <tr>
                    <td>
                        <strong>{{ $module->nombre }}</strong>
                        @if ($paquete_id && in_array($module->id, $modulos_paquete))
                            <span class="text-muted"
                                style="font-size: 10px; font-weight: normal; margin-left: 5px;">(Incluido en Plan
                                Base)</span>
                        @endif
                    </td>
                    <td class="text-end text-muted">
                        @if ($paquete_id && in_array($module->id, $modulos_paquete))
                            $0.00
                        @else
                            +${{ number_format($module->costo_base, 2) }}
                        @endif
                    </td>
                </tr>
            @endforeach

            @if (!$tieneModulos)
                <tr>
                    <td colspan="2" class="text-center text-muted">Ningún módulo adicional aprovisionado en este
                        ciclo.</td>
                </tr>
            @endif
        </tbody>
    </table>

    <table class="w-100 mt-4" style="page-break-inside: avoid;">
        <tr>
            <td style="width: 45%;">
                <div class="card-resumen">
                    <strong>Ciclo de Facturación:</strong><br>
                    <span style="text-transform: uppercase; font-weight: bold;">{{ $periodicidad_pagos }}</span>
                    <br><br>
                    <strong>Términos de Pago:</strong><br>
                    <span class="text-muted">Pago anticipado los primeros 5 días hábiles de la fecha de cobro
                        indicada.</span>
                </div>
            </td>
            <td style="width: 10%;"></td>
            <td style="width: 45%;">
                <table class="w-100" style="font-size: 13px;">
                    <tr>
                        <td class="text-muted" style="padding: 4px 0;">Base Cloud (Plan):</td>
                        <td class="text-end" style="padding: 4px 0;">${{ number_format($precio_paquete, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted" style="padding: 4px 0;">Módulos Extra / Custom:</td>
                        <td class="text-end text-danger" style="padding: 4px 0;">+
                            ${{ number_format($precio_extra, 2) }}</td>
                    </tr>
                    @if ($descuento > 0)
                        <tr>
                            <td class="text-success" style="padding: 4px 0;">
                                Descuento Aplicado ({{ number_format($porcentaje_descuento, 1) }}%):
                            </td>
                            <td class="text-end text-success" style="padding: 4px 0;">-
                                ${{ number_format($descuento, 2) }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td colspan="2">
                            <hr style="border: 0; border-top: 1px dashed #6c757d; margin: 5px 0;">
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; padding: 6px 0;">Total Neto Recurrente:</td>
                        <td class="text-end" style="padding: 6px 0;">
                            <div class="total-display">${{ number_format($precio_total, 2) }}</div>
                            <small class="text-muted"
                                style="font-size: 9px; font-weight: normal; text-transform: uppercase;">
                                {{ $moneda }} / Neto
                            </small>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="footer-legal">
        <p style="margin: 0 0 5px 0; font-weight: bold;">Términos y Condiciones del Aprovisionamiento Cloud:</p>
        <p style="margin: 0; text-align: justify;">
            El inicio de operaciones y entrega de credenciales maestras está sujeto a la validación de este documento y,
            en su caso, al cobro del primer periodo recurrente. Las ampliaciones de capacidad (sucursales, terminales o
            usuarios) solicitadas con posterioridad se añadirán de manera prorrateada al ciclo vigente. Al hacer uso del
            entorno digital aprovisionado, el cliente manifiesta su conformidad con las capacidades técnicas y los
            módulos enumerados en la presente confirmación de suscripción.
        </p>
        <table class="w-100" style="margin-top: 40px; text-align: center;">
            <tr>
                <td style="width: 45%; border-top: 1px solid #6c757d; padding-top: 5px; font-size: 11px;">
                    Sello Digital de Validación<br>
                    <span
                        style="font-family: monospace; font-size: 9px; color: #999;">UUID-{{ md5($cliente_id . $precio_total) }}</span>
                </td>
                <td style="width: 10%;"></td>
                <td style="width: 45%; border-top: 1px solid #6c757d; padding-top: 5px; font-size: 11px;">
                    Firma de Conformidad del Cliente<br>
                    <span style="font-size: 9px; color: #999;">Aceptado vía plataforma digital</span>
                </td>
            </tr>
        </table>
    </div>

</body>

</html>
