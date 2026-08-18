<table>
    <thead>
    <tr>
        <td colspan="6" style="font-weight: bold; font-size: 16px">{{$name}}</td>
    </tr>
    <tr></tr>
    <tr>
        <th style="text-align: center">{{__('site.reports.income.date')}}</th>
        <th style="text-align: center">{{__('site.reports.income.internal_folio')}}</th>
        <th>{{__('site.reports.income.client')}}</th>
        <th style="text-align: center">{{__('site.reports.income.uuid')}}</th>
        <th style="text-align: center">{{__('site.reports.income.currency')}}</th>
        <th style="text-align: center">{{__('site.reports.income.import')}}</th>
    </tr>
    </thead>
    <tbody>
    @if($ingresos->isEmpty())
        <tr>
            <td colspan="6">
                <div style="text-align: center">{{__('site.common.results_not_found')}}...</div>
            </td>
        </tr>
    @else
            <?php
            $total_total_usd = 0;
            $total_total_mxn = 0;
            ?>
        @foreach($ingresos as $ingreso)
                <?php
                if ($ingreso->moneda === 'USD') {
                    $total_total_usd += $ingreso->monto;
                } elseif ($ingreso->moneda === 'MXN') {
                    $total_total_mxn += $ingreso->monto;
                }
                ?>
            <tr>
                <td style="text-align: center">{!! $ingreso->fecha_str !!}</td>
                <td style="text-align: center">{!! $ingreso->folio_interno !!}</td>
                <td>{!! $ingreso->razon_social !!}</td>
                <td style="text-align: center">{!! $ingreso->uuid !!}</td>
                <td style="text-align: center">{!! $ingreso->moneda !!}</td>
                <td style="text-align: center">{!! number_format($ingreso->monto, 2) !!}</td>
            </tr>
        @endforeach
        <tr>
            <td colspan="3" rowspan="2" style="vertical-align: middle; text-align: center; font-weight: bold">
                {{__('site.reports.income.total')}}:
            </td>
            <td style="text-align: right; font-weight: bold">
                MXN:
            </td>
            <td style="text-align: center; font-weight: bold">
                {{number_format($total_total_mxn, 2)}}
            </td>
        </tr>
        <tr style="background: aliceblue">
            <td style="text-align: right; font-weight: bold">
                USD:
            </td>
            <td style="text-align: center; font-weight: bold">
                {{number_format($total_total_usd, 2)}}
            </td>
        </tr>
    @endif
    </tbody>
</table>
