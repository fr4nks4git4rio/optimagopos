<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;

class HistoricoTicketsVkExport implements FromView
{
    use Exportable;
    private $name;
    private $sorts;
    private $records;
    private $totalGeneral;
    private $fechaInicio;
    private $fechaFin;
    private $estadosSeleccionados;
    private $sucursalesSeleccionadas;
    private $terminalesSeleccionadas;

    public function __construct($name, $sorts, $records, $totalGeneral, $fechaInicio, $fechaFin, $estadosSeleccionados, $sucursalesSeleccionadas, $terminalesSeleccionadas)
    {
        $this->name = $name;
        $this->sorts = $sorts;
        $this->records = $records;
        $this->totalGeneral = $totalGeneral;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
        $this->estadosSeleccionados = $estadosSeleccionados;
        $this->sucursalesSeleccionadas = $sucursalesSeleccionadas;
        $this->terminalesSeleccionadas = $terminalesSeleccionadas;
    }

    public function view(): View
    {
        return view('reports.reportes.historico-tickets-vk.excel', [
            'name' => $this->name,
            'sorts' => $this->sorts,
            'records' => $this->records,
            'totalGeneral' => $this->totalGeneral,
            'fechaInicio' => $this->fechaInicio,
            'fechaFin' => $this->fechaFin,
            'estadosSeleccionados' => $this->estadosSeleccionados,
            'sucursalesSeleccionadas' => $this->sucursalesSeleccionadas,
            'terminalesSeleccionadas' => $this->terminalesSeleccionadas,
        ]);
    }
}
