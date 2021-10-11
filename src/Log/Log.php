<?php

namespace Xenon\LaravelBDSms\Log;

use Xenon\LaravelBDSms\Models\LaravelBDSmsLog;

class Log
{
    /**
     * Add New Log to Model
     */
    public function createLog(array $data)
    {
        LaravelBDSmsLog::create($data);
        //todo:: crate log data in table
    }

    public function viewLastLog()
    {
        //todo:: view last log
    }

    public function viewAllLog()
    {
        //todo:: view all logs
    }

    public function clearLog()
    {
        //todo:: clear log table

    }

    public function logByProvider()
    {
        //todo:: log for specific provider
    }

    public function logByDefaultProvider()
    {
        //todo:: default provider log list
    }

    public function total()
    {
        //todo:: count total log
    }

    public function toArray()
    {

    }

    public function toJson()
    {

    }

}
