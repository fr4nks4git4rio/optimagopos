<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class RfcYRegimenCoherentesRule implements ValidationRule
{
    private $rfc;

    private const REGIMENES_FISICAS = [
        '605',
        '606',
        '607',
        '611',
        '612',
        '614',
        '615',
        '616',
        '625',
        '626'
    ];

    private const REGIMENES_MORALES = [
        '601',
        '603',
        '610',
        '620',
        '622',
        '623',
        '624',
        '628',
        '629',
        '630'
    ];

    private $message = 'El RFC y el régimen fiscal no son coherentes.';

    public function __construct($rfc)
    {
        $this->rfc = $rfc;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $regimen = DB::table('tb_regimen_fiscales')
            ->select('codigo')
            ->where('id', $value)
            ->get()->first();
        $value = $regimen ? $regimen->codigo : '';
        $isFisica = preg_match('/^[A-ZÑ&]{4}\d{6}[A-Z0-9]{3}$/i', $this->rfc);
        $isMoral  = preg_match('/^[A-ZÑ&]{3}\d{6}[A-Z0-9]{3}$/i', $this->rfc);

        if (!$isFisica && !$isMoral)
            $fail($this->message);

        if ($isFisica && !in_array($value, self::REGIMENES_FISICAS)) {
            $fail($this->message);
        }

        if ($isMoral && !in_array($value, self::REGIMENES_MORALES)) {
            $fail($this->message);
        }
    }
}
