<?php

namespace Xenon\LaravelBDSms\Helper;

class Helper
{
    /**
     * Mobile Number Validation
     * @param $number
     * @return bool
     * @since v1.0.12
     */
    public static function numberValidation($number)
    {
        $validCheckPattern = "/^(?:\+88|01)?(?:\d{11}|\d{13})$/";
        if (preg_match($validCheckPattern, $number)) {
            if (preg_match('/^(?:01)\d+$/', $number)) {
                $number = '+88' . $number;
            }

            return true;
        }

        return false;
    }
}
