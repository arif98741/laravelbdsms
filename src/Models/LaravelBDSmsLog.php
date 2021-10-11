<?php

namespace Xenon\LaravelBDSms\Models;

use Illuminate\Database\Eloquent\Model;

class LaravelBDSmsLog extends Model
{
    
    protected $table = 'lbs_log';

    protected $fillable = [
        'provider',
        'request_json',
        'response_json'
    ];

    protected $guarded = [];
}
