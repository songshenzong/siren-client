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


if (!function_exists('statisticSetToken')) {
    /**
     * Get the instance
     *
     *
     * @param     $token string
     */
    function statisticSetToken($token)
    {
        \Songshenzong\StatisticClient\StatisticClient::setToken($token);
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
     * @param       $alert
     *
     * @return bool
     */
    function statisticError($module, $interface, $code, $message, $alert = -1)
    {
        \Songshenzong\StatisticClient\StatisticClient::backtrace(debug_backtrace());
        return \Songshenzong\StatisticClient\StatisticClient::error($module, $interface, $code, $message, $alert);
    }
}


if (!function_exists('statisticException')) {
    /**
     * Get the instance
     *
     * @param           $module
     * @param           $interface
     * @param Exception $exception
     * @param           $alert
     *
     * @return bool
     */
    function statisticException($module, $interface, Exception $exception, $alert = -1)
    {
        return \Songshenzong\StatisticClient\StatisticClient::exception($module, $interface, $exception, $alert);
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