<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;

class ReporteIngresosExport implements FromView
{
    use Exportable;
    private $ingresos;

    public function __construct($ingresos)
    {
        $this->ingresos = $ingresos;
    }

    public function view(): View
    {
        return view('reports.excel.ingresos', [
            'ingresos' => $this->ingresos
        ]);
    }
}
