<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

class RfcRule implements ValidationRule
{
    protected $tipo_persona;
    private $message = "Formato incorrecto.";

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
        $isFisica = preg_match('/^[A-ZÑ&]{4}\d{6}[A-Z0-9]{3}$/i', $value);
        $isMoral  = preg_match('/^[A-ZÑ&]{3}\d{6}[A-Z0-9]{3}$/i', $value);

        if ($this->tipo_persona == 'persona_fisica' && !$isFisica)
            $fail($this->message);
        if ($this->tipo_persona == 'persona_moral' && !$isMoral)
            $fail($this->message);
        if (!$isFisica && !$isMoral)
            $fail($this->message);
    }
}
