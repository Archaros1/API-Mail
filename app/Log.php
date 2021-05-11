<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'request',
        'response',
        'ip', 'token',
        'login_result', 'sent',
    ];
    protected $table = 'logs';
    protected $connection = 'sqlite';
}
