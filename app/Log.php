<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $fillable = [
        'request',
        'ip', 'token',
        'login_result', 'sent',
    ];

    public $timestamps = false;
    protected $table = 'logs';
    protected $connection = 'sqlite';
}
