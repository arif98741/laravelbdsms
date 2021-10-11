<?php

namespace Xenon\LaravelBDSms\Helper;

class Helper
{
    /**
     * Mobile Number Validation
     * @param $number
     * @return bool
     * @since v1.0.12
     * @version v1.0.12
     */
    public static function numberValidation($number): bool
    {
        $validCheckPattern = "/^(?:\+88|01)?(?:\d{11}|\d{13})$/";
        if (preg_match($validCheckPattern, $number)) {
            return true;
        }

        return false;
    }
}
