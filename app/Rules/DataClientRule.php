<?php

namespace App\Rules;

use App\Models\Cliente;
use App\Models\Directorio\Empresa;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class DataClientRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $client = Cliente::find($value);

        if ($client->razon_social == null || $client->rfc == null) {
            $fail('La Razón Social y el RFC son requeridos!');
        }
    }
}
