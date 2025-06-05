<?php

namespace App\Rules;

use App\Models\Ventas\Empresa;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class DataClienteRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $client = Empresa::find($value);

        if($client->razon_social == null || $client->rfc == null){
            $fail('La Razón Social y el RFC son requeridos!');
        }
    }
}
