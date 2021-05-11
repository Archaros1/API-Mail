<?php

namespace Tests;

abstract class AbstractJobTest extends TestCase
{
}

namespace App;

class Log
{
    public $id;

    public static function where()
    {
        return new self();
    }

    public function update()
    {
        return 0;
    }
}
