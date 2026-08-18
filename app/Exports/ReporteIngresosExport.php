<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;

class ReporteIngresosExport implements FromView
{
    use Exportable;
    private $name;
    private $ingresos;

    public function __construct($name, $ingresos)
    {
        $this->name = $name;
        $this->ingresos = $ingresos;
    }

    public function view(): View
    {
        return view('reports.excel.ingresos', [
            'name' => $this->name,
            'ingresos' => $this->ingresos,
        ]);
    }
}
