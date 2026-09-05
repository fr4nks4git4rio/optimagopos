<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;

class VentasTotalesDepartamentoExport implements FromView
{
    use Exportable;
    private $name;
    private $sorts;
    private $records;
    private $grandTotal;
    private $fechaInicio;
    private $fechaFin;
    private $sucursalesSeleccionadas;

    public function __construct($name, $sorts, $records, $grandTotal, $fechaInicio, $fechaFin, $sucursalesSeleccionadas)
    {
        $this->name = $name;
        $this->sorts = $sorts;
        $this->records = $records;
        $this->grandTotal = $grandTotal;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
        $this->sucursalesSeleccionadas = $sucursalesSeleccionadas;
    }

    public function view(): View
    {
        return view('reports.reportes.ventas-totales-departamento.excel', [
            'name' => $this->name,
            'sorts' => $this->sorts,
            'records' => $this->records,
            'grandTotal' => $this->grandTotal,
            'fechaInicio' => $this->fechaInicio,
            'fechaFin' => $this->fechaFin,
            'sucursalesSeleccionadas' => $this->sucursalesSeleccionadas,
        ]);
    }
}
