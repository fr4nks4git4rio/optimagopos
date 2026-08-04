<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;

class VentasOperadorExport implements FromView
{
    use Exportable;
    private $name;
    private $sorts;
    private $records;
    private $grandTotal;

    public function __construct($name, $sorts, $records, $grandTotal)
    {
        $this->name = $name;
        $this->sorts = $sorts;
        $this->records = $records;
        $this->grandTotal = $grandTotal;
    }

    public function view(): View
    {
        return view('reports.reportes.ventas-operador.excel', [
            'name' => $this->name,
            'sorts' => $this->sorts,
            'records' => $this->records,
            'grandTotal' => $this->grandTotal
        ]);
    }
}
