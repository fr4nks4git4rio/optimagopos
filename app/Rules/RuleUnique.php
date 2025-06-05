<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RuleUnique implements ValidationRule
{
    protected Collection | array $collection;
    protected string $ignore_column;
    protected mixed $ignore_value;

    /**
     * Create a new rule instance.
     *
     * @param Collection $collection
     * @param $ignore_value
     * @param string $ignore_column
     */
    public function __construct(Collection $collection, $ignore_value = null, string $ignore_column = 'id')
    {
        $this->collection = $collection;
        $this->ignore_column = $ignore_column;
        $this->ignore_value = $ignore_value;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (Str::contains($attribute, '.')) {
            $attribute = explode('.', $attribute);
            $attribute = $attribute[count($attribute) - 1];
        }
        if ($value) {
            $passes = true;

            if ($this->ignore_value) {
                $this->collection = $this->collection->where($this->ignore_column, '!=', $this->ignore_value)->all();
            }

            if (is_array($this->collection))
                $this->collection = collect($this->collection);

            $this->collection->map(function ($element) use ($attribute, $value, &$passes) {
                if (Str::upper($element->{$attribute}) === Str::upper($value))
                    $passes = false;
            });

            if (!$passes) {
                $fail('El/La :attribute ya se encuentra en uso!');
            }
        }
    }
}
