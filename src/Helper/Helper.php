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

    /**
     * @param array $numbers
     * @return string
     * @since v1.0.12
     * @version v1.0.43.1-dev
     */
    public static function getCommaSeperatedNumbers(array $numbers)
    {
        return implode(',', $numbers);
    }

    /**
     * @param string $mobile
     * @return string
     * @since v1.0.12
     * @version v1.0.43.1-dev
     */
    public static function checkMobileNumberPrefixExistence($mobile)
    {
        $prefix = substr($mobile, 0, 3);
        if ($prefix === '880') {
            return $mobile;
        }
        return '88' . $mobile;
    }
}
