<?php

declare(strict_types=1);

namespace App\Core;

final class Validator
{
    public static function make(array $rules, array $input): array
    {
        $errors = [];
        $data = [];

        foreach ($rules as $field => $ruleStr) {
            $value = $input[$field] ?? null;
            if (is_string($value)) {
                $value = trim($value);
            }
            $data[$field] = $value;
            $ruleList = is_array($ruleStr) ? $ruleStr : explode('|', (string) $ruleStr);

            foreach ($ruleList as $rule) {
                $name = $rule;
                $param = null;
                if (str_contains($rule, ':')) {
                    [$name, $param] = explode(':', $rule, 2);
                }
                $fail = false;
                $message = '';

                switch ($name) {
                    case 'required':
                        $fail = $value === null || $value === '';
                        $message = 'This field is required.';
                        break;
                    case 'email':
                        $fail = $value !== '' && $value !== null && !filter_var((string) $value, FILTER_VALIDATE_EMAIL);
                        $message = 'Enter a valid email address.';
                        break;
                    case 'min':
                        $fail = is_string($value) && mb_strlen($value) < (int) $param;
                        $message = 'Please enter at least ' . $param . ' characters.';
                        break;
                    case 'max':
                        $fail = is_string($value) && mb_strlen($value) > (int) $param;
                        $message = 'Please keep this under ' . $param . ' characters.';
                        break;
                    case 'phone':
                        $fail = $value !== '' && $value !== null && !preg_match('/^[0-9+\-\s()]{7,20}$/', (string) $value);
                        $message = 'Enter a valid phone number.';
                        break;
                    case 'in':
                        $allowed = explode(',', (string) $param);
                        $fail = $value !== null && $value !== '' && !in_array((string) $value, $allowed, true);
                        $message = 'Please choose a valid option.';
                        break;
                    case 'slug':
                        $fail = $value !== '' && $value !== null && !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', (string) $value);
                        $message = 'Use lowercase letters, numbers, and hyphens only.';
                        break;
                }

                if ($fail) {
                    $errors[$field] = $message;
                    break;
                }
            }
        }

        return ['data' => $data, 'errors' => $errors, 'ok' => $errors === []];
    }
}
