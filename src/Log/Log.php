<?php

namespace Xenon\LaravelBDSms\Log;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
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
    }

    /**
     * @return Model|\Illuminate\Database\Query\Builder|object|LaravelBDSmsLog|null
     * @version v1.0.35
     * @since v1.0.35
     */
    public function viewLastLog()
    {
        return LaravelBDSmsLog::orderBy('id', 'desc')->first();
    }

    /**
     * @return Collection
     * @since v1.0.35
     * @version v1.0.35
     */
    public function viewAllLog()
    {
        return LaravelBDSmsLog::all();
    }

    /**
     * @since v1.0.35
     * @version v1.0.35
     */
    public function clearLog()
    {
        DB::statement("SET foreign_key_checks=0");
        LaravelBDSmsLog::truncate();
        DB::statement("SET foreign_key_checks=1");
    }

    /**
     * @param $provider
     * @return Builder[]|Collection|\Illuminate\Support\Collection|LaravelBDSmsLog[]
     * @since v1.0.35
     * @version v1.0.35
     */
    public function logByProvider($provider)
    {
        return LaravelBDSmsLog::where('provider', $provider)->get();
    }

    /**
     * @return Builder[]|Collection|\Illuminate\Support\Collection|LaravelBDSmsLog[]
     * @since v1.0.35
     * @version v1.0.35
     */
    public function logByDefaultProvider()
    {
        $provider = config('sms.default_provider');
        return LaravelBDSmsLog::where('provider', config('sms.providers')[$provider])->get();
    }

    /**
     * @return int
     * @since v1.0.35
     * @version v1.0.35
     */
    public function total(): int
    {
        return LaravelBDSmsLog::count();
    }

    /**
     * @param $object
     * @return mixed
     * @since v1.0.35
     * @version v1.0.35
     */
    public function toArray($object)
    {
        return $object->toArray();
    }

    /**
     * @since v1.0.35
     * @version v1.0.35
     */
    public function toJson()
    {

    }
}
