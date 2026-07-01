<?php

namespace App\Rules;

use App\Models\Facturacion\Factura;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class IngresoNotaCreditoRule implements ValidationRule
{
    public $item;

    /**
     * @param $nota_credito_id
     */
    public function __construct($item)
    {
        $this->item = $item;
    }


    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // if($this->item['nota_credito_id']){
        //     if($value != $this->item['limite']){
        //         $fail("El monto debe cubrir el total ({$this->item['limite']}) de la Nota de Crédito!");
        //     }
        // }else{
        if ($value > $this->item['limite'])
            $fail("El monto no puede ser superior a: {$this->item['limite']}!");
        // }
    }
}
