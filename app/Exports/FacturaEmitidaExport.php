<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;

class FacturaEmitidaExport implements FromView
{
    use Exportable;
    private $facturas;

    public function __construct($facturas)
    {
        $this->facturas = $facturas;
    }

    public function view(): View
    {
        return view('reports.excel.facturas_emitidas', [
            'facturas' => $this->facturas
        ]);
    }
}
