<?php

namespace Core\Libraries;

use Respect\Validation\Validator as Validator;
use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\DNSCheckValidation;
use Egulias\EmailValidator\Validation\MultipleValidationWithAnd;
use Egulias\EmailValidator\Validation\RFCValidation;

class Assertions
{
    public static function alpha($value)
    {
        return Validator::alpha()->validate($value);
    }

    public static function alphanum($value)
    {
        return Validator::alnum()->validate($value); 
    }

    public static function array($value)
    {
        return Validator::arrayType()->validate($value);
    }

    public static function email($value)
    {
        $validator = new EmailValidator();
        $multipleValidations = new MultipleValidationWithAnd([
            new RFCValidation(),
            new DNSCheckValidation()
        ]);

        return $validator->isValid($value, $multipleValidations);
    }

    public static function integer($value)
    {
        return Validator::intType()->validate($value);
    }

    public static function max($value, $max)
    {
        $strlen = mb_strlen($value);

        return Validator::intVal()->min($strlen, true)->validate($max);
    }

    public static function min($value, $min)
    {
        $strlen = mb_strlen($value);

        return Validator::intVal()->max($strlen, true)->validate($min);
    }

    public static function numeric($value)
    {
        return Validator::digit()->validate($value);
    }

    public static function required($value)
    {
        return Validator::notEmpty()->validate($value);
    }
}