<?php

namespace Xenon\LaravelBDSms\Log;

use Illuminate\Support\Facades\DB;
use Xenon\LaravelBDSms\Models\LaravelBDSmsLog;

/**
 *
 */
class Log
{
    /**
     * Add New Log to Model
     */
    public function createLog(array $data)
    {
        LaravelBDSmsLog::create($data);
    }

    /**
     * @return mixed
     */
    public function viewLastLog()
    {
        return LaravelBDSmsLog::orderBy('id', 'desc')->first();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection|LaravelBDSmsLog[]
     */
    public function viewAllLog()
    {
        return LaravelBDSmsLog::all();
    }

    /**
     *
     */
    public function clearLog()
    {
        DB::statement("SET foreign_key_checks=0");
        Model::truncate();
        DB::statement("SET foreign_key_checks=1");
    }

    /**
     * @param $provider
     * @return mixed
     */
    public function logByProvider($provider)
    {
        return LaravelBDSmsLog::where('provider', $provider)->get();
    }

    /**
     * @return mixed
     */
    public function logByDefaultProvider()
    {
        $provider = config('sms.default_provider');
        return LaravelBDSmsLog::where('provider', config('sms.providers')[$provider])->get();
    }

    /**
     * @return mixed
     */
    public function total()
    {
        return LaravelBDSmsLog::count();
    }

    /**
     * @param $object
     * @return mixed
     */
    public function toArray($object)
    {
        return $object->toArray();
    }

    /**
     *
     */
    public function toJson()
    {

    }
}
