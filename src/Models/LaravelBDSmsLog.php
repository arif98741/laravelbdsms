<?php

namespace Xenon\LaravelBDSms\Models;

use Illuminate\Database\Eloquent\Model;

class LaravelBDSmsLog extends Model
{
    /**
     * @var string
     */
    protected $table = 'lbs_log';

    /**
     * @var string[]
     */
    protected $fillable = [
        'provider',
        'request_json',
        'response_json'
    ];

    /**
     * @var array
     */
    protected $guarded = [];
}
