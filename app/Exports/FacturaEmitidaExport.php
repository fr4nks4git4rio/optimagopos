<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;

class FacturaEmitidaExport implements FromView
{
    use Exportable;
    private $name;
    private $facturas;

    public function __construct($name, $facturas)
    {
        $this->name = $name;
        $this->facturas = $facturas;
    }

    public function view(): View
    {
        return view('reports.excel.facturas_emitidas', [
            'name' => $this->name,
            'facturas' => $this->facturas,
        ]);
    }
}
