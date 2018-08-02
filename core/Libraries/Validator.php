<?php

namespace Core\Libraries;

use Core\Libraries\Assertions;

class Validator
{
    private static $errors = array();
    private static $request = array();

    public static function validate($rules)
    {
        if (count(self::$request) === 0) {
            $request = \Core\Loader::load('Requests');
            self::$request = $request->request();
        }

        foreach ($rules as $rules_key => $rules_value) {
            $rule = explode('|', $rules_value);
            
            foreach ($rule as $rule_key => $rule_value) {
                if (strpos($rule_value, ':') !== false) {
                    $rule_key_value = explode(':', $rule_value);

                    $rule_key = $rule_key_value[0];
                    $rule_value = $rule_key_value[1];

                    if (Assertions::$rule_key(self::$request->get($rules_key), $rule_value) === false) {
                        self::$errors[$rules_key] = $rule_key;
                    }
                } else {
                    if (Assertions::$rule_value(self::$request->get($rules_key)) === false) {
                        self::$errors[$rules_key] = $rule_value;
                    }
                }
            }
        }

        if (count(self::$errors) > 0) {
            return self::$errors;
        } else {
            return true;
        }
    }
}