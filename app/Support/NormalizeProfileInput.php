<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NormalizeProfileInput
{
    public static function apply(Request $request): void
    {
        // Leave arrays and other invalid types intact for the validator.
        foreach (['name', 'nim', 'no_telp', 'email'] as $field) {
            $value = $request->input($field);
            if (! is_string($value)) {
                continue;
            }

            $request->merge([$field => match ($field) {
                'name' => Str::squish($value),
                'no_telp' => preg_replace('/\s+/', '', $value),
                'email' => Str::lower(trim($value)),
                default => trim($value),
            }]);
        }
    }
}
