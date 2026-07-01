<x-modal form-action="cancel">
    <x-slot:title>
        Detalles {{$this->tipo}}: <strong>{{$factura->folio_interno}}</strong>
    </x-slot:title>

    <x-slot:content>
        <table class="table table-striped table-responsive">
            <thead>
            <tr>
                <th class="text-center">Fecha de Pago</th>
                <th class="text-center">Moneda</th>
                <th class="text-center">Importe</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $total = 0;
            ?>
            @foreach($pagos as $index => $pago)
                    <?php
                    $total += $pago['monto'];
                    ?>
                <tr>
                    <td class="text-center">{{$pago['fecha_str']}}</td>
                    <td class="text-center">{{$factura->moneda}}</td>
                    <td class="text-center">{{number_format($pago['monto'], 2)}}</td>
                </tr>
            @endforeach
            @if(count($pagos) > 0)
                <tr>
                    <td colspan="2" class="fw-bold text-end">Total:</td>
                    <td class="fw-bold text-center">{{number_format($total, 2)}}</td>
                </tr>
            @endif
            </tbody>
        </table>
    </x-slot:content>

    <x-slot:buttons>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="$emit('closeModal')">
            Cerrar
        </button>
    </x-slot:buttons>
</x-modal>
