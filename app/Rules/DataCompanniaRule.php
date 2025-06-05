<?php

namespace App\Rules;

use App\Models\Ventas\Empresa;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class DataCompanniaRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $company = Empresa::find($value);

        if($company->razon_social == null || $company->rfc == null){
            $fail('La Razón Social y el RFC son requeridos!');
        }
    }
}
