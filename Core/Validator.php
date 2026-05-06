<?php

namespace Core;

class Validator
{
    private static $errors = [];

    public static function validate($data, $rules)
    {
        self::$errors = [];
        
        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;
            
            foreach (explode('|', $fieldRules) as $rule) {
                if (strpos($rule, ':') !== false) {
                    [$ruleName, $ruleParam] = explode(':', $rule);
                } else {
                    $ruleName = $rule;
                    $ruleParam = null;
                }

                self::applyRule($field, $value, $ruleName, $ruleParam);
            }
        }

        return empty(self::$errors);
    }

    private static function applyRule($field, $value, $rule, $param = null)
    {
        switch ($rule) {
            case 'required':
                if (empty($value)) {
                    self::$errors[$field] = "{$field} es requerido.";
                }
                break;
            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    self::$errors[$field] = "{$field} debe ser un correo válido.";
                }
                break;
            case 'numeric':
                if (!empty($value) && !is_numeric($value)) {
                    self::$errors[$field] = "{$field} debe ser numérico.";
                }
                break;
            case 'integer':
                if (!empty($value) && filter_var($value, FILTER_VALIDATE_INT) === false) {
                    self::$errors[$field] = "{$field} debe ser un entero.";
                }
                break;
            case 'min':
                if (!empty($value) && is_numeric($value) && (float)$value < (float)$param) {
                    self::$errors[$field] = "{$field} debe ser al menos {$param}.";
                } elseif (!empty($value) && !is_numeric($value) && strlen((string)$value) < (int)$param) {
                    self::$errors[$field] = "{$field} debe tener al menos {$param} caracteres.";
                }
                break;
            case 'max':
                if (!empty($value) && strlen((string)$value) > (int)$param) {
                    self::$errors[$field] = "{$field} no puede exceder {$param} caracteres.";
                }
                break;
            case 'unique':
                // Skipped in validator; handled at model level
                break;
        }
    }

    public static function errors()
    {
        return self::$errors;
    }

    public static function fails()
    {
        return !empty(self::$errors);
    }

    public static function getError($field)
    {
        return self::$errors[$field] ?? null;
    }
}
