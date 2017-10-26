<?php


if (!function_exists('statisticTick')) {
    /**
     * Get the instance
     *
     * @return \Songshenzong\StatisticClient\StatisticClient
     *
     * @param string $module
     * @param string $interface
     */
    function statisticTick($module = '', $interface = '')
    {
        \Songshenzong\StatisticClient\StatisticClient::tick($module, $interface);
    }
}


if (!function_exists('statisticSetAddress')) {
    /**
     * Get the instance
     *
     * @return \Songshenzong\StatisticClient\StatisticClient
     *
     * @param     $ip
     * @param int $port
     */
    function statisticSetAddress($ip, $port = 55656)
    {
        \Songshenzong\StatisticClient\StatisticClient::setAddress($ip, $port);
    }
}


if (!function_exists('statisticError')) {
    /**
     * Get the instance
     *
     * @return \Songshenzong\StatisticClient\StatisticClient
     *
     * @param       $code
     * @param array ...$args
     */
    function statisticError($code, ...$args)
    {
        \Songshenzong\StatisticClient\StatisticClient::error($code, ...$args);
    }
}


if (!function_exists('statisticException')) {
    /**
     * Get the instance
     *
     * @return \Songshenzong\StatisticClient\StatisticClient
     *
     * @param Exception $exception
     */
    function statisticException(Exception $exception)
    {
        \Songshenzong\StatisticClient\StatisticClient::exception($exception);
    }
}


if (!function_exists('statisticSuccess')) {
    /**
     * Get the instance
     *
     * @return \Songshenzong\StatisticClient\StatisticClient
     *
     * @param int    $code
     * @param string $msg
     */
    function statisticSuccess($code = 0, $msg = 'success')
    {
        \Songshenzong\StatisticClient\StatisticClient::success($code, $msg);
    }
}