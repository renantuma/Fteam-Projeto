<?php
// app/Models/RequestLog.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestLog extends Model
{
    protected $fillable = [
        'client_id',
        'route',
        'method',
        'status_code',
        'response_time_ms',
        'request_data'
    ];

    protected $casts = [
        'request_data' => 'array'
    ];
}