<?php
/*
 *  Last Modified: 6/28/21, 11:18 PM
 *  Copyright (c) 2021
 *  -created by Ariful Islam
 *  -All Rights Preserved By
 *  -If you have any query then knock me at
 *  arif98741@gmail.com
 *  See my profile @ https://github.com/arif98741
 */

namespace Xenon\LaravelBDSms\Handler;

/**
 * Class XenonException
 * @package Xenon\LaravelBDSms\Handler
 * @version v1.0.10
 * @since v1.0.10
 * @deprecated
 */
class XenonException extends \Exception
{
    /**
     * @var
     */
    protected $message;
    /**
     * @var
     */
    protected $code;

    /**
     * XenonException constructor.
     * @param $message
     * @param null $code
     */
    public function __construct($message, $code = null)
    {
        parent::__construct($message, $code);
    }

    /**
     * @param null $object
     * @return array
     */
    public function showException($object = null): array
    {
        $bt = debug_backtrace();
        $exception = [
            'exception' => $this->getMessage(),
            'used_file' => [
                'file' => $bt[0]['file'] . ' at line: ' . $bt[0]['line'],
                'class' => $bt[1]['class'],
                'method' => $bt[1]['function'] . '()',
                'called by' => $this->getCaller()
            ]
        ];
        return $exception;
    }

    /**
     * Gets the caller of the function where this function is called from
     * @param string what to return? (Leave empty to get all, or specify: "class", "function", "line", "class", etc.) -
     * options see: http://php.net/manual/en/function.debug-backtrace.php
     * @return mixed
     */
    private function getCaller($what = NULL)
    {
        $trace = debug_backtrace();
        $previousCall = $trace[2]; // 0 is this call, 1 is call in previous function, 2 is caller of that function

        if (isset($what)) {
            return $previousCall[$what];
        } else {
            return $previousCall;
        }
    }

}
