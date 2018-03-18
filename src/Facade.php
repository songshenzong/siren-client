<?php

namespace Songshenzong\Siren;

/**
 * Class Facade
 *
 * @package Songshenzong\Siren
 */
class Facade extends \Illuminate\Support\Facades\Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'Siren';
    }
}
