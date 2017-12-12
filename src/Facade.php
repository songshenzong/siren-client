<?php

namespace Songshenzong\SirenClient;

/**
 * Class Facade
 *
 * @package Songshenzong\SirenClient
 */
class Facade extends \Illuminate\Support\Facades\Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'SirenClient';
    }
}
