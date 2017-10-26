<?php

if (!function_exists('statisticSetAddress')) {
    /**
     * Get the instance
     *
     *
     * @param     $ip
     * @param int $port
     */
    function statisticSetAddress($ip, $port = 55656)
    {
        \Songshenzong\StatisticClient\StatisticClient::setAddress($ip, $port);
    }
}


if (!function_exists('statisticTick')) {
    /**
     * Get the instance
     *
     *
     * @param string $module
     * @param string $interface
     */
    function statisticTick($module = '', $interface = '')
    {
        \Songshenzong\StatisticClient\StatisticClient::tick($module, $interface);
    }
}


if (!function_exists('statisticError')) {
    /**
     * Get the instance
     *
     * @param       $module
     * @param       $interface
     * @param       $code
     * @param       $message
     *
     * @return bool
     */
    function statisticError($module, $interface, $code, $message)
    {
        return \Songshenzong\StatisticClient\StatisticClient::error($module, $interface, $code, $message);
    }
}


if (!function_exists('statisticException')) {
    /**
     * Get the instance
     *
     * @param           $module
     * @param           $interface
     * @param Exception $exception
     *
     * @return bool
     */
    function statisticException($module, $interface, Exception $exception)
    {
        return \Songshenzong\StatisticClient\StatisticClient::exception($module, $interface, $exception);
    }
}


if (!function_exists('statisticSuccess')) {
    /**
     * Get the instance
     *
     * @param        $module
     * @param        $interface
     * @param int    $code
     * @param string $msg
     *
     * @return bool
     */
    function statisticSuccess($module, $interface, $code = 0, $msg = 'success')
    {
        return \Songshenzong\StatisticClient\StatisticClient::success($module, $interface, $code, $msg);
    }
}