<?php
namespace App\Facades;

use Illuminate\Support\Facades\Facade;


class LineBot extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'line-bot';
    }
}