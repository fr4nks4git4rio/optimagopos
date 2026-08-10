<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;

class VentasDiariasExport implements FromView
{
    use Exportable;
    private $name;
    private $sorts;
    private $records;
    private $formasPago;
    private $grandTotal;
    private $fechaInicio;
    private $fechaFin;
    private $sucursalesSeleccionadas;

    public function __construct($name, $sorts, $records, $formasPago, $grandTotal, $fechaInicio, $fechaFin, $sucursalesSeleccionadas)
    {
        $this->name = $name;
        $this->sorts = $sorts;
        $this->records = $records;
        $this->formasPago = $formasPago;
        $this->grandTotal = $grandTotal;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
        $this->sucursalesSeleccionadas = $sucursalesSeleccionadas;
    }

    public function view(): View
    {
        return view('reports.reportes.ventas-diarias.excel', [
            'name' => $this->name,
            'sorts' => $this->sorts,
            'records' => $this->records,
            'formasPago' => $this->formasPago,
            'grandTotal' => $this->grandTotal,
            'fechaInicio' => $this->fechaInicio,
            'fechaFin' => $this->fechaFin,
            'sucursalesSeleccionadas' => $this->sucursalesSeleccionadas,
        ]);
    }
}
