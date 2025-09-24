<?php

namespace Ondine;

class Validation
{
    // Validate $data according to $rules where rules is ['field' => ['required','min:3','int',...]]
    public static function validate(array $data, array $rules): array
    {
        $errors = [];
        foreach ($rules as $field => $checks) {
            $value = $data[$field] ?? null;
            foreach ($checks as $check) {
                if ($check === 'required') {
                    if ($value === null || $value === '') {
                        $errors[$field] = $errors[$field] ?? [];
                        $errors[$field][] = 'required';
                    }
                    continue;
                }
                if ($value === null || $value === '') {
                    continue; // skip other checks when not present
                }

                if (strpos($check, 'min:') === 0) {
                    $n = (int)substr($check, 4);
                    if (mb_strlen((string)$value) < $n) {
                        $errors[$field] = $errors[$field] ?? [];
                        $errors[$field][] = "min:$n";
                    }
                    continue;
                }
                if (strpos($check, 'max:') === 0) {
                    $n = (int)substr($check, 4);
                    if (mb_strlen((string)$value) > $n) {
                        $errors[$field] = $errors[$field] ?? [];
                        $errors[$field][] = "max:$n";
                    }
                    continue;
                }
                if ($check === 'int') {
                    if (!filter_var($value, FILTER_VALIDATE_INT) && !is_int($value)) {
                        $errors[$field] = $errors[$field] ?? [];
                        $errors[$field][] = 'int';
                    }
                    continue;
                }
                if (strpos($check, 'in:') === 0) {
                    $opts = explode(',', substr($check, 3));
                    if (!in_array((string)$value, $opts, true)) {
                        $errors[$field] = $errors[$field] ?? [];
                        $errors[$field][] = 'in:' . implode(',', $opts);
                    }
                    continue;
                }
                if ($check === 'json') {
                    json_decode($value);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $errors[$field] = $errors[$field] ?? [];
                        $errors[$field][] = 'json';
                    }
                    continue;
                }
            }
        }
        return $errors;
    }

    public static function sanitize(array $data, array $rules): array
    {
        $out = $data;
        foreach ($rules as $field => $checks) {
            if (!isset($out[$field])) {
                continue;
            }
            if (is_string($out[$field])) {
                $s = trim($out[$field]);
                // allow limited basic tag stripping
                $s = strip_tags($s);
                // cap length if max provided
                foreach ($checks as $c) {
                    if (strpos($c, 'max:') === 0) {
                        $n = (int)substr($c, 4);
                        if (mb_strlen($s) > $n) {
                            $s = mb_substr($s, 0, $n);
                        }
                    }
                }
                $out[$field] = $s;
            }
        }
        return $out;
    }
}
