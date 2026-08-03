<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;

class ArticulosVendidosExport implements FromView
{
    use Exportable;
    private $name;
    private $sorts;
    private $records;
    private $sucursales;
    private $grandTotal;

    public function __construct($name, $sorts, $records, $sucursales, $grandTotal)
    {
        $this->name = $name;
        $this->sorts = $sorts;
        $this->records = $records;
        $this->sucursales = $sucursales;
        $this->grandTotal = $grandTotal;
    }

    public function view(): View
    {
        return view('reports.reportes.articulos-vendidos.excel', [
            'name' => $this->name,
            'sorts' => $this->sorts,
            'records' => $this->records,
            'sucursales' => $this->sucursales,
            'grandTotal' => $this->grandTotal
        ]);
    }
}
