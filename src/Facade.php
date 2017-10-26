<?php

namespace Songshenzong\StatisticClient;

/**
 * Class Facade
 *
 * @package Songshenzong\StatisticClient
 */
class Facade extends \Illuminate\Support\Facades\Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'StatisticClient';
    }
}
