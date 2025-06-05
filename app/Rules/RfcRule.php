<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

class RfcRule implements ValidationRule
{
    protected $tipo_persona;

    /**
     * @param $tipo_persona 'persona_fisica' o 'persona_moral'
     */
    public function __construct($tipo_persona = 'persona_fisica')
    {
        $this->tipo_persona = $tipo_persona;
    }

    /**
     * Run the validation rule.
     *
     * @param \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if($this->tipo_persona == 'ambas')
            $this->tipo_persona = strlen($value) == 12 ? 'persona_moral' : 'persona_fisica';

        if ($this->tipo_persona == 'persona_moral') {
            if (strlen($value) != 12) {
                $fail('Debe tener 12 caracteres');
                return;
            }

            if (is_numeric($value[0]) || is_numeric($value[1]) || is_numeric($value[2])) {
                $fail('Formato incorrecto.');
                return;
            }

            $string = substr($value, 3, 6);
        } else {
            if (strlen($value) != 13) {
                $fail('Debe tener 13 caracteres');
                return;
            }

            if (is_numeric($value[0]) || is_numeric($value[1]) || is_numeric($value[2]) || is_numeric($value[3])) {
                $fail('Formato incorrecto.');
                return;
            }

            $string = substr($value, 4, 6);
        }
        if (!is_numeric($string[0])
            || !is_numeric($string[1])
            || !is_numeric($string[2])
            || !is_numeric($string[3])
            || !is_numeric($string[4])
            || !is_numeric($string[5]))
            $fail('Formato incorrecto.');
    }
}
