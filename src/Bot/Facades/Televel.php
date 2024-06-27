<?php

namespace Televel\Bot\Facades;

use Illuminate\Support\Facades\Facade;

class Televel extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'televel';
    }
}
