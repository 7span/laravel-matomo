<?php

declare(strict_types=1);

namespace SevenSpan\Matomo\Facades;

use Illuminate\Support\Facades\Facade;

class Matomo extends Facade
{
    public static function getFacadeAccessor()
    {
        return 'Matomo';
    }
}
