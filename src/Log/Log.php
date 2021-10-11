<?php

namespace Xenon\LaravelBDSms\Log;

use Xenon\LaravelBDSms\Models\LaravelBDSmsLog;

class Log
{
    /**
     * Add New Log to Model
     * @since v1.0.35
     * @version v1.0.35
     */
    public function createLog(array $data)
    {
        LaravelBDSmsLog::create($data);
        //todo:: crate log data in table
    }

    /**
     * @since v1.0.35
     * @version v1.0.35
     */
    public function viewLastLog()
    {
        //todo:: view last log
    }

    /**
     * @since v1.0.35
     * @version v1.0.35
     */
    public function viewAllLog()
    {
        //todo:: view all logs
    }

    /**
     * @since v1.0.35
     * @version v1.0.35
     */
    public function clearLog()
    {
        //todo:: clear log table

    }

    /**
     * @since v1.0.35
     * @version v1.0.35
     */
    public function logByProvider()
    {
        //todo:: log for specific provider
    }

    /**
     * @since v1.0.35
     * @version v1.0.35
     */
    public function logByDefaultProvider()
    {
        //todo:: default provider log list
    }

    /**
     * @since v1.0.35
     * @version v1.0.35
     */
    public function total()
    {
        //todo:: count total log
    }

    /**
     * @since v1.0.35
     * @version v1.0.35
     */
*/
    public function toArray()
    {

    }

    /**
     * @since v1.0.35
     * @version v1.0.35
     */
    public function toJson()
    {

    }

}
